<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Access denied');
}

$transactionId = $_GET['id'] ?? '';
if (empty($transactionId)) {
    http_response_code(400);
    exit('Transaction ID required');
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get transaction details
    $stmt = $db->prepare("
        SELECT pt.*, u.full_name, u.agent_code 
        FROM payment_transactions pt 
        JOIN users u ON pt.user_id = u.id 
        WHERE pt.id = :id
    ");
    $stmt->execute([':id' => $transactionId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        http_response_code(404);
        exit('Transaction not found');
    }
    
    // Check access permissions
    $isAdmin = $_SESSION['user_role'] === 'admin';
    $isOwner = $_SESSION['user_id'] == $transaction['user_id'];
    
    if (!$isAdmin && !$isOwner) {
        http_response_code(403);
        exit('Access denied');
    }
    
    // Check if payment proof exists
    if (empty($transaction['payment_proof'])) {
        http_response_code(404);
        exit('No payment receipt found');
    }
    
    $filePath = dirname(__DIR__, 2) . '/uploads/payments/' . $transaction['payment_proof'];
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        exit('Receipt file not found');
    }
    
    // Get file info
    $fileInfo = pathinfo($filePath);
    $mimeType = mime_content_type($filePath);
    
    // Set headers for image display
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    header('Content-Disposition: inline; filename="receipt_' . $transaction['transaction_id'] . '.' . $fileInfo['extension'] . '"');
    
    // Output file
    readfile($filePath);
    
} catch (Exception $e) {
    http_response_code(500);
    exit('Error loading receipt: ' . $e->getMessage());
}
?>