<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Handle both JSON and form data (for file uploads)
$isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;

if ($isJson) {
    $data = json_decode(file_get_contents("php://input"));
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'No data received']);
        exit;
    }
} else {
    // Form data with file upload
    $data = (object) $_POST;
}

$agent_code = trim($data->agent_code ?? '');
$email      = trim($data->email ?? '');
$full_name  = trim($data->full_name ?? '');
$username   = trim($data->username ?? $agent_code);
$position   = trim($data->position ?? 'Agent');
$password   = $data->password ?? '';

// Payment fields (optional for now, can be added later)
$gcash_reference = trim($data->gcash_reference ?? '');
$payment_amount = floatval($data->payment_amount ?? 0);

if (empty($agent_code) || empty($email) || empty($full_name) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate password strength
if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must have uppercase, lowercase, number, and special character.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get registration fee from settings
$feeStmt = $db->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = 'registration_fee'");
$feeStmt->execute();
$registrationFee = floatval($feeStmt->fetchColumn() ?: 500.00);

$check = $db->prepare("SELECT id FROM users WHERE agent_code=:ac OR email=:em OR username=:un");
$check->execute([':ac' => $agent_code, ':em' => $email, ':un' => $username]);

if ($check->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Agent code or email already registered.']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Determine payment status
    $paymentStatus = 'unpaid';
    $hasPaymentProof = false;
    
    // Check if payment proof was uploaded
    if (!$isJson && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $hasPaymentProof = true;
        $paymentStatus = 'pending'; // Payment submitted, awaiting verification
    }
    
    // Create user account
    $stmt = $db->prepare("INSERT INTO users (agent_code, username, email, password, full_name, position, role, status, payment_status, registration_fee)
                          VALUES (:ac, :un, :em, :pw, :fn, :pos, 'agent', 'pending', :ps, :rf)");
    $stmt->execute([
        ':ac'  => $agent_code,
        ':un'  => $username,
        ':em'  => $email,
        ':pw'  => password_hash($password, PASSWORD_DEFAULT),
        ':fn'  => $full_name,
        ':pos' => $position,
        ':ps'  => $paymentStatus,
        ':rf'  => $registrationFee
    ]);
    
    $userId = $db->lastInsertId();
    
    // If payment proof was uploaded, create payment transaction
    if ($hasPaymentProof) {
        // Handle file upload
        $uploadDir = dirname(__DIR__, 2) . '/uploads/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
        }
        
        if ($_FILES['payment_proof']['size'] > 5 * 1024 * 1024) { // 5MB limit
            throw new Exception('File size too large. Maximum 5MB allowed.');
        }
        
        $fileName = 'payment_' . $userId . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $filePath)) {
            throw new Exception('Failed to upload payment proof.');
        }
        
        // Create payment transaction
        $transactionId = 'TXN_' . $userId . '_' . time();
        $amount = $payment_amount > 0 ? $payment_amount : $registrationFee;
        
        $paymentStmt = $db->prepare("
            INSERT INTO payment_transactions (user_id, transaction_id, gcash_reference, amount, payment_method, status, payment_proof, created_at)
            VALUES (:uid, :tid, :ref, :amt, 'gcash', 'pending', :proof, NOW())
        ");
        $paymentStmt->execute([
            ':uid' => $userId,
            ':tid' => $transactionId,
            ':ref' => $gcash_reference,
            ':amt' => $amount,
            ':proof' => $fileName
        ]);
    }
    
    $db->commit();
    
    $message = 'Registration successful! ';
    if ($hasPaymentProof) {
        $message .= 'Your payment has been submitted and is awaiting admin verification.';
    } else {
        $message .= 'Please complete your payment to activate your account.';
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'user_id' => $userId,
        'payment_required' => !$hasPaymentProof
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
