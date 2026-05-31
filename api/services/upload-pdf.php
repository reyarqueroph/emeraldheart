<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}

$itemId = intval($_POST['item_id'] ?? 0);
if (!$itemId) { echo json_encode(['success'=>false,'message'=>'Item ID required']); exit; }

if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'message'=>'No file uploaded']); exit;
}

$file    = $_FILES['pdf_file'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$maxSize = 20 * 1024 * 1024;

if ($ext !== 'pdf') { echo json_encode(['success'=>false,'message'=>'Only PDF files allowed']); exit; }
if ($file['size'] > $maxSize) { echo json_encode(['success'=>false,'message'=>'File must be under 20MB']); exit; }

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if ($mime !== 'application/pdf') { echo json_encode(['success'=>false,'message'=>'Invalid file type']); exit; }

$uploadDir = dirname(__DIR__,2).'/uploads/services/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Delete old file
    $old = $db->prepare("SELECT pdf_file FROM service_items WHERE id=:id");
    $old->execute([':id'=>$itemId]);
    $row = $old->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['pdf_file'] && file_exists($uploadDir.$row['pdf_file'])) unlink($uploadDir.$row['pdf_file']);

    $filename = 'service_'.$itemId.'_'.time().'.pdf';
    if (!move_uploaded_file($file['tmp_name'], $uploadDir.$filename)) {
        echo json_encode(['success'=>false,'message'=>'Failed to save file']); exit;
    }

    $db->prepare("UPDATE service_items SET pdf_file=:f WHERE id=:id")->execute([':f'=>$filename,':id'=>$itemId]);
    echo json_encode(['success'=>true,'message'=>'PDF uploaded successfully','filename'=>$filename]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
