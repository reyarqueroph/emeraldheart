<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Add mood_rating column if it doesn't exist
try {
    $db->exec("ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS mood_rating INT DEFAULT NULL");
} catch (Exception $e) {}

$data = json_decode(file_get_contents("php://input"));

$subject = trim($data->subject ?? '');
$message = trim($data->message ?? '');
$mood_rating = isset($data->mood_rating) ? intval($data->mood_rating) : null;

if (empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
    exit;
}

$stmt = $db->prepare("INSERT INTO feedbacks (user_id, subject, message, mood_rating, status) VALUES (:uid, :sub, :msg, :mood, 'pending')");
$stmt->execute([
    ':uid' => $_SESSION['user_id'], 
    ':sub' => $subject, 
    ':msg' => $message,
    ':mood' => $mood_rating
]);

echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
?>
