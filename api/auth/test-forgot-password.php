<?php
// Simple test file to verify the forgot-password-admin.php is accessible
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'API endpoint is accessible',
    'file' => __FILE__,
    'time' => date('Y-m-d H:i:s')
]);
?>
