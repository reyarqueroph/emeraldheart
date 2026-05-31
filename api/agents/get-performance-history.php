<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Create table if it doesn't exist
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS performance_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total_ape DECIMAL(12,2) DEFAULT 0.00,
            total_prospects INT DEFAULT 0,
            total_clients INT DEFAULT 0,
            last_sale_date DATE DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        )
    ");
} catch (Exception $e) {}

try {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    // Get performance history for current user
    $stmt = $db->prepare("
        SELECT 
            id,
            total_ape,
            total_prospects,
            total_clients,
            last_sale_date,
            notes,
            created_at,
            DATE_FORMAT(created_at, '%M %d, %Y %h:%i %p') as formatted_date
        FROM performance_history
        WHERE user_id = :user_id
        ORDER BY created_at DESC
        LIMIT :limit
    ");
    
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $history
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch performance history: ' . $e->getMessage()
    ]);
}
?>
