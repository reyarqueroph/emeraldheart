<?php
/**
 * Get Admin Announcements API
 * Returns active announcements for agents
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $type = trim($_GET['type'] ?? '');
    
    // Build query for active announcements
    $query = "
        SELECT 
            a.id, a.title, a.message, a.announcement_type, 
            a.start_date, a.end_date, a.created_at,
            u.full_name as created_by_name
        FROM admin_announcements a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.is_active = 1
    ";
    
    $params = [];
    
    // Filter by type if specified
    if ($type !== '' && $type !== 'all') {
        $query .= " AND a.announcement_type = :type";
        $params[':type'] = $type;
    }
    
    // Filter by date range (show current and future announcements)
    $query .= " AND (a.end_date IS NULL OR a.end_date >= CURDATE())";
    
    $query .= " ORDER BY 
        CASE a.announcement_type 
            WHEN 'urgent' THEN 1 
            WHEN 'event' THEN 2 
            WHEN 'reminder' THEN 3 
            ELSE 4 
        END,
        a.created_at DESC 
        LIMIT :limit";
    
    $params[':limit'] = $limit;
    
    $stmt = $db->prepare($query);
    
    // Bind parameters
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    foreach ($params as $key => $value) {
        if ($key !== ':limit') {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates and add additional info
    foreach ($announcements as &$announcement) {
        $announcement['is_new'] = strtotime($announcement['created_at']) > strtotime('-3 days');
        $announcement['is_urgent'] = $announcement['announcement_type'] === 'urgent';
        $announcement['formatted_date'] = date('M j, Y', strtotime($announcement['created_at']));
        
        if ($announcement['start_date']) {
            $announcement['formatted_start_date'] = date('M j, Y', strtotime($announcement['start_date']));
        }
        if ($announcement['end_date']) {
            $announcement['formatted_end_date'] = date('M j, Y', strtotime($announcement['end_date']));
        }
    }
    
    echo json_encode([
        'success' => true, 
        'data' => $announcements,
        'count' => count($announcements)
    ]);
    
} catch (Exception $e) {
    error_log("Get announcements error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to get announcements']);
}
?>