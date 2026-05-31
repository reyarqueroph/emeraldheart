<?php
// Script to update admin email addresses
header('Content-Type: application/json');

try {
    $host = "localhost";
    $db_name = "pru_life_db";
    $username = "root";
    $password = "";
    
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update all admin emails
    $stmt = $db->prepare("UPDATE users SET email = :email WHERE role = 'admin'");
    $stmt->execute([':email' => 'reyarqueroofficial25@gmail.com']);
    
    $rowsAffected = $stmt->rowCount();
    
    // Get updated admin accounts
    $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => "Updated {$rowsAffected} admin account(s)",
        'admins' => $admins
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
