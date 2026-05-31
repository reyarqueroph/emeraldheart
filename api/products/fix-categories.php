<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

try {
    $database = new Database();
    $db       = $database->getConnection();
    $fixed    = 0;

    // First, get all current categories to see what we're dealing with
    $currentCats = $db->query("SELECT DISTINCT TRIM(category) as cat FROM products ORDER BY cat")->fetchAll(PDO::FETCH_COLUMN);
    
    // Comprehensive mapping - case insensitive
    $maps = [
        'Stand-Alone Product' => [
            'Personal Accident', 'personal accident', 'PERSONAL ACCIDENT',
            'Stand-Alone', 'stand-alone', 'Stand Alone', 'stand alone', 'STAND-ALONE',
            'Stand-alone Product', 'stand-alone product', 'Stand alone product',
            'Standalone', 'standalone', 'STANDALONE',
            'Standalone Product', 'standalone product'
        ],
        'Product Guides' => [
            'Product Guide', 'product guide', 'PRODUCT GUIDE',
            'Product Guides', 'product guides', 'PRODUCT GUIDES',
            'Guide', 'guide', 'GUIDE',
            'Guides', 'guides', 'GUIDES'
        ],
        'Traditional Life Insurance' => [
            'Traditional', 'traditional', 'TRADITIONAL',
            'Traditional Life', 'traditional life', 'TRADITIONAL LIFE',
            'Traditional Insurance', 'traditional insurance'
        ],
        'VUL' => [
            'VUL', 'vul',
            'Variable Unit-Linked', 'variable unit-linked', 'Variable Unit Linked',
            'Variable Unit Linked', 'variable unit linked'
        ]
    ];

    // Apply fixes
    foreach ($maps as $canonical => $variants) {
        foreach ($variants as $variant) {
            // Exact match (case insensitive, trimmed)
            $stmt = $db->prepare("UPDATE products SET category = ? WHERE LOWER(TRIM(category)) = LOWER(?)");
            $stmt->execute([$canonical, $variant]);
            $count = $stmt->rowCount();
            if ($count > 0) {
                $fixed += $count;
            }
        }
    }

    // Special case: anything with "accident" in the name
    $stmt = $db->prepare("UPDATE products SET category = 'Stand-Alone Product'
                          WHERE LOWER(TRIM(category)) LIKE '%accident%'
                          AND TRIM(category) NOT IN ('VUL','Traditional Life Insurance','Stand-Alone Product','Product Guides')");
    $stmt->execute();
    $fixed += $stmt->rowCount();

    // Special case: anything with "guide" in the name
    $stmt = $db->prepare("UPDATE products SET category = 'Product Guides'
                          WHERE LOWER(TRIM(category)) LIKE '%guide%'
                          AND TRIM(category) NOT IN ('VUL','Traditional Life Insurance','Stand-Alone Product','Product Guides')");
    $stmt->execute();
    $fixed += $stmt->rowCount();

    // Get final state
    $all = $db->query("SELECT id, product_name, TRIM(category) as category FROM products ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    // Count by category
    $categoryCounts = [];
    foreach ($all as $product) {
        $cat = $product['category'];
        if (!isset($categoryCounts[$cat])) {
            $categoryCounts[$cat] = 0;
        }
        $categoryCounts[$cat]++;
    }

    echo json_encode([
        'success' => true, 
        'message' => "Fixed $fixed record(s).", 
        'products' => $all,
        'category_counts' => $categoryCounts,
        'before_categories' => $currentCats
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
