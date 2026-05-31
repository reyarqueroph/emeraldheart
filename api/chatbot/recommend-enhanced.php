<?php
/**
 * Enhanced AI Chatbot Recommendation Engine
 * Features:
 * - Multi-criteria product matching
 * - Conversational AI with context
 * - Product knowledge base
 * - Smart intent detection
 */

session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$message = trim($data['message'] ?? '');
$context = $data['context'] ?? [];
$sessionId = $data['session_id'] ?? session_id();

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// ═══════════════════════════════════════════════════════════
// INTENT DETECTION
// ═══════════════════════════════════════════════════════════

$msg = strtolower($message);
$intent = detectIntent($msg);
$confidence = 0.8;

// ═══════════════════════════════════════════════════════════
// ENTITY EXTRACTION
// ═══════════════════════════════════════════════════════════

$entities = extractEntities($message, $context);

// ═══════════════════════════════════════════════════════════
// PRODUCT RECOMMENDATION ENGINE
// ═══════════════════════════════════════════════════════════

$products = [];
$reply = '';
$suggestions = [];

switch ($intent) {
    case 'greeting':
        $reply = generateGreeting();
        $suggestions = [
            "I need insurance for a 30 year old",
            "Show me VUL products",
            "Budget under ₱3,000/month",
            "What's the difference between VUL and Traditional?"
        ];
        break;
        
    case 'product_inquiry':
        $products = findProducts($db, $entities);
        $reply = generateProductRecommendation($entities, $products);
        $suggestions = generateFollowUpSuggestions($entities, $products);
        break;
        
    case 'comparison':
        $reply = generateComparison($db, $entities);
        $suggestions = [
            "Show me VUL products",
            "Show me Traditional products",
            "What about Stand-Alone products?"
        ];
        break;
        
    case 'product_details':
        $productName = $entities['product_name'] ?? '';
        if ($productName) {
            $productDetails = getProductDetails($db, $productName);
            $reply = generateProductDetails($productDetails);
            $products = [$productDetails];
        } else {
            $reply = "Which product would you like to know more about?";
        }
        break;
        
    case 'help':
        $reply = generateHelpMessage();
        $suggestions = [
            "Recommend a product",
            "Compare VUL vs Traditional",
            "Show all products",
            "Explain insurance terms"
        ];
        break;
        
    default:
        // Try to find products anyway
        $products = findProducts($db, $entities);
        if (!empty($products)) {
            $reply = generateProductRecommendation($entities, $products);
            $suggestions = generateFollowUpSuggestions($entities, $products);
        } else {
            $reply = "I'm here to help you find the right insurance product! You can ask me about:\n\n• Product recommendations (age, budget, goals)\n• Product comparisons\n• Specific product details\n• Insurance terms and concepts\n\nWhat would you like to know?";
            $suggestions = [
                "Recommend a product for me",
                "Show me all VUL products",
                "What's your most popular product?",
                "Explain VUL vs Traditional"
            ];
        }
}

// ═══════════════════════════════════════════════════════════
// SAVE CONVERSATION HISTORY
// ═══════════════════════════════════════════════════════════

saveConversation($db, $_SESSION['user_id'], $sessionId, $message, $reply, $context, $products, $intent, $confidence);

// ═══════════════════════════════════════════════════════════
// RESPONSE
// ═══════════════════════════════════════════════════════════

echo json_encode([
    'success' => true,
    'reply' => $reply,
    'products' => $products,
    'suggestions' => $suggestions,
    'context' => array_merge($context, $entities, ['intent' => $intent]),
    'intent' => $intent,
    'confidence' => $confidence
]);

// ═══════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════

