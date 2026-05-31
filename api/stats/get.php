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

$total_agents = $db->query("SELECT COUNT(*) FROM users WHERE role='agent' AND status='active'")->fetchColumn();
$active_today = $db->query("SELECT COUNT(*) FROM users WHERE role='agent' AND DATE(last_active)=CURDATE()")->fetchColumn();
$password_requests = $db->query("SELECT COUNT(*) FROM password_requests WHERE status='pending'")->fetchColumn();
$agent_feedbacks = $db->query("SELECT COUNT(*) FROM feedbacks WHERE status='pending'")->fetchColumn();

echo json_encode([
    'success' => true,
    'total_agents' => (int)$total_agents,
    'active_today' => (int)$active_today,
    'password_requests' => (int)$password_requests,
    'agent_feedbacks' => (int)$agent_feedbacks
]);
?>
