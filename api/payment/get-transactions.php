<?php
/**
 * Get Payment Transactions API
 * For admin payment management
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
    
    $query = "
        SELECT 
            pt.*,
            u.full_name,
            u.agent_code,
            u.email,
            v.full_name as verified_by_name
        FROM payment_transactions pt
        LEFT JOIN users u ON pt.user_id = u.id
        LEFT JOIN users v ON pt.verified_by = v.id
        ORDER BY pt.created_at DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $transactions
    ]);
    
} catch (Exception $e) {
    error_log("Get transactions error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to get transactions']);
}
?>