function detectIntent($msg) {
    // Greeting
    if (preg_match('/\b(hi|hello|hey|good morning|good afternoon|good evening)\b/i', $msg)) {
        return 'greeting';
    }
    
    // Help
    if (preg_match('/\b(help|assist|guide|how to|what can you)\b/i', $msg)) {
        return 'help';
    }
    
    // Comparison
    if (preg_match('/\b(compare|difference|vs|versus|better|which one)\b/i', $msg)) {
        return 'comparison';
    }
    
    // Product details
    if (preg_match('/\b(tell me (more )?about|details|information about|what is|explain)\b/i', $msg)) {
        return 'product_details';
    }
    
    // Product inquiry (default for most queries)
    if (preg_match('/\b(recommend|suggest|show|find|need|want|looking for|best|good)\b/i', $msg)) {
        return 'product_inquiry';
    }
    
    return 'general';
}

function extractEntities($message, $context) {
    $entities = [];
    $msg = strtolower($message);
    
    // Extract age
    if (preg_match('/\b(\d{1,2})\s*(years?\s*old|yo|yrs?|year old)?\b/i', $message, $m)) {
        $entities['age'] = intval($m[1]);
    } elseif (isset($context['age'])) {
        $entities['age'] = $context['age'];
    }
    
    // Extract budget
    if (preg_match('/[₱P]?\s*(\d[\d,]*)\s*(\/mo|per month|monthly|a month|month)?/i', $message, $m)) {
        $entities['budget'] = intval(str_replace(',', '', $m[1]));
    } elseif (isset($context['budget'])) {
        $entities['budget'] = $context['budget'];
    }
    
    // Extract payment type
    if (preg_match('/\b(regular|limited|single)\s*(pay|payment)?\b/i', $message, $m)) {
        $entities['payment_type'] = ucfirst(strtolower($m[1]));
    }
    
    // Extract goal
    $goals = [
        'investment' => ['invest', 'grow', 'fund', 'vul', 'wealth', 'savings', 'return', 'money grow'],
        'protection' => ['protect', 'life insurance', 'death', 'term', 'coverage', 'beneficiary', 'family security'],
        'accident' => ['accident', 'injury', 'disability', 'personal accident', 'pa'],
        'health' => ['health', 'hospital', 'medical', 'sick', 'illness', 'hospitalization'],
        'education' => ['education', 'school', 'college', 'child', 'kids', 'tuition'],
        'retirement' => ['retire', 'retirement', 'pension', 'old age', 'senior'],
    ];
    
    foreach ($goals as $goal => $keywords) {
        foreach ($keywords as $kw) {
            if (str_contains($msg, $kw)) {
                $entities['goal'] = $goal;
                break 2;
            }
        }
    }
    
    // Extract category
    if (str_contains($msg, 'vul') || str_contains($msg, 'variable unit')) {
        $entities['category'] = 'VUL';
    } elseif (str_contains($msg, 'traditional')) {
        $entities['category'] = 'Traditional Life Insurance';
    } elseif (str_contains($msg, 'stand-alone') || str_contains($msg, 'standalone')) {
        $entities['category'] = 'Stand-Alone Product';
    }
    
    // Extract product name (look for product names in message)
    // This would need to query the database for actual product names
    
    return $entities;
}

