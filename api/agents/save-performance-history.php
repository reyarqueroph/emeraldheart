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

// Create performance_history table if it doesn't exist
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
    
    // Rename column in users table from monthly_sales to total_ape
    try {
        $db->exec("ALTER TABLE users CHANGE COLUMN monthly_sales total_ape DECIMAL(12,2) DEFAULT 0.00");
    } catch (Exception $e) {
        // Column might already be renamed or doesn't exist
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_ape DECIMAL(12,2) DEFAULT 0.00");
    }
} catch (Exception $e) {}

$data = json_decode(file_get_contents("php://input"));

// Validate and sanitize inputs
$total_ape = isset($data->total_ape) ? floatval($data->total_ape) : 0.00;
$total_prospects = isset($data->total_prospects) ? intval($data->total_prospects) : 0;
$total_clients = isset($data->total_clients) ? intval($data->total_clients) : 0;
$last_sale_date = !empty($data->last_sale_date) ? trim($data->last_sale_date) : null;
$notes = !empty($data->notes) ? trim($data->notes) : null;

// Validate last_sale_date format if provided
if ($last_sale_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $last_sale_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid last sale date format. Use YYYY-MM-DD']);
    exit;
}

try {
    // Insert into performance_history table (keeps all records)
    $stmt = $db->prepare("
        INSERT INTO performance_history 
        (user_id, total_ape, total_prospects, total_clients, last_sale_date, notes)
        VALUES (:user_id, :total_ape, :total_prospects, :total_clients, :last_sale_date, :notes)
    ");
    
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':total_ape' => $total_ape,
        ':total_prospects' => $total_prospects,
        ':total_clients' => $total_clients,
        ':last_sale_date' => $last_sale_date,
        ':notes' => $notes
    ]);
    
    // Update users table with latest values (for quick access)
    $stmt = $db->prepare("
        UPDATE users 
        SET total_ape = :total_ape,
            total_prospects = :total_prospects,
            total_clients = :total_clients,
            last_sale_date = :last_sale_date,
            profile_updated_at = CURRENT_TIMESTAMP
        WHERE id = :user_id
    ");
    
    $stmt->execute([
        ':total_ape' => $total_ape,
        ':total_prospects' => $total_prospects,
        ':total_clients' => $total_clients,
        ':last_sale_date' => $last_sale_date,
        ':user_id' => $_SESSION['user_id']
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Performance updated successfully. History saved.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update performance: ' . $e->getMessage()
    ]);
}
?>
