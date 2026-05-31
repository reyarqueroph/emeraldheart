<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

$id        = intval($data->id ?? 0);
$full_name = trim($data->full_name ?? '');
$email     = trim($data->email ?? '');
$position  = trim($data->position ?? '');
$status    = trim($data->status ?? '');

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Agent ID required']);
    exit;
}

// Fetch current agent status before update
$prevStmt = $db->prepare("SELECT status, payment_status FROM users WHERE id = :id AND role = 'agent'");
$prevStmt->execute([':id' => $id]);
$prevAgent = $prevStmt->fetch(PDO::FETCH_ASSOC);

if (!$prevAgent) {
    echo json_encode(['success' => false, 'message' => 'Agent not found']);
    exit;
}

$fields = [];
$params = [':id' => $id];

if ($full_name) { $fields[] = 'full_name=:fn';  $params[':fn']  = $full_name; }
if ($email)     { $fields[] = 'email=:em';       $params[':em']  = $email; }
if ($position)  { $fields[] = 'position=:pos';   $params[':pos'] = $position; }
if ($status)    { $fields[] = 'status=:st';      $params[':st']  = $status; }

// Optional password reset
if (!empty($data->password)) {
    $fields[] = 'password=:pw';
    $params[':pw'] = password_hash($data->password, PASSWORD_DEFAULT);
}

if (empty($fields)) {
    echo json_encode(['success' => false, 'message' => 'Nothing to update']);
    exit;
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE users SET " . implode(',', $fields) . " WHERE id=:id AND role='agent'");
    $stmt->execute($params);

    // ── When admin approves an agent (status → active) ──────────────────────
    $beingApproved = ($status === 'active' && $prevAgent['status'] !== 'active');

    if ($beingApproved) {
        // Check if a payment transaction already exists for this agent
        $txnCheck = $db->prepare("SELECT id, status FROM payment_transactions WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1");
        $txnCheck->execute([':uid' => $id]);
        $existingTxn = $txnCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingTxn) {
            // Promote existing transaction to 'paid' (pending admin verification) if it was in pending state
            if (in_array($existingTxn['status'], ['pending', 'unpaid'])) {
                $db->prepare("UPDATE payment_transactions SET status = 'paid' WHERE id = :tid")
                   ->execute([':tid' => $existingTxn['id']]);
            }
        } else {
            // No transaction yet — create a placeholder so it appears in payments.php
            // Get registration fee from settings
            $feeStmt = $db->prepare("SELECT setting_value FROM payment_settings WHERE setting_key = 'registration_fee'");
            $feeStmt->execute();
            $registrationFee = floatval($feeStmt->fetchColumn() ?: 500.00);

            $agentStmt = $db->prepare("SELECT agent_code FROM users WHERE id = :id");
            $agentStmt->execute([':id' => $id]);
            $agentCode = $agentStmt->fetchColumn();

            $transactionId = 'TXN_' . $id . '_' . time();
            $db->prepare("
                INSERT INTO payment_transactions (user_id, transaction_id, gcash_reference, amount, payment_method, status, created_at)
                VALUES (:uid, :tid, '', :amt, 'gcash', 'paid', NOW())
            ")->execute([
                ':uid' => $id,
                ':tid' => $transactionId,
                ':amt' => $registrationFee
            ]);
        }

        // Mark user payment_status as 'paid' (awaiting verification in payments.php)
        $db->prepare("UPDATE users SET payment_status = 'paid' WHERE id = :id")
           ->execute([':id' => $id]);
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Agent updated successfully']);

} catch (Exception $e) {
    $db->rollBack();
    error_log("Agent update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()]);
}
?>
