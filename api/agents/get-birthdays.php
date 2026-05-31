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
    // Get current user's birthday
    $stmt = $db->prepare("SELECT birthday, full_name FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $today = new DateTime();
    $currentMonth = $today->format('m');
    $currentDay = $today->format('d');
    
    $upcomingBirthdays = [];
    
    // Check if current user's birthday is today
    if ($currentUser && $currentUser['birthday']) {
        $birthday = new DateTime($currentUser['birthday']);
        $birthdayMonth = $birthday->format('m');
        $birthdayDay = $birthday->format('d');
        
        if ($birthdayMonth == $currentMonth && $birthdayDay == $currentDay) {
            $upcomingBirthdays[] = [
                'name' => $currentUser['full_name'],
                'birthday' => $currentUser['birthday'],
                'is_today' => true,
                'is_current_user' => true,
                'days_until' => 0,
                'formatted_date' => $birthday->format('F d')
            ];
        }
    }
    
    // Get upcoming birthdays in the next 7 days (including today)
    // This query finds birthdays by comparing month and day
    $stmt = $db->query("
        SELECT id, full_name, agent_code, birthday,
               EXTRACT(MONTH FROM birthday) as birth_month,
               EXTRACT(DAY FROM birthday) as birth_day
        FROM users 
        WHERE birthday IS NOT NULL 
          AND status = 'active'
        ORDER BY birth_month, birth_day
    ");
    
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allUsers as $user) {
        $birthMonth = intval($user['birth_month']);
        $birthDay = intval($user['birth_day']);
        
        // Create a birthday date for this year
        $birthdayThisYear = DateTime::createFromFormat('Y-m-d', $today->format('Y') . '-' . sprintf('%02d', $birthMonth) . '-' . sprintf('%02d', $birthDay));
        
        // If birthday already passed this year, check next year
        if ($birthdayThisYear < $today) {
            $birthdayThisYear->modify('+1 year');
        }
        
        // Calculate days until birthday
        $interval = $today->diff($birthdayThisYear);
        $daysUntil = $interval->days;
        
        // Only include birthdays within the next 7 days
        if ($daysUntil <= 7) {
            $isCurrentUser = ($user['id'] == $_SESSION['user_id']);
            $isToday = ($daysUntil == 0);
            
            // Skip if already added (current user's birthday today)
            if ($isCurrentUser && $isToday) {
                continue;
            }
            
            $upcomingBirthdays[] = [
                'name' => $user['full_name'],
                'agent_code' => $user['agent_code'],
                'birthday' => $user['birthday'],
                'is_today' => $isToday,
                'is_current_user' => $isCurrentUser,
                'days_until' => $daysUntil,
                'formatted_date' => $birthdayThisYear->format('F d')
            ];
        }
    }
    
    // Sort by days until birthday
    usort($upcomingBirthdays, function($a, $b) {
        return $a['days_until'] - $b['days_until'];
    });
    
    echo json_encode([
        'success' => true,
        'data' => $upcomingBirthdays
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch birthdays: ' . $e->getMessage()
    ]);
}
?>
