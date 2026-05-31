<?php
// Enhanced debug endpoint for products - provides detailed analysis
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); 
    exit;
}

try {
    $db = (new Database())->getConnection();
    
    // Get products with only essential fields for performance
    $query = "SELECT 
        id, 
        product_name, 
        category, 
        LENGTH(category) as cat_len,
        TRIM(category) as cat_trimmed,
        LENGTH(TRIM(category)) as cat_trimmed_len,
        sub_category,
        primer_file,
        is_active,
        created_at
    FROM products 
    ORDER BY id 
    LIMIT 500"; // Limit for performance
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Quick analysis
    $validCategories = ['VUL', 'Traditional Life Insurance', 'Stand-Alone Product', 'Product Guides'];
    $categoryStats = array_fill_keys($validCategories, 0);
    $categoryStats['Unknown'] = 0;
    
    $issues = [];
    $recommendations = [];
    
    foreach ($products as $product) {
        $rawCategory = $product['category'] ?? '';
        $trimmedCategory = trim($rawCategory);
        
        // Count valid categories
        if (in_array($trimmedCategory, $validCategories)) {
            $categoryStats[$trimmedCategory]++;
        } else {
            $categoryStats['Unknown']++;
            
            // Quick suggestion mapping
            $suggestion = null;
            $lower = strtolower($trimmedCategory);
            
            if (strpos($lower, 'accident') !== false || strpos($lower, 'stand') !== false) {
                $suggestion = 'Stand-Alone Product';
            } elseif (strpos($lower, 'guide') !== false) {
                $suggestion = 'Product Guides';
            } elseif (strpos($lower, 'traditional') !== false) {
                $suggestion = 'Traditional Life Insurance';
            } elseif ($lower === 'vul') {
                $suggestion = 'VUL';
            }
            
            $issues[] = [
                'id' => $product['id'],
                'product_name' => $product['product_name'],
                'issue' => 'Unknown category',
                'current_category' => $rawCategory,
                'suggested_category' => $suggestion,
                'severity' => $suggestion ? 'medium' : 'high'
            ];
        }
        
        // Check for whitespace issues
        if ($rawCategory !== $trimmedCategory) {
            $issues[] = [
                'id' => $product['id'],
                'product_name' => $product['product_name'],
                'issue' => 'Category has whitespace',
                'current_category' => $rawCategory,
                'suggested_category' => $trimmedCategory,
                'severity' => 'low'
            ];
        }
        
        // Check for missing PDF (only for high-priority issues)
        if (empty($product['primer_file']) && $product['is_active'] == 1) {
            $issues[] = [
                'id' => $product['id'],
                'product_name' => $product['product_name'],
                'issue' => 'Missing PDF file',
                'current_category' => $trimmedCategory,
                'suggested_category' => null,
                'severity' => 'medium'
            ];
        }
    }
    
    // Generate quick recommendations
    if ($categoryStats['Unknown'] > 0) {
        $recommendations[] = "Run the 'Fix Categories' function to automatically map {$categoryStats['Unknown']} unknown categories.";
    }
    
    $missingPdfCount = count(array_filter($issues, fn($i) => $i['issue'] === 'Missing PDF file'));
    if ($missingPdfCount > 0) {
        $recommendations[] = "Upload PDF files for {$missingPdfCount} products missing documentation.";
    }
    
    if (empty($recommendations)) {
        $recommendations[] = "All products appear to be properly configured!";
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'products' => $products,
            'total_count' => count($products),
            'category_stats' => $categoryStats,
            'issues' => array_slice($issues, 0, 100), // Limit issues for performance
            'recommendations' => $recommendations,
            'valid_categories' => $validCategories
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Debug analysis failed'
    ]);
}
?>