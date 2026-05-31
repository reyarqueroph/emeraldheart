<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $database = new Database();
    $db       = $database->getConnection();

    if ($_SESSION['user_role'] === 'admin') {
        $stmt = $db->query("SELECT f.*, u.full_name, u.agent_code
                            FROM feedbacks f
                            JOIN users u ON f.user_id = u.id
                            ORDER BY f.created_at DESC");
    } else {
        $stmt = $db->prepare("SELECT f.*, u.full_name, u.agent_code
                              FROM feedbacks f
                              JOIN users u ON f.user_id = u.id
                              WHERE f.user_id = :uid
                              ORDER BY f.created_at DESC");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
    }

    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
