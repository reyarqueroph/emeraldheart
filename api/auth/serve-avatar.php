<?php
session_start();
$file = basename($_GET['file'] ?? '');
if (!$file) { http_response_code(404); exit; }

$path = dirname(__DIR__, 2) . '/uploads/avatars/' . $file;
if (!file_exists($path)) { http_response_code(404); exit; }

$ext   = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimes = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp'];
$mime  = $mimes[$ext] ?? 'image/jpeg';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: max-age=86400');
readfile($path);
?>
