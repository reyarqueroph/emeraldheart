<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']); exit;
}

$file    = $_FILES['avatar'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg','jpeg','png','gif','webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, or WEBP images allowed']); exit;
}
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'Image must be under 5MB']); exit;
}

// Verify MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid image file']); exit;
}

$uploadDir = dirname(__DIR__, 2) . '/uploads/avatars/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Delete old avatar
try {
    $database = new Database();
    $db       = $database->getConnection();
    $old = $db->prepare("SELECT avatar FROM users WHERE id=:id");
    $old->execute([':id' => $_SESSION['user_id']]);
    $row = $old->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['avatar']) && file_exists($uploadDir . $row['avatar'])) {
        unlink($uploadDir . $row['avatar']);
    }
} catch (Exception $e) {}

$filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save image']); exit;
}

try {
    // Add avatar column if not exists
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL");
    $stmt = $db->prepare("UPDATE users SET avatar=:avatar WHERE id=:id");
    $stmt->execute([':avatar' => $filename, ':id' => $_SESSION['user_id']]);
    $_SESSION['user_avatar'] = $filename;
    echo json_encode(['success' => true, 'message' => 'Profile picture updated', 'avatar' => $filename]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
