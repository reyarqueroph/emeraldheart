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

// Add columns if they don't exist and rename monthly_sales to total_ape
try {
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS birthday DATE DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone_number VARCHAR(20) DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact_name VARCHAR(100) DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS emergency_contact_phone VARCHAR(20) DEFAULT NULL");
    
    // Try to rename column from monthly_sales to total_ape
    try {
        $db->exec("ALTER TABLE users CHANGE COLUMN monthly_sales total_ape DECIMAL(12,2) DEFAULT 0.00");
    } catch (Exception $e) {
        // Column might already be renamed or doesn't exist
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_ape DECIMAL(12,2) DEFAULT 0.00");
    }
    
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_prospects INT DEFAULT 0");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_clients INT DEFAULT 0");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_sale_date DATE DEFAULT NULL");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_completed BOOLEAN DEFAULT FALSE");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
} catch (Exception $e) {}

$data = json_decode(file_get_contents("php://input"));

// Validate and sanitize inputs
$birthday = !empty($data->birthday) ? trim($data->birthday) : null;
$phone_number = !empty($data->phone_number) ? trim($data->phone_number) : null;
$address = !empty($data->address) ? trim($data->address) : null;
$emergency_contact_name = !empty($data->emergency_contact_name) ? trim($data->emergency_contact_name) : null;
$emergency_contact_phone = !empty($data->emergency_contact_phone) ? trim($data->emergency_contact_phone) : null;

// Validate birthday format if provided
if ($birthday && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
    echo json_encode(['success' => false, 'message' => 'Invalid birthday format. Use YYYY-MM-DD']);
    exit;
}

try {
    $stmt = $db->prepare("
        UPDATE users 
        SET birthday = :birthday,
            phone_number = :phone_number,
            address = :address,
            emergency_contact_name = :emergency_contact_name,
            emergency_contact_phone = :emergency_contact_phone,
            profile_completed = TRUE,
            profile_updated_at = CURRENT_TIMESTAMP
        WHERE id = :user_id
    ");
    
    $stmt->execute([
        ':birthday' => $birthday,
        ':phone_number' => $phone_number,
        ':address' => $address,
        ':emergency_contact_name' => $emergency_contact_name,
        ':emergency_contact_phone' => $emergency_contact_phone,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update profile: ' . $e->getMessage()
    ]);
}
?>
