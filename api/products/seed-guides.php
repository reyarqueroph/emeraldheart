<?php
/**
 * One-time seeder — adds the three Product Guide entries.
 * Run once from browser: /api/products/seed-guides.php
 * Delete this file after running.
 */
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Allow only admin or direct server access
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

try {
    $db = (new Database())->getConnection();

    $guides = [
        [
            'product_name'        => 'Product Placemat – Choosing the Pru Life UK Product Solution',
            'category'            => 'Product Guides',
            'sub_category'        => 'Product Placemat',
            'payment_type'        => 'Regular',
            'age_range'           => 'All ages',
            'min_premium_monthly' => 0.00,
            'description'         => 'A quick-reference guide for choosing the right Pru Life UK product solution based on client needs — covering Protection, Critical Illness, Retirement, Education, Medium- to Long-term Goals, Short-term Goals, and Diversification of Investment.',
        ],
        [
            'product_name'        => 'PRULink Product Specification Guide (PSG) – August 2025',
            'category'            => 'Product Guides',
            'sub_category'        => 'Product Specification Guide',
            'payment_type'        => 'Regular',
            'age_range'           => '0 – 70 years old',
            'min_premium_monthly' => 0.00,
            'description'         => 'Complete product specification guide covering all Investment-Linked (PRUMillionaire, PRULink Investor Account Plus, PRULink Exact Protector, PRULink Elite Protector, PRUHealth Prime, PRULink Assurance Account Plus, PRUMax Invest) and Traditional products (PRULife, PRULifetime Income, PRULove for Life, PRUHealth FamLove, PRUTerm 15, PRUMultiple Life Care Plus, PRULife Care Advance Plus, PRULife Care Plus, PRUPersonal Accident Standard, PRUWellness, PRUShield). PSG as of August 2025.',
        ],
        [
            'product_name'        => 'Accelerated Total and Permanent Disability (ATPD) – Benefits and Limitations',
            'category'            => 'Product Guides',
            'sub_category'        => 'Rider Guide',
            'payment_type'        => 'Regular',
            'age_range'           => 'All ages',
            'min_premium_monthly' => 0.00,
            'description'         => 'Detailed guide on the Accelerated Total and Permanent Disability (ATPD) benefit — covering qualifying conditions (employed/unemployed), activities of daily living criteria, and exclusions including pregnancy, psychiatric disorders, war, self-inflicted injuries, hazardous sports, and AIDS.',
        ],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO products
        (product_name, category, sub_category, payment_type, age_range, min_premium_monthly, description, is_active)
        VALUES (:pn, :cat, :sub, :pt, :ar, :mp, :desc, 1)");

    $inserted = 0;
    foreach ($guides as $g) {
        $stmt->execute([
            ':pn'  => $g['product_name'],
            ':cat' => $g['category'],
            ':sub' => $g['sub_category'],
            ':pt'  => $g['payment_type'],
            ':ar'  => $g['age_range'],
            ':mp'  => $g['min_premium_monthly'],
            ':desc'=> $g['description'],
        ]);
        $inserted += $stmt->rowCount();
    }

    // Verify
    $rows = $db->query("SELECT id, product_name, category FROM products WHERE category='Product Guides' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'  => true,
        'message'  => "$inserted guide(s) inserted successfully.",
        'guides'   => $rows,
        'next_step'=> 'Go to Admin → Manage Products → filter by Product Guides → click Edit on each guide → upload the corresponding PDF file.',
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
