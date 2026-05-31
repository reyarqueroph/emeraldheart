<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$data    = json_decode(file_get_contents("php://input"), true);
$message = trim($data['message'] ?? '');
$context = $data['context'] ?? [];

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message required']); exit;
}

$database = new Database();
$db = $database->getConnection();

// Parse intent from message
$msg   = strtolower($message);
$reply = '';
$products = [];
$step  = $context['step'] ?? 'start';

// ── Keyword-based intent engine ──────────────────────────────
$ageMatch     = null;
$budgetMatch  = null;
$goalMatch    = null;

// Extract age
if (preg_match('/\b(\d{1,2})\s*(years?\s*old|yo|yrs?)?\b/i', $message, $m)) {
    $ageMatch = intval($m[1]);
}

// Extract budget (PHP amounts)
if (preg_match('/[₱P]?\s*(\d[\d,]*)\s*(\/mo|per month|monthly|a month)?/i', $message, $m)) {
    $budgetMatch = intval(str_replace(',', '', $m[1]));
}

// Goal keywords
$goals = [
    'investment' => ['invest', 'grow', 'fund', 'vul', 'wealth', 'savings', 'return'],
    'protection' => ['protect', 'life insurance', 'death', 'term', 'coverage', 'beneficiary'],
    'accident'   => ['accident', 'injury', 'disability', 'personal accident'],
    'health'     => ['health', 'hospital', 'medical', 'sick', 'illness'],
    'education'  => ['education', 'school', 'college', 'child', 'kids'],
    'retirement' => ['retire', 'retirement', 'pension', 'old age'],
];

foreach ($goals as $goal => $keywords) {
    foreach ($keywords as $kw) {
        if (str_contains($msg, $kw)) { $goalMatch = $goal; break 2; }
    }
}

// ── Build SQL query based on extracted criteria ──────────────
$where  = ["is_active = 1"];
$params = [];

if ($ageMatch !== null) {
    // Simple age filter — products with age ranges that could include this age
    // We store age_range as text so we do a loose match
    $where[]  = "(age_range LIKE :age1 OR age_range LIKE :age2 OR age_range LIKE '%days%' OR age_range LIKE '%month%')";
    $params[':age1'] = '%' . $ageMatch . '%';
    $params[':age2'] = '%years%';
}

if ($budgetMatch !== null) {
    $where[]  = "min_premium_monthly <= :budget";
    $params[':budget'] = $budgetMatch;
}

if ($goalMatch !== null) {
    $catMap = [
        'investment' => 'VUL',
        'protection' => 'Traditional Life Insurance',
        'accident'   => 'Personal Accident',
        'health'     => 'Traditional Life Insurance',
        'education'  => 'VUL',
        'retirement' => 'VUL',
    ];
    if (isset($catMap[$goalMatch])) {
        $where[]  = "category = :cat";
        $params[':cat'] = $catMap[$goalMatch];
    }
}

$sql  = "SELECT id, product_name, category, age_range, min_premium_monthly, payment_type, description FROM products WHERE " . implode(' AND ', $where) . " ORDER BY min_premium_monthly ASC LIMIT 4";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Generate conversational reply ────────────────────────────
$nextStep = 'done';

if ($step === 'start' || (empty($ageMatch) && empty($budgetMatch) && empty($goalMatch))) {
    // Greeting / no criteria detected
    if (str_contains($msg, 'hello') || str_contains($msg, 'hi') || str_contains($msg, 'hey')) {
        $reply = "Hi there! 👋 I'm your eHeart product advisor. I can help you find the right insurance plan.\n\nTell me a bit about your client:\n• How old are they?\n• What's their monthly budget?\n• What's their main goal? (protection, investment, health, etc.)";
        $nextStep = 'gather';
    } elseif (str_contains($msg, 'help') || str_contains($msg, 'recommend') || str_contains($msg, 'suggest')) {
        $reply = "Sure! To find the best match, I need a few details:\n\n1️⃣ **Age** — How old is the client?\n2️⃣ **Budget** — Monthly premium budget (e.g. ₱2,000/mo)\n3️⃣ **Goal** — Protection, investment, accident coverage, or health?";
        $nextStep = 'gather';
    } else {
        // Try to answer with all products if no criteria
        $stmt2 = $db->query("SELECT id, product_name, category, age_range, min_premium_monthly, payment_type FROM products WHERE is_active=1 ORDER BY min_premium_monthly ASC LIMIT 4");
        $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $reply = "I found some popular products for you. You can also tell me the client's **age**, **budget**, or **goal** for more specific recommendations.";
    }
} elseif (!empty($products)) {
    $parts = [];
    if ($ageMatch)    $parts[] = "age **{$ageMatch}**";
    if ($budgetMatch) $parts[] = "budget **₱" . number_format($budgetMatch) . "/mo**";
    if ($goalMatch)   $parts[] = "goal: **{$goalMatch}**";
    $criteria = implode(', ', $parts);
    $reply = "Based on " . ($criteria ?: "your criteria") . ", here are my top recommendations:";
} else {
    $reply = "I couldn't find products matching those exact criteria. Let me show you all available options instead.";
    $stmt2 = $db->query("SELECT id, product_name, category, age_range, min_premium_monthly, payment_type FROM products WHERE is_active=1 ORDER BY min_premium_monthly ASC LIMIT 4");
    $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

// ── Suggested follow-up questions ────────────────────────────
$suggestions = [];
if ($step !== 'done') {
    $suggestions = [
        "Show me VUL products",
        "Budget under ₱3,000/month",
        "Best for 30 year old",
        "Accident coverage options",
    ];
}

echo json_encode([
    'success'     => true,
    'reply'       => $reply,
    'products'    => $products,
    'suggestions' => $suggestions,
    'context'     => ['step' => $nextStep, 'age' => $ageMatch, 'budget' => $budgetMatch, 'goal' => $goalMatch],
]);
?>
