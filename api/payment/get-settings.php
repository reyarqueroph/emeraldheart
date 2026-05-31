<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get payment settings
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM payment_settings");
    $stmt->execute();
    
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // For admin page compatibility
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/admin/') !== false) {
        echo json_encode([
            'success' => true,
            'data' => $settings
        ]);
    } else {
        // For registration page compatibility
        echo json_encode([
            'success' => true,
            'settings' => $settings
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load payment settings: ' . $e->getMessage()
    ]);
}
?>