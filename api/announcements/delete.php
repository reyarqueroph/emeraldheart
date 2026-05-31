<?php
/**
 * Delete Admin Announcement API
 * Only accessible by admin users
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || empty($data->id)) {
    echo json_encode(['success' => false, 'message' => 'Announcement ID is required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $id = (int)$data->id;
    
    // Check if announcement exists
    $checkStmt = $db->prepare("SELECT id, title FROM admin_announcements WHERE id = :id");
    $checkStmt->execute([':id' => $id]);
    $announcement = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$announcement) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        exit;
    }
    
    // Delete the announcement
    $stmt = $db->prepare("DELETE FROM admin_announcements WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Announcement deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete announcement']);
    }
    
} catch (Exception $e) {
    error_log("Delete announcement error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to delete announcement']);
}
?>