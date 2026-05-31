<?php
session_start();
require_once 'api/config/database.php';

// Must be logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Admin login required.');
}

$database = new Database();
$db = $database->getConnection();

$results = [];
$errors = [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Performance Database</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #D50032; }
        h2 { color: #333; border-bottom: 2px solid #D50032; padding-bottom: 10px; margin-top: 0; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .warning { background: #fff3cd; color: #856404; padding: 12px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #ffc107; }
        button { background: #D50032; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #a8002a; }
        pre { background: #f8f8f8; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        .step { margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        .step-number { display: inline-block; width: 30px; height: 30px; background: #D50032; color: white; border-radius: 50%; text-align: center; line-height: 30px; font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Performance Database</h1>
        <p>This tool will add the necessary database columns for the performance tracking system.</p>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_fix'])) {
            echo '<div class="card"><h2>Running Database Fixes...</h2>';
            
            // Step 1: Try to rename monthly_sales to total_ape
            echo '<div class="step"><span class="step-number">1</span><strong>Checking for monthly_sales column...</strong><br>';
            try {
                $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'monthly_sales'");
                if ($stmt->fetch()) {
                    // Column exists, rename it
                    $db->exec("ALTER TABLE users CHANGE COLUMN monthly_sales total_ape DECIMAL(12,2) DEFAULT 0.00");
                    echo '<div class="success">✓ Renamed monthly_sales to total_ape</div>';
                    $results[] = 'Renamed monthly_sales to total_ape';
                } else {
                    echo '<div class="info">ℹ Column monthly_sales does not exist (this is OK)</div>';
                }
            } catch (Exception $e) {
                echo '<div class="warning">⚠ Could not rename monthly_sales: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            echo '</div>';
            
            // Step 2: Add total_ape if it doesn't exist
            echo '<div class="step"><span class="step-number">2</span><strong>Adding total_ape column...</strong><br>';
            try {
                $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_ape DECIMAL(12,2) DEFAULT 0.00");
                echo '<div class="success">✓ Column total_ape is ready</div>';
                $results[] = 'Added/verified total_ape column';
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'total_ape: ' . $e->getMessage();
            }
            echo '</div>';
            
            // Step 3: Add total_prospects
            echo '<div class="step"><span class="step-number">3</span><strong>Adding total_prospects column...</strong><br>';
            try {
                $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_prospects INT DEFAULT 0");
                echo '<div class="success">✓ Column total_prospects is ready</div>';
                $results[] = 'Added/verified total_prospects column';
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'total_prospects: ' . $e->getMessage();
            }
            echo '</div>';
            
            // Step 4: Add total_clients
            echo '<div class="step"><span class="step-number">4</span><strong>Adding total_clients column...</strong><br>';
            try {
                $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS total_clients INT DEFAULT 0");
                echo '<div class="success">✓ Column total_clients is ready</div>';
                $results[] = 'Added/verified total_clients column';
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'total_clients: ' . $e->getMessage();
            }
            echo '</div>';
            
            // Step 5: Add last_sale_date
            echo '<div class="step"><span class="step-number">5</span><strong>Adding last_sale_date column...</strong><br>';
            try {
                $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_sale_date DATE DEFAULT NULL");
                echo '<div class="success">✓ Column last_sale_date is ready</div>';
                $results[] = 'Added/verified last_sale_date column';
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'last_sale_date: ' . $e->getMessage();
            }
            echo '</div>';
            
            // Step 6: Add profile_updated_at
            echo '<div class="step"><span class="step-number">6</span><strong>Adding profile_updated_at column...</strong><br>';
            try {
                $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                echo '<div class="success">✓ Column profile_updated_at is ready</div>';
                $results[] = 'Added/verified profile_updated_at column';
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'profile_updated_at: ' . $e->getMessage();
            }
            echo '</div>';
            
            // Step 7: Create performance_history table
            echo '<div class="step"><span class="step-number">7</span><strong>Creating performance_history table...</strong><br>';
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
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                echo '<div class="success">✓ Table performance_history is ready</div>';
                $results[] = 'Created/verified performance_history table';
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                $errors[] = 'performance_history: ' . $e->getMessage();
            }
            echo '</div>';
            
            // Step 8: Verify columns
            echo '<div class="step"><span class="step-number">8</span><strong>Verifying all columns...</strong><br>';
            try {
                $stmt = $db->query("
                    SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'users' 
                      AND COLUMN_NAME IN ('total_ape', 'total_prospects', 'total_clients', 'last_sale_date', 'profile_updated_at')
                    ORDER BY COLUMN_NAME
                ");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($columns) >= 5) {
                    echo '<div class="success">✓ All required columns exist!</div>';
                    echo '<pre>' . print_r($columns, true) . '</pre>';
                } else {
                    echo '<div class="warning">⚠ Only ' . count($columns) . ' of 5 columns found</div>';
                    echo '<pre>' . print_r($columns, true) . '</pre>';
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ Error verifying: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            echo '</div>';
            
            // Step 9: Show current agent data
            echo '<div class="step"><span class="step-number">9</span><strong>Current agent data:</strong><br>';
            try {
                $stmt = $db->query("
                    SELECT id, full_name, agent_code, total_ape, total_prospects, total_clients, last_sale_date, status
                    FROM users 
                    WHERE user_role = 'agent'
                    ORDER BY id
                    LIMIT 10
                ");
                $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($agents) {
                    echo '<div class="info">Found ' . count($agents) . ' agent(s)</div>';
                    echo '<pre>' . print_r($agents, true) . '</pre>';
                } else {
                    echo '<div class="warning">No agents found in database</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            echo '</div>';
            
            // Summary
            echo '<div class="card" style="background: #d4edda; border-left: 4px solid #28a745;">';
            echo '<h2 style="color: #155724; border: none;">✓ Database Fix Complete!</h2>';
            echo '<p><strong>Successful operations:</strong> ' . count($results) . '</p>';
            if ($errors) {
                echo '<p style="color: #721c24;"><strong>Errors:</strong> ' . count($errors) . '</p>';
                echo '<ul>';
                foreach ($errors as $error) {
                    echo '<li>' . htmlspecialchars($error) . '</li>';
                }
                echo '</ul>';
            }
            echo '<h3>Next Steps:</h3>';
            echo '<ol>';
            echo '<li>Go to <a href="agent/account.php">Agent Account Page</a> and update performance data</li>';
            echo '<li>Go to <a href="admin/dashboard.php">Admin Dashboard</a> and click "Refresh Data"</li>';
            echo '<li>Verify the data appears correctly</li>';
            echo '</ol>';
            echo '</div>';
            
            echo '</div>';
        } else {
            // Show form
            ?>
            <div class="card">
                <h2>What This Will Do</h2>
                <p>This script will:</p>
                <ol>
                    <li>Check if <code>monthly_sales</code> column exists and rename it to <code>total_ape</code></li>
                    <li>Add <code>total_ape</code> column if it doesn't exist</li>
                    <li>Add <code>total_prospects</code> column</li>
                    <li>Add <code>total_clients</code> column</li>
                    <li>Add <code>last_sale_date</code> column</li>
                    <li>Add <code>profile_updated_at</code> column</li>
                    <li>Create <code>performance_history</code> table</li>
                    <li>Verify all columns were created successfully</li>
                </ol>
                
                <div class="warning">
                    <strong>⚠ Important:</strong> This will modify your database structure. Make sure you have a backup before proceeding.
                </div>
                
                <form method="POST">
                    <button type="submit" name="run_fix" value="1">Run Database Fix</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Current Database Status</h2>
                <?php
                // Check current status
                try {
                    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'total_ape'");
                    $totalApeExists = $stmt->fetch() !== false;
                    
                    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'monthly_sales'");
                    $monthlySalesExists = $stmt->fetch() !== false;
                    
                    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'total_prospects'");
                    $totalProspectsExists = $stmt->fetch() !== false;
                    
                    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'total_clients'");
                    $totalClientsExists = $stmt->fetch() !== false;
                    
                    $stmt = $db->query("SHOW TABLES LIKE 'performance_history'");
                    $historyTableExists = $stmt->fetch() !== false;
                    
                    echo '<ul>';
                    echo '<li>Column <code>total_ape</code>: ' . ($totalApeExists ? '<span style="color:green;">✓ EXISTS</span>' : '<span style="color:red;">✗ MISSING</span>') . '</li>';
                    echo '<li>Column <code>monthly_sales</code>: ' . ($monthlySalesExists ? '<span style="color:orange;">⚠ EXISTS (needs rename)</span>' : '<span style="color:green;">✓ Not present</span>') . '</li>';
                    echo '<li>Column <code>total_prospects</code>: ' . ($totalProspectsExists ? '<span style="color:green;">✓ EXISTS</span>' : '<span style="color:red;">✗ MISSING</span>') . '</li>';
                    echo '<li>Column <code>total_clients</code>: ' . ($totalClientsExists ? '<span style="color:green;">✓ EXISTS</span>' : '<span style="color:red;">✗ MISSING</span>') . '</li>';
                    echo '<li>Table <code>performance_history</code>: ' . ($historyTableExists ? '<span style="color:green;">✓ EXISTS</span>' : '<span style="color:red;">✗ MISSING</span>') . '</li>';
                    echo '</ul>';
                    
                    if (!$totalApeExists || !$totalProspectsExists || !$totalClientsExists || !$historyTableExists) {
                        echo '<div class="warning"><strong>Action Required:</strong> Click "Run Database Fix" button above to add missing columns.</div>';
                    } else {
                        echo '<div class="success"><strong>✓ All columns exist!</strong> Your database is ready. If data still doesn\'t show, check the <a href="test-performance-sync.php">test page</a>.</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="error">Error checking database: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
            <?php
        }
        ?>
        
        <div class="card">
            <h2>Quick Links</h2>
            <p>
                <a href="test-performance-sync.php">→ Test Performance Sync</a><br>
                <a href="agent/account.php">→ Agent Account Page</a><br>
                <a href="admin/dashboard.php">→ Admin Dashboard</a><br>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>">→ Refresh This Page</a>
            </p>
        </div>
    </div>
</body>
</html>
