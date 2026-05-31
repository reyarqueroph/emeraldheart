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

try {
    // Check if columns exist
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'total_ape'");
    $totalApeExists = $stmt->fetch() !== false;
    
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'monthly_sales'");
    $monthlySalesExists = $stmt->fetch() !== false;
    
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'total_prospects'");
    $totalProspectsExists = $stmt->fetch() !== false;
    
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'total_clients'");
    $totalClientsExists = $stmt->fetch() !== false;
    
    // Get current user's data
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all agents' performance data
    $stmt = $db->query("
        SELECT 
            id,
            full_name,
            agent_code,
            total_ape,
            total_prospects,
            total_clients,
            last_sale_date,
            status
        FROM users 
        WHERE user_role = 'agent'
        ORDER BY id
    ");
    $allAgents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check performance_history table
    $stmt = $db->query("SHOW TABLES LIKE 'performance_history'");
    $historyTableExists = $stmt->fetch() !== false;
    
    $historyRecords = [];
    if ($historyTableExists) {
        $stmt = $db->prepare("
            SELECT * FROM performance_history 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $historyRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'debug_info' => [
            'columns_exist' => [
                'total_ape' => $totalApeExists,
                'monthly_sales' => $monthlySalesExists,
                'total_prospects' => $totalProspectsExists,
                'total_clients' => $totalClientsExists
            ],
            'current_user_id' => $_SESSION['user_id'],
            'current_user_role' => $_SESSION['user_role'],
            'current_user_data' => [
                'id' => $userData['id'],
                'full_name' => $userData['full_name'],
                'total_ape' => $userData['total_ape'] ?? 'NULL',
                'total_prospects' => $userData['total_prospects'] ?? 'NULL',
                'total_clients' => $userData['total_clients'] ?? 'NULL',
                'last_sale_date' => $userData['last_sale_date'] ?? 'NULL',
                'profile_updated_at' => $userData['profile_updated_at'] ?? 'NULL'
            ],
            'all_agents' => $allAgents,
            'performance_history_table_exists' => $historyTableExists,
            'recent_history_records' => $historyRecords
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Debug failed: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
