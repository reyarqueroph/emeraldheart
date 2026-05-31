<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Add birthday column if it doesn't exist
try {
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS birthday DATE DEFAULT NULL");
} catch (Exception $e) {}

try {
    // Get all users with birthdays (for calendar display)
    $stmt = $db->query("
        SELECT 
            id,
            full_name,
            agent_code,
            birthday,
            user_role,
            EXTRACT(MONTH FROM birthday) as birth_month,
            EXTRACT(DAY FROM birthday) as birth_day,
            EXTRACT(YEAR FROM birthday) as birth_year
        FROM users 
        WHERE birthday IS NOT NULL 
          AND status = 'active'
        ORDER BY birth_month, birth_day
    ");
    
    $allBirthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for calendar events
    $calendarEvents = [];
    $currentYear = date('Y');
    
    foreach ($allBirthdays as $user) {
        $birthMonth = intval($user['birth_month']);
        $birthDay = intval($user['birth_day']);
        
        // Create birthday event for current year
        $birthdayDate = sprintf('%04d-%02d-%02d', $currentYear, $birthMonth, $birthDay);
        
        $calendarEvents[] = [
            'id' => 'birthday-' . $user['id'],
            'title' => $user['full_name'] . "'s Birthday",
            'start' => $birthdayDate,
            'allDay' => true,
            'event_type' => 'birthday',
            'description' => $user['full_name'] . ' (' . ($user['agent_code'] ?: 'Staff') . ') - Birthday',
            'user_id' => $user['id'],
            'user_role' => $user['user_role']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $calendarEvents
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch birthdays: ' . $e->getMessage()
    ]);
}
?>
