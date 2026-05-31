<?php
// Debug script to check OTP records
header('Content-Type: application/json');

try {
    $host = "localhost";
    $db_name = "pru_life_db";
    $username = "root";
    $password = "";
    
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all OTP records
    $stmt = $db->prepare("SELECT * FROM password_reset_otps ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $otps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get current server time
    $serverTime = date('Y-m-d H:i:s');
    
    echo json_encode([
        'success' => true,
        'server_time' => $serverTime,
        'server_timestamp' => time(),
        'recent_otps' => $otps
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
