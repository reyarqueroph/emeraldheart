<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$database = new Database();
$db       = $database->getConnection();
$data     = json_decode(file_get_contents("php://input"));

$product_name        = trim($data->product_name ?? '');
$category            = trim($data->category ?? '');
$sub_category        = trim($data->sub_category ?? '');
$payment_type        = trim($data->payment_type ?? 'Regular');
$age_range           = trim($data->age_range ?? '');
$min_premium_monthly = floatval($data->min_premium_monthly ?? 0);
$description         = trim($data->description ?? '');

if (empty($product_name)) {
    echo json_encode(['success' => false, 'message' => 'Product name is required']); exit;
}

// Whitelist of valid categories
$validCategories = ['VUL', 'Traditional Life Insurance', 'Stand-Alone Product', 'Product Guides'];
if (!in_array($category, $validCategories, true)) {
    echo json_encode(['success' => false, 'message' => "Invalid category: '$category'"]); exit;
}

$stmt = $db->prepare("INSERT INTO products
    (product_name, category, sub_category, payment_type, age_range, min_premium_monthly, description, is_active)
    VALUES (:pn, :cat, :sub, :pt, :ar, :mp, :desc, 1)");

$stmt->execute([
    ':pn'  => $product_name,
    ':cat' => $category,
    ':sub' => $sub_category,
    ':pt'  => $payment_type,
    ':ar'  => $age_range,
    ':mp'  => $min_premium_monthly,
    ':desc'=> $description,
]);

echo json_encode(['success' => true, 'message' => 'Product created successfully', 'id' => $db->lastInsertId()]);
?>
