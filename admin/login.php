<?php
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    header('Location: dashboard.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            background: #1C1C1C;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Background pattern */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(213,0,50,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(213,0,50,0.08) 0%, transparent 50%);
            pointer-events: none;
        }

        .admin-login-wrap {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        /* Top badge */
        .admin-badge {
            text-align: center;
            margin-bottom: 28px;
        }

        .admin-badge .logo {
            width: 64px; height: 64px;
            background: #D50032;
            border-radius: 16px;
            display: inline-flex;
            align-items: center; justify-content: center;
            font-size: 26px; font-weight: 900; color: white;
            box-shadow: 0 8px 28px rgba(213,0,50,0.45);
            margin-bottom: 14px;
        }

        .admin-badge h2 {
            color: white;
            font-size: 20px; font-weight: 800;
            margin: 0 0 4px;
        }

        .admin-badge p {
            color: rgba(255,255,255,0.35);
            font-size: 12px; margin: 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Card */
        .admin-card {
            background: white;
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.5);
        }

        .admin-card .card-title {
            font-size: 16px; font-weight: 800;
            color: #1C1C1C; margin: 0 0 4px;
        }

        .admin-card .card-sub {
            font-size: 12px; color: #999;
            margin: 0 0 24px;
        }

        /* Role pill */
        .role-pill {
            display: inline-flex;
            align-items: center; gap: 6px;
            background: rgba(213,0,50,0.08);
            color: #D50032;
            border: 1px solid rgba(213,0,50,0.2);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 20px;
        }

        /* Fields */
        .field { margin-bottom: 16px; }

        .field label {
            display: block;
            font-size: 11px; font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px;
        }

        .field-wrap { position: relative; }

        .field-wrap .fi {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: #bbb; font-size: 13px;
            pointer-events: none;
        }

        .field-wrap input {
            width: 100%;
            padding: 11px 14px 11px 38px;
            border: 1.5px solid #E0E0E0;
            border-radius: 10px;
            font-size: 13px; color: #1C1C1C;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
        }

        .field-wrap input:focus {
            border-color: #D50032;
            box-shadow: 0 0 0 3px rgba(213,0,50,0.08);
            background: white;
        }

        .field-wrap .eye-btn {
            position: absolute; right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #bbb; cursor: pointer;
            font-size: 13px; padding: 4px;
            transition: color 0.2s;
        }

        .field-wrap .eye-btn:hover { color: #D50032; }

        /* Submit */
        .btn-admin-login {
            width: 100%;
            padding: 13px;
            background: #D50032;
            color: white; border: none;
            border-radius: 10px;
            font-size: 14px; font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.2s;
            margin-top: 8px;
        }

        .btn-admin-login:hover {
            background: #b0002a;
            box-shadow: 0 6px 20px rgba(213,0,50,0.4);
            transform: translateY(-1px);
        }

        .btn-admin-login:disabled {
            opacity: 0.7; cursor: not-allowed; transform: none;
        }

        /* Forgot password link */
        .forgot-link {
            text-align: center;
            margin-top: 12px;
        }

        .forgot-link a {
            color: #D50032;
            font-size: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .forgot-link a:hover {
            color: #b0002a;
            text-decoration: underline;
        }

        /* Modal overlay */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Modal box */
        .modal-box {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            padding: 24px 28px;
            border-bottom: 1px solid #E0E0E0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            color: #1C1C1C;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            color: #999;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: #D50032;
        }

        .modal-body {
            padding: 28px;
        }

        .modal-body p {
            color: #666;
            font-size: 13px;
            margin: 0 0 20px;
            line-height: 1.6;
        }

        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .step-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #E0E0E0;
            transition: all 0.3s;
        }

        .step-dot.active {
            background: #D50032;
            width: 32px;
            border-radius: 5px;
        }

        .otp-input-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 24px 0;
        }

        .otp-input {
            width: 50px;
            height: 56px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            border: 2px solid #E0E0E0;
            border-radius: 10px;
            outline: none;
            transition: all 0.2s;
        }

        .otp-input:focus {
            border-color: #D50032;
            box-shadow: 0 0 0 3px rgba(213, 0, 50, 0.1);
        }

        .btn-modal {
            width: 100%;
            padding: 13px;
            background: #D50032;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-modal:hover {
            background: #b0002a;
            transform: translateY(-1px);
        }

        .btn-modal:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-modal-secondary {
            background: #f5f5f5;
            color: #666;
            margin-top: 10px;
        }

        .btn-modal-secondary:hover {
            background: #e0e0e0;
            color: #333;
        }

        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #17a2b8;
            padding: 14px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 12px;
            color: #0c5460;
        }

        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 14px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 12px;
            color: #155724;
        }

        .pw-strength {
            height: 4px;
            background: #E0E0E0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .pw-strength.weak { background: #dc3545; width: 33%; }
        .pw-strength.medium { background: #ffc107; width: 66%; }
        .pw-strength.strong { background: #28a745; width: 100%; }

        .pw-hint {
            font-size: 11px;
            margin-top: 6px;
            color: #999;
        }

        /* Footer link */
        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: rgba(255,255,255,0.4);
            font-size: 12px; text-decoration: none;
            transition: color 0.2s;
        }

        .back-link a:hover { color: rgba(255,255,255,0.7); }

        /* Toast */
        .toast-stack {
            position: fixed; top: 20px; right: 20px;
            z-index: 9999;
            display: flex; flex-direction: column; gap: 10px;
        }

        .pru-toast {
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            padding: 14px 18px;
            display: flex; align-items: flex-start; gap: 12px;
            min-width: 260px;
            border-left: 4px solid #D50032;
            animation: slideIn 0.3s ease;
        }

        .pru-toast.success { border-left-color: #28a745; }
        .pru-toast.error   { border-left-color: #dc3545; }

        .pru-toast .t-icon { font-size: 15px; margin-top: 1px; }
        .pru-toast.success .t-icon { color: #28a745; }
        .pru-toast.error   .t-icon { color: #dc3545; }

        .pru-toast .t-msg { font-size: 13px; color: #333; flex: 1; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="admin-login-wrap">
    <div class="admin-badge">
        <div class="logo" style="font-size:18px;font-weight:900;letter-spacing:-1px;">eH</div>
        <h2>eHeart</h2>
        <p>PRU LIFE U.K. · Administration Portal</p>
    </div>

    <div class="admin-card">
        <div class="card-title">Admin Sign In</div>
        <div class="card-sub">Restricted access — authorized personnel only</div>

        <div class="role-pill">
            <i class="fas fa-shield-alt"></i> Administrator
        </div>

        <form id="adminLoginForm" novalidate>
            <div class="field">
                <label>Username</label>
                <div class="field-wrap">
                    <i class="fas fa-user-shield fi"></i>
                    <input type="text" id="adminUser" placeholder="Enter admin username"
                           required autocomplete="username">
                </div>
            </div>

            <div class="field">
                <label>Password</label>
                <div class="field-wrap">
                    <i class="fas fa-lock fi"></i>
                    <input type="password" id="adminPass" placeholder="Enter admin password"
                           required autocomplete="current-password" style="padding-right:40px;">
                    <button type="button" class="eye-btn" id="eyeBtn">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-admin-login" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i> Sign In to Admin Panel
            </button>
        </form>

        <div class="forgot-link">
            <a href="#" id="forgotPasswordLink">
                <i class="fas fa-key"></i> Forgot Password?
            </a>
        </div>
    </div>

    <div class="back-link">
        <a href="../index.php">
            <i class="fas fa-arrow-left" style="margin-right:5px;"></i>
            Back to Home
        </a>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal-overlay" id="forgotPasswordModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Reset Admin Password</h3>
            <button class="modal-close" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <!-- Step 1: Enter Username -->
            <div id="step1" class="modal-step">
                <div class="step-indicator">
                    <div class="step-dot active"></div>
                    <div class="step-dot"></div>
                    <div class="step-dot"></div>
                </div>
                <p>Enter your admin username to receive a One-Time Password (OTP) via email.</p>
                <div class="field">
                    <label>Admin Username</label>
                    <div class="field-wrap">
                        <i class="fas fa-user-shield fi"></i>
                        <input type="text" id="resetUsername" placeholder="Enter your username">
                    </div>
                </div>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i> An OTP will be sent to the registered admin email address.
                </div>
                <button class="btn-modal" id="sendOtpBtn">
                    <i class="fas fa-paper-plane"></i> Send OTP
                </button>
                <button class="btn-modal btn-modal-secondary" id="cancelStep1">
                    Cancel
                </button>
            </div>

            <!-- Step 2: Enter OTP -->
            <div id="step2" class="modal-step" style="display:none;">
                <div class="step-indicator">
                    <div class="step-dot"></div>
                    <div class="step-dot active"></div>
                    <div class="step-dot"></div>
                </div>
                <p>Enter the 6-digit OTP sent to your email address.</p>
                <div class="info-box" id="emailHint">
                    <i class="fas fa-envelope"></i> OTP sent to: <strong id="emailHintText"></strong>
                </div>
                <div class="otp-input-group">
                    <input type="text" class="otp-input" maxlength="1" id="otp1" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" id="otp2" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" id="otp3" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" id="otp4" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" id="otp5" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" id="otp6" autocomplete="off">
                </div>
                <button class="btn-modal" id="verifyOtpBtn">
                    <i class="fas fa-check-circle"></i> Verify OTP
                </button>
                <button class="btn-modal btn-modal-secondary" id="resendOtpBtn">
                    <i class="fas fa-redo"></i> Resend OTP
                </button>
            </div>

            <!-- Step 3: Set New Password -->
            <div id="step3" class="modal-step" style="display:none;">
                <div class="step-indicator">
                    <div class="step-dot"></div>
                    <div class="step-dot"></div>
                    <div class="step-dot active"></div>
                </div>
                <p>Create a new strong password for your admin account.</p>
                <div class="success-box">
                    <i class="fas fa-check-circle"></i> OTP verified successfully! You can now set a new password.
                </div>
                <div class="field">
                    <label>New Password</label>
                    <div class="field-wrap">
                        <i class="fas fa-lock fi"></i>
                        <input type="password" id="newPassword" placeholder="Enter new password" style="padding-right:40px;">
                        <button type="button" class="eye-btn" id="eyeBtn2">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="pw-strength" id="pwStrength"></div>
                    <div class="pw-hint" id="pwHint">Password must be at least 8 characters</div>
                </div>
                <div class="field">
                    <label>Confirm Password</label>
                    <div class="field-wrap">
                        <i class="fas fa-lock fi"></i>
                        <input type="password" id="confirmPassword" placeholder="Confirm new password">
                    </div>
                </div>
                <button class="btn-modal" id="resetPasswordBtn">
                    <i class="fas fa-save"></i> Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

<div class="toast-stack" id="toastStack"></div>

<script>
function showToast(msg, type = 'info') {
    const stack = document.getElementById('toastStack');
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
    const t = document.createElement('div');
    t.className = `pru-toast ${type}`;
    t.innerHTML = `<i class="fas ${icons[type]||icons.info} t-icon"></i><div class="t-msg">${msg}</div>`;
    stack.appendChild(t);
    
    // Keep OTP messages visible longer (15 seconds instead of 4)
    const duration = msg.includes('OTP:') ? 15000 : 4000;
    
    setTimeout(() => { t.style.opacity='0'; t.style.transform='translateX(100%)'; t.style.transition='all 0.3s'; setTimeout(()=>t.remove(),300); }, duration);
}

// Password toggle
document.getElementById('eyeBtn').addEventListener('click', function() {
    const input = document.getElementById('adminPass');
    const icon  = this.querySelector('i');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
});

// Login submit
document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn  = document.getElementById('loginBtn');
    const user = document.getElementById('adminUser').value.trim();
    const pass = document.getElementById('adminPass').value;

    if (!user || !pass) {
        showToast('Please enter username and password.', 'error');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';

    try {
        const res  = await fetch('../api/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: user, password: pass, role: 'admin' })
        });
        const data = await res.json();

        if (data.success && data.role === 'admin') {
            showToast('Welcome back, Admin!', 'success');
            setTimeout(() => window.location.href = 'dashboard.php', 800);
        } else if (data.success && data.role !== 'admin') {
            showToast('This account does not have admin privileges.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In to Admin Panel';
        } else {
            showToast(data.message || 'Login failed.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In to Admin Panel';
        }
    } catch (_) {
        showToast('Connection error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In to Admin Panel';
    }
});

// ============================================
// FORGOT PASSWORD FUNCTIONALITY
// ============================================

let currentUsername = '';
let resetToken = '';

// Open modal
document.getElementById('forgotPasswordLink').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('forgotPasswordModal').classList.add('active');
    showStep(1);
});

// Close modal
document.getElementById('closeModal').addEventListener('click', () => {
    document.getElementById('forgotPasswordModal').classList.remove('active');
    resetModal();
});

// Close on overlay click
document.getElementById('forgotPasswordModal').addEventListener('click', (e) => {
    if (e.target.id === 'forgotPasswordModal') {
        document.getElementById('forgotPasswordModal').classList.remove('active');
        resetModal();
    }
});

// Cancel step 1
document.getElementById('cancelStep1').addEventListener('click', () => {
    document.getElementById('forgotPasswordModal').classList.remove('active');
    resetModal();
});

// Show specific step
function showStep(step) {
    document.getElementById('step1').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('step2').style.display = step === 2 ? 'block' : 'none';
    document.getElementById('step3').style.display = step === 3 ? 'block' : 'none';
}

// Reset modal
function resetModal() {
    showStep(1);
    document.getElementById('resetUsername').value = '';
    document.querySelectorAll('.otp-input').forEach(input => input.value = '');
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    currentUsername = '';
    resetToken = '';
}

// Step 1: Send OTP
document.getElementById('sendOtpBtn').addEventListener('click', async () => {
    const username = document.getElementById('resetUsername').value.trim();
    
    if (!username) {
        showToast('Please enter your username', 'error');
        return;
    }
    
    const btn = document.getElementById('sendOtpBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending OTP...';
    
    // First test with debug endpoint
    const apiUrl = '../api/auth/forgot-password-admin.php';
    console.log('Attempting to connect to:', apiUrl);
    
    try {
        const res = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'send_otp',
                username: username
            })
        });
        
        console.log('Response status:', res.status);
        console.log('Response ok:', res.ok);
        
        if (!res.ok) {
            const errorText = await res.text();
            console.error('Response error text:', errorText);
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const contentType = res.headers.get('content-type');
        console.log('Content-Type:', contentType);
        
        // Get the raw text first to see what we're receiving
        const responseText = await res.text();
        console.log('Raw response:', responseText);
        
        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response text:', responseText.substring(0, 500));
            throw new Error('Server returned invalid JSON. Check server logs.');
        }
        
        console.log('Response data:', data);
        
        if (data.success) {
            currentUsername = username;
            document.getElementById('emailHintText').textContent = data.email_hint || 'your registered email';
            
            // Show debug OTP if available (for testing when email is not configured)
            if (data.debug_otp) {
                showToast('OTP: ' + data.debug_otp + ' (Email not configured, showing OTP for testing)', 'info');
            } else {
                showToast(data.message, 'success');
            }
            
            showStep(2);
            document.getElementById('otp1').focus();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Send OTP Error:', error);
        showToast('Connection error: ' + error.message + ' (Check browser console for details)', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send OTP';
    }
});

// OTP input auto-focus
document.querySelectorAll('.otp-input').forEach((input, index, inputs) => {
    input.addEventListener('input', (e) => {
        if (e.target.value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });
    
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && index > 0) {
            inputs[index - 1].focus();
        }
    });
    
    // Only allow numbers
    input.addEventListener('keypress', (e) => {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
});

// Step 2: Verify OTP
document.getElementById('verifyOtpBtn').addEventListener('click', async () => {
    const otp = Array.from(document.querySelectorAll('.otp-input'))
        .map(input => input.value)
        .join('');
    
    if (otp.length !== 6) {
        showToast('Please enter the complete 6-digit OTP', 'error');
        return;
    }
    
    const btn = document.getElementById('verifyOtpBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    
    try {
        const res = await fetch('../api/auth/forgot-password-admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'verify_otp',
                username: currentUsername,
                otp: otp
            })
        });
        
        const data = await res.json();
        
        if (data.success) {
            resetToken = data.reset_token;
            showToast(data.message, 'success');
            showStep(3);
            document.getElementById('newPassword').focus();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Connection error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Verify OTP';
    }
});

// Resend OTP
document.getElementById('resendOtpBtn').addEventListener('click', async () => {
    const btn = document.getElementById('resendOtpBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resending...';
    
    try {
        const res = await fetch('../api/auth/forgot-password-admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'send_otp',
                username: currentUsername
            })
        });
        
        const data = await res.json();
        
        if (data.success) {
            showToast('OTP resent successfully', 'success');
            document.querySelectorAll('.otp-input').forEach(input => input.value = '');
            document.getElementById('otp1').focus();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Connection error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-redo"></i> Resend OTP';
    }
});

// Password strength checker
document.getElementById('newPassword').addEventListener('input', function() {
    const password = this.value;
    const strength = document.getElementById('pwStrength');
    const hint = document.getElementById('pwHint');
    
    if (password.length === 0) {
        strength.className = 'pw-strength';
        hint.textContent = 'Password must be at least 8 characters';
        return;
    }
    
    let score = 0;
    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;
    
    if (score <= 2) {
        strength.className = 'pw-strength weak';
        hint.textContent = 'Weak password';
        hint.style.color = '#dc3545';
    } else if (score <= 4) {
        strength.className = 'pw-strength medium';
        hint.textContent = 'Medium strength password';
        hint.style.color = '#ffc107';
    } else {
        strength.className = 'pw-strength strong';
        hint.textContent = 'Strong password';
        hint.style.color = '#28a745';
    }
});

// Password toggle for new password
document.getElementById('eyeBtn2').addEventListener('click', function() {
    const input = document.getElementById('newPassword');
    const icon = this.querySelector('i');
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
});

// Step 3: Reset Password
document.getElementById('resetPasswordBtn').addEventListener('click', async () => {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (!newPassword || !confirmPassword) {
        showToast('Please fill in all fields', 'error');
        return;
    }
    
    if (newPassword.length < 8) {
        showToast('Password must be at least 8 characters', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    const btn = document.getElementById('resetPasswordBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
    
    try {
        const res = await fetch('../api/auth/forgot-password-admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'reset_password',
                reset_token: resetToken,
                new_password: newPassword
            })
        });
        
        const data = await res.json();
        
        if (data.success) {
            showToast('Password reset successfully! You can now login.', 'success');
            document.getElementById('forgotPasswordModal').classList.remove('active');
            resetModal();
            
            // Pre-fill username
            document.getElementById('adminUser').value = currentUsername;
            document.getElementById('adminPass').focus();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('Connection error. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Reset Password';
    }
});
</script>
</body>
</html>
