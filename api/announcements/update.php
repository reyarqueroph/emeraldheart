<?php
/**
 * Update Admin Announcement API
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
    $checkStmt = $db->prepare("SELECT id FROM admin_announcements WHERE id = :id");
    $checkStmt->execute([':id' => $id]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        exit;
    }
    
    // Build update query dynamically based on provided fields
    $updateFields = [];
    $params = [':id' => $id];
    
    if (isset($data->title) && !empty(trim($data->title))) {
        $updateFields[] = "title = :title";
        $params[':title'] = trim($data->title);
    }
    
    if (isset($data->message) && !empty(trim($data->message))) {
        $updateFields[] = "message = :message";
        $params[':message'] = trim($data->message);
    }
    
    if (isset($data->announcement_type)) {
        $type = trim($data->announcement_type);
        $validTypes = ['general', 'urgent', 'reminder', 'event'];
        if (in_array($type, $validTypes)) {
            $updateFields[] = "announcement_type = :type";
            $params[':type'] = $type;
        }
    }
    
    if (isset($data->start_date)) {
        $updateFields[] = "start_date = :start_date";
        $params[':start_date'] = !empty($data->start_date) ? $data->start_date : null;
    }
    
    if (isset($data->end_date)) {
        $updateFields[] = "end_date = :end_date";
        $params[':end_date'] = !empty($data->end_date) ? $data->end_date : null;
    }
    
    if (isset($data->is_active)) {
        $updateFields[] = "is_active = :is_active";
        $params[':is_active'] = $data->is_active ? 1 : 0;
    }
    
    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    
    // Validate dates if both are provided
    if (isset($params[':start_date']) && isset($params[':end_date']) && 
        $params[':start_date'] && $params[':end_date'] && 
        strtotime($params[':start_date']) > strtotime($params[':end_date'])) {
        echo json_encode(['success' => false, 'message' => 'Start date cannot be after end date']);
        exit;
    }
    
    $updateFields[] = "updated_at = CURRENT_TIMESTAMP";
    
    $query = "UPDATE admin_announcements SET " . implode(', ', $updateFields) . " WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Announcement updated successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Update announcement error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update announcement']);
}
?>