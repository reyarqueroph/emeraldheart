<?php
/**
 * Email Configuration for Gmail SMTP
 * 
 * SETUP INSTRUCTIONS:
 * 1. Go to https://myaccount.google.com/security
 * 2. Enable "2-Step Verification"
 * 3. Search for "App passwords"
 * 4. Create a new app password for "Mail"
 * 5. Copy the 16-character password and paste it below
 */

return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls', // or 'ssl' for port 465
    'smtp_auth' => true,
    'smtp_username' => 'reyarqueroofficial25@gmail.com', // Your Gmail address
    'smtp_password' => 'ofanxdxjfvaimyta', // Gmail App Password
    'from_email' => 'reyarqueroofficial25@gmail.com',
    'from_name' => 'eHeart Admin Portal',
    'admin_email' => 'reyarqueroofficial25@gmail.com'
];
?>
