<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

// Read raw POST body
$raw  = file_get_contents("php://input");
$data = json_decode($raw, true); // decode as associative array

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . $raw]); exit;
}

$id       = intval($data['id'] ?? 0);
$category = trim($data['category'] ?? '');

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']); exit;
}
if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Category is required. Received: ' . json_encode($data)]); exit;
}

try {
    $database = new Database();
    $db       = $database->getConnection();

    $stmt = $db->prepare("UPDATE products SET
        product_name        = :pn,
        category            = :cat,
        sub_category        = :sub,
        payment_type        = :pt,
        age_range           = :ar,
        min_premium_monthly = :mp,
        description         = :desc
        WHERE id = :id");

    $stmt->execute([
        ':pn'  => trim($data['product_name'] ?? ''),
        ':cat' => $category,
        ':sub' => trim($data['sub_category'] ?? ''),
        ':pt'  => trim($data['payment_type'] ?? 'Regular'),
        ':ar'  => trim($data['age_range'] ?? ''),
        ':mp'  => floatval($data['min_premium_monthly'] ?? 0),
        ':desc'=> trim($data['description'] ?? ''),
        ':id'  => $id,
    ]);

    echo json_encode([
        'success'        => true,
        'message'        => 'Product updated successfully',
        'saved_category' => $category,
        'rows_affected'  => $stmt->rowCount()
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
