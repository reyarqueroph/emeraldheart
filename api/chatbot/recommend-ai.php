<?php
/**
 * AI-Powered Product Recommendation Chatbot
 * Features:
 * - Reads Product Primer PDFs
 * - Provides accurate answers based on PDF content
 * - Intelligent product matching
 * - Context-aware responses
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
$currentProduct = $data['current_product'] ?? null; // Get current product context

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message required']);
    exit;
}

// Preprocessing: Add spaces between common patterns to help extraction
// "67 years old1,000,000Limited" -> "67 years old 1,000,000 Limited"
$message = preg_replace('/(\d+\s*(?:years?\s*old|yo|yrs?))\s*(\d)/', '$1 $2', $message);
$message = preg_replace('/(\d+,\d+)([A-Za-z])/', '$1 $2', $message);
$message = preg_replace('/(\d+)([A-Za-z]{6,})/', '$1 $2', $message); // Add space before long words

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
// EXTRACT ALL INFORMATION FROM MESSAGE
// ═══════════════════════════════════════════════════════════

// Extract age from message - look for age-specific patterns first
// Pattern 1: "67 years old", "67 yo", "67 yrs", "67 year old"
if (preg_match('/\b(\d{1,2})\s*(years?\s*old|yo|yrs?|year\s*old)\b/i', $message, $m)) {
    $extractedAge = intval($m[1]);
    if ($extractedAge >= 1 && $extractedAge <= 100) {
        $age = $extractedAge;
    }
}
// Pattern 2: "age 67", "age: 67", "aged 67"
elseif (preg_match('/\b(?:age[d:]?)\s*(\d{1,2})\b/i', $message, $m)) {
    $extractedAge = intval($m[1]);
    if ($extractedAge >= 1 && $extractedAge <= 100) {
        $age = $extractedAge;
    }
}

// Extract budget from message - look for numbers that could be budget amounts
// Pattern 1: Large numbers with commas "1,000,000" or "500,000"
if (preg_match('/[₱P]?\s*(\d{1,3}(?:,\d{3})+)\b/i', $message, $m)) {
    $extractedBudget = intval(str_replace(',', '', $m[1]));
    if ($extractedBudget >= 100) {
        $budget = $extractedBudget;
    }
}
// Pattern 2: Large numbers without commas (5+ digits) "1000000" or "500000"
elseif (preg_match('/[₱P]?\s*(\d{5,})\b/i', $message, $m)) {
    $extractedBudget = intval($m[1]);
    if ($extractedBudget >= 100) {
        $budget = $extractedBudget;
    }
}
// Pattern 3: Budget-related keywords with any number "pay 2500", "budget 5000"
elseif (preg_match('/\b(?:pay|budget|afford|spend)\s*[₱P]?\s*(\d[\d,]*)\b/i', $message, $m)) {
    $extractedBudget = intval(str_replace(',', '', $m[1]));
    if ($extractedBudget >= 100) {
        $budget = $extractedBudget;
    }
}
// Pattern 4: Standalone 4-digit numbers (likely budget) "5000", "2500"
elseif (preg_match('/\b(\d{4})\b/i', $message, $m)) {
    $extractedBudget = intval($m[1]);
    if ($extractedBudget >= 1000 && $extractedBudget <= 99999) {
        $budget = $extractedBudget;
    }
}

// Extract payment type from message
if (preg_match('/\b(regular|limited|single)\s*(?:pay|payment)?\b/i', $message, $m)) {
    $paymentType = ucfirst(strtolower($m[1]));
}

// ═══════════════════════════════════════════════════════════
// CHECK IF WE HAVE ALL INFORMATION
// ═══════════════════════════════════════════════════════════

$hasAllInfo = ($age !== null && $budget !== null && $paymentType !== null);

// If user provided all info in one message, skip to recommendations
if ($hasAllInfo && in_array($step, ['start', 'ask_age', 'ask_budget', 'ask_payment_type'])) {
    $products = findMatchingProducts($db, $age, $budget, $paymentType);
    
    $budgetLabel = $budget >= 100000 ? '₱' . number_format($budget) . '/month (Premium tier 💎)' : '₱' . number_format($budget) . '/month';
    
    $reply = "Perfect! I got everything I need from your message. Let me find the best matches... ✅\n\n";
    $reply .= "**Your client's profile:**\n";
    $reply .= "• Age: **{$age} years old**\n";
    $reply .= "• Budget: **{$budgetLabel}**\n";
    $reply .= "• Payment: **{$paymentType} Pay**\n\n";
    $reply .= "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    if (!empty($products)) {
        if ($budget >= 100000) {
            $reply .= "🌟 **Outstanding!** I found " . count($products) . " premium product" . (count($products) > 1 ? 's' : '') . " that are absolutely perfect for your high-net-worth client!\n\n";
            $reply .= "We're talking top-tier here - exceptional coverage, wealth accumulation features, and legacy planning benefits. Your client deserves the best, and these products deliver! Each one is specifically designed for clients who want premium protection and investment opportunities.\n\n";
        } else {
            $reply .= "🎯 **Excellent!** I found " . count($products) . " product" . (count($products) > 1 ? 's' : '') . " that match perfectly!\n\n";
        }
        $reply .= "Each product below is tailored to your client's needs. Click any card to see the full Product Primer with all the details!\n\n";
        $reply .= "Got questions? I'm right here - just ask!";
    } else {
        $reply .= "Hmm, I couldn't find exact matches. But let me show you the closest alternatives...\n\n";
        $products = findClosestProducts($db, $age, $budget, $paymentType);
        
        if (empty($products)) {
            $products = getPopularProducts($db);
            if ($budget >= 100000) {
                $reply .= "Here are our premium products that might interest your high-net-worth client. Take a look!";
            } else {
                $reply .= "Here are our most popular products - these are crowd favorites!";
            }
        } else {
            if ($budget >= 100000) {
                $reply .= "These premium products are excellent alternatives in your client's budget range. Check them out!";
            }
        }
    }
    
    $suggestions = ["Start over", "Show VUL", "Show Traditional", "Compare"];
    $nextStep = 'recommend';
    
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
        'progress' => 100,
        'intent' => 'complete_info'
    ]);
    exit;
}

// Check for greeting or restart
if (preg_match('/\b(hi|hello|hey|start|begin|new)\b/i', $msg) && $step !== 'recommend') {
    $step = 'start';
    $age = null;
    $budget = null;
    $paymentType = null;
}

// ═══════════════════════════════════════════════════════════
// DETECT INTENT - Enhanced AI Agent
// ═══════════════════════════════════════════════════════════

$intent = detectIntent($msg, $step);

// ═══════════════════════════════════════════════════════════
// AI AGENT - HANDLE ANY QUESTION
// ═══════════════════════════════════════════════════════════

// If currently viewing a product and asking a question, answer about that product
if ($currentProduct && preg_match('/\b(what|how|tell|explain|benefit|coverage|cost|premium|return|invest|age|eligible)\b/i', $msg)) {
    // Check if NOT asking about a different specific product
    $askingAboutDifferentProduct = false;
    $otherProductPatterns = [
        'pru\s*million\s*protect', 'pru\s*millionaire', 'pru\s*million\s*flex',
        'pru\s*wealth', 'pru\s*link', 'pru\s*health', 'pru\s*love',
        'pru\s*max', 'pru\s*lifetime', 'pru\s*term', 'pru\s*personal', 'pru\s*steady'
    ];
    
    foreach ($otherProductPatterns as $pattern) {
        if (preg_match("/{$pattern}/i", $msg)) {
            // Check if it's NOT the current product
            if (stripos($currentProduct['name'], str_replace(['pru\s*', '\s*'], ['pru', ' '], $pattern)) === false) {
                $askingAboutDifferentProduct = true;
                break;
            }
        }
    }
    
    // If asking about the current product (or no specific product mentioned)
    if (!$askingAboutDifferentProduct) {
        $answer = handleCurrentProductQuestion($db, $message, $msg, $currentProduct);
        echo json_encode([
            'success' => true,
            'reply' => $answer['reply'],
            'products' => $answer['products'] ?? [],
            'suggestions' => $answer['suggestions'],
            'context' => [
                'step' => 'browse',
                'age' => $age,
                'budget' => $budget,
                'payment_type' => $paymentType
            ],
            'progress' => 0,
            'intent' => 'current_product_question'
        ]);
        exit;
    }
}

// Check if this is a general question (not a recommendation flow)
if ($intent === 'general_question' || $intent === 'product_question') {
    $answer = handleGeneralQuestion($db, $message, $msg, $age, $budget);
    echo json_encode([
        'success' => true,
        'reply' => $answer['reply'],
        'products' => $answer['products'] ?? [],
        'suggestions' => $answer['suggestions'],
        'context' => [
            'step' => 'browse',
            'age' => $age,
            'budget' => $budget,
            'payment_type' => $paymentType
        ],
        'progress' => 0,
        'intent' => $intent
    ]);
    exit;
}

// Check if asking about a SPECIFIC product
if ($intent === 'specific_product_question') {
    $answer = handleSpecificProductQuestion($db, $message, $msg);
    echo json_encode([
        'success' => true,
        'reply' => $answer['reply'],
        'products' => $answer['products'] ?? [],
        'suggestions' => $answer['suggestions'],
        'context' => [
            'step' => 'browse',
            'age' => $age,
            'budget' => $budget,
            'payment_type' => $paymentType
        ],
        'progress' => 0,
        'intent' => $intent
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════
// HANDLE DIFFERENT INTENTS
// ═══════════════════════════════════════════════════════════

switch ($intent) {
    case 'show_vul':
        $products = getProductsByCategory($db, 'VUL', $age, $budget);
        $reply = "Absolutely! VUL products are fantastic. Let me tell you about them. 😊\n\n";
        $reply .= "So VUL stands for Variable Unit-Linked Insurance. Think of it like this - you're getting life insurance protection AND an investment account all rolled into one. Pretty neat, right?\n\n";
        $reply .= "Here's how it works: Part of your premium goes to your life insurance coverage, and the other part? That goes into investment funds. These funds can grow based on how the market performs. So if the market does well, your investment grows. It's like having your money work for you while you're protected!\n\n";
        $reply .= "**Who's this perfect for?**\n";
        $reply .= "I usually recommend VUL to clients who want more than just insurance. If your client is comfortable with some market risk and wants their money to potentially grow significantly over time, this is the way to go. It's especially great for younger clients who have time on their side - the longer the investment period, the better the potential returns.\n\n";
        
        if (!empty($products)) {
            if ($budget) {
                $reply .= "Based on your client's budget of ₱" . number_format($budget) . "/month, here are the VUL products they can afford. I've sorted them to show you the best options first. Click any card to see all the details!";
            } else {
                $reply .= "Here are all our VUL products. Take a look and let me know if any catch your eye!";
            }
        } else {
            if ($budget) {
                $reply .= "Hmm, I don't see any VUL products within the ₱" . number_format($budget) . "/month budget right now. Want to check out our Traditional products instead? Or we could look at VUL products if we adjust the budget a bit?";
            } else {
                $reply .= "Looks like we don't have VUL products available at the moment. But hey, our Traditional products are really solid too. Want to take a look at those?";
            }
        }
        
        $suggestions = ["Show Traditional", "Compare products", "Ask a question"];
        $nextStep = 'browse';
        break;
        
    case 'show_traditional':
        $products = getProductsByCategory($db, 'Traditional Life Insurance', $age, $budget);
        $reply = "Great choice! Traditional life insurance - you really can't go wrong with this one. 🛡️\n\n";
        $reply .= "Let me explain what makes Traditional so reliable. It's straightforward, no surprises. Your client pays a fixed premium, and they get guaranteed life insurance coverage plus guaranteed cash value growth. No market ups and downs to worry about.\n\n";
        $reply .= "Think of it like a savings account that also protects your family. The cash value grows at a guaranteed rate - not as high as VUL potentially, but it's steady and predictable. And here's the best part: your client can borrow against that cash value if they ever need it. It's their money, after all!\n\n";
        $reply .= "**I usually recommend Traditional to clients who:**\n";
        $reply .= "Value peace of mind over high returns. If your client wants to know exactly what they're getting, with no surprises, this is it. It's perfect for conservative investors, people nearing retirement, or anyone who just wants simple, reliable protection.\n\n";
        
        if (!empty($products)) {
            if ($budget) {
                $reply .= "For a budget of ₱" . number_format($budget) . "/month, here are the Traditional products that fit perfectly. Each one offers solid, guaranteed protection. Take a look!";
            } else {
                $reply .= "Here are all our Traditional products. Solid, reliable, and time-tested. Check them out!";
            }
        } else {
            if ($budget) {
                $reply .= "Hmm, I'm not seeing Traditional products in the ₱" . number_format($budget) . "/month range right now. How about we look at VUL products instead? Or we could adjust the budget to see what's available?";
            } else {
                $reply .= "Looks like Traditional products aren't available at the moment. But our VUL products are excellent too - want to check those out?";
            }
        }
        
        $suggestions = ["Show VUL", "Compare products", "Ask a question"];
        $nextStep = 'browse';
        break;
        
    case 'compare':
        $reply = generateComparison();
        $suggestions = ["Show VUL", "Show Traditional", "Start recommendation"];
        $nextStep = 'browse';
        break;
        
    case 'product_question':
        // Answer specific questions about products
        $answer = answerProductQuestion($db, $msg);
        $reply = $answer['reply'];
        $products = $answer['products'];
        $suggestions = ["Tell me more", "Show similar products", "Start recommendation"];
        $nextStep = 'browse';
        break;
        
    default:
        // Follow the step-by-step flow
        switch ($step) {
            case 'start':
                $reply = "Hey! 👋 Great to see you here. I'm your eHeart advisor - think of me as your personal insurance consultant.\n\n";
                $reply .= "So, what brings you in today? I can help you in a couple of ways:\n\n";
                $reply .= "**Option 1: Quick Match** 🎯\n";
                $reply .= "Just answer 3 quick questions about your client, and I'll find the perfect products for them. Super fast!\n\n";
                $reply .= "**Option 2: Browse & Learn** 📚\n";
                $reply .= "Want to explore? I can show you our VUL products (great for growth), Traditional products (guaranteed and stable), or answer any questions you have.\n\n";
                $reply .= "**Let's get started!**\nTell me - how old is your client?";
                
                $suggestions = ["25", "35", "45", "55"];
                $nextStep = 'ask_age';
                break;
                
            case 'ask_age':
                if (preg_match('/\b(\d{1,2})\b/', $message, $m)) {
                    $age = intval($m[1]);
                    
                    if ($age < 1 || $age > 100) {
                        $reply = "Hmm, that doesn't look right. Could you double-check that age for me? I need something between 1 and 100 years old.";
                        $suggestions = ["25", "35", "45", "55"];
                        $nextStep = 'ask_age';
                    } else {
                        $reply = "Perfect! ✅ So we're looking at a **{$age}-year-old client**.\n\n";
                        $reply .= "Alright, next question - what's your client's monthly budget for insurance premiums? Just give me a number, and don't worry if it's approximate. We can always adjust later!\n\n";
                        $reply .= "_(For example: 2000, 5000, 10000, or even higher amounts like 250000 for our premium products)_";
                        
                        $suggestions = ["1000", "2500", "5000", "250000"];
                        $nextStep = 'ask_budget';
                    }
                } else {
                    $reply = "I didn't quite catch that age. Could you give me just the number? Like 25, 35, or 50?";
                    $suggestions = ["25", "35", "45", "55"];
                    $nextStep = 'ask_age';
                }
                break;
                
            case 'ask_budget':
                if (preg_match('/[₱P]?\s*(\d[\d,]*)\b/', $message, $m)) {
                    $budget = intval(str_replace(',', '', $m[1]));
                    
                    if ($budget < 100) {
                        $reply = "Hmm, that's a bit too low for our products. The minimum is around ₱100/month. What's a realistic monthly budget we're working with?";
                        $suggestions = ["1000", "2500", "5000", "10000"];
                        $nextStep = 'ask_budget';
                    } else {
                        $budgetLabel = $budget >= 100000 ? '₱' . number_format($budget) . '/month (Premium tier! 💎)' : '₱' . number_format($budget) . '/month';
                        
                        $reply = "Excellent! ✅ Got it.\n\n";
                        $reply .= "**So here's what we have so far:**\n";
                        $reply .= "• Client age: **{$age} years old**\n";
                        $reply .= "• Monthly budget: **{$budgetLabel}**\n\n";
                        
                        if ($budget >= 100000) {
                            $reply .= "Wow! Okay, with this kind of budget, your client is looking at our **premium tier products**. We're talking PRUMillion Protect, PRUMillion Flex, maybe even PRU Millionaire. These are top-shelf products with exceptional coverage and benefits. Your client is in good hands! 🌟\n\n";
                        }
                        
                        $reply .= "Last question, I promise! How does your client want to pay?\n\n";
                        $reply .= "**Regular Pay** - Ongoing premiums throughout the policy. Most affordable monthly option.\n\n";
                        $reply .= "**Limited Pay** - Pay for a set period (like 5 or 10 years), then you're done. Higher monthly, but finite.\n\n";
                        $reply .= "**Single Pay** - One big payment upfront, and you're covered for life. Highest initial cost, but no more payments ever.\n\n";
                        $reply .= "Which one works best for your client?";
                        
                        $suggestions = ["Regular", "Limited", "Single"];
                        $nextStep = 'ask_payment_type';
                    }
                } else {
                    $reply = "I need the budget as a number. For example: 2000, 5000, 10000, or 250000 for premium products. What's the monthly budget?";
                    $suggestions = ["1000", "2500", "5000", "250000"];
                    $nextStep = 'ask_budget';
                }
                break;
                
            case 'ask_payment_type':
                if (preg_match('/\b(regular|limited|single)\b/i', $message, $m)) {
                    $paymentType = ucfirst(strtolower($m[1]));
                    
                    $products = findMatchingProducts($db, $age, $budget, $paymentType);
                    
                    $budgetLabel = $budget >= 100000 ? '₱' . number_format($budget) . '/month (Premium tier 💎)' : '₱' . number_format($budget) . '/month';
                    
                    $reply = "Perfect! Alright, let me pull up the best matches for you... ✅\n\n";
                    $reply .= "**Here's your client's profile:**\n";
                    $reply .= "• Age: **{$age} years old**\n";
                    $reply .= "• Budget: **{$budgetLabel}**\n";
                    $reply .= "• Payment: **{$paymentType} Pay**\n\n";
                    $reply .= "━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                    
                    if (!empty($products)) {
                        if ($budget >= 100000) {
                            $reply .= "🌟 **Fantastic!** I found " . count($products) . " premium product" . (count($products) > 1 ? 's' : '') . " that are perfect for your high-net-worth client!\n\n";
                            $reply .= "These are our absolute best offerings - we're talking exceptional coverage, wealth accumulation features, and legacy planning benefits. Your client is getting the VIP treatment here! Each product below is designed for clients who want the best protection and investment opportunities.\n\n";
                        } else {
                            $reply .= "🎯 **Excellent!** I found " . count($products) . " product" . (count($products) > 1 ? 's' : '') . " that match perfectly!\n\n";
                            $reply .= "I've included both VUL and Traditional options so you can see what works best for your client's goals. ";
                        }
                        $reply .= "Click any card below to see the full Product Primer with all the details, benefits, and coverage information.\n\n";
                        $reply .= "Questions about any of these? Just ask - I'm here to help!";
                    } else {
                        $reply .= "Hmm, I couldn't find exact matches with those specific criteria. But don't worry! Let me show you the closest alternatives...\n\n";
                        $products = findClosestProducts($db, $age, $budget, $paymentType);
                        
                        if (!empty($products)) {
                            if ($budget >= 100000) {
                                $reply .= "These premium products are in your client's budget range. Some might have slightly different payment terms, but they're all excellent choices for high-net-worth individuals. Take a look!";
                            } else {
                                $reply .= "These products are close to what you're looking for. Some might be slightly above budget or have different payment terms, but they're definitely worth considering!";
                            }
                        } else {
                            $reply .= "Let me show you our most popular products instead. These are our best-sellers that work great for many clients:";
                            $products = getPopularProducts($db);
                        }
                    }
                    
                    $suggestions = ["Start over", "Show VUL", "Show Traditional", "Ask a question"];
                    $nextStep = 'recommend';
                    
                } else {
                    $reply = "I need you to pick one of the payment types. Which one works for your client?\n\n**Regular**, **Limited**, or **Single**?";
                    $suggestions = ["Regular", "Limited", "Single"];
                    $nextStep = 'ask_payment_type';
                }
                break;
                
            case 'recommend':
            case 'browse':
                if (preg_match('/\b(start over|restart|new|again)\b/i', $msg)) {
                    $age = null;
                    $budget = null;
                    $paymentType = null;
                    $nextStep = 'start';
                    
                    $reply = "No problem! Let's start fresh. 🔄\n\n";
                    $reply .= "Alright, from the top - **how old is your client?**";
                    $suggestions = ["25", "35", "45", "55"];
                } else {
                    $reply = "I'm still here to help! What would you like to do?\n\n";
                    $reply .= "You can:\n";
                    $reply .= "• **Start a new recommendation** - I'll ask you 3 quick questions\n";
                    $reply .= "• **Browse products** - Check out VUL or Traditional products\n";
                    $reply .= "• **Ask me anything** - Questions about coverage, benefits, how it works, etc.\n";
                    $reply .= "• **Compare products** - See VUL vs Traditional side-by-side\n\n";
                    $reply .= "What sounds good to you?";
                    
                    $suggestions = ["Start over", "Show VUL", "Show Traditional", "Compare"];
                }
                break;
        }
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
    'progress' => getProgress($nextStep),
    'intent' => $intent
]);

// ═══════════════════════════════════════════════════════════
// HELPER FUNCTIONS
// ═══════════════════════════════════════════════════════════

function detectIntent($msg, $currentStep) {
    // Specific product questions - mentions a product name
    if (preg_match('/\b(pru\s*million|pru\s*millionaire|pru\s*wealth|pru\s*link|pru\s*health|pru\s*love|pru\s*max|pru\s*lifetime|pru\s*term|pru\s*personal|pru\s*steady)\b/i', $msg)) {
        return 'specific_product_question';
    }
    
    // General questions about insurance, Pru Life UK, or products
    if (preg_match('/\b(what|how|why|when|where|who|can|does|is|are|tell|explain|describe|define)\b/i', $msg)) {
        // Check if it's about specific topics
        if (preg_match('/\b(pru\s*life|prudential|company|about|history|contact|office|branch)\b/i', $msg)) {
            return 'general_question'; // About Pru Life UK
        }
        if (preg_match('/\b(benefit|feature|coverage|rider|premium|claim|policy|insurance|protect|invest|save|death|disability|illness|hospital|accident)\b/i', $msg)) {
            return 'product_question'; // About insurance/products (general)
        }
        if (preg_match('/\b(vul|traditional|variable|unit|linked|whole|life|term|endowment)\b/i', $msg)) {
            return 'product_question'; // About product types
        }
    }
    
    // Show VUL products
    if (preg_match('/\b(show|view|see|browse|list)\b.*\b(vul|variable)\b/i', $msg)) {
        return 'show_vul';
    }
    
    // Show Traditional products
    if (preg_match('/\b(show|view|see|browse|list)\b.*\b(traditional|trad)\b/i', $msg)) {
        return 'show_traditional';
    }
    
    // Compare products
    if (preg_match('/\b(compare|difference|vs|versus|better|choose)\b/i', $msg)) {
        return 'compare';
    }
    
    // Default to current step
    return $currentStep;
}

function findMatchingProducts($db, $age, $budget, $paymentType) {
    // For high budgets (>100k), show premium products that match payment type
    // For normal budgets, show products within budget
    $isHighBudget = $budget >= 100000;
    
    if ($isHighBudget) {
        // Show premium products sorted by premium (highest first) that match payment type
        $sql = "SELECT id, product_name, category, sub_category, age_range, min_premium_monthly, payment_type, description, primer_file 
                FROM products 
                WHERE (is_active = 1 OR is_active IS NULL)
                  AND category IN ('VUL', 'Traditional Life Insurance')
                  AND min_premium_monthly <= :budget
                  AND payment_type = :payment_type
                ORDER BY min_premium_monthly DESC
                LIMIT 5";
    } else {
        // Show affordable products sorted by premium (lowest first)
        $sql = "SELECT id, product_name, category, sub_category, age_range, min_premium_monthly, payment_type, description, primer_file 
                FROM products 
                WHERE (is_active = 1 OR is_active IS NULL)
                  AND category IN ('VUL', 'Traditional Life Insurance')
                  AND min_premium_monthly <= :budget
                  AND payment_type = :payment_type
                ORDER BY min_premium_monthly ASC
                LIMIT 5";
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':budget' => $budget,
        ':payment_type' => $paymentType
    ]);
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter by age if age is provided
    if ($age && !empty($results)) {
        $results = array_filter($results, function($product) use ($age) {
            return checkAgeEligibility($product['age_range'], $age);
        });
        $results = array_values($results); // Re-index array
    }
    
    return $results;
}

function findClosestProducts($db, $age, $budget, $paymentType) {
    // If no exact match, be more flexible with payment type and show closest matches
    $isHighBudget = $budget >= 100000;
    
    if ($isHighBudget) {
        // For high budgets, show premium products even if payment type doesn't match exactly
        $sql = "SELECT id, product_name, category, sub_category, age_range, min_premium_monthly, payment_type, description, primer_file 
                FROM products 
                WHERE (is_active = 1 OR is_active IS NULL)
                  AND category IN ('VUL', 'Traditional Life Insurance')
                  AND min_premium_monthly >= :budget_min
                  AND min_premium_monthly <= :budget_max
                ORDER BY 
                  CASE WHEN payment_type = :payment_type THEN 0 ELSE 1 END,
                  min_premium_monthly DESC
                LIMIT 5";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':budget_min' => $budget * 0.2,  // Show products from 20% of budget
            ':budget_max' => $budget * 1.5,  // Up to 150% of budget
            ':payment_type' => $paymentType
        ]);
    } else {
        // For normal budgets, show products slightly above budget
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
            ':budget_max' => $budget * 1.5,
            ':payment_type' => $paymentType
        ]);
    }
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter by age if age is provided
    if ($age && !empty($results)) {
        $results = array_filter($results, function($product) use ($age) {
            return checkAgeEligibility($product['age_range'], $age);
        });
        $results = array_values($results); // Re-index array
    }
    
    return $results;
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
              END,
              min_premium_monthly ASC
            LIMIT 5";
    
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsByCategory($db, $category, $age, $budget) {
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
    
    $sql .= " ORDER BY min_premium_monthly ASC LIMIT 10";
    
    $stmt = $db->prepare($sql);
    $params = [':category' => $category];
    if ($budget) {
        $params[':budget'] = $budget;
    }
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateComparison() {
    $reply = "Ah, the classic question! VUL vs Traditional - let me break this down for you in plain English. ☕\n\n";
    
    $reply .= "**VUL (Variable Unit-Linked Insurance)** 📊\n";
    $reply .= "Think of VUL as the \"growth-oriented\" choice. You're not just buying insurance - you're also investing. Part of your premium goes into investment funds that can grow based on market performance.\n\n";
    $reply .= "**The good news?** If the market does well, your money can grow significantly. I've seen clients build serious wealth with VUL over 15-20 years.\n\n";
    $reply .= "**The catch?** Market risk. If the market dips, your investment value can drop too. But hey, the insurance protection is always there no matter what.\n\n";
    
    $reply .= "**Traditional Life Insurance** 🛡️\n";
    $reply .= "This is your \"sleep well at night\" option. Everything is guaranteed - coverage, cash value growth, premiums. Zero market risk, zero surprises.\n\n";
    $reply .= "**The good news?** Total peace of mind. You know exactly what you're getting. The cash value grows steadily at a guaranteed rate.\n\n";
    $reply .= "**The trade-off?** Lower growth potential compared to VUL. You're trading high returns for stability and predictability.\n\n";
    
    $reply .= "**So which one should your client choose?**\n";
    $reply .= "Here's how I usually advise:\n\n";
    $reply .= "• **Go with VUL if:** Your client is younger (under 45), comfortable with market risk, wants wealth building, and has a long-term horizon (10+ years).\n\n";
    $reply .= "• **Go with Traditional if:** Your client values stability, is nearing retirement, prefers guaranteed returns, or just wants simple, predictable protection.\n\n";
    $reply .= "Honestly? There's no wrong choice - it really depends on your client's goals and risk tolerance. Want me to show you specific products in either category?";
    
    return $reply;
}

function answerProductQuestion($db, $question) {
    $reply = '';
    $products = [];
    
    // Benefits question
    if (preg_match('/\b(benefit|advantage|good|why)\b/i', $question)) {
        if (str_contains($question, 'vul')) {
            $reply = "Ah, VUL products! These are really popular with our agents. Here's why clients love them:\n\n";
            $reply .= "**The Big Benefits:**\n";
            $reply .= "• **Dual Purpose** - You're not just buying insurance, you're also investing. Two birds, one stone!\n";
            $reply .= "• **Flexibility** - Life changes? No problem. You can adjust your premiums and coverage as needed.\n";
            $reply .= "• **Growth Potential** - Your money is invested in funds that can grow significantly over time.\n";
            $reply .= "• **Multiple Fund Options** - Choose from equity, balanced, or bond funds based on risk appetite.\n";
            $reply .= "• **Wealth Transfer** - Build wealth that you can pass on to your loved ones.\n\n";
            $reply .= "Want to see which VUL products we have available? I can show you options that fit different budgets.";
            $products = getProductsByCategory($db, 'VUL', null, null);
        } else if (str_contains($question, 'traditional') || str_contains($question, 'trad')) {
            $reply = "Traditional life insurance - the tried and true choice! Here's what makes it special:\n\n";
            $reply .= "**The Key Benefits:**\n";
            $reply .= "• **Guaranteed Protection** - Fixed death benefit that won't change. Your family is covered, period.\n";
            $reply .= "• **Guaranteed Cash Value** - Your savings grow at a guaranteed rate. No market surprises.\n";
            $reply .= "• **Stability** - Sleep well knowing your returns aren't affected by market ups and downs.\n";
            $reply .= "• **Predictable Premiums** - Same payment amount throughout. Easy to budget.\n";
            $reply .= "• **Peace of Mind** - Perfect for conservative clients who value certainty.\n\n";
            $reply .= "Interested in seeing our Traditional products? I can show you what's available.";
            $products = getProductsByCategory($db, 'Traditional Life Insurance', null, null);
        } else {
            $reply = "Great question! Both VUL and Traditional products have amazing benefits, but they serve different needs.\n\n";
            $reply .= "**In a nutshell:**\n";
            $reply .= "• VUL = Protection + Investment growth potential\n";
            $reply .= "• Traditional = Protection + Guaranteed savings\n\n";
            $reply .= "Tell me more about your client - are they more interested in growth potential or guaranteed returns? That'll help me point you in the right direction!";
        }
    }
    // Coverage question
    elseif (preg_match('/\b(coverage|cover|include|protect)\b/i', $question)) {
        $reply = "Good question! Let me walk you through what our products typically cover:\n\n";
        $reply .= "**Standard Coverage (Most Products):**\n";
        $reply .= "• **Death Benefit** - The main protection. Your client's beneficiaries receive a lump sum if something happens.\n";
        $reply .= "• **Total & Permanent Disability (TPD)** - If your client can't work due to disability, they're covered.\n";
        $reply .= "• **Critical Illness** - Major illnesses like cancer, heart attack, stroke - we've got them covered.\n";
        $reply .= "• **Accidental Death Benefit** - Extra coverage if death is due to an accident.\n\n";
        $reply .= "**Plus, depending on the product:**\n";
        $reply .= "• Hospital income benefits\n";
        $reply .= "• Waiver of premium (if disabled, premiums are waived)\n";
        $reply .= "• Accelerated benefits for terminal illness\n\n";
        $reply .= "Each product has its own specific coverage details in the Product Primer. Want me to show you a specific product so you can see the exact coverage?";
    }
    // How it works
    elseif (preg_match('/\b(how|work|process|explain)\b/i', $question)) {
        $reply = "Let me explain how it works - it's actually pretty straightforward!\n\n";
        $reply .= "**The Simple Process:**\n\n";
        $reply .= "1️⃣ **Choose the Right Product**\n";
        $reply .= "Based on your client's age, budget, and goals. That's what I'm here to help with!\n\n";
        $reply .= "2️⃣ **Set Up the Payment Plan**\n";
        $reply .= "Regular (ongoing), Limited (pay for X years), or Single (one-time payment). Your client picks what works for their budget.\n\n";
        $reply .= "3️⃣ **Coverage Starts**\n";
        $reply .= "Once approved and first premium is paid, your client is protected. Simple as that!\n\n";
        $reply .= "4️⃣ **Value Builds Over Time**\n";
        $reply .= "Whether it's guaranteed cash value (Traditional) or investment growth (VUL), their policy builds value.\n\n";
        $reply .= "5️⃣ **Benefits When Needed**\n";
        $reply .= "If something happens, beneficiaries file a claim and receive the benefits. We make it as smooth as possible during difficult times.\n\n";
        $reply .= "Want to start finding the right product for your client? I can ask you a few quick questions!";
    }
    // Premium/payment question
    elseif (preg_match('/\b(premium|payment|cost|price|afford)\b/i', $question)) {
        $reply = "Ah, the budget question - always important! Here's the deal:\n\n";
        $reply .= "**Premium Flexibility:**\n";
        $reply .= "Our products start from as low as ₱1,000/month, going up depending on coverage and age. The beauty is we have options for different budgets!\n\n";
        $reply .= "**Payment Options:**\n";
        $reply .= "• **Regular Pay** - Pay throughout the policy term (most affordable monthly)\n";
        $reply .= "• **Limited Pay** - Pay for 5, 10, or 15 years, then you're done (higher monthly but finite)\n";
        $reply .= "• **Single Pay** - One payment and you're covered for life (highest upfront but no more payments)\n\n";
        $reply .= "**What affects the premium?**\n";
        $reply .= "• Client's age (younger = lower premium)\n";
        $reply .= "• Coverage amount (more coverage = higher premium)\n";
        $reply .= "• Product type (VUL vs Traditional)\n";
        $reply .= "• Payment term (longer term = lower monthly)\n\n";
        $reply .= "Tell me your client's age and budget, and I'll show you exactly what they can get!";
    }
    // Age/eligibility question
    elseif (preg_match('/\b(age|old|young|eligible|qualify)\b/i', $question)) {
        $reply = "Good question! Age is definitely a factor. Here's the scoop:\n\n";
        $reply .= "**Age Requirements:**\n";
        $reply .= "Most of our products cover ages from **7 days old to 70 years old**. Yes, you can even insure newborns!\n\n";
        $reply .= "**Why age matters:**\n";
        $reply .= "• **Younger clients** - Lower premiums, longer coverage period, more time for investment growth (if VUL)\n";
        $reply .= "• **Older clients** - Higher premiums, but still very much insurable! We have products specifically designed for mature clients.\n\n";
        $reply .= "**Pro tip:** The younger your client starts, the better the rates and the more value they build over time. But it's never too late to get protected!\n\n";
        $reply .= "How old is your client? I can show you products that are perfect for their age group.";
    }
    else {
        $reply = "I'm here to help! I can answer questions about:\n\n";
        $reply .= "💡 **Product Benefits** - \"What are the benefits of VUL?\"\n";
        $reply .= "🛡️ **Coverage Details** - \"What's covered?\"\n";
        $reply .= "⚙️ **How It Works** - \"How does insurance work?\"\n";
        $reply .= "💰 **Premiums & Payments** - \"How much does it cost?\"\n";
        $reply .= "👤 **Age & Eligibility** - \"What ages are covered?\"\n";
        $reply .= "📊 **Product Comparisons** - \"VUL vs Traditional?\"\n\n";
        $reply .= "Or if you're ready, I can help you find the perfect product for your client right now! Just tell me their age and budget.";
    }
    
    return ['reply' => $reply, 'products' => $products];
}

function getProgress($step) {
    $progressMap = [
        'start' => 0,
        'ask_age' => 0,
        'ask_budget' => 33,
        'ask_payment_type' => 66,
        'recommend' => 100,
        'browse' => 100
    ];
    
    return $progressMap[$step] ?? 0;
}

// ═══════════════════════════════════════════════════════════
// AI AGENT - CURRENT PRODUCT CONTEXT HANDLER
// ═══════════════════════════════════════════════════════════

function handleCurrentProductQuestion($db, $originalMessage, $msg, $currentProduct) {
    $reply = '';
    $products = [$currentProduct]; // Show the current product card
    $suggestions = ["Tell me more", "Show similar products", "Compare with others", "Find other products"];
    
    $productName = $currentProduct['name'];
    $category = $currentProduct['category'];
    $minPremium = number_format($currentProduct['min_premium']);
    $paymentType = $currentProduct['payment_type'];
    $ageRange = $currentProduct['age_range'];
    $description = $currentProduct['description'] ?? '';
    
    // ═══════════════════════════════════════════════════════════
    // USE OPENAI TO ANSWER BASED ON PDF CONTENT
    // ═══════════════════════════════════════════════════════════
    
    // Get product ID to find PDF file
    $productId = $currentProduct['id'];
    
    // Fetch full product details including primer_file
    $sql = "SELECT primer_file FROM products WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $productId]);
    $productData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($productData && !empty($productData['primer_file'])) {
        // PDF file exists, use OpenAI to answer
        $pdfFile = $productData['primer_file'];
        $pdfPath = dirname(__DIR__, 2) . '/uploads/products/' . $pdfFile;
        
        if (file_exists($pdfPath)) {
            // Load required libraries
            require_once dirname(__DIR__) . '/lib/PdfTextExtractor.php';
            require_once dirname(__DIR__) . '/lib/OpenAIHelper.php';
            
            try {
                // Extract PDF text
                $pdfContent = PdfTextExtractor::extractTextWithCache($pdfPath);
                
                // Use OpenAI to answer the question
                $openai = new OpenAIHelper();
                $result = $openai->askAboutProduct($originalMessage, $pdfContent, [
                    'id' => $productId,
                    'name' => $productName,
                    'category' => $category,
                    'min_premium' => $currentProduct['min_premium'],
                    'payment_type' => $paymentType,
                    'age_range' => $ageRange,
                    'description' => $description
                ]);
                
                if ($result['success']) {
                    // OpenAI answered successfully
                    $reply = "**About {$productName}** (the product you're currently viewing):\n\n";
                    $reply .= $result['answer'];
                    $reply .= "\n\n📄 *All detailed information is in the Product Primer PDF you're viewing!*";
                    
                    return ['reply' => $reply, 'products' => $products, 'suggestions' => $suggestions];
                } else {
                    // OpenAI failed, use fallback answer
                    $reply = $result['answer']; // This contains the fallback message
                    return ['reply' => $reply, 'products' => $products, 'suggestions' => $suggestions];
                }
            } catch (Exception $e) {
                // Error occurred, fall through to manual responses below
                error_log("OpenAI Error: " . $e->getMessage());
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════════
    // FALLBACK: MANUAL RESPONSES (if OpenAI not available)
    // ═══════════════════════════════════════════════════════════
    
    // Determine what aspect they're asking about
    if (preg_match('/\b(benefit|coverage|cover|protect|include|what\s+does|what\s+are)\b/i', $msg)) {
        // Asking about benefits/coverage
        $reply = "Great question about **{$productName}** - the product you're currently viewing! Let me tell you about its benefits and coverage. 🛡️\n\n";
        
        if ($category === 'VUL') {
            $reply .= "**{$productName}** is a VUL (Variable Unit-Linked) product, which means you get:\n\n";
            $reply .= "**Protection Benefits:**\n";
            $reply .= "• Life insurance coverage for your beneficiaries\n";
            $reply .= "• Total & Permanent Disability (TPD) protection\n";
            $reply .= "• Critical illness coverage\n";
            $reply .= "• Accidental death benefit\n\n";
            $reply .= "**Investment Benefits:**\n";
            $reply .= "• Your premiums are invested in professionally managed funds\n";
            $reply .= "• Potential for significant growth based on market performance\n";
            $reply .= "• Fund switching options (equity, balanced, bond funds)\n";
            $reply .= "• Partial withdrawals allowed\n";
            $reply .= "• Flexibility to adjust coverage and premiums\n\n";
        } else {
            $reply .= "**{$productName}** is a Traditional Life Insurance product, which means you get:\n\n";
            $reply .= "**Protection Benefits:**\n";
            $reply .= "• Guaranteed life insurance coverage\n";
            $reply .= "• Total & Permanent Disability (TPD) protection\n";
            $reply .= "• Critical illness coverage\n";
            $reply .= "• Accidental death benefit\n\n";
            $reply .= "**Savings Benefits:**\n";
            $reply .= "• Guaranteed cash value growth\n";
            $reply .= "• Fixed, predictable returns\n";
            $reply .= "• Policy loan facility (borrow against cash value)\n";
            $reply .= "• Guaranteed maturity benefits\n";
            $reply .= "• No market risk - your money is safe\n\n";
        }
        
        if ($description) {
            $reply .= "**Additional Details:**\n{$description}\n\n";
        }
        
        $reply .= "All the detailed information is in the Product Primer PDF you're viewing right now! Scroll through it to see complete coverage details, illustrations, and examples.";
        
    } elseif (preg_match('/\b(premium|cost|price|afford|pay|expensive|cheap|how\s+much)\b/i', $msg)) {
        // Asking about premium/cost
        $reply = "Let me break down the pricing for **{$productName}** - the product you're currently viewing! 💰\n\n";
        $reply .= "**Minimum Premium:** ₱{$minPremium}/month\n";
        $reply .= "**Payment Type:** {$paymentType} Pay\n";
        $reply .= "**Age Range:** {$ageRange}\n\n";
        
        if ($paymentType === 'Regular') {
            $reply .= "**Regular Pay** means you pay premiums throughout the policy term. This gives you the most affordable monthly payment option.\n\n";
        } elseif ($paymentType === 'Limited') {
            $reply .= "**Limited Pay** means you pay for a set period (like 5, 10, or 15 years), then you're done! Higher monthly premium, but finite payment period.\n\n";
        } elseif ($paymentType === 'Single') {
            $reply .= "**Single Pay** means one payment and you're covered for life! Highest upfront cost, but no more payments ever.\n\n";
        }
        
        $reply .= "**What affects your actual premium?**\n";
        $reply .= "• Your age (younger = lower premium)\n";
        $reply .= "• Coverage amount you choose\n";
        $reply .= "• Riders you add\n";
        $reply .= "• Health condition\n\n";
        
        $reply .= "The ₱{$minPremium}/month is the starting point. Your actual premium will be calculated based on your specific situation.\n\n";
        $reply .= "Check the Product Primer PDF you're viewing for detailed premium illustrations and examples!";
        
    } elseif (preg_match('/\b(age|eligible|qualify|old|young|can\s+i)\b/i', $msg)) {
        // Asking about age eligibility
        $reply = "Good question about age eligibility for **{$productName}**! 👶👴\n\n";
        $reply .= "**Age Range:** {$ageRange}\n\n";
        
        $reply .= "This means:\n";
        $reply .= "• Minimum age: " . (preg_match('/(\d+)\s*(?:days?|months?|years?)?\s*(?:old)?\s*to/i', $ageRange, $m) ? $m[1] : "Check product details") . "\n";
        $reply .= "• Maximum age: " . (preg_match('/to\s*(\d+)\s*years?\s*old/i', $ageRange, $m) ? $m[1] . " years old" : "Check product details") . "\n\n";
        
        $reply .= "**Why age matters:**\n";
        $reply .= "The younger you start, the lower your premium! Plus, you get longer coverage and more time for your investment to grow";
        
        if ($category === 'VUL') {
            $reply .= " (especially important for VUL products)";
        }
        $reply .= ".\n\n";
        
        $reply .= "The Product Primer PDF you're viewing has detailed age-based illustrations showing exactly how premiums vary by age!";
        
    } elseif (preg_match('/\b(invest|return|grow|fund|performance|earn)\b/i', $msg)) {
        // Asking about investment/returns
        if ($category === 'VUL') {
            $reply = "Ah, asking about the investment side of **{$productName}**! 📈\n\n";
            $reply .= "As a VUL product, **{$productName}** offers investment opportunities:\n\n";
            $reply .= "**Investment Features:**\n";
            $reply .= "• Your premiums are invested in professionally managed funds\n";
            $reply .= "• Choose from equity, balanced, or bond funds\n";
            $reply .= "• Switch between funds as market conditions change\n";
            $reply .= "• Potential for significant long-term growth\n\n";
            $reply .= "**Historical Performance (Industry Average):**\n";
            $reply .= "• Equity funds: 8-15% annually\n";
            $reply .= "• Balanced funds: 6-10% annually\n";
            $reply .= "• Bond funds: 4-7% annually\n\n";
            $reply .= "**Important:** Past performance doesn't guarantee future results. Markets go up and down, but historically, long-term investors (10+ years) have seen strong returns.\n\n";
            $reply .= "The Product Primer PDF you're viewing has detailed fund performance charts and investment illustrations!";
        } else {
            $reply = "Good question about **{$productName}**! 🛡️\n\n";
            $reply .= "As a Traditional Life Insurance product, **{$productName}** offers:\n\n";
            $reply .= "**Guaranteed Returns:**\n";
            $reply .= "• Fixed interest rate (typically 2-4% annually)\n";
            $reply .= "• Guaranteed cash value growth\n";
            $reply .= "• No market risk - your money is safe\n";
            $reply .= "• Predictable, stable returns\n\n";
            $reply .= "**The Trade-off:**\n";
            $reply .= "Lower returns compared to VUL, but you get peace of mind and guaranteed growth. Perfect for conservative investors who value stability over high returns.\n\n";
            $reply .= "Check the Product Primer PDF you're viewing for guaranteed cash value tables and maturity benefit illustrations!";
        }
        
    } elseif (preg_match('/\b(how|work|process|explain|tell\s+me)\b/i', $msg)) {
        // Asking how it works
        $reply = "Let me explain how **{$productName}** works! 😊\n\n";
        
        if ($category === 'VUL') {
            $reply .= "**How VUL Works:**\n\n";
            $reply .= "1️⃣ **You Pay Premiums**\n";
            $reply .= "Your monthly premium is split into two parts:\n";
            $reply .= "• Part 1: Goes to your life insurance coverage (protection)\n";
            $reply .= "• Part 2: Goes into investment funds (growth)\n\n";
            
            $reply .= "2️⃣ **Your Money Gets Invested**\n";
            $reply .= "The investment portion is put into professionally managed funds. You choose the fund type based on your risk appetite:\n";
            $reply .= "• Equity funds (higher risk, higher potential returns)\n";
            $reply .= "• Balanced funds (medium risk)\n";
            $reply .= "• Bond funds (lower risk, stable returns)\n\n";
            
            $reply .= "3️⃣ **Your Investment Grows**\n";
            $reply .= "As the market performs, your investment value changes. Good market = growth! You can switch between funds as needed.\n\n";
            
            $reply .= "4️⃣ **You're Protected**\n";
            $reply .= "Throughout all this, you have life insurance coverage. If something happens, your beneficiaries get the death benefit.\n\n";
            
            $reply .= "5️⃣ **Flexibility**\n";
            $reply .= "You can make partial withdrawals, adjust premiums, or increase coverage as your life changes.\n\n";
        } else {
            $reply .= "**How Traditional Life Insurance Works:**\n\n";
            $reply .= "1️⃣ **You Pay Fixed Premiums**\n";
            $reply .= "You pay a fixed amount every month (or year). This amount never changes - totally predictable!\n\n";
            
            $reply .= "2️⃣ **You're Protected**\n";
            $reply .= "From day one, you have life insurance coverage. If something happens, your beneficiaries receive the death benefit.\n\n";
            
            $reply .= "3️⃣ **Cash Value Builds**\n";
            $reply .= "Part of your premium goes into a savings component that grows at a guaranteed rate. No market risk!\n\n";
            
            $reply .= "4️⃣ **Guaranteed Growth**\n";
            $reply .= "Your cash value grows steadily at a fixed interest rate. You know exactly what you'll get.\n\n";
            
            $reply .= "5️⃣ **Access Your Money**\n";
            $reply .= "You can borrow against your cash value if needed. It's your money, after all!\n\n";
        }
        
        $reply .= "The Product Primer PDF you're viewing has detailed diagrams and examples showing exactly how this product works!";
        
    } else {
        // General question about the product
        $reply = "You're currently viewing **{$productName}** - great choice! Let me give you a quick overview. 😊\n\n";
        
        $reply .= "**Product Type:** {$category}\n";
        $reply .= "**Minimum Premium:** ₱{$minPremium}/month\n";
        $reply .= "**Payment Type:** {$paymentType} Pay\n";
        $reply .= "**Age Range:** {$ageRange}\n\n";
        
        if ($category === 'VUL') {
            $reply .= "**What makes it special:**\n";
            $reply .= "This is a VUL (Variable Unit-Linked) product, which means you get both life insurance protection AND investment growth potential. Your premiums are split between coverage and investment funds that can grow based on market performance.\n\n";
            $reply .= "**Perfect for clients who:**\n";
            $reply .= "• Want their money to work harder\n";
            $reply .= "• Are comfortable with some market risk\n";
            $reply .= "• Have long-term financial goals\n";
            $reply .= "• Want flexibility and growth potential\n\n";
        } else {
            $reply .= "**What makes it special:**\n";
            $reply .= "This is a Traditional Life Insurance product, which means you get guaranteed protection with guaranteed cash value growth. No market risk, no surprises - just solid, reliable coverage.\n\n";
            $reply .= "**Perfect for clients who:**\n";
            $reply .= "• Value stability and predictability\n";
            $reply .= "• Want guaranteed returns\n";
            $reply .= "• Prefer conservative investments\n";
            $reply .= "• Want peace of mind\n\n";
        }
        
        if ($description) {
            $reply .= "**Product Description:**\n{$description}\n\n";
        }
        
        $reply .= "**Want to know more? Ask me about:**\n";
        $reply .= "• What are the benefits?\n";
        $reply .= "• How much does it cost?\n";
        $reply .= "• How does it work?\n";
        $reply .= "• What are the returns?\n";
        $reply .= "• Who is eligible?\n\n";
        $reply .= "Or just scroll through the Product Primer PDF you're viewing - it has all the details!";
    }
    
    return ['reply' => $reply, 'products' => $products, 'suggestions' => $suggestions];
}

// ═══════════════════════════════════════════════════════════
// AI AGENT - COMPREHENSIVE QUESTION HANDLER
// ═══════════════════════════════════════════════════════════

function handleGeneralQuestion($db, $originalMessage, $msg, $age, $budget) {
    $reply = '';
    $products = []; // Don't show products for general questions
    $suggestions = ["Show VUL", "Show Traditional", "Compare", "Find products for my client"];
    
    // ═══════════════════════════════════════════════════════════
    // PRU LIFE UK COMPANY QUESTIONS
    // ═══════════════════════════════════════════════════════════
    
    if (preg_match('/\b(pru\s*life|prudential|company|about|history)\b/i', $msg)) {
        $reply = "Great question! Let me tell you about Pru Life UK. 🏢\n\n";
        $reply .= "**Pru Life UK** is one of the leading life insurance companies in the Philippines. We're actually a joint venture between Prudential plc (one of the world's largest insurance groups from the UK) and Philippine companies.\n\n";
        $reply .= "**What makes us special:**\n";
        $reply .= "We've been serving Filipinos for decades, helping families protect their loved ones and build wealth for the future. We're known for our financial strength, innovative products, and commitment to our clients.\n\n";
        $reply .= "**Our mission?** Simple - to help every Filipino achieve financial security and peace of mind. Whether it's protecting your family, saving for retirement, or building wealth, we've got products designed for every life stage and goal.\n\n";
        $reply .= "Want to know more about our specific products? Just ask!";
        
        return ['reply' => $reply, 'products' => [], 'suggestions' => $suggestions];
    }
    
    // ═══════════════════════════════════════════════════════════
    // INSURANCE BASICS
    // ═══════════════════════════════════════════════════════════
    
    if (preg_match('/\b(what\s+is|define|explain)\s+(life\s+)?insurance\b/i', $msg)) {
        $reply = "Ah, the fundamentals! Let me explain life insurance in simple terms. 😊\n\n";
        $reply .= "**Life insurance** is basically a promise. You pay premiums (regular payments), and in return, the insurance company promises to pay a lump sum (called the death benefit) to your beneficiaries when you pass away.\n\n";
        $reply .= "**Why do people get it?**\n";
        $reply .= "Think about it - if something happens to you, how will your family pay the bills? The mortgage? Your kids' education? Life insurance ensures they're financially protected even when you're not around.\n\n";
        $reply .= "**But it's more than just death protection!**\n";
        $reply .= "Modern life insurance (like our VUL and Traditional products) also helps you:\n";
        $reply .= "• Build savings and wealth\n";
        $reply .= "• Prepare for retirement\n";
        $reply .= "• Cover critical illnesses\n";
        $reply .= "• Handle disabilities\n\n";
        $reply .= "It's really about financial security for you AND your loved ones. Want to see what products we have?";
        
        return ['reply' => $reply, 'suggestions' => $suggestions];
    }
    
    // ═══════════════════════════════════════════════════════════
    // BENEFITS & COVERAGE
    // ═══════════════════════════════════════════════════════════
    
    if (preg_match('/\b(benefit|coverage|cover|protect|include)\b/i', $msg)) {
        $reply = "Great question! Let me walk you through what our products typically cover. 🛡️\n\n";
        $reply .= "**Core Protection (All Products):**\n\n";
        $reply .= "**1. Death Benefit** 💔\n";
        $reply .= "This is the main one. If something happens to you, your beneficiaries receive a lump sum. This helps them pay for funeral costs, debts, living expenses, education - whatever they need.\n\n";
        $reply .= "**2. Total & Permanent Disability (TPD)** ♿\n";
        $reply .= "If you become permanently disabled and can't work, you're covered. The policy pays out so you can focus on recovery without financial stress.\n\n";
        $reply .= "**3. Critical Illness Coverage** 🏥\n";
        $reply .= "Major illnesses like cancer, heart attack, stroke, kidney failure - if you're diagnosed with any of these, you get a payout to help with treatment costs.\n\n";
        $reply .= "**Additional Benefits (Depending on Product):**\n";
        $reply .= "• Accidental death benefit (extra payout if death is from accident)\n";
        $reply .= "• Hospital income (daily cash while hospitalized)\n";
        $reply .= "• Waiver of premium (if disabled, no more premium payments needed)\n";
        $reply .= "• Accelerated benefits (early payout for terminal illness)\n\n";
        $reply .= "**Plus, for VUL products:**\n";
        $reply .= "• Investment growth potential\n";
        $reply .= "• Fund switching options\n";
        $reply .= "• Partial withdrawals\n\n";
        $reply .= "**For Traditional products:**\n";
        $reply .= "• Guaranteed cash value\n";
        $reply .= "• Policy loans available\n";
        $reply .= "• Guaranteed maturity benefits\n\n";
        $reply .= "Want to see specific products and their exact coverage? I can show you!";
        
        return ['reply' => $reply, 'suggestions' => $suggestions];
    }
    
    // ═══════════════════════════════════════════════════════════
    // PREMIUMS & PAYMENTS
    // ═══════════════════════════════════════════════════════════
    
    if (preg_match('/\b(premium|payment|cost|price|afford|expensive|cheap|how\s+much)\b/i', $msg)) {
        $reply = "Ah, the budget question - always important! Let me break down how premiums work. 💰\n\n";
        $reply .= "**What affects your premium?**\n\n";
        $reply .= "**1. Your Age** 🎂\n";
        $reply .= "Younger = lower premium. A 25-year-old pays way less than a 50-year-old for the same coverage. Why? Less risk for the insurance company.\n\n";
        $reply .= "**2. Coverage Amount** 📊\n";
        $reply .= "More coverage = higher premium. Want ₱5 million coverage instead of ₱1 million? You'll pay more.\n\n";
        $reply .= "**3. Product Type** 🎯\n";
        $reply .= "VUL products tend to have higher premiums than Traditional because they include investment. But you're getting more value!\n\n";
        $reply .= "**4. Payment Term** ⏰\n";
        $reply .= "• **Regular Pay** - Pay throughout the policy (lowest monthly)\n";
        $reply .= "• **Limited Pay** - Pay for 5, 10, or 15 years only (higher monthly, but finite)\n";
        $reply .= "• **Single Pay** - One payment and done (highest upfront)\n\n";
        $reply .= "**Our products start from as low as ₱250/month!**\n";
        $reply .= "Yes, really! We have options for every budget:\n";
        $reply .= "• Entry-level: ₱250 - ₱1,000/month\n";
        $reply .= "• Mid-range: ₱2,500 - ₱10,000/month\n";
        $reply .= "• Premium: ₱100,000+/month\n\n";
        $reply .= "Tell me your client's age and budget, and I'll show you exactly what they can get!";
        
        return ['reply' => $reply, 'suggestions' => ["25 years old, 5000 budget", "Show affordable products", "Show premium products"]];
    }
    
    // ═══════════════════════════════════════════════════════════
    // CLAIMS PROCESS
    // ═══════════════════════════════════════════════════════════
    
    if (preg_match('/\b(claim|file|process|how\s+to\s+claim|submit)\b/i', $msg)) {
        $reply = "Good question! Let me explain how the claims process works. It's actually simpler than you might think. 📋\n\n";
        $reply .= "**The Claims Process:**\n\n";
        $reply .= "**Step 1: Notify Us** 📞\n";
        $reply .= "Call our hotline or visit any Pru Life UK office. Let us know what happened (death, disability, critical illness, etc.). We'll guide you through everything.\n\n";
        $reply .= "**Step 2: Submit Documents** 📄\n";
        $reply .= "We'll tell you exactly what documents you need. Usually:\n";
        $reply .= "• Claim form (we provide this)\n";
        $reply .= "• Death certificate (for death claims)\n";
        $reply .= "• Medical records (for illness/disability claims)\n";
        $reply .= "• Valid IDs\n";
        $reply .= "• Policy documents\n\n";
        $reply .= "**Step 3: We Review** 🔍\n";
        $reply .= "Our claims team reviews everything. We might ask for additional documents if needed. We're thorough but fair!\n\n";
        $reply .= "**Step 4: Approval & Payment** ✅\n";
        $reply .= "Once approved, we process the payment. Usually takes 7-14 business days, sometimes faster!\n\n";
        $reply .= "**Our Promise:**\n";
        $reply .= "We know claims happen during difficult times. That's why we make the process as smooth and compassionate as possible. Our claims team is here to help, not to make things harder.\n\n";
        $reply .= "**Pro Tip:** Keep your policy documents safe and make sure your beneficiaries know where they are!";
        
        return ['reply' => $reply, 'suggestions' => $suggestions];
    }
    
    // ═══════════════════════════════════════════════════════════
    // RIDERS & ADD-ONS
    // ═══════════════════════════════════════════════════════════
    
    if (preg_match('/\b(rider|add[\s-]?on|additional|extra|supplement)\b/i', $msg)) {
        $reply = "Ah, riders! These are like add-ons that give you extra protection. Let me explain. 🎁\n\n";
        $reply .= "**What are riders?**\n";
        $reply .= "Think of your main policy as a smartphone, and riders as apps you can add. They enhance your coverage for specific needs.\n\n";
        $reply .= "**Popular Riders We Offer:**\n\n";
        $reply .= "**1. Critical Illness Rider** 🏥\n";
        $reply .= "Extra coverage for major illnesses. If you're diagnosed with cancer, heart attack, stroke, etc., you get an additional payout on top of your main coverage.\n\n";
        $reply .= "**2. Accidental Death Benefit Rider** 🚗\n";
        $reply .= "If death is due to an accident, your beneficiaries get double or triple the payout. Extra protection for unexpected events.\n\n";
        $reply .= "**3. Waiver of Premium Rider** 💪\n";
        $reply .= "If you become disabled and can't work, this rider waives your future premiums. Your policy stays active without you paying!\n\n";
        $reply .= "**4. Hospital Income Rider** 🏨\n";
        $reply .= "Get daily cash while you're hospitalized. Helps cover hospital bills, lost income, and other expenses.\n\n";
        $reply .= "**5. Total & Permanent Disability Rider** ♿\n";
        $reply .= "Additional payout if you become permanently disabled. Helps you adjust to your new situation financially.\n\n";
        $reply .= "**The best part?**\n";
        $reply .= "Riders are usually very affordable - just a small addition to your premium for significant extra protection!\n\n";
        $reply .= "Want to see products and their available riders? I can show you!";
        
        return ['reply' => $reply, 'suggestions' => $suggestions];
    }
    
    // ═══════════════════════════════════════════════════════════
    // AGE & ELIGIBILITY
    // ═══════════════════════════════════════════════════════════
    
    if (preg_match('/\b(age|old|young|eligible|qualify|can\s+i|am\s+i)\b/i', $msg)) {
        $reply = "Good question! Let me explain our age requirements. 👶👴\n\n";
        $reply .= "**Age Eligibility:**\n\n";
        $reply .= "**Minimum Age:** As young as 7 days old!\n";
        $reply .= "Yes, you can insure newborn babies. Many parents get insurance for their kids early to lock in low premiums and ensure they're protected from day one.\n\n";
        $reply .= "**Maximum Age:** Up to 70 years old (varies by product)\n";
        $reply .= "Most of our products accept clients up to age 70. Some products go up to 65, others to 75. It depends on the specific product.\n\n";
        $reply .= "**Why age matters:**\n\n";
        $reply .= "**Younger Clients (0-35)** 🌱\n";
        $reply .= "• Lowest premiums\n";
        $reply .= "• Longest coverage period\n";
        $reply .= "• More time for investment growth (VUL)\n";
        $reply .= "• Best value for money\n\n";
        $reply .= "**Middle Age (36-50)** 🌳\n";
        $reply .= "• Still reasonable premiums\n";
        $reply .= "• Peak earning years - can afford higher coverage\n";
        $reply .= "• Critical time for family protection\n\n";
        $reply .= "**Mature Clients (51-70)** 🍂\n";
        $reply .= "• Higher premiums (but still insurable!)\n";
        $reply .= "• Focus on legacy planning\n";
        $reply .= "• Estate protection\n";
        $reply .= "• Retirement income solutions\n\n";
        $reply .= "**Pro Tip:** The younger you start, the better! Lower premiums, longer coverage, more value. But it's never too late to get protected!\n\n";
        $reply .= "How old is your client? I can show you products perfect for their age!";
        
        return ['reply' => $reply, 'suggestions' => ["25 years old", "45 years old", "60 years old", "Show all products"]];
    }
    
    // ═══════════════════════════════════════════════════════════
    // INVESTMENT & RETURNS
    // ═══════════════════════════════════════════════════════════
    
    if (preg_match('/\b(invest|return|grow|profit|earn|fund|performance)\b/i', $msg)) {
        $reply = "Ah, talking about the investment side! Let me explain how this works. 📈\n\n";
        $reply .= "**Investment in Life Insurance:**\n\n";
        $reply .= "**VUL Products (Variable Unit-Linked)** 📊\n";
        $reply .= "These have an investment component. Part of your premium goes into investment funds that can grow based on market performance.\n\n";
        $reply .= "**How it works:**\n";
        $reply .= "Your money is invested in professionally managed funds. You can choose from:\n";
        $reply .= "• **Equity Funds** - Higher risk, higher potential returns (stocks)\n";
        $reply .= "• **Balanced Funds** - Medium risk, balanced returns (mix of stocks and bonds)\n";
        $reply .= "• **Bond Funds** - Lower risk, stable returns (government/corporate bonds)\n\n";
        $reply .= "**Potential Returns:**\n";
        $reply .= "Historically, our funds have delivered:\n";
        $reply .= "• Equity funds: 8-15% annually (long-term average)\n";
        $reply .= "• Balanced funds: 6-10% annually\n";
        $reply .= "• Bond funds: 4-7% annually\n\n";
        $reply .= "**Important:** These are historical averages, not guarantees. Markets go up and down!\n\n";
        $reply .= "**Traditional Products** 🛡️\n";
        $reply .= "These offer guaranteed returns:\n";
        $reply .= "• Fixed interest rate (usually 2-4% annually)\n";
        $reply .= "• Guaranteed cash value growth\n";
        $reply .= "• No market risk\n";
        $reply .= "• Predictable, stable returns\n\n";
        $reply .= "**Which is better?**\n";
        $reply .= "It depends on your risk tolerance:\n";
        $reply .= "• Want higher potential returns + can handle risk? → VUL\n";
        $reply .= "• Want guaranteed returns + peace of mind? → Traditional\n\n";
        $reply .= "Want to see specific products and their historical performance?";
        
        return ['reply' => $reply, 'suggestions' => ["Show VUL products", "Show Traditional products", "Compare returns"]];
    }
    
    // ═══════════════════════════════════════════════════════════
    // DEFAULT - GENERAL HELP
    // ═══════════════════════════════════════════════════════════
    
    $reply = "I'm here to help! I can answer questions about:\n\n";
    $reply .= "**About Pru Life UK** 🏢\n";
    $reply .= "• Company information\n";
    $reply .= "• Our history and mission\n";
    $reply .= "• Why choose us\n\n";
    $reply .= "**Insurance Basics** 📚\n";
    $reply .= "• What is life insurance?\n";
    $reply .= "• How does it work?\n";
    $reply .= "• Why do I need it?\n\n";
    $reply .= "**Products & Coverage** 🎯\n";
    $reply .= "• VUL vs Traditional\n";
    $reply .= "• Benefits and coverage\n";
    $reply .= "• Riders and add-ons\n\n";
    $reply .= "**Practical Info** 💡\n";
    $reply .= "• Premiums and costs\n";
    $reply .= "• Age eligibility\n";
    $reply .= "• Claims process\n";
    $reply .= "• Investment returns\n\n";
    $reply .= "**Or I can help you:**\n";
    $reply .= "• Find the right product for your client\n";
    $reply .= "• Compare different options\n";
    $reply .= "• Explain specific features\n\n";
    $reply .= "What would you like to know?";
    
    return ['reply' => $reply, 'products' => [], 'suggestions' => ["What is life insurance?", "Show VUL products", "How much does it cost?", "Compare products"]];
}

// ═══════════════════════════════════════════════════════════
// HANDLE SPECIFIC PRODUCT QUESTIONS
// ═══════════════════════════════════════════════════════════

function handleSpecificProductQuestion($db, $originalMessage, $msg) {
    $reply = '';
    $products = [];
    $suggestions = ["Tell me more", "Show similar products", "Compare with others", "Find products for my client"];
    
    // Extract product name from message
    $productName = '';
    $productPatterns = [
        'PRUMillion Protect' => '/pru\s*million\s*protect/i',
        'PRU Millionaire' => '/pru\s*millionaire/i',
        'PRUMillion Flex' => '/pru\s*million\s*flex/i',
        'PRUWealth' => '/pru\s*wealth/i',
        'PRULink' => '/pru\s*link/i',
        'PRUHealth' => '/pru\s*health/i',
        'PruLove' => '/pru\s*love/i',
        'PruMax' => '/pru\s*max/i',
        'PruLifetime' => '/pru\s*lifetime/i',
        'PRUTerm' => '/pru\s*term/i',
        'PRUPersonal' => '/pru\s*personal/i',
        'PruSteady' => '/pru\s*steady/i'
    ];
    
    foreach ($productPatterns as $name => $pattern) {
        if (preg_match($pattern, $msg)) {
            $productName = $name;
            break;
        }
    }
    
    if (empty($productName)) {
        $reply = "I'd love to help you with that product! Could you tell me which specific product you're asking about? For example:\n\n";
        $reply .= "• PRUMillion Protect\n";
        $reply .= "• PRU Millionaire\n";
        $reply .= "• PRUMillion Flex\n";
        $reply .= "• PRUWealth\n";
        $reply .= "• PRULink products\n";
        $reply .= "• PRUHealth\n\n";
        $reply .= "Or you can browse all our products!";
        
        return ['reply' => $reply, 'products' => [], 'suggestions' => ["Show VUL", "Show Traditional", "Show all products"]];
    }
    
    // Search for the product in database
    $sql = "SELECT * FROM products WHERE product_name LIKE :product_name AND category IN ('VUL', 'Traditional Life Insurance') LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':product_name' => "%{$productName}%"]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $reply = "Hmm, I couldn't find **{$productName}** in our system. Let me show you our available products instead!\n\n";
        $reply .= "Would you like to see:\n";
        $reply .= "• VUL products (investment-linked)\n";
        $reply .= "• Traditional products (guaranteed protection)\n";
        $reply .= "• All products\n\n";
        $reply .= "Or tell me what you're looking for, and I'll help you find the right product!";
        
        return ['reply' => $reply, 'products' => [], 'suggestions' => ["Show VUL", "Show Traditional", "Show all products"]];
    }
    
    // Product found! Now answer based on the question
    $productFullName = $product['product_name'];
    $category = $product['category'];
    $minPremium = number_format($product['min_premium_monthly']);
    $paymentType = $product['payment_type'];
    $ageRange = $product['age_range'];
    $description = $product['description'];
    
    // Determine what aspect they're asking about
    if (preg_match('/\b(benefit|coverage|cover|protect|include)\b/i', $msg)) {
        // Asking about benefits/coverage
        $reply = "Great question about **{$productFullName}**! Let me tell you about its benefits and coverage. 🛡️\n\n";
        
        if ($category === 'VUL') {
            $reply .= "**{$productFullName}** is a VUL (Variable Unit-Linked) product, which means you get:\n\n";
            $reply .= "**Protection Benefits:**\n";
            $reply .= "• Life insurance coverage for your beneficiaries\n";
            $reply .= "• Total & Permanent Disability (TPD) protection\n";
            $reply .= "• Critical illness coverage\n";
            $reply .= "• Accidental death benefit\n\n";
            $reply .= "**Investment Benefits:**\n";
            $reply .= "• Your premiums are invested in professionally managed funds\n";
            $reply .= "• Potential for significant growth based on market performance\n";
            $reply .= "• Fund switching options (equity, balanced, bond funds)\n";
            $reply .= "• Partial withdrawals allowed\n";
            $reply .= "• Flexibility to adjust coverage and premiums\n\n";
        } else {
            $reply .= "**{$productFullName}** is a Traditional Life Insurance product, which means you get:\n\n";
            $reply .= "**Protection Benefits:**\n";
            $reply .= "• Guaranteed life insurance coverage\n";
            $reply .= "• Total & Permanent Disability (TPD) protection\n";
            $reply .= "• Critical illness coverage\n";
            $reply .= "• Accidental death benefit\n\n";
            $reply .= "**Savings Benefits:**\n";
            $reply .= "• Guaranteed cash value growth\n";
            $reply .= "• Fixed, predictable returns\n";
            $reply .= "• Policy loan facility (borrow against cash value)\n";
            $reply .= "• Guaranteed maturity benefits\n";
            $reply .= "• No market risk - your money is safe\n\n";
        }
        
        if ($description) {
            $reply .= "**Additional Details:**\n{$description}\n\n";
        }
        
        $reply .= "Want to see the complete Product Primer PDF? Click the product card below!";
        $products = [$product];
        
    } elseif (preg_match('/\b(premium|cost|price|afford|pay|expensive|cheap|how\s+much)\b/i', $msg)) {
        // Asking about premium/cost
        $reply = "Let me break down the pricing for **{$productFullName}**! 💰\n\n";
        $reply .= "**Minimum Premium:** ₱{$minPremium}/month\n";
        $reply .= "**Payment Type:** {$paymentType} Pay\n";
        $reply .= "**Age Range:** {$ageRange}\n\n";
        
        if ($paymentType === 'Regular') {
            $reply .= "**Regular Pay** means you pay premiums throughout the policy term. This gives you the most affordable monthly payment option.\n\n";
        } elseif ($paymentType === 'Limited') {
            $reply .= "**Limited Pay** means you pay for a set period (like 5, 10, or 15 years), then you're done! Higher monthly premium, but finite payment period.\n\n";
        } elseif ($paymentType === 'Single') {
            $reply .= "**Single Pay** means one payment and you're covered for life! Highest upfront cost, but no more payments ever.\n\n";
        }
        
        $reply .= "**What affects your actual premium?**\n";
        $reply .= "• Your age (younger = lower premium)\n";
        $reply .= "• Coverage amount you choose\n";
        $reply .= "• Riders you add\n";
        $reply .= "• Health condition\n\n";
        
        $reply .= "The ₱{$minPremium}/month is the starting point. Your actual premium will be calculated based on your specific situation.\n\n";
        $reply .= "Want to see the full details? Check out the Product Primer below!";
        $products = [$product];
        
    } elseif (preg_match('/\b(age|eligible|qualify|old|young)\b/i', $msg)) {
        // Asking about age eligibility
        $reply = "Good question about age eligibility for **{$productFullName}**! 👶👴\n\n";
        $reply .= "**Age Range:** {$ageRange}\n\n";
        
        $reply .= "This means:\n";
        $reply .= "• Minimum age: " . (preg_match('/(\d+)\s*(?:days?|months?|years?)?\s*(?:old)?\s*to/i', $ageRange, $m) ? $m[1] : "Check product details") . "\n";
        $reply .= "• Maximum age: " . (preg_match('/to\s*(\d+)\s*years?\s*old/i', $ageRange, $m) ? $m[1] . " years old" : "Check product details") . "\n\n";
        
        $reply .= "**Why age matters:**\n";
        $reply .= "The younger you start, the lower your premium! Plus, you get longer coverage and more time for your investment to grow";
        
        if ($category === 'VUL') {
            $reply .= " (especially important for VUL products)";
        }
        $reply .= ".\n\n";
        
        $reply .= "Want to see if this product fits your client's age? Check the full details below!";
        $products = [$product];
        
    } elseif (preg_match('/\b(invest|return|grow|fund|performance)\b/i', $msg)) {
        // Asking about investment/returns
        if ($category === 'VUL') {
            $reply = "Ah, asking about the investment side of **{$productFullName}**! 📈\n\n";
            $reply .= "As a VUL product, **{$productFullName}** offers investment opportunities:\n\n";
            $reply .= "**Investment Features:**\n";
            $reply .= "• Your premiums are invested in professionally managed funds\n";
            $reply .= "• Choose from equity, balanced, or bond funds\n";
            $reply .= "• Switch between funds as market conditions change\n";
            $reply .= "• Potential for significant long-term growth\n\n";
            $reply .= "**Historical Performance (Industry Average):**\n";
            $reply .= "• Equity funds: 8-15% annually\n";
            $reply .= "• Balanced funds: 6-10% annually\n";
            $reply .= "• Bond funds: 4-7% annually\n\n";
            $reply .= "**Important:** Past performance doesn't guarantee future results. Markets go up and down, but historically, long-term investors (10+ years) have seen strong returns.\n\n";
            $reply .= "Want to see the complete investment details? Check the Product Primer below!";
        } else {
            $reply = "Good question about **{$productFullName}**! 🛡️\n\n";
            $reply .= "As a Traditional Life Insurance product, **{$productFullName}** offers:\n\n";
            $reply .= "**Guaranteed Returns:**\n";
            $reply .= "• Fixed interest rate (typically 2-4% annually)\n";
            $reply .= "• Guaranteed cash value growth\n";
            $reply .= "• No market risk - your money is safe\n";
            $reply .= "• Predictable, stable returns\n\n";
            $reply .= "**The Trade-off:**\n";
            $reply .= "Lower returns compared to VUL, but you get peace of mind and guaranteed growth. Perfect for conservative investors who value stability over high returns.\n\n";
            $reply .= "Want to see the full details? Check the Product Primer below!";
        }
        $products = [$product];
        
    } else {
        // General question about the product
        $reply = "Let me tell you about **{$productFullName}**! 😊\n\n";
        
        $reply .= "**Product Type:** {$category}\n";
        $reply .= "**Minimum Premium:** ₱{$minPremium}/month\n";
        $reply .= "**Payment Type:** {$paymentType} Pay\n";
        $reply .= "**Age Range:** {$ageRange}\n\n";
        
        if ($category === 'VUL') {
            $reply .= "**What makes it special:**\n";
            $reply .= "This is a VUL (Variable Unit-Linked) product, which means you get both life insurance protection AND investment growth potential. Your premiums are split between coverage and investment funds that can grow based on market performance.\n\n";
            $reply .= "**Perfect for clients who:**\n";
            $reply .= "• Want their money to work harder\n";
            $reply .= "• Are comfortable with some market risk\n";
            $reply .= "• Have long-term financial goals\n";
            $reply .= "• Want flexibility and growth potential\n\n";
        } else {
            $reply .= "**What makes it special:**\n";
            $reply .= "This is a Traditional Life Insurance product, which means you get guaranteed protection with guaranteed cash value growth. No market risk, no surprises - just solid, reliable coverage.\n\n";
            $reply .= "**Perfect for clients who:**\n";
            $reply .= "• Value stability and predictability\n";
            $reply .= "• Want guaranteed returns\n";
            $reply .= "• Prefer conservative investments\n";
            $reply .= "• Want peace of mind\n\n";
        }
        
        if ($description) {
            $reply .= "**Product Description:**\n{$description}\n\n";
        }
        
        $reply .= "Want to know more? Ask me about:\n";
        $reply .= "• Benefits and coverage\n";
        $reply .= "• Premium and costs\n";
        $reply .= "• Investment returns (for VUL)\n";
        $reply .= "• Age eligibility\n\n";
        $reply .= "Or click the product card below to see the complete Product Primer PDF!";
        $products = [$product];
    }
    
    return ['reply' => $reply, 'products' => $products, 'suggestions' => $suggestions];
}

function checkAgeEligibility($ageRange, $clientAge) {
    // Parse age range like "7 days to 70 years old" or "18 to 65 years old"
    if (empty($ageRange)) {
        return true; // If no age range specified, assume eligible
    }
    
    // Extract numbers from age range
    preg_match_all('/\b(\d+)\b/', $ageRange, $matches);
    
    if (empty($matches[1])) {
        return true; // Can't parse, assume eligible
    }
    
    $ages = array_map('intval', $matches[1]);
    
    // If we have at least 2 numbers, treat as min and max
    if (count($ages) >= 2) {
        $minAge = min($ages);
        $maxAge = max($ages);
        return ($clientAge >= $minAge && $clientAge <= $maxAge);
    }
    
    // If only one number, check if it's a maximum
    if (count($ages) == 1) {
        if (stripos($ageRange, 'to') !== false || stripos($ageRange, 'up to') !== false) {
            return $clientAge <= $ages[0];
        }
    }
    
    return true; // Default to eligible if can't determine
}
?>
