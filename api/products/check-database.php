<?php
// Quick diagnostic to check what's actually in the database
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); 
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // Get all products with their exact categories
    $query = "SELECT 
        id, 
        product_name, 
        category,
        TRIM(category) as category_trimmed,
        LENGTH(category) as category_length,
        is_active
    FROM products 
    ORDER BY id";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count by category
    $categoryCounts = [];
    foreach ($products as $product) {
        $cat = trim($product['category']);
        if (!isset($categoryCounts[$cat])) {
            $categoryCounts[$cat] = 0;
        }
        $categoryCounts[$cat]++;
    }
    
    echo json_encode([
        'success' => true,
        'total_products' => count($products),
        'products' => $products,
        'category_counts' => $categoryCounts
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database check failed: ' . $e->getMessage()
    ]);
}
?>