function findProducts($db, $entities) {
    $where = ["(is_active = 1 OR is_active IS NULL)"];
    $params = [];
    $score = [];
    
    // Age filter
    if (isset($entities['age'])) {
        $age = $entities['age'];
        // For now, we'll do a simple text search in age_range
        // In production, you'd want to parse age ranges properly
        $where[] = "(age_range LIKE :age_search OR age_range LIKE '%days%' OR age_range LIKE '%months%')";
        $params[':age_search'] = '%' . $age . '%';
    }
    
    // Budget filter
    if (isset($entities['budget'])) {
        $where[] = "min_premium_monthly <= :budget";
        $params[':budget'] = $entities['budget'];
    }
    
    // Payment type filter
    if (isset($entities['payment_type'])) {
        $where[] = "payment_type = :payment_type";
        $params[':payment_type'] = $entities['payment_type'];
    }
    
    // Category filter
    if (isset($entities['category'])) {
        $where[] = "category = :category";
        $params[':category'] = $entities['category'];
    } elseif (isset($entities['goal'])) {
        // Map goal to category
        $goalCategoryMap = [
            'investment' => 'VUL',
            'protection' => 'Traditional Life Insurance',
            'accident' => 'Stand-Alone Product',
            'health' => 'Traditional Life Insurance',
            'education' => 'VUL',
            'retirement' => 'VUL',
        ];
        if (isset($goalCategoryMap[$entities['goal']])) {
            $where[] = "category = :category";
            $params[':category'] = $goalCategoryMap[$entities['goal']];
        }
    }
    
    $sql = "SELECT id, product_name, category, sub_category, age_range, min_premium_monthly, payment_type, description, primer_file 
            FROM products 
            WHERE " . implode(' AND ', $where) . " 
            ORDER BY min_premium_monthly ASC 
            LIMIT 5";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateGreeting() {
    $greetings = [
        "Hi there! 👋 I'm your eHeart insurance advisor. I can help you find the perfect insurance product for your clients.",
        "Hello! 😊 I'm here to help you discover the best insurance solutions. What are you looking for today?",
        "Hey! 👋 Ready to find the right insurance product? Tell me about your client's needs!",
    ];
    
    $greeting = $greetings[array_rand($greetings)];
    $greeting .= "\n\nI can help with:\n";
    $greeting .= "• 🎯 Product recommendations based on age, budget, and goals\n";
    $greeting .= "• 📊 Comparing different insurance products\n";
    $greeting .= "• 📋 Detailed product information\n";
    $greeting .= "• 💡 Insurance advice and guidance\n\n";
    $greeting .= "What would you like to know?";
    
    return $greeting;
}

function generateProductRecommendation($entities, $products) {
    if (empty($products)) {
        return "I couldn't find products matching those exact criteria. Let me show you our most popular options instead. Could you provide more details like:\n\n• Client's age\n• Monthly budget\n• Main goal (protection, investment, health, etc.)";
    }
    
    $parts = [];
    if (isset($entities['age'])) $parts[] = "age **{$entities['age']}**";
    if (isset($entities['budget'])) $parts[] = "budget **₱" . number_format($entities['budget']) . "/mo**";
    if (isset($entities['goal'])) $parts[] = "goal: **{$entities['goal']}**";
    if (isset($entities['payment_type'])) $parts[] = "payment: **{$entities['payment_type']}**";
    
    $criteria = !empty($parts) ? implode(', ', $parts) : "your criteria";
    
    $reply = "Based on {$criteria}, here are my top recommendations:\n\n";
    $reply .= "I found **" . count($products) . "** product" . (count($products) > 1 ? 's' : '') . " that match your needs. ";
    $reply .= "Check out the product cards below for details!\n\n";
    $reply .= "💡 **Tip:** Click on any product card to see full details and the product primer PDF.";
    
    return $reply;
}

function generateFollowUpSuggestions($entities, $products) {
    $suggestions = [];
    
    if (!isset($entities['age'])) {
        $suggestions[] = "What's the client's age?";
    }
    
    if (!isset($entities['budget'])) {
        $suggestions[] = "What's the monthly budget?";
    }
    
    if (!empty($products)) {
        $suggestions[] = "Tell me more about " . $products[0]['product_name'];
        if (count($products) > 1) {
            $suggestions[] = "Compare these products";
        }
    }
    
    $suggestions[] = "Show me other options";
    
    return array_slice($suggestions, 0, 4);
}

