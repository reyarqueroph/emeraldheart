<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once '../api/config/database.php';
$db   = (new Database())->getConnection();
// Add avatar column if not exists
try { $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL"); } catch(Exception $e){}
$stmt = $db->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_name   = $user['full_name'] ?? 'Agent';
$agent_code  = $user['agent_code'] ?? '';
$initials    = strtoupper(substr($user_name, 0, 1));
$active_page = 'account';
$avatar_url  = !empty($user['avatar']) ? '../api/auth/serve-avatar.php?file=' . urlencode($user['avatar']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – My Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/agent-dashboard.css">
    <link rel="stylesheet" href="../assets/css/theme-toggle.css">
    <style>
        .avatar-wrap {
            position: relative;
            width: 90px; height: 90px;
            margin: 0 auto 16px;
        }
        .avatar-img {
            width: 90px; height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--pru-red);
        }
        .avatar-initials {
            width: 90px; height: 90px;
            background: var(--pru-red);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 34px; font-weight: 800; color: white;
        }
        .avatar-edit-btn {
            position: absolute;
            bottom: 2px; right: 2px;
            width: 28px; height: 28px;
            background: var(--pru-red);
            border: 2px solid white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 11px; color: white;
            transition: background 0.2s;
        }
        .avatar-edit-btn:hover { background: var(--pru-red-dark, #a8002a); }
        #avatarInput { display: none; }
        .avatar-upload-hint {
            font-size: 11px; color: var(--pru-muted);
            margin-top: -8px; margin-bottom: 16px;
        }
    </style>
</head>
<body class="agent-dash-body">

<?php include '../includes/agent-sidebar.php'; ?>

<div class="ad-main-wrap" id="adMainWrap">
    <main class="ad-content">

        <div class="page-header">
            <h2>My Account</h2>
            <p>View your profile information and manage your password.</p>
        </div>

        <div class="row g-4">
            <!-- Profile Card -->
            <div class="col-lg-4">
                <div class="pru-card text-center" style="padding:32px 24px;">

                    <!-- Avatar with upload -->
                    <div class="avatar-wrap">
                        <?php if ($avatar_url): ?>
                        <img src="<?php echo $avatar_url; ?>" alt="Profile" class="avatar-img" id="avatarPreview">
                        <?php else: ?>
                        <div class="avatar-initials" id="avatarInitials"><?php echo $initials; ?></div>
                        <img src="" alt="Profile" class="avatar-img" id="avatarPreview" style="display:none;">
                        <?php endif; ?>
                        <label class="avatar-edit-btn" for="avatarInput" title="Change photo">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <p class="avatar-upload-hint">Click the camera icon to change photo</p>

                    <h4 style="font-size:18px;font-weight:800;margin-bottom:4px;"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <?php
                    $posLabels = ['Agent'=>'Agent','OM'=>'Office Manager','UM'=>'Unit Manager','BM'=>'Branch Manager'];
                    $posColors = ['Agent'=>'#D50032','OM'=>'#17a2b8','UM'=>'#28a745','BM'=>'#e6a800'];
                    $posBgs    = ['Agent'=>'rgba(213,0,50,0.1)','OM'=>'rgba(23,162,184,0.1)','UM'=>'rgba(40,167,69,0.1)','BM'=>'rgba(255,193,7,0.12)'];
                    $pos       = $user['position'] ?? 'Agent';
                    $posLabel  = $posLabels[$pos] ?? $pos;
                    $posColor  = $posColors[$pos]  ?? '#6c757d';
                    $posBg     = $posBgs[$pos]     ?? 'rgba(108,117,125,0.1)';
                    ?>
                    <div style="margin-bottom:8px;">
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:20px;font-size:12px;font-weight:700;background:<?php echo $posBg; ?>;color:<?php echo $posColor; ?>;">
                            <i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($posLabel); ?>
                        </span>
                    </div>
                    <span class="badge-status badge-<?php echo $user['status']; ?>" style="margin-bottom:20px;display:inline-flex;"><?php echo ucfirst($user['status']); ?></span>
                    <div class="divider"></div>
                    <div style="text-align:left;">
                        <?php
                        $fields = [
                            ['Agent Code',   'fa-id-badge',  $user['agent_code']],
                            ['Position',     'fa-briefcase', $posLabel],
                            ['Username',     'fa-user',      $user['username']],
                            ['Email',        'fa-envelope',  $user['email']],
                            ['Member Since', 'fa-calendar',  date('M d, Y', strtotime($user['created_at']))],
                        ];
                        foreach ($fields as [$label, $icon, $value]): ?>
                        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--pru-border);">
                            <i class="fas <?php echo $icon; ?>" style="width:16px;color:var(--pru-red);font-size:13px;"></i>
                            <div>
                                <div style="font-size:10px;font-weight:700;color:var(--pru-muted);text-transform:uppercase;"><?php echo $label; ?></div>
                                <div style="font-size:13px;font-weight:600;color:var(--pru-text);"><?php echo htmlspecialchars($value); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-lg-8">
                <!-- Personal Information Card -->
                <div class="pru-card" style="margin-bottom:20px;">
                    <div class="card-header">
                        <h5><i class="fas fa-user-edit" style="color:var(--pru-red);margin-right:8px;"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <form id="personalInfoForm" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Birthday</label>
                                        <input type="date" class="form-control" id="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
                                        <small class="form-text text-muted">Your birthday will appear in the dashboard calendar</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phoneNumber" placeholder="e.g. 09123456789" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" id="address" rows="2" placeholder="Complete address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Emergency Contact Name</label>
                                        <input type="text" class="form-control" id="emergencyName" placeholder="Full name" value="<?php echo htmlspecialchars($user['emergency_contact_name'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Emergency Contact Phone</label>
                                        <input type="tel" class="form-control" id="emergencyPhone" placeholder="e.g. 09123456789" value="<?php echo htmlspecialchars($user['emergency_contact_phone'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn-pru" style="margin-top:12px;"><i class="fas fa-save"></i> Save Personal Info</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="pru-card">
                    <div class="card-header">
                        <h5><i class="fas fa-lock" style="color:var(--pru-red);margin-right:8px;"></i>Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form id="changePasswordForm" novalidate>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="currentPw" required placeholder="Enter current password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="newPw" required placeholder="Min. 8 characters">
                                        <div class="pw-strength" id="pwBar" style="width:0;margin-top:6px;"></div>
                                        <div class="pw-hint" id="pwHint"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirmPw" required placeholder="Repeat new password">
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top:8px;padding:12px;background:var(--pru-light);border-radius:var(--radius-sm);font-size:11px;color:var(--pru-muted);margin-bottom:16px;">
                                <i class="fas fa-shield-alt" style="color:var(--pru-red);margin-right:6px;"></i>
                                Password must contain uppercase, lowercase, number, and special character.
                            </div>
                            <button type="submit" class="btn-pru"><i class="fas fa-save"></i> Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<div class="toast-stack"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/scripts.js"></script>
<script>
attachPasswordStrength('newPw', 'pwBar', 'pwHint');

// Personal Information Form
document.getElementById('personalInfoForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    const res = await fetch('../api/agents/update-profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            birthday: document.getElementById('birthday').value,
            phone_number: document.getElementById('phoneNumber').value,
            address: document.getElementById('address').value,
            emergency_contact_name: document.getElementById('emergencyName').value,
            emergency_contact_phone: document.getElementById('emergencyPhone').value
        })
    });
    
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Save Personal Info';
});

