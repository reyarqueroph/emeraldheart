<?php
/**
 * Verify/Reject Payment API
 * Admin verification of payments
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

if (!$data || empty($data->transaction_id) || empty($data->action)) {
    echo json_encode(['success' => false, 'message' => 'Transaction ID and action are required']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $transactionId = (int)$data->transaction_id;
    $action = $data->action; // 'verify' or 'reject'
    $notes = trim($data->notes ?? '');
    $adminId = $_SESSION['user_id'];
    
    // Get transaction details
    $stmt = $db->prepare("SELECT * FROM payment_transactions WHERE id = :id");
    $stmt->execute([':id' => $transactionId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaction) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        exit;
    }
    
    if ($transaction['status'] !== 'paid') {
        echo json_encode(['success' => false, 'message' => 'Transaction cannot be modified']);
        exit;
    }
    
    $db->beginTransaction();
    
    if ($action === 'verify') {
        // Update transaction status to verified
        $updateStmt = $db->prepare("
            UPDATE payment_transactions 
            SET status = 'verified', 
                admin_notes = :notes, 
                verified_by = :admin_id, 
                verified_at = NOW() 
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':notes' => $notes,
            ':admin_id' => $adminId,
            ':id' => $transactionId
        ]);
        
        // Update user status and set subscription expiry
        $userUpdateStmt = $db->prepare("
            UPDATE users 
            SET payment_status = 'verified',
                status = 'active',
                subscription_expires = DATE_ADD(NOW(), INTERVAL 1 YEAR)
            WHERE id = :user_id
        ");
        $userUpdateStmt->execute([':user_id' => $transaction['user_id']]);
        
        $message = 'Payment verified successfully';
        
    } elseif ($action === 'reject') {
        // Update transaction status to rejected
        $updateStmt = $db->prepare("
            UPDATE payment_transactions 
            SET status = 'rejected', 
                admin_notes = :notes, 
                verified_by = :admin_id, 
                verified_at = NOW() 
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':notes' => $notes,
            ':admin_id' => $adminId,
            ':id' => $transactionId
        ]);
        
        // Update user status back to unpaid
        $userUpdateStmt = $db->prepare("
            UPDATE users 
            SET payment_status = 'unpaid' 
            WHERE id = :user_id
        ");
        $userUpdateStmt->execute([':user_id' => $transaction['user_id']]);
        
        $message = 'Payment rejected';
        
    } else {
        throw new Exception('Invalid action');
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    error_log("Payment verification error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to process payment verification']);
}
?>