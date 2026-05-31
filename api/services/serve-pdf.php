<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(401); exit; }
$file = basename($_GET['file'] ?? '');
if (!$file) { http_response_code(404); exit; }
$path = dirname(__DIR__,2).'/uploads/services/'.$file;
if (!file_exists($path)) { http_response_code(404); exit; }
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="'.$file.'"');
header('Content-Length: '.filesize($path));
header('Cache-Control: max-age=3600');
readfile($path);
?>
