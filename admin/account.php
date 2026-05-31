<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../api/config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$admin_name = $admin['full_name'] ?? 'Administrator';
$admin_email = $admin['email'] ?? '';
$admin_username = $admin['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Settings – eHeart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .account-header {
            background: linear-gradient(135deg, var(--pru-dark) 0%, #2a0010 60%, var(--pru-red) 100%);
            border-radius: var(--radius-lg);
            padding: 32px;
            margin-bottom: 28px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .account-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .account-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .account-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 900;
            color: white;
            flex-shrink: 0;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }

        .account-info h2 {
            font-size: 28px;
            font-weight: 900;
            margin: 0 0 6px;
        }

        .account-info p {
            font-size: 14px;
            opacity: 0.7;
            margin: 0;
        }

        .settings-card {
            background: white;
            border: 1px solid var(--pru-border);
            border-radius: var(--radius-lg);
            padding: 28px;
            margin-bottom: 20px;
        }

        .settings-card-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--pru-text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-card-title i {
            color: var(--pru-red);
        }

        .info-row {
            display: flex;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid var(--pru-border);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--pru-muted);
            width: 140px;
            flex-shrink: 0;
        }

        .info-value {
            font-size: 14px;
            color: var(--pru-text);
            font-weight: 600;
        }

        .password-strength {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: #dc3545;
        }

        .password-strength-bar.medium {
            width: 66%;
            background: #ffc107;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: #28a745;
        }

        .password-requirements {
            margin-top: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 12px;
        }

        .password-requirements ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .password-requirements li {
            color: #666;
            margin-bottom: 4px;
        }

        .password-requirements li.valid {
            color: #28a745;
        }

        .password-requirements li i {
            margin-right: 6px;
        }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<main class="pru-main">
    <div class="pru-container">
        
        <!-- Account Header -->
        <div class="account-header">
            <div class="account-header-content">
                <div class="account-avatar">
                    <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                </div>
                <div class="account-info">
                    <h2><?php echo htmlspecialchars($admin_name); ?></h2>
                    <p><i class="fas fa-shield-alt"></i> Administrator Account</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Account Information -->
            <div class="col-lg-6">
                <div class="settings-card">
                    <h3 class="settings-card-title">
                        <i class="fas fa-user-circle"></i>
                        Account Information
                    </h3>
                    <div class="info-row">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin_name); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin_username); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin_email); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Role</div>
                        <div class="info-value">
                            <span class="badge-status badge-active">Administrator</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-lg-6">
                <div class="settings-card">
                    <h3 class="settings-card-title">
                        <i class="fas fa-key"></i>
                        Change Password
                    </h3>
                    <form id="changePasswordForm">
                        <div class="form-group mb-3">
                            <label class="form-label">Current Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="currentPassword" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('currentPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">New Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control" id="newPassword" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="strengthBar"></div>
                            </div>
                            <div class="password-requirements">
                                <strong>Password Requirements:</strong>
                                <ul id="passwordChecks">
                                    <li id="check-length"><i class="fas fa-circle"></i> At least 8 characters</li>
                                    <li id="check-uppercase"><i class="fas fa-circle"></i> One uppercase letter</li>
                                    <li id="check-lowercase"><i class="fas fa-circle"></i> One lowercase letter</li>
                                    <li id="check-number"><i class="fas fa-circle"></i> One number</li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label">Confirm New Password *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                <input type="password" class="form-control" id="confirmPassword" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="matchMessage"></small>
                        </div>

                        <button type="submit" class="btn-pru btn-pru-sm w-100">
                            <i class="fas fa-save"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Password strength checker
document.getElementById('newPassword').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthBar');
    
    // Check requirements
    const checks = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password)
    };
    
    // Update visual checks
    document.getElementById('check-length').classList.toggle('valid', checks.length);
    document.getElementById('check-uppercase').classList.toggle('valid', checks.uppercase);
    document.getElementById('check-lowercase').classList.toggle('valid', checks.lowercase);
    document.getElementById('check-number').classList.toggle('valid', checks.number);
    
    // Update icons
    Object.keys(checks).forEach(key => {
        const el = document.getElementById(`check-${key}`);
        const icon = el.querySelector('i');
        if (checks[key]) {
            icon.classList.remove('fa-circle');
            icon.classList.add('fa-check-circle');
        } else {
            icon.classList.remove('fa-check-circle');
            icon.classList.add('fa-circle');
        }
    });
    
    // Calculate strength
    const validCount = Object.values(checks).filter(v => v).length;
    strengthBar.className = 'password-strength-bar';
    
    if (validCount <= 1) {
        strengthBar.classList.add('weak');
    } else if (validCount <= 3) {
        strengthBar.classList.add('medium');
    } else {
        strengthBar.classList.add('strong');
    }
});

// Check password match
document.getElementById('confirmPassword').addEventListener('input', function() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = this.value;
    const matchMessage = document.getElementById('matchMessage');
    
    if (confirmPassword === '') {
        matchMessage.textContent = '';
        matchMessage.className = 'text-muted';
    } else if (newPassword === confirmPassword) {
        matchMessage.textContent = '✓ Passwords match';
        matchMessage.className = 'text-success';
    } else {
        matchMessage.textContent = '✗ Passwords do not match';
        matchMessage.className = 'text-danger';
    }
});

// Handle form submission
document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validate passwords match
    if (newPassword !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    // Validate password strength
    const checks = {
        length: newPassword.length >= 8,
        uppercase: /[A-Z]/.test(newPassword),
        lowercase: /[a-z]/.test(newPassword),
        number: /[0-9]/.test(newPassword)
    };
    
    if (!Object.values(checks).every(v => v)) {
        showToast('Password does not meet all requirements', 'error');
        return;
    }
    
    // Submit form
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    
    try {
        const response = await fetch('../api/auth/change-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Password updated successfully!', 'success');
            this.reset();
            document.getElementById('strengthBar').className = 'password-strength-bar';
            document.getElementById('matchMessage').textContent = '';
            
            // Reset password checks
            ['length', 'uppercase', 'lowercase', 'number'].forEach(key => {
                const el = document.getElementById(`check-${key}`);
                el.classList.remove('valid');
                const icon = el.querySelector('i');
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
            });
        } else {
            showToast(result.message || 'Failed to update password', 'error');
        }
    } catch (error) {
        console.error('Password change error:', error);
        showToast('An error occurred. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Password';
    }
});
</script>

</body>
</html>
