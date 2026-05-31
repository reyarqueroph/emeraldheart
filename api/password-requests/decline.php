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

$id = intval($data->id ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Request ID required']);
    exit;
}

$stmt = $db->prepare("UPDATE password_requests SET status='declined', processed_at=NOW() WHERE id=:id");
$stmt->execute([':id' => $id]);

echo json_encode(['success' => true, 'message' => 'Password request declined']);
?>
