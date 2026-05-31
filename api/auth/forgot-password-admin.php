<?php
// Suppress all PHP errors/warnings from being displayed
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any stray output
ob_start();

session_start();

// Clear any previous output
ob_clean();

header('Content-Type: application/json');

// Get input data
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);
$action = isset($data['action']) ? $data['action'] : '';

// Admin email (hardcoded as requested)
$ADMIN_EMAIL = 'reyarqueroofficial25@gmail.com';

// Database connection
try {
    $host = "localhost";
    $db_name = "pru_life_db";
    $username = "root";
    $password = "";
    
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($action === 'send_otp') {
    $username = isset($data['username']) ? trim($data['username']) : '';
    
    if (empty($username)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit;
    }
    
    // Verify username exists and is admin
    try {
        $stmt = $db->prepare("SELECT id, username, role FROM users WHERE username = :username AND role = 'admin'");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Admin account not found. Please check your username.']);
            exit;
        }
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
    
    // Generate 6-digit OTP
    $otp = sprintf('%06d', mt_rand(0, 999999));
    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Store OTP in database
    try {
        // Create OTP table if not exists
        $db->exec("
            CREATE TABLE IF NOT EXISTS password_reset_otps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                otp VARCHAR(6) NOT NULL,
                expires_at DATETIME NOT NULL,
                used TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_otp (otp)
            )
        ");
        
        // Delete old OTPs for this user
        $stmt = $db->prepare("DELETE FROM password_reset_otps WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user['id']]);
        
        // Insert new OTP
        $stmt = $db->prepare("INSERT INTO password_reset_otps (user_id, otp, expires_at) VALUES (:user_id, :otp, :expires_at)");
        $stmt->execute([
            ':user_id' => $user['id'],
            ':otp' => $otp,
            ':expires_at' => $expires_at
        ]);
        
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to generate OTP']);
        exit;
    }
    
    // Send email using SMTP
    $emailConfig = require_once __DIR__ . '/../config/email-config.php';
    
    // Check if SMTP password is configured
    if (empty($emailConfig['smtp_password'])) {
        // Email not configured, show OTP in response for testing
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'OTP generated (Email not configured). OTP: ' . $otp,
            'email_hint' => substr($ADMIN_EMAIL, 0, 3) . '***@' . explode('@', $ADMIN_EMAIL)[1],
            'debug_otp' => $otp
        ]);
        exit;
    }
    
    require_once __DIR__ . '/../lib/EmailSender.php';
    $emailSender = new EmailSender($emailConfig);
    
    $subject = 'eHeart Admin - Password Reset OTP';
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #D50032; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; }
            .otp-box { background: white; border: 2px dashed #D50032; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
            .otp-code { font-size: 32px; font-weight: bold; color: #D50032; letter-spacing: 8px; }
            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1 style='margin:0;'>eHeart Admin Portal</h1>
                <p style='margin:5px 0 0;'>PRU LIFE U.K.</p>
            </div>
            <div class='content'>
                <h2 style='color:#D50032;'>Password Reset Request</h2>
                <p>Hello <strong>" . htmlspecialchars($user['username']) . "</strong>,</p>
                <p>You have requested to reset your admin password. Use the OTP code below to proceed:</p>
                
                <div class='otp-box'>
                    <p style='margin:0 0 10px;color:#666;font-size:14px;'>Your OTP Code:</p>
                    <div class='otp-code'>" . $otp . "</div>
                    <p style='margin:10px 0 0;color:#666;font-size:12px;'>Valid for 15 minutes</p>
                </div>
                
                <div class='warning'>
                    <strong>Security Notice:</strong><br>
                    Do not share this code with anyone<br>
                    This code expires in 15 minutes<br>
                    If you did not request this, please ignore this email
                </div>
                
                <p style='margin-top:20px;'>If you did not request a password reset, please contact your system administrator immediately.</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from eHeart Admin Portal<br>
                PRU LIFE U.K. &copy; " . date('Y') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Send email via SMTP
    $emailSent = $emailSender->sendEmail($ADMIN_EMAIL, $subject, $message);
    
    ob_clean();
    if ($emailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent to your email address',
            'email_hint' => substr($ADMIN_EMAIL, 0, 3) . '***@' . explode('@', $ADMIN_EMAIL)[1]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'OTP generated (Email sending failed). OTP: ' . $otp,
            'email_hint' => substr($ADMIN_EMAIL, 0, 3) . '***@' . explode('@', $ADMIN_EMAIL)[1],
            'debug_otp' => $otp
        ]);
    }
    
} elseif ($action === 'verify_otp') {
    $username = isset($data['username']) ? trim($data['username']) : '';
    $otp = isset($data['otp']) ? trim($data['otp']) : '';
    
    if (empty($username) || empty($otp)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Username and OTP are required']);
        exit;
    }
    
    // Get user
    try {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username AND role = 'admin'");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Admin account not found']);
            exit;
        }
        
        // Verify OTP
        $stmt = $db->prepare("
            SELECT id, otp, expires_at FROM password_reset_otps 
            WHERE user_id = :user_id 
              AND otp = :otp 
              AND used = 0
        ");
        $stmt->execute([
            ':user_id' => $user['id'],
            ':otp' => $otp
        ]);
        $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$otpRecord) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
            exit;
        }
        
        // Check if OTP is expired
        $expiresAt = strtotime($otpRecord['expires_at']);
        $now = time();
        
        if ($now > $expiresAt) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
            exit;
        }
        
        // Mark OTP as used
        $stmt = $db->prepare("UPDATE password_reset_otps SET used = 1 WHERE id = :id");
        $stmt->execute([':id' => $otpRecord['id']]);
        
        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $_SESSION['password_reset_token'] = $resetToken;
        $_SESSION['password_reset_user_id'] = $user['id'];
        $_SESSION['password_reset_expires'] = time() + 600; // 10 minutes
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'OTP verified successfully',
            'reset_token' => $resetToken
        ]);
        
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Verification error']);
        exit;
    }
    
} elseif ($action === 'reset_password') {
    $resetToken = isset($data['reset_token']) ? trim($data['reset_token']) : '';
    $newPassword = isset($data['new_password']) ? trim($data['new_password']) : '';
    
    if (empty($resetToken) || empty($newPassword)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Reset token and new password are required']);
        exit;
    }
    
    // Verify reset token
    if (!isset($_SESSION['password_reset_token']) || 
        $_SESSION['password_reset_token'] !== $resetToken ||
        !isset($_SESSION['password_reset_expires']) ||
        $_SESSION['password_reset_expires'] < time()) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid or expired reset token']);
        exit;
    }
    
    $userId = $_SESSION['password_reset_user_id'];
    
    // Validate password strength
    if (strlen($newPassword) < 8) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    try {
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $userId
        ]);
        
        // Clear session
        unset($_SESSION['password_reset_token']);
        unset($_SESSION['password_reset_user_id']);
        unset($_SESSION['password_reset_expires']);
        
        // Delete all OTPs for this user
        $stmt = $db->prepare("DELETE FROM password_reset_otps WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
        
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
        exit;
    }
    
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// End output buffering and send
ob_end_flush();
?>
