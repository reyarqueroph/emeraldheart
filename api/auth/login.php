<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || empty($data->username) || empty($data->password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$username = trim($data->username);
$password = $data->password; // do NOT trim passwords
$role_hint = isset($data->role) ? trim($data->role) : null; // optional role hint from tab

// Build query — optionally filter by role if tab hint provided
$sql = "SELECT * FROM users WHERE (username = :u OR email = :u OR agent_code = :u) AND status = 'active'";
if ($role_hint === 'admin' || $role_hint === 'agent') {
    $sql .= " AND role = :role";
}
$sql .= " LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->bindValue(':u', $username);
if ($role_hint === 'admin' || $role_hint === 'agent') {
    $stmt->bindValue(':role', $role_hint);
}
$stmt->execute();

if ($stmt->rowCount() === 0) {
    // Check if user exists at all (any status) to give better message
    $chk = $db->prepare("SELECT status FROM users WHERE username = :u OR email = :u OR agent_code = :u LIMIT 1");
    $chk->execute([':u' => $username]);
    $found = $chk->fetch(PDO::FETCH_ASSOC);
    if ($found && $found['status'] === 'pending') {
        echo json_encode(['success' => false, 'message' => 'Your account is pending admin approval.']);
    } elseif ($found && $found['status'] === 'inactive') {
        echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Contact admin.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Account not found. Check your credentials.']);
    }
    exit;
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
    exit;
}

$_SESSION['user_id']    = $user['id'];
$_SESSION['user_role']  = $user['role'];
$_SESSION['user_name']  = $user['full_name'];
$_SESSION['agent_code'] = $user['agent_code'];

$db->prepare("UPDATE users SET last_active = NOW() WHERE id = :id")->execute([':id' => $user['id']]);

echo json_encode([
    'success'        => true,
    'role'           => $user['role'],
    'payment_status' => $user['payment_status'] ?? 'unpaid',
    'message'        => 'Login successful'
]);
?>
