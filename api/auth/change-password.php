<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

$current_password = $data->current_password ?? '';
$new_password     = $data->new_password ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Both fields are required']);
    exit;
}

$stmt = $db->prepare("SELECT password FROM users WHERE id=:id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($current_password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    exit;
}

$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$upd = $db->prepare("UPDATE users SET password=:pw WHERE id=:id");
$upd->execute([':pw' => $hashed, ':id' => $_SESSION['user_id']]);

echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
?>
