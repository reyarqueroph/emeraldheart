<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$type = $_GET['type'] ?? 'agents';

if ($type === 'agents') {
    $stmt = $db->query("SELECT agent_code, username, email, full_name, position, status, last_active, created_at FROM users WHERE role='agent'");
    $filename = 'agents_export_' . date('Ymd');
} elseif ($type === 'products') {
    $stmt = $db->query("SELECT product_name, category, sub_category, payment_type, age_range, min_premium_monthly, description FROM products WHERE is_active=1");
    $filename = 'products_export_' . date('Ymd');
} elseif ($type === 'feedbacks') {
    $stmt = $db->query("SELECT u.agent_code, u.full_name, f.subject, f.message, f.status, f.admin_reply, f.created_at FROM feedbacks f JOIN users u ON f.user_id=u.id");
    $filename = 'feedbacks_export_' . date('Ymd');
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid export type']);
    exit;
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

$out = fopen('php://output', 'w');
if (!empty($rows)) {
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
}
fclose($out);
exit;
?>
