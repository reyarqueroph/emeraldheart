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
$target = $data['target'] ?? ''; // 'section' or 'item'

try {
    $database = new Database();
    $db       = $database->getConnection();

    if ($target === 'section') {
        if ($action === 'update') {
            $stmt = $db->prepare("UPDATE service_sections SET title=:t,icon=:i,description=:d,external_url=:u,sort_order=:s WHERE id=:id");
            $stmt->execute([':t'=>$data['title'],':i'=>$data['icon'],':d'=>$data['description']??'',':u'=>$data['external_url']??null,':s'=>intval($data['sort_order']??0),':id'=>intval($data['id'])]);
            echo json_encode(['success'=>true,'message'=>'Section updated']);
        } elseif ($action === 'toggle') {
            $db->prepare("UPDATE service_sections SET is_active=NOT is_active WHERE id=:id")->execute([':id'=>intval($data['id'])]);
            echo json_encode(['success'=>true,'message'=>'Status updated']);
        }

    } elseif ($target === 'item') {
        if ($action === 'create') {
            $stmt = $db->prepare("INSERT INTO service_items (section_id,title,description,external_url,item_type,sort_order) VALUES (:sid,:t,:d,:u,:type,:s)");
            $stmt->execute([':sid'=>intval($data['section_id']),':t'=>$data['title'],':d'=>$data['description']??'',':u'=>$data['external_url']??null,':type'=>$data['item_type']??'document',':s'=>intval($data['sort_order']??0)]);
            echo json_encode(['success'=>true,'message'=>'Item added','id'=>$db->lastInsertId()]);
        } elseif ($action === 'update') {
            $stmt = $db->prepare("UPDATE service_items SET title=:t,description=:d,external_url=:u,item_type=:type,sort_order=:s WHERE id=:id");
            $stmt->execute([':t'=>$data['title'],':d'=>$data['description']??'',':u'=>$data['external_url']??null,':type'=>$data['item_type']??'document',':s'=>intval($data['sort_order']??0),':id'=>intval($data['id'])]);
            echo json_encode(['success'=>true,'message'=>'Item updated']);
        } elseif ($action === 'delete') {
            // Delete PDF file if exists
            $row = $db->prepare("SELECT pdf_file FROM service_items WHERE id=:id");
            $row->execute([':id'=>intval($data['id'])]);
            $r = $row->fetch(PDO::FETCH_ASSOC);
            if ($r && $r['pdf_file']) {
                $path = dirname(__DIR__,2).'/uploads/services/'.$r['pdf_file'];
                if (file_exists($path)) unlink($path);
            }
            $db->prepare("DELETE FROM service_items WHERE id=:id")->execute([':id'=>intval($data['id'])]);
            echo json_encode(['success'=>true,'message'=>'Item deleted']);
        } elseif ($action === 'toggle') {
            $db->prepare("UPDATE service_items SET is_active=NOT is_active WHERE id=:id")->execute([':id'=>intval($data['id'])]);
            echo json_encode(['success'=>true,'message'=>'Status updated']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Invalid action']);
        }
    } else {
        echo json_encode(['success'=>false,'message'=>'Invalid target']);
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
