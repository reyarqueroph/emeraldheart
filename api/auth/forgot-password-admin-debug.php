<?php
// Debug version to identify connection issues
session_start();
header('Content-Type: application/json');

// Log all incoming data
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Check if we can even reach this file
if (empty($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'No data received',
        'debug' => [
            'raw_input' => $rawInput,
            'method' => $_SERVER['REQUEST_METHOD'],
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
        ]
    ]);
    exit;
}

// Check database config
try {
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    echo json_encode([
        'success' => true,
        'message' => 'API is working correctly',
        'debug' => [
            'action' => $data['action'] ?? 'not set',
            'username' => $data['username'] ?? 'not set',
            'database_connected' => true,
            'session_id' => session_id()
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'error_type' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
