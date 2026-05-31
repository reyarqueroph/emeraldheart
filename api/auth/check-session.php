<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['user_role'],
            'name' => $_SESSION['user_name'],
            'agent_code' => $_SESSION['agent_code']
        ]
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>