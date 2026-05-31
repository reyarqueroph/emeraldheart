# Gmail SMTP Setup Guide for OTP Email Feature

## ✅ Files Created
1. `api/config/email-config.php` - Email configuration file
2. `api/lib/EmailSender.php` - SMTP email sender class
3. `api/auth/forgot-password-admin.php` - Updated to use SMTP

## 📧 Setup Instructions

### Step 1: Enable 2-Step Verification on Gmail

1. Go to your Google Account: https://myaccount.google.com/security
2. Scroll down to "How you sign in to Google"
3. Click on "2-Step Verification"
4. Follow the prompts to enable it (if not already enabled)

### Step 2: Generate Gmail App Password

1. After enabling 2-Step Verification, go back to: https://myaccount.google.com/security
2. Scroll down to "How you sign in to Google"
3. Click on "App passwords" (or search for "App passwords" in the search bar)
4. You may need to sign in again
5. In the "Select app" dropdown, choose **"Mail"**
6. In the "Select device" dropdown, choose **"Windows Computer"** or **"Other (Custom name)"**
7. If you choose "Other", enter a name like: **"eHeart Admin Portal"**
8. Click **"Generate"**
9. Google will show you a **16-character password** (like: `abcd efgh ijkl mnop`)
10. **COPY THIS PASSWORD** - you'll need it in the next step

### Step 3: Configure Email Settings

1. Open the file: `api/config/email-config.php`
2. Find this line:
   ```php
   'smtp_password' => '', // PASTE YOUR GMAIL APP PASSWORD HERE
   ```
3. Paste your 16-character app password between the quotes (remove spaces):
   ```php
   'smtp_password' => 'abcdefghijklmnop',
   ```
4. Save the file

### Step 4: Test Email Sending

1. Go to your admin login page
2. Click "Forgot Password?"
3. Enter your admin username (e.g., `admin` or `eheart_admin`)
4. Click "Send OTP"
5. Check your email: **reyarqueroofficial25@gmail.com**
6. You should receive an email with the OTP code within a few seconds

## 🔧 Configuration File Location

**File:** `api/config/email-config.php`

```php
return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_auth' => true,
    'smtp_username' => 'reyarqueroofficial25@gmail.com',
    'smtp_password' => '', // ← PASTE YOUR APP PASSWORD HERE
    'from_email' => 'reyarqueroofficial25@gmail.com',
    'from_name' => 'eHeart Admin Portal',
    'admin_email' => 'reyarqueroofficial25@gmail.com'
];
```

## 🎯 How It Works

1. **Before Configuration:**
   - OTP is displayed on screen for testing
   - No email is sent

2. **After Configuration:**
   - OTP is sent to your Gmail address via SMTP
   - Email includes professional HTML template
   - OTP is valid for 15 minutes

## 🔒 Security Notes

- **Never commit** the `email-config.php` file with your password to Git
- The app password is specific to this application
- You can revoke it anytime from your Google Account settings
- The app password is different from your Gmail password

## 🐛 Troubleshooting

### Email Not Received?

1. **Check Spam/Junk folder**
2. **Verify app password is correct** (16 characters, no spaces)
3. **Check Gmail settings:**
   - 2-Step Verification is enabled
   - App password was generated correctly
4. **Check server logs** for error messages
5. **Test with debug mode** - OTP will show on screen if email fails

### "Less secure app access" Error?

- This is an old setting that's no longer needed
- Use App Passwords instead (as described above)

### Connection Timeout?

- Check if your firewall is blocking port 587
- Try using port 465 with SSL instead:
  ```php
  'smtp_port' => 465,
  'smtp_secure' => 'ssl',
  ```

## 📝 Email Template

The OTP email includes:
- eHeart branding header
- Large, clear OTP code display
- 15-minute validity notice
- Security warnings
- Professional HTML design

## ✨ Features

✅ Secure SMTP connection (TLS encryption)
✅ Gmail authentication
✅ HTML email template
✅ Fallback to debug mode if email fails
✅ No external dependencies required
✅ Works on localhost and production

## 🚀 Next Steps

After setting up email:
1. Test the complete password reset flow
2. Verify OTP emails are received
3. Test OTP expiration (15 minutes)
4. Test invalid OTP handling
5. Test password reset completion

## 📞 Support

If you encounter any issues:
1. Check the browser console for errors
2. Check PHP error logs
3. Verify all configuration settings
4. Test with the debug OTP display first

---

**Current Status:** Email configuration ready, waiting for Gmail App Password
