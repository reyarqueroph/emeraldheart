<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Create settings table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS app_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    if ($action === 'get') {
        $row = $db->query("SELECT setting_value FROM app_settings WHERE setting_key='clinic_list_pdf'")->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'pdf_file' => $row ? $row['setting_value'] : null]);

    } elseif ($action === 'upload') {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
        }
        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']); exit;
        }
        $file    = $_FILES['pdf_file'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $maxSize = 30 * 1024 * 1024;
        if ($ext !== 'pdf') { echo json_encode(['success' => false, 'message' => 'Only PDF files allowed']); exit; }
        if ($file['size'] > $maxSize) { echo json_encode(['success' => false, 'message' => 'File must be under 30MB']); exit; }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') { echo json_encode(['success' => false, 'message' => 'Invalid file type']); exit; }

        $uploadDir = dirname(__DIR__, 2) . '/uploads/clinics/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Delete old file
        $old = $db->query("SELECT setting_value FROM app_settings WHERE setting_key='clinic_list_pdf'")->fetch(PDO::FETCH_ASSOC);
        if ($old && !empty($old['setting_value']) && file_exists($uploadDir . $old['setting_value'])) {
            unlink($uploadDir . $old['setting_value']);
        }

        $filename = 'clinic_list_' . time() . '.pdf';
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save file']); exit;
        }

        $db->prepare("INSERT INTO app_settings (setting_key, setting_value) VALUES ('clinic_list_pdf', :v)
                      ON DUPLICATE KEY UPDATE setting_value=:v2")
           ->execute([':v' => $filename, ':v2' => $filename]);

        echo json_encode(['success' => true, 'message' => 'Clinic list PDF uploaded successfully', 'filename' => $filename]);

    } elseif ($action === 'delete') {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
        }
        $old = $db->query("SELECT setting_value FROM app_settings WHERE setting_key='clinic_list_pdf'")->fetch(PDO::FETCH_ASSOC);
        if ($old && !empty($old['setting_value'])) {
            $path = dirname(__DIR__, 2) . '/uploads/clinics/' . $old['setting_value'];
            if (file_exists($path)) unlink($path);
        }
        $db->exec("DELETE FROM app_settings WHERE setting_key='clinic_list_pdf'");
        echo json_encode(['success' => true, 'message' => 'Clinic list PDF removed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
