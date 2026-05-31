<?php
/**
 * Create Admin Announcement API
 * Only accessible by admin users
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Debug logging
error_log("Create announcement API called");
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Session user_role: " . ($_SESSION['user_role'] ?? 'not set'));

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    error_log("Unauthorized access attempt");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = file_get_contents("php://input");
error_log("Raw input: " . $input);

$data = json_decode($input);
error_log("Decoded data: " . print_r($data, true));

if (!$data) {
    error_log("Failed to decode JSON data");
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

if (empty($data->title) || empty($data->message)) {
    error_log("Missing required fields - title: " . ($data->title ?? 'empty') . ", message: " . ($data->message ?? 'empty'));
    echo json_encode(['success' => false, 'message' => 'Title and message are required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        error_log("Database connection failed");
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Check if table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'admin_announcements'");
    if ($tableCheck->rowCount() == 0) {
        error_log("admin_announcements table does not exist");
        echo json_encode(['success' => false, 'message' => 'Database table not found. Please run the SQL setup script.']);
        exit;
    }
    
    $title = trim($data->title);
    $message = trim($data->message);
    $type = trim($data->announcement_type ?? 'general');
    $startDate = !empty($data->start_date) ? $data->start_date : null;
    $endDate = !empty($data->end_date) ? $data->end_date : null;
    $isActive = isset($data->is_active) ? ($data->is_active ? 1 : 0) : 1;
    $createdBy = $_SESSION['user_id'];
    
    error_log("Processing announcement - Title: $title, Type: $type, Created by: $createdBy");
    
    // Validate announcement type
    $validTypes = ['general', 'urgent', 'reminder', 'event'];
    if (!in_array($type, $validTypes)) {
        $type = 'general';
    }
    
    // Validate dates
    if ($startDate && $endDate && strtotime($startDate) > strtotime($endDate)) {
        echo json_encode(['success' => false, 'message' => 'Start date cannot be after end date']);
        exit;
    }
    
    $stmt = $db->prepare("
        INSERT INTO admin_announcements 
        (title, message, announcement_type, start_date, end_date, start_time, end_time, is_active, created_by) 
        VALUES (:title, :message, :type, :start_date, :end_date, :start_time, :end_time, :is_active, :created_by)
    ");
    
    $result = $stmt->execute([
        ':title' => $title,
        ':message' => $message,
        ':type' => $type,
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':start_time' => !empty($data->start_time) ? $data->start_time : null,
        ':end_time' => !empty($data->end_time) ? $data->end_time : null,
        ':is_active' => $isActive,
        ':created_by' => $createdBy
    ]);
    
    if (!$result) {
        error_log("SQL execution failed: " . print_r($stmt->errorInfo(), true));
        echo json_encode(['success' => false, 'message' => 'Failed to execute SQL query']);
        exit;
    }
    
    $announcementId = $db->lastInsertId();
    error_log("Announcement created successfully with ID: $announcementId");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Announcement created successfully',
        'announcement_id' => $announcementId
    ]);
    
} catch (Exception $e) {
    error_log("Create announcement error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>