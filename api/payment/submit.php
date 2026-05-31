<?php
/**
 * Submit Payment API
 * Handle GCash payment submissions from agents
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $userId = $_SESSION['user_id'];
    $amount = floatval($_POST['amount'] ?? 0);
    $gcashReference = trim($_POST['gcash_reference'] ?? '');
    
    // Validate required fields
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        exit;
    }
    
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Payment receipt is required']);
        exit;
    }
    
    // Validate file
    $file = $_FILES['payment_proof'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed.']);
        exit;
    }
    
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum 5MB allowed.']);
        exit;
    }
    
    // Create uploads directory if it doesn't exist
    $uploadDir = '../../uploads/payments/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'receipt_' . $userId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit;
    }
    
    // Generate transaction ID
    $transactionId = 'TXN' . date('Ymd') . str_pad($userId, 4, '0', STR_PAD_LEFT) . rand(1000, 9999);
    
    // Insert payment transaction
    $stmt = $db->prepare("
        INSERT INTO payment_transactions 
        (user_id, transaction_id, gcash_reference, amount, payment_method, status, payment_proof) 
        VALUES (:user_id, :transaction_id, :gcash_reference, :amount, 'gcash', 'paid', :payment_proof)
    ");
    
    $stmt->execute([
        ':user_id' => $userId,
        ':transaction_id' => $transactionId,
        ':gcash_reference' => $gcashReference,
        ':amount' => $amount,
        ':payment_proof' => $filename
    ]);
    
    // Update user payment status
    $updateStmt = $db->prepare("UPDATE users SET payment_status = 'paid' WHERE id = :id");
    $updateStmt->execute([':id' => $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment submitted successfully! Please wait for admin verification.',
        'transaction_id' => $transactionId
    ]);
    
} catch (Exception $e) {
    error_log("Payment submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit payment']);
}
?>