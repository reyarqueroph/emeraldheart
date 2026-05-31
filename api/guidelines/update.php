<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id   = intval($data['id'] ?? 0);
if (!$id) { echo json_encode(['success' => false, 'message' => 'ID required']); exit; }

try {
    $database = new Database();
    $db       = $database->getConnection();
    $stmt = $db->prepare("UPDATE guidelines SET title=:t, description=:d, sort_order=:s WHERE id=:id");
    $stmt->execute([':t' => $data['title'], ':d' => $data['description'] ?? '', ':s' => intval($data['sort_order'] ?? 0), ':id' => $id]);
    echo json_encode(['success' => true, 'message' => 'Updated successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