function generateComparison($db, $entities) {
    $reply = "Let me explain the differences:\n\n";
    
    $reply .= "🔵 **VUL (Variable Unit-Linked)**\n";
    $reply .= "• Life insurance + investment component\n";
    $reply .= "• Flexible premium payments\n";
    $reply .= "• Market-linked returns (higher potential)\n";
    $reply .= "• Best for: Long-term wealth building\n\n";
    
    $reply .= "🔴 **Traditional Life Insurance**\n";
    $reply .= "• Pure protection + guaranteed savings\n";
    $reply .= "• Fixed premium payments\n";
    $reply .= "• Guaranteed returns (more stable)\n";
    $reply .= "• Best for: Conservative clients\n\n";
    
    $reply .= "🟢 **Stand-Alone Products**\n";
    $reply .= "• Specific coverage (accident, health)\n";
    $reply .= "• Lower premiums\n";
    $reply .= "• Focused protection\n";
    $reply .= "• Best for: Supplemental coverage\n\n";
    
    $reply .= "Which type interests you?";
    
    return $reply;
}

function getProductDetails($db, $productName) {
    $stmt = $db->prepare("SELECT * FROM products WHERE product_name LIKE :name LIMIT 1");
    $stmt->execute([':name' => '%' . $productName . '%']);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function generateProductDetails($product) {
    if (!$product) {
        return "I couldn't find that product. Could you check the name and try again?";
    }
    
    $reply = "📋 **{$product['product_name']}**\n\n";
    $reply .= "**Category:** {$product['category']}\n";
    if ($product['sub_category']) $reply .= "**Type:** {$product['sub_category']}\n";
    $reply .= "**Age Range:** {$product['age_range']}\n";
    $reply .= "**Min. Premium:** ₱" . number_format($product['min_premium_monthly']) . "/month\n";
    $reply .= "**Payment Type:** {$product['payment_type']}\n\n";
    
    if ($product['description']) {
        $reply .= "**Description:**\n{$product['description']}\n\n";
    }
    
    if ($product['primer_file']) {
        $reply .= "📄 **Product Primer PDF available** - Click the product card to view!\n\n";
    }
    
    $reply .= "Would you like to see similar products or compare with others?";
    
    return $reply;
}

function generateHelpMessage() {
    $reply = "I'm here to help! Here's what I can do:\n\n";
    $reply .= "🎯 **Product Recommendations**\n";
    $reply .= "Tell me the client's age, budget, and goals, and I'll find the best match.\n";
    $reply .= "*Example: \"I need insurance for a 35 year old with ₱3,000 monthly budget\"*\n\n";
    
    $reply .= "📊 **Product Comparisons**\n";
    $reply .= "Ask me to compare different product types or specific products.\n";
    $reply .= "*Example: \"What's the difference between VUL and Traditional?\"*\n\n";
    
    $reply .= "📋 **Product Details**\n";
    $reply .= "Get detailed information about any product.\n";
    $reply .= "*Example: \"Tell me about PRULife Protector\"*\n\n";
    
    $reply .= "💡 **General Advice**\n";
    $reply .= "Ask me anything about insurance products and I'll do my best to help!\n\n";
    
    $reply .= "What would you like to know?";
    
    return $reply;
}

function saveConversation($db, $userId, $sessionId, $message, $response, $context, $products, $intent, $confidence) {
    try {
        // Check if table exists first
        $stmt = $db->query("SHOW TABLES LIKE 'chatbot_conversations'");
        if ($stmt->rowCount() == 0) {
            // Table doesn't exist, skip saving
            return;
        }
        
        $stmt = $db->prepare("
            INSERT INTO chatbot_conversations 
            (user_id, session_id, message, response, context_data, products_recommended, intent, confidence_score) 
            VALUES 
            (:user_id, :session_id, :message, :response, :context, :products, :intent, :confidence)
        ");
        
        $stmt->execute([
            ':user_id' => $userId,
            ':session_id' => $sessionId,
            ':message' => $message,
            ':response' => $response,
            ':context' => json_encode($context),
            ':products' => json_encode(array_column($products, 'id')),
            ':intent' => $intent,
            ':confidence' => $confidence
        ]);
    } catch (Exception $e) {
        // Silently fail if table doesn't exist yet
        error_log("Chatbot conversation save failed: " . $e->getMessage());
    }
}
?>
