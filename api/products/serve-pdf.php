<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit; }

$filename = basename($_GET['file'] ?? '');
if (!$filename || !preg_match('/^product_\d+_\d+\.pdf$/', $filename)) {
    http_response_code(400); exit;
}

$path = dirname(__DIR__, 2) . '/uploads/primers/' . $filename;
if (!file_exists($path)) { http_response_code(404); exit; }

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=3600');
readfile($path);
exit;
?>
