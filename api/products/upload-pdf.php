<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$id = intval($_POST['product_id'] ?? 0);
if (!$id) { echo json_encode(['success' => false, 'message' => 'Product ID required']); exit; }

if (!isset($_FILES['primer_file']) || $_FILES['primer_file']['error'] !== UPLOAD_ERR_OK) {
    $errors = [1=>'File too large',2=>'File too large',3=>'Partial upload',4=>'No file',6=>'No temp folder',7=>'Write failed'];
    $code = $_FILES['primer_file']['error'] ?? 4;
    echo json_encode(['success' => false, 'message' => $errors[$code] ?? 'Upload failed']); exit;
}

$file     = $_FILES['primer_file'];
$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$maxSize  = 10 * 1024 * 1024; // 10MB

if ($ext !== 'pdf') { echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed']); exit; }
if ($file['size'] > $maxSize) { echo json_encode(['success' => false, 'message' => 'File must be under 10MB']); exit; }

// Verify it's actually a PDF
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if ($mime !== 'application/pdf') { echo json_encode(['success' => false, 'message' => 'Invalid file type']); exit; }

$uploadDir = dirname(__DIR__, 2) . '/uploads/primers/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Delete old file if exists
$database = new Database();
$db = $database->getConnection();
$old = $db->prepare("SELECT primer_file FROM products WHERE id=:id");
$old->execute([':id' => $id]);
$row = $old->fetch(PDO::FETCH_ASSOC);
if ($row && $row['primer_file'] && file_exists($uploadDir . $row['primer_file'])) {
    unlink($uploadDir . $row['primer_file']);
}

$filename = 'product_' . $id . '_' . time() . '.pdf';
if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']); exit;
}

$stmt = $db->prepare("UPDATE products SET primer_file=:f WHERE id=:id");
$stmt->execute([':f' => $filename, ':id' => $id]);

echo json_encode(['success' => true, 'message' => 'PDF uploaded successfully', 'filename' => $filename]);
?>
