<?php
session_start();
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data       = json_decode(file_get_contents("php://input"));
$identifier = trim($data->identifier ?? $data->agent_code ?? '');

if (empty($identifier)) {
    echo json_encode(['success' => false, 'message' => 'Agent code or email is required']);
    exit;
}

$database = new Database();
$db       = $database->getConnection();

// Find by agent_code OR email
$userStmt = $db->prepare("SELECT * FROM users WHERE (agent_code = :id OR email = :id2) AND role = 'agent' LIMIT 1");
$userStmt->execute([':id' => $identifier, ':id2' => $identifier]);

if ($userStmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'message' => 'No agent found with that code or email']);
    exit;
}

$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Check for existing pending request
$checkStmt = $db->prepare("SELECT id FROM password_requests WHERE user_id = :uid AND status = 'pending'");
$checkStmt->execute([':uid' => $user['id']]);

if ($checkStmt->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'You already have a pending password reset request']);
    exit;
}

// Insert request
$stmt = $db->prepare("INSERT INTO password_requests (user_id, agent_code, email, full_name, status)
                      VALUES (:user_id, :agent_code, :email, :full_name, 'pending')");
$stmt->execute([
    ':user_id'    => $user['id'],
    ':agent_code' => $user['agent_code'],
    ':email'      => $user['email'],
    ':full_name'  => $user['full_name'],
]);

echo json_encode(['success' => true, 'message' => 'Password reset request submitted. The admin will process it shortly.']);
?>
