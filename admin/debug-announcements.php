<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../api/config/database.php';

echo "<h1>Announcements Debug Information</h1>";

// Check session
echo "<h2>Session Information</h2>";
echo "<pre>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "User Role: " . ($_SESSION['user_role'] ?? 'NOT SET') . "\n";
echo "User Name: " . ($_SESSION['user_name'] ?? 'NOT SET') . "\n";
echo "</pre>";

// Check database connection
echo "<h2>Database Connection</h2>";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Check if table exists
    echo "<h2>Table Check</h2>";
    $tableCheck = $db->query("SHOW TABLES LIKE 'admin_announcements'");
    if ($tableCheck->rowCount() > 0) {
        echo "<p style='color: green;'>✓ admin_announcements table exists</p>";
        
        // Show table structure
        echo "<h3>Table Structure</h3>";
        $columns = $db->query("DESCRIBE admin_announcements")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show existing data
        echo "<h3>Existing Announcements</h3>";
        $announcements = $db->query("SELECT * FROM admin_announcements ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        if (count($announcements) > 0) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Title</th><th>Type</th><th>Active</th><th>Created</th></tr>";
            foreach ($announcements as $ann) {
                echo "<tr>";
                echo "<td>" . $ann['id'] . "</td>";
                echo "<td>" . htmlspecialchars($ann['title']) . "</td>";
                echo "<td>" . $ann['announcement_type'] . "</td>";
                echo "<td>" . ($ann['is_active'] ? 'Yes' : 'No') . "</td>";
                echo "<td>" . $ann['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No announcements found</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ admin_announcements table does not exist</p>";
        echo "<p>Run this SQL to create the table:</p>";
        echo "<textarea rows='15' cols='80' readonly>";
        echo "CREATE TABLE admin_announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    announcement_type ENUM('general', 'urgent', 'reminder', 'event') DEFAULT 'general',
    start_date DATE NULL,
    end_date DATE NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_type (announcement_type),
    INDEX idx_created_at (created_at)
);";
        echo "</textarea>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test API endpoints
echo "<h2>API Endpoint Tests</h2>";
echo "<button onclick='testCreateAPI()'>Test Create API</button>";
echo "<button onclick='testGetAPI()'>Test Get API</button>";
echo "<div id='apiResults'></div>";

?>

<script>
async function testCreateAPI() {
    const testData = {
        title: "Test Announcement",
        message: "This is a test announcement created from debug page",
        announcement_type: "general",
        is_active: 1
    };
    
    try {
        const response = await fetch('../api/announcements/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(testData)
        });
        
        const result = await response.json();
        document.getElementById('apiResults').innerHTML = '<h3>Create API Result:</h3><pre>' + JSON.stringify(result, null, 2) + '</pre>';
    } catch (error) {
        document.getElementById('apiResults').innerHTML = '<h3>Create API Error:</h3><pre>' + error.message + '</pre>';
    }
}

async function testGetAPI() {
    try {
        const response = await fetch('../api/announcements/get-admin.php');
        const result = await response.json();
        document.getElementById('apiResults').innerHTML = '<h3>Get API Result:</h3><pre>' + JSON.stringify(result, null, 2) + '</pre>';
    } catch (error) {
        document.getElementById('apiResults').innerHTML = '<h3>Get API Error:</h3><pre>' + error.message + '</pre>';
    }
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
button { margin: 5px; padding: 10px; }
textarea { margin: 10px 0; }
</style>