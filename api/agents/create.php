<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database(); 
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']); 
    exit; 
}

$agent_code = trim($data->agent_code ?? '');
$username   = trim($data->username ?? '');
$email      = trim($data->email ?? '');
$full_name  = trim($data->full_name ?? '');
$position   = trim($data->position ?? 'Agent');
$status     = trim($data->status ?? 'active');
$password   = password_hash($data->password ?? 'password', PASSWORD_DEFAULT);

if (empty($agent_code) || empty($username) || empty($email) || empty($full_name)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

$check = $db->prepare("SELECT id FROM users WHERE agent_code=:ac OR email=:em OR username=:un");
$check->execute([':ac' => $agent_code, ':em' => $email, ':un' => $username]);
if ($check->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Agent code, username, or email already exists']);
    exit;
}

$stmt = $db->prepare("INSERT INTO users (agent_code, username, email, password, full_name, position, role, status)
                      VALUES (:ac, :un, :em, :pw, :fn, :pos, 'agent', :st)");
$stmt->execute([':ac'=>$agent_code,':un'=>$username,':em'=>$email,':pw'=>$password,
                ':fn'=>$full_name,':pos'=>$position,':st'=>$status]);

echo json_encode(['success' => true, 'message' => 'Agent created successfully']);
?>
