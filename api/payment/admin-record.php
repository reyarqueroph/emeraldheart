<?php
/**
 * Admin Record Payment API
 * Allows admin to record a payment on behalf of an agent when activating their account.
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$agentId        = intval($_POST['agent_id'] ?? 0);
$gcashReference = trim($_POST['gcash_reference'] ?? '');
$amount         = floatval($_POST['amount'] ?? 0);
$notes          = trim($_POST['notes'] ?? '');
$adminId        = $_SESSION['user_id'];

if (!$agentId) {
    echo json_encode(['success' => false, 'message' => 'Agent ID is required']);
    exit;
}

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'A valid payment amount is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verify agent exists
    $agentStmt = $db->prepare("SELECT id, payment_status FROM users WHERE id = :id AND role = 'agent'");
    $agentStmt->execute([':id' => $agentId]);
    $agent = $agentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$agent) {
        echo json_encode(['success' => false, 'message' => 'Agent not found']);
        exit;
    }

    // Handle optional receipt upload
    $fileName = null;
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed.']);
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum 5MB allowed.']);
            exit;
        }

        $uploadDir = dirname(__DIR__, 2) . '/uploads/payments/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = 'payment_' . $agentId . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload receipt']);
            exit;
        }
    }

    $db->beginTransaction();

    // Remove any old unpaid/pending placeholder transactions for this agent
    $db->prepare("DELETE FROM payment_transactions WHERE user_id = :uid AND status IN ('pending','unpaid')")
       ->execute([':uid' => $agentId]);

    // Create verified payment transaction (admin-recorded = immediately verified)
    $transactionId = 'TXN_ADM_' . $agentId . '_' . time();
    $db->prepare("
        INSERT INTO payment_transactions
            (user_id, transaction_id, gcash_reference, amount, payment_method, status, payment_proof, admin_notes, verified_by, verified_at, created_at)
        VALUES
            (:uid, :tid, :ref, :amt, 'gcash', 'verified', :proof, :notes, :admin, NOW(), NOW())
    ")->execute([
        ':uid'   => $agentId,
        ':tid'   => $transactionId,
        ':ref'   => $gcashReference,
        ':amt'   => $amount,
        ':proof' => $fileName,
        ':notes' => $notes ?: 'Recorded by admin during account activation',
        ':admin' => $adminId,
    ]);

    // Activate agent and mark payment as verified
    $db->prepare("
        UPDATE users
        SET status = 'active',
            payment_status = 'verified',
            subscription_expires = DATE_ADD(NOW(), INTERVAL 1 YEAR)
        WHERE id = :id
    ")->execute([':id' => $agentId]);

    $db->commit();

    echo json_encode([
        'success'        => true,
        'message'        => 'Payment recorded and agent activated successfully.',
        'transaction_id' => $transactionId,
    ]);

} catch (Exception $e) {
    $db->rollBack();
    error_log("Admin record payment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to record payment: ' . $e->getMessage()]);
}
?>
