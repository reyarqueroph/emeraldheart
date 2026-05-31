<?php
/**
 * Simple Product Recommendation Chatbot
 * Step-by-step approach:
 * 1. Ask for Age
 * 2. Ask for Capacity to Pay (Budget)
 * 3. Ask for Payment Type
 * 4. Suggest matching products
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

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Initialize context
$age = $context['age'] ?? null;
$budget = $context['budget'] ?? null;
$paymentType = $context['payment_type'] ?? null;
$step = $context['step'] ?? 'start';

$msg = strtolower($message);
$reply = '';
$products = [];
$suggestions = [];
$nextStep = $step;

// ═══════════════════════════════════════════════════════════
// STEP-BY-STEP CONVERSATION FLOW
// ═══════════════════════════════════════════════════════════

// Check for greeting or restart
if (preg_match('/\b(hi|hello|hey|start|begin|new)\b/i', $msg) && $step !== 'recommend') {
    $step = 'start';
    $age = null;
    $budget = null;
    $paymentType = null;
}

switch ($step) {
    case 'start':
        // Step 1: Ask for Age
        $reply = "👋 Hi! I'll help you find the perfect insurance product.\n\n";
        $reply .= "Let's start with some basic information:\n\n";
        $reply .= "**Question 1 of 3:** What is the client's age?\n";
        $reply .= "_(Please enter age in years, e.g., 25, 35, 50)_";
        
        $suggestions = ["25", "35", "45", "55"];
        $nextStep = 'ask_age';
        break;
        
    case 'ask_age':
        // Extract age from message
        if (preg_match('/\b(\d{1,2})\b/', $message, $m)) {
            $age = intval($m[1]);
            
            if ($age < 1 || $age > 100) {
                $reply = "⚠️ Please enter a valid age between 1 and 100 years.";
                $nextStep = 'ask_age';
            } else {
                // Age captured, move to budget
                $reply = "✅ Age: **{$age} years old**\n\n";
                $reply .= "**Question 2 of 3:** What is the client's monthly budget (capacity to pay)?\n";
                $reply .= "_(Please enter amount in pesos, e.g., 2000, 5000, 10000)_";
                
                $suggestions = ["1000", "2500", "5000", "10000"];
                $nextStep = 'ask_budget';
            }
        } else {
            $reply = "⚠️ I didn't catch the age. Please enter a number (e.g., 25, 35, 50).";
            $suggestions = ["25", "35", "45", "55"];
            $nextStep = 'ask_age';
        }
        break;
        
    case 'ask_budget':
        // Extract budget from message
        if (preg_match('/[₱P]?\s*(\d[\d,]*)\b/', $message, $m)) {
            $budget = intval(str_replace(',', '', $m[1]));
            
            if ($budget < 100) {
                $reply = "⚠️ Please enter a valid monthly budget (minimum ₱100).";
                $nextStep = 'ask_budget';
            } else {
                // Budget captured, move to payment type
                $reply = "✅ Age: **{$age} years old**\n";
                $reply .= "✅ Monthly Budget: **₱" . number_format($budget) . "**\n\n";
                $reply .= "**Question 3 of 3:** What payment type does the client prefer?\n\n";
                $reply .= "• **Regular** - Pay premiums throughout the policy term\n";
                $reply .= "• **Limited** - Pay for a limited period (e.g., 5, 10 years)\n";
                $reply .= "• **Single** - One-time payment\n\n";
                $reply .= "_(Please choose: Regular, Limited, or Single)_";
                
                $suggestions = ["Regular", "Limited", "Single"];
                $nextStep = 'ask_payment_type';
            }
        } else {
            $reply = "⚠️ I didn't catch the budget amount. Please enter a number (e.g., 2000, 5000, 10000).";
            $suggestions = ["1000", "2500", "5000", "10000"];
            $nextStep = 'ask_budget';
        }
        break;
        
    case 'ask_payment_type':
        // Extract payment type from message
        if (preg_match('/\b(regular|limited|single)\b/i', $message, $m)) {
            $paymentType = ucfirst(strtolower($m[1]));
            
            // All information collected, find products
            $products = findMatchingProducts($db, $age, $budget, $paymentType);
            
            $reply = "✅ Age: **{$age} years old**\n";
            $reply .= "✅ Monthly Budget: **₱" . number_format($budget) . "**\n";
            $reply .= "✅ Payment Type: **{$paymentType}**\n\n";
            $reply .= "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            
            if (!empty($products)) {
                $reply .= "🎯 **Perfect! I found " . count($products) . " product" . (count($products) > 1 ? 's' : '') . " that match your criteria:**\n\n";
                $reply .= "Check out the product cards below for detailed information. Each card shows:\n";
                $reply .= "• Product name and category\n";
                $reply .= "• Age range coverage\n";
                $reply .= "• Monthly premium\n";
                $reply .= "• Payment type\n";
                $reply .= "• Product primer PDF (if available)\n\n";
                $reply .= "💡 **Tip:** Click on any product card to view the full product primer PDF!";
            } else {
                $reply .= "😔 **No exact matches found.**\n\n";
                $reply .= "Let me show you our closest alternatives that might work:\n";
                
                // Find closest matches with relaxed criteria
                $products = findClosestProducts($db, $age, $budget, $paymentType);
                
                if (!empty($products)) {
                    $reply .= "\n📋 These products are close to your criteria:";
                } else {
                    $reply .= "\n📋 Here are our most popular products:";
                    $products = getPopularProducts($db);
                }
            }
            
            $suggestions = [
                "Start over",
                "Show me VUL products",
                "Show me Traditional products",
                "Change my budget"
            ];
            $nextStep = 'recommend';
            
        } else {
            $reply = "⚠️ Please choose a valid payment type: **Regular**, **Limited**, or **Single**.";
            $suggestions = ["Regular", "Limited", "Single"];
            $nextStep = 'ask_payment_type';
        }
        break;
        
    case 'recommend':
        // User wants to modify or start over
        if (preg_match('/\b(start over|restart|new|begin again)\b/i', $msg)) {
            $age = null;
            $budget = null;
            $paymentType = null;
            $nextStep = 'start';
            
            $reply = "🔄 Let's start fresh!\n\n";
            $reply .= "**Question 1 of 3:** What is the client's age?";
            $suggestions = ["25 years old", "35 years old", "45 years old", "55 years old"];
            
        } elseif (preg_match('/\b(change|modify|update)\s*(age|budget|payment)\b/i', $msg, $m)) {
            $field = strtolower($m[2]);
            
            if ($field === 'age') {
                $nextStep = 'ask_age';
                $reply = "**Update Age:** What is the client's age?";
                $suggestions = ["25", "35", "45", "55"];
            } elseif ($field === 'budget') {
                $nextStep = 'ask_budget';
                $reply = "**Update Budget:** What is the monthly budget?";
                $suggestions = ["1000", "2500", "5000", "10000"];
            } elseif ($field === 'payment') {
                $nextStep = 'ask_payment_type';
                $reply = "**Update Payment Type:** Choose Regular, Limited, or Single.";
                $suggestions = ["Regular", "Limited", "Single"];
            }
            
        } elseif (preg_match('/\b(vul|variable)\b/i', $msg)) {
            $products = getProductsByCategory($db, 'VUL', $age, $budget);
            $reply = "📊 **VUL (Variable Unit-Linked) Products:**\n\n";
            $reply .= "These products combine life insurance with investment. Here are the matches:";
            $suggestions = ["Show Traditional products", "Start over", "Compare VUL vs Traditional"];
            
        } elseif (preg_match('/\b(traditional|trad)\b/i', $msg)) {
            $products = getProductsByCategory($db, 'Traditional Life Insurance', $age, $budget);
            $reply = "🛡️ **Traditional Life Insurance Products:**\n\n";
            $reply .= "These products offer guaranteed protection and savings. Here are the matches:";
            $suggestions = ["Show VUL products", "Start over", "Compare VUL vs Traditional"];
            
        } else {
            // Show current criteria and ask what they want to do
            $reply = "**Current Criteria:**\n";
            $reply .= "• Age: {$age} years old\n";
            $reply .= "• Budget: ₱" . number_format($budget) . "/month\n";
            $reply .= "• Payment: {$paymentType}\n\n";
            $reply .= "What would you like to do?";
            
            $suggestions = [
                "Start over",
                "Show me VUL products",
                "Show me Traditional products",
                "Change my budget"
            ];
        }
        break;
}

// ═══════════════════════════════════════════════════════════
// RESPONSE
// ═══════════════════════════════════════════════════════════

echo json_encode([
    'success' => true,
    'reply' => $reply,
    'products' => $products,
    'suggestions' => $suggestions,
    'context' => [
        'step' => $nextStep,
        'age' => $age,
        'budget' => $budget,
        'payment_type' => $paymentType
    ],
    'progress' => getProgress($nextStep)
]);

// ═══════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════

function findMatchingProducts($db, $age, $budget, $paymentType) {
    $sql = "SELECT id, product_name, category, sub_category, age_range, min_premium_monthly, payment_type, description, primer_file 
            FROM products 
            WHERE (is_active = 1 OR is_active IS NULL)
              AND category IN ('VUL', 'Traditional Life Insurance')
              AND min_premium_monthly <= :budget
              AND payment_type = :payment_type
            ORDER BY 
              CASE 
                WHEN min_premium_monthly <= :budget THEN 0
                ELSE 1
              END,
              min_premium_monthly ASC
            LIMIT 5";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':budget' => $budget,
        ':payment_type' => $paymentType
    ]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter by age (simple text matching for now)
    return array_filter($products, function($p) use ($age) {
        $ageRange = strtolower($p['age_range']);
        // If age range contains the age number, or contains "days" or "months" (covers all ages)
        return str_contains($ageRange, (string)$age) || 
               str_contains($ageRange, 'days') || 
               str_contains($ageRange, 'months') ||
               str_contains($ageRange, 'all ages');
    });
}

function findClosestProducts($db, $age, $budget, $paymentType) {
    // Relax payment type requirement but keep VUL and Traditional only
    $sql = "SELECT id, product_name, category, sub_category, age_range, min_premium_monthly, payment_type, description, primer_file 
            FROM products 
            WHERE (is_active = 1 OR is_active IS NULL)
              AND category IN ('VUL', 'Traditional Life Insurance')
              AND min_premium_monthly <= :budget_max
            ORDER BY 
              CASE WHEN payment_type = :payment_type THEN 0 ELSE 1 END,
              ABS(min_premium_monthly - :budget) ASC
            LIMIT 5";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':budget' => $budget,
        ':budget_max' => $budget * 1.5, // Allow 50% over budget
        ':payment_type' => $paymentType
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPopularProducts($db) {
    $sql = "SELECT id, product_name, category, sub_category, age_range, min_premium_monthly, payment_type, description, primer_file 
            FROM products 
            WHERE (is_active = 1 OR is_active IS NULL)
              AND category IN ('VUL', 'Traditional Life Insurance')
            ORDER BY 
              CASE category
                WHEN 'VUL' THEN 1
                WHEN 'Traditional Life Insurance' THEN 2
                ELSE 3
              END,
              min_premium_monthly ASC
            LIMIT 5";
    
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsByCategory($db, $category, $age, $budget) {
    // Only allow VUL and Traditional
    if (!in_array($category, ['VUL', 'Traditional Life Insurance'])) {
        return [];
    }
    
    $sql = "SELECT id, product_name, category, sub_category, age_range, min_premium_monthly, payment_type, description, primer_file 
            FROM products 
            WHERE (is_active = 1 OR is_active IS NULL)
              AND category = :category";
    
    if ($budget) {
        $sql .= " AND min_premium_monthly <= :budget";
    }
    
    $sql .= " ORDER BY min_premium_monthly ASC LIMIT 5";
    
    $stmt = $db->prepare($sql);
    $params = [':category' => $category];
    if ($budget) {
        $params[':budget'] = $budget;
    }
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProgress($step) {
    $progressMap = [
        'start' => 0,
        'ask_age' => 0,
        'ask_budget' => 33,
        'ask_payment_type' => 66,
        'recommend' => 100
    ];
    
    return $progressMap[$step] ?? 0;
}
?>
