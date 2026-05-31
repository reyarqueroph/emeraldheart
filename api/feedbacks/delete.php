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

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Feedback ID required']);
    exit;
}

$stmt = $db->prepare("DELETE FROM feedbacks WHERE id=:id");
$stmt->execute([':id' => $id]);

echo json_encode(['success' => true, 'message' => 'Feedback deleted successfully']);
?>
