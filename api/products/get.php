<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $database = new Database();
    $db       = $database->getConnection();

    $search   = trim($_GET['search']   ?? '');
    $category = trim($_GET['category'] ?? '');
    $limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000; // Add limit for performance

    // Normalise category param to canonical value
    $catMap = [
        'stand-alone product'        => 'Stand-Alone Product',
        'stand-alone'                => 'Stand-Alone Product',
        'stand alone product'        => 'Stand-Alone Product',
        'personal accident'          => 'Stand-Alone Product',
        'product guides'             => 'Product Guides',
        'product guide'              => 'Product Guides',
        'traditional life insurance' => 'Traditional Life Insurance',
        'traditional'                => 'Traditional Life Insurance',
        'vul'                        => 'VUL',
    ];
    if ($category !== '' && $category !== 'all') {
        $category = $catMap[strtolower($category)] ?? $category;
    }

    $query  = "SELECT id, product_name, category, sub_category, payment_type, age_range, min_premium_monthly, description, primer_file, is_active, created_at FROM products WHERE is_active = 1";
    $params = [];

    if ($search !== '') {
        $query .= " AND (product_name LIKE :s1 OR category LIKE :s2)";
        $params[':s1'] = "%$search%";
        $params[':s2'] = "%$search%";
    }

    if ($category !== '' && $category !== 'all') {
        // Use TRIM + case-insensitive compare to catch any whitespace/case issues
        $query .= " AND LOWER(TRIM(category)) = LOWER(:cat)";
        $params[':cat'] = $category;
    }

    $query .= " ORDER BY id DESC LIMIT :limit";
    $params[':limit'] = $limit;
    
    $stmt = $db->prepare($query);
    
    // Bind limit parameter as integer
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    foreach ($params as $key => $value) {
        if ($key !== ':limit') {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows, 'count' => count($rows)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
