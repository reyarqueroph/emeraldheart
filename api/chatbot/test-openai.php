<?php
/**
 * Test OpenAI Integration
 * Use this to verify OpenAI is configured correctly
 */

session_start();
header('Content-Type: application/json');

// Allow access for testing (remove in production)
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login first'
    ]);
    exit;
}

require_once '../lib/OpenAIHelper.php';

$action = $_GET['action'] ?? 'test';

if ($action === 'test') {
    // Test OpenAI connection
    $openai = new OpenAIHelper();
    $result = $openai->testConnection();
    
    echo json_encode($result);
    
} elseif ($action === 'extract') {
    // Test PDF extraction
    require_once '../lib/PdfTextExtractor.php';
    
    $testFile = $_GET['file'] ?? '';
    
    if (empty($testFile)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide a file parameter'
        ]);
        exit;
    }
    
    $pdfPath = dirname(__DIR__, 2) . '/uploads/products/' . $testFile;
    
    if (!file_exists($pdfPath)) {
        echo json_encode([
            'success' => false,
            'message' => 'PDF file not found: ' . $testFile
        ]);
        exit;
    }
    
    $text = PdfTextExtractor::extractTextWithCache($pdfPath);
    
    echo json_encode([
        'success' => true,
        'file' => $testFile,
        'text_length' => strlen($text),
        'preview' => substr($text, 0, 500) . '...',
        'full_text' => $text
    ]);
    
} elseif ($action === 'ask') {
    // Test asking a question about a product
    require_once '../lib/PdfTextExtractor.php';
    require_once '../lib/OpenAIHelper.php';
    require_once '../config/database.php';
    
    $productId = $_GET['product_id'] ?? '';
    $question = $_GET['question'] ?? 'What are the benefits of this product?';
    
    if (empty($productId)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide a product_id parameter'
        ]);
        exit;
    }
    
    // Get product details
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "SELECT * FROM products WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not found'
        ]);
        exit;
    }
    
    if (empty($product['primer_file'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Product has no PDF file'
        ]);
        exit;
    }
    
    $pdfPath = dirname(__DIR__, 2) . '/uploads/products/' . $product['primer_file'];
    
    if (!file_exists($pdfPath)) {
        echo json_encode([
            'success' => false,
            'message' => 'PDF file not found on server'
        ]);
        exit;
    }
    
    // Extract PDF text
    $pdfContent = PdfTextExtractor::extractTextWithCache($pdfPath);
    
    // Ask OpenAI
    $openai = new OpenAIHelper();
    $result = $openai->askAboutProduct($question, $pdfContent, [
        'id' => $product['id'],
        'name' => $product['product_name'],
        'category' => $product['category'],
        'min_premium' => $product['min_premium_monthly'],
        'payment_type' => $product['payment_type'],
        'age_range' => $product['age_range'],
        'description' => $product['description']
    ]);
    
    echo json_encode([
        'success' => $result['success'],
        'product' => $product['product_name'],
        'question' => $question,
        'answer' => $result['answer'],
        'error' => $result['error'] ?? null,
        'pdf_text_length' => strlen($pdfContent)
    ]);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action. Use: test, extract, or ask'
    ]);
}
?>
