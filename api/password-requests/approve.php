<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

$id           = intval($data->id ?? 0);
$new_password = trim($data->new_password ?? 'password');

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Request ID required']);
    exit;
}

// Get the request
$req = $db->prepare("SELECT * FROM password_requests WHERE id=:id AND status='pending'");
$req->execute([':id' => $id]);
$request = $req->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'Request not found or already processed']);
    exit;
}

// Update user password
$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$upd = $db->prepare("UPDATE users SET password=:pw WHERE id=:uid");
$upd->execute([':pw' => $hashed, ':uid' => $request['user_id']]);

// Mark request as approved
$stmt = $db->prepare("UPDATE password_requests SET status='approved', processed_at=NOW() WHERE id=:id");
$stmt->execute([':id' => $id]);

echo json_encode(['success' => true, 'message' => 'Password reset approved. New password: ' . $new_password]);
?>