// Change Password Form
document.getElementById('changePasswordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const newPw = document.getElementById('newPw').value;
    const cfPw  = document.getElementById('confirmPw').value;
    if (newPw !== cfPw) { showToast('New passwords do not match.', 'error'); return; }
    if (checkPasswordStrength(newPw) === 'weak') { showToast('Password is too weak.', 'warning'); return; }
    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled = true;
    const res    = await fetch('../api/auth/change-password.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ current_password: document.getElementById('currentPw').value, new_password: newPw }) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) e.target.reset();
    btn.disabled = false;
});

// Avatar upload
document.getElementById('avatarInput').addEventListener('change', async function() {
    const file = this.files[0];
    if (!file) return;

    const allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!allowed.includes(file.type)) { showToast('Only JPG, PNG, GIF, or WEBP allowed.', 'error'); return; }
    if (file.size > 5 * 1024 * 1024) { showToast('Image must be under 5MB.', 'error'); return; }

    // Preview immediately
    const reader = new FileReader();
    reader.onload = (e) => {
        const preview = document.getElementById('avatarPreview');
        const initials = document.getElementById('avatarInitials');
        preview.src = e.target.result;
        preview.style.display = '';
        if (initials) initials.style.display = 'none';
    };
    reader.readAsDataURL(file);

    // Upload
    const fd = new FormData();
    fd.append('avatar', file);
    try {
        const res    = await fetch('../api/auth/upload-avatar.php', { method:'POST', body: fd });
        const result = await res.json();
        showToast(result.message, result.success ? 'success' : 'error');
    } catch(e) { showToast('Upload failed.', 'error'); }
});
</script>

<?php include '../includes/agent-footer.php'; ?>
