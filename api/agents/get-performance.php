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
    // Try to rename column
    try {
        $db->exec("ALTER TABLE users CHANGE COLUMN monthly_sales total_ape DECIMAL(12,2) DEFAULT 0.00");
    } catch (Exception $e) {
        // Column might already be renamed or doesn't exist
        $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_ape DECIMAL(12,2) DEFAULT 0.00");
    }
    
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_prospects INT DEFAULT 0");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_clients INT DEFAULT 0");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_sale_date DATE DEFAULT NULL");
} catch (Exception $e) {}

try {
    // Check if admin or agent
    $isAdmin = ($_SESSION['user_role'] === 'admin');
    
    if ($isAdmin) {
        // Admin: Get all agents' performance data
        $stmt = $db->query("
            SELECT 
                id,
                full_name,
                agent_code,
                position,
                total_ape,
                total_prospects,
                total_clients,
                last_sale_date,
                status
            FROM users 
            WHERE user_role = 'agent'
            ORDER BY total_ape DESC
        ");
        
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate totals and averages
        $totalAPE = 0;
        $totalProspects = 0;
        $totalClients = 0;
        $activeAgents = 0;
        
        foreach ($agents as $agent) {
            if ($agent['status'] === 'active') {
                $totalAPE += floatval($agent['total_ape']);
                $totalProspects += intval($agent['total_prospects']);
                $totalClients += intval($agent['total_clients']);
                $activeAgents++;
            }
        }
        
        $avgAPE = $activeAgents > 0 ? $totalAPE / $activeAgents : 0;
        $avgProspects = $activeAgents > 0 ? $totalProspects / $activeAgents : 0;
        $avgClients = $activeAgents > 0 ? $totalClients / $activeAgents : 0;
        
        // Get top performers (top 10)
        $topPerformers = array_slice(
            array_filter($agents, function($a) { return $a['status'] === 'active'; }),
            0,
            10
        );
        
        echo json_encode([
            'success' => true,
            'data' => [
                'agents' => $agents,
                'top_performers' => $topPerformers,
                'summary' => [
                    'total_ape' => $totalAPE,
                    'total_prospects' => $totalProspects,
                    'total_clients' => $totalClients,
                    'active_agents' => $activeAgents,
                    'avg_ape' => $avgAPE,
                    'avg_prospects' => $avgProspects,
                    'avg_clients' => $avgClients
                ]
            ]
        ]);
        
    } else {
        // Agent: Get only their own performance data
        $stmt = $db->prepare("
            SELECT 
                id,
                full_name,
                agent_code,
                position,
                total_ape,
                total_prospects,
                total_clients,
                last_sale_date,
                profile_updated_at
            FROM users 
            WHERE id = :user_id
        ");
        
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$agent) {
            echo json_encode(['success' => false, 'message' => 'Agent not found']);
            exit;
        }
        
        // Calculate conversion rate
        $prospects = intval($agent['total_prospects']);
        $clients = intval($agent['total_clients']);
        $conversionRate = $prospects > 0 ? ($clients / $prospects) * 100 : 0;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'agent' => $agent,
                'metrics' => [
                    'total_ape' => floatval($agent['total_ape']),
                    'total_prospects' => $prospects,
                    'total_clients' => $clients,
                    'conversion_rate' => round($conversionRate, 2)
                ]
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch performance data: ' . $e->getMessage()
    ]);
}
?>
