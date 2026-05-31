# Setup Email Sending for OTP Feature

## Current Status
- OTP feature is working
- OTP is displayed on screen for testing
- Emails are NOT being sent (requires mail server configuration)

## Option 1: Use PHPMailer with Gmail SMTP (Recommended)

### Step 1: Install PHPMailer
Run this command in your project root:
```bash
composer require phpmailer/phpmailer
```

### Step 2: Get Gmail App Password
1. Go to your Google Account: https://myaccount.google.com/
2. Click "Security" in the left menu
3. Enable "2-Step Verification" if not already enabled
4. Search for "App passwords" 
5. Create a new app password for "Mail"
6. Copy the 16-character password

### Step 3: I'll update the code to use PHPMailer
Once you have the app password, tell me and I'll update the forgot-password-admin.php file.

## Option 2: Use a Local Mail Testing Tool

### MailHog (Easy for Testing)
1. Download MailHog: https://github.com/mailhog/MailHog/releases
2. Run MailHog.exe
3. Configure PHP to use MailHog (I can help with this)
4. View emails at: http://localhost:8025

## Option 3: Deploy to a Server
Deploy your application to a web hosting server that has mail configured (most hosting providers have this).

## For Now: Testing Without Email
The current implementation shows the OTP on screen, so you can:
1. Click "Send OTP"
2. See the OTP in the notification (top-right corner)
3. Enter it in the OTP boxes
4. Complete the password reset

This allows you to test the complete flow without email configuration.
