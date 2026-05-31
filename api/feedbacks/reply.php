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

$id    = intval($data->id ?? 0);
$reply = trim($data->reply ?? '');

if (!$id || empty($reply)) {
    echo json_encode(['success' => false, 'message' => 'Feedback ID and reply are required']);
    exit;
}

$stmt = $db->prepare("UPDATE feedbacks SET admin_reply=:reply, status='replied', replied_at=NOW() WHERE id=:id");
$stmt->execute([':reply' => $reply, ':id' => $id]);

echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);
?>
