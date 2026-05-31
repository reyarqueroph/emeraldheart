<?php
// Temporary debug — shows exact category values in DB
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

try {
    $db   = (new Database())->getConnection();
    $rows = $db->query("SELECT id, product_name, category, LENGTH(category) as cat_len, HEX(category) as cat_hex FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
