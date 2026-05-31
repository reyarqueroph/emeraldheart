<?php
/**
 * Get All Announcements for Admin Management
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get all announcements (including inactive ones for admin)
    $query = "
        SELECT 
            a.id, a.title, a.message, a.announcement_type, 
            a.start_date, a.end_date, a.start_time, a.end_time, a.is_active, a.created_at, a.updated_at,
            u.full_name as created_by_name
        FROM admin_announcements a
        LEFT JOIN users u ON a.created_by = u.id
        ORDER BY a.created_at DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates and add additional info
    foreach ($announcements as &$announcement) {
        $announcement['formatted_date'] = date('M j, Y', strtotime($announcement['created_at']));
        
        if ($announcement['start_date']) {
            $announcement['formatted_start_date'] = date('M j, Y', strtotime($announcement['start_date']));
        }
        if ($announcement['end_date']) {
            $announcement['formatted_end_date'] = date('M j, Y', strtotime($announcement['end_date']));
        }
        
        // Convert is_active to integer for consistency
        $announcement['is_active'] = (int)$announcement['is_active'];
    }
    
    echo json_encode([
        'success' => true, 
        'data' => $announcements,
        'count' => count($announcements)
    ]);
    
} catch (Exception $e) {
    error_log("Get admin announcements error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to get announcements']);
}
?>