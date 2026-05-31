<?php
session_start();
require_once 'api/config/database.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    die('Please login first');
}

$database = new Database();
$db = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Sync Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #D50032; }
        h2 { color: #333; border-bottom: 2px solid #D50032; padding-bottom: 10px; }
        button { background: #D50032; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #a8002a; }
        pre { background: #f8f8f8; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #D50032; color: white; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status.ok { background: #d4edda; color: #155724; }
        .status.fail { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Performance Sync Diagnostic Tool</h1>
        <p>Current User: <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown'); ?></strong> (Role: <?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Unknown'); ?>)</p>

        <!-- Step 1: Check Database Structure -->
        <div class="card">
            <h2>Step 1: Database Structure Check</h2>
            <?php
            try {
                $columns = [];
                $requiredColumns = ['total_ape', 'total_prospects', 'total_clients', 'last_sale_date', 'profile_updated_at'];
                
                foreach ($requiredColumns as $col) {
                    $stmt = $db->query("SHOW COLUMNS FROM users LIKE '$col'");
                    $columns[$col] = $stmt->fetch() !== false;
                }
                
                $stmt = $db->query("SHOW TABLES LIKE 'performance_history'");
                $historyTableExists = $stmt->fetch() !== false;
                
                echo '<table>';
                echo '<tr><th>Item</th><th>Status</th></tr>';
                foreach ($columns as $col => $exists) {
                    $status = $exists ? '<span class="status ok">✓ EXISTS</span>' : '<span class="status fail">✗ MISSING</span>';
                    echo "<tr><td>Column: users.$col</td><td>$status</td></tr>";
                }
                $status = $historyTableExists ? '<span class="status ok">✓ EXISTS</span>' : '<span class="status fail">✗ MISSING</span>';
                echo "<tr><td>Table: performance_history</td><td>$status</td></tr>";
                echo '</table>';
                
            } catch (Exception $e) {
                echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <!-- Step 2: Current User Data -->
        <div class="card">
            <h2>Step 2: Current User Performance Data</h2>
            <?php
            try {
                $stmt = $db->prepare("SELECT id, full_name, agent_code, total_ape, total_prospects, total_clients, last_sale_date, profile_updated_at FROM users WHERE id = :id");
                $stmt->execute([':id' => $_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Value</th></tr>';
                    foreach ($user as $key => $value) {
                        echo '<tr><td>' . htmlspecialchars($key) . '</td><td>' . htmlspecialchars($value ?? 'NULL') . '</td></tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="error">User not found</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <!-- Step 3: All Agents Data -->
        <div class="card">
            <h2>Step 3: All Agents Performance Data</h2>
            <?php
            try {
                $stmt = $db->query("
                    SELECT id, full_name, agent_code, total_ape, total_prospects, total_clients, last_sale_date, status
                    FROM users 
                    WHERE user_role = 'agent'
                    ORDER BY id
                ");
                $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($agents) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Name</th><th>Code</th><th>Total APE</th><th>Prospects</th><th>Clients</th><th>Last Sale</th><th>Status</th></tr>';
                    foreach ($agents as $agent) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($agent['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($agent['full_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($agent['agent_code'] ?? '-') . '</td>';
                        echo '<td>₱' . number_format($agent['total_ape'] ?? 0, 2) . '</td>';
                        echo '<td>' . htmlspecialchars($agent['total_prospects'] ?? 0) . '</td>';
                        echo '<td>' . htmlspecialchars($agent['total_clients'] ?? 0) . '</td>';
                        echo '<td>' . htmlspecialchars($agent['last_sale_date'] ?? '-') . '</td>';
                        echo '<td>' . htmlspecialchars($agent['status']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p>No agents found</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <!-- Step 4: Performance History -->
        <div class="card">
            <h2>Step 4: Recent Performance History</h2>
            <?php
            try {
                $stmt = $db->query("SHOW TABLES LIKE 'performance_history'");
                if ($stmt->fetch()) {
                    $stmt = $db->query("
                        SELECT ph.*, u.full_name, u.agent_code
                        FROM performance_history ph
                        JOIN users u ON ph.user_id = u.id
                        ORDER BY ph.created_at DESC
                        LIMIT 10
                    ");
                    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($history) {
                        echo '<table>';
                        echo '<tr><th>ID</th><th>Agent</th><th>Total APE</th><th>Prospects</th><th>Clients</th><th>Notes</th><th>Created</th></tr>';
                        foreach ($history as $record) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($record['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($record['full_name']) . ' (' . htmlspecialchars($record['agent_code'] ?? '-') . ')</td>';
                            echo '<td>₱' . number_format($record['total_ape'], 2) . '</td>';
                            echo '<td>' . htmlspecialchars($record['total_prospects']) . '</td>';
                            echo '<td>' . htmlspecialchars($record['total_clients']) . '</td>';
                            echo '<td>' . htmlspecialchars($record['notes'] ?? '-') . '</td>';
                            echo '<td>' . htmlspecialchars($record['created_at']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    } else {
                        echo '<p>No history records found</p>';
                    }
                } else {
                    echo '<p class="error">Performance history table does not exist</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>

        <!-- Step 5: API Tests -->
        <div class="card">
            <h2>Step 5: API Endpoint Tests</h2>
            <button onclick="testSaveAPI()">Test Save Performance API</button>
            <button onclick="testGetAPI()">Test Get Performance API</button>
            <button onclick="testDebugAPI()">Test Debug API</button>
            <div id="apiResults" style="margin-top: 20px;"></div>
        </div>

        <div class="card">
            <h2>Quick Actions</h2>
            <button onclick="location.href='agent/account.php'">Go to Agent Account</button>
            <button onclick="location.href='admin/dashboard.php'">Go to Admin Dashboard</button>
            <button onclick="location.reload()">Refresh This Page</button>
        </div>
    </div>

    <script>
        async function testSaveAPI() {
            const results = document.getElementById('apiResults');
            results.innerHTML = '<p>Testing save API...</p>';
            
            try {
                const response = await fetch('api/agents/save-performance-history.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        total_ape: 99999.99,
                        total_prospects: 99,
                        total_clients: 99,
                        last_sale_date: '2026-05-08',
                        notes: 'Test from diagnostic tool'
                    })
                });
                
                const result = await response.json();
                results.innerHTML = '<h3>Save API Response:</h3><pre>' + JSON.stringify(result, null, 2) + '</pre>';
                
                if (result.success) {
                    results.innerHTML += '<p class="success">✓ Save successful! Refresh page to see updated data.</p>';
                } else {
                    results.innerHTML += '<p class="error">✗ Save failed: ' + result.message + '</p>';
                }
            } catch (error) {
                results.innerHTML = '<p class="error">✗ Error: ' + error.message + '</p>';
            }
        }

        async function testGetAPI() {
            const results = document.getElementById('apiResults');
            results.innerHTML = '<p>Testing get API...</p>';
            
            try {
                const response = await fetch('api/agents/get-performance.php?t=' + Date.now());
                const result = await response.json();
                results.innerHTML = '<h3>Get API Response:</h3><pre>' + JSON.stringify(result, null, 2) + '</pre>';
                
                if (result.success) {
                    results.innerHTML += '<p class="success">✓ Get successful!</p>';
                } else {
                    results.innerHTML += '<p class="error">✗ Get failed: ' + result.message + '</p>';
                }
            } catch (error) {
                results.innerHTML = '<p class="error">✗ Error: ' + error.message + '</p>';
            }
        }

        async function testDebugAPI() {
            const results = document.getElementById('apiResults');
            results.innerHTML = '<p>Testing debug API...</p>';
            
            try {
                const response = await fetch('api/agents/debug-performance.php');
                const result = await response.json();
                results.innerHTML = '<h3>Debug API Response:</h3><pre>' + JSON.stringify(result, null, 2) + '</pre>';
                
                if (result.success) {
                    results.innerHTML += '<p class="success">✓ Debug successful!</p>';
                } else {
                    results.innerHTML += '<p class="error">✗ Debug failed: ' + result.message + '</p>';
                }
            } catch (error) {
                results.innerHTML = '<p class="error">✗ Error: ' + error.message + '</p>';
            }
        }
    </script>
</body>
</html>
