<?php
/**
 * Save Payment Settings API
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

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $adminId = $_SESSION['user_id'];
    
    $settings = [
        'registration_fee' => $data->registration_fee ?? '500.00',
        'gcash_number' => $data->gcash_number ?? '',
        'gcash_name' => $data->gcash_name ?? '',
        'payment_instructions' => $data->payment_instructions ?? '',
        'auto_approve' => $data->auto_approve ?? '0'
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("
            INSERT INTO payment_settings (setting_key, setting_value, updated_by) 
            VALUES (:key, :value, :admin_id)
            ON DUPLICATE KEY UPDATE 
            setting_value = :value, 
            updated_by = :admin_id
        ");
        
        $stmt->execute([
            ':key' => $key,
            ':value' => $value,
            ':admin_id' => $adminId
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Settings saved successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Save settings error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save settings']);
}
?>