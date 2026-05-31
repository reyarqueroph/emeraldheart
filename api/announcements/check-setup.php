<?php
/**
 * Check Database Setup for Announcements
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
    
    // Check if admin_announcements table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'admin_announcements'");
    $tableExists = $tableCheck->rowCount() > 0;
    
    if (!$tableExists) {
        // Try to create the table
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS admin_announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            announcement_type ENUM('general', 'urgent', 'reminder', 'event') DEFAULT 'general',
            start_date DATE NULL,
            end_date DATE NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_active (is_active),
            INDEX idx_dates (start_date, end_date),
            INDEX idx_type (announcement_type),
            INDEX idx_created_at (created_at)
        )";
        
        $db->exec($createTableSQL);
        
        // Insert sample data
        $sampleSQL = "
        INSERT INTO admin_announcements (title, message, announcement_type, created_by) VALUES
        ('Welcome to eHeart', 'Welcome to the new announcement system! Administrators can now create and manage announcements for all agents.', 'general', 1),
        ('System Update', 'The system has been updated with new features. Please explore the enhanced interface.', 'event', 1)
        ";
        
        try {
            $db->exec($sampleSQL);
        } catch (Exception $e) {
            // Sample data insertion failed, but table creation succeeded
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Database table created successfully',
            'table_created' => true
        ]);
    } else {
        // Check table structure
        $columns = $db->query("DESCRIBE admin_announcements")->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Database setup is correct',
            'table_exists' => true,
            'columns' => count($columns)
        ]);
    }
    
} catch (Exception $e) {
    error_log("Database setup check error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'error_details' => $e->getTraceAsString()
    ]);
}
?>