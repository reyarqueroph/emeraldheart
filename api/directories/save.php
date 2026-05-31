<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit;
}

$data   = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$type   = $data['type']   ?? '';

if (!in_array($type, ['clinics', 'emails'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid type']); exit;
}

$table = $type === 'clinics' ? 'accredited_clinics' : 'email_directories';

try {
    $database = new Database();
    $db       = $database->getConnection();

    if ($action === 'create') {
        if ($type === 'clinics') {
            $stmt = $db->prepare("INSERT INTO accredited_clinics (name, address, region, contact, sort_order) VALUES (:name,:address,:region,:contact,:sort)");
            $stmt->execute([':name'=>$data['name'],':address'=>$data['address'],':region'=>$data['region'],':contact'=>$data['contact'],':sort'=>intval($data['sort_order']??0)]);
        } else {
            $stmt = $db->prepare("INSERT INTO email_directories (department, icon, email, sort_order) VALUES (:dept,:icon,:email,:sort)");
            $stmt->execute([':dept'=>$data['department'],':icon'=>$data['icon']??'fa-envelope',':email'=>$data['email'],':sort'=>intval($data['sort_order']??0)]);
        }
        echo json_encode(['success' => true, 'message' => 'Entry added successfully']);

    } elseif ($action === 'update') {
        $id = intval($data['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); exit; }
        if ($type === 'clinics') {
            $stmt = $db->prepare("UPDATE accredited_clinics SET name=:name,address=:address,region=:region,contact=:contact,sort_order=:sort WHERE id=:id");
            $stmt->execute([':name'=>$data['name'],':address'=>$data['address'],':region'=>$data['region'],':contact'=>$data['contact'],':sort'=>intval($data['sort_order']??0),':id'=>$id]);
        } else {
            $stmt = $db->prepare("UPDATE email_directories SET department=:dept,icon=:icon,email=:email,sort_order=:sort WHERE id=:id");
            $stmt->execute([':dept'=>$data['department'],':icon'=>$data['icon']??'fa-envelope',':email'=>$data['email'],':sort'=>intval($data['sort_order']??0),':id'=>$id]);
        }
        echo json_encode(['success' => true, 'message' => 'Entry updated successfully']);

    } elseif ($action === 'delete') {
        $id = intval($data['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID required']); exit; }
        $stmt = $db->prepare("DELETE FROM $table WHERE id=:id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Entry deleted']);

    } elseif ($action === 'toggle') {
        $id = intval($data['id'] ?? 0);
        $stmt = $db->prepare("UPDATE $table SET is_active = NOT is_active WHERE id=:id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'message' => 'Status updated']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
