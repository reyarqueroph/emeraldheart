<?php
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'agent') {
    header('Location: dashboard.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – Agent Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --red:#D50032; --red-dark:#a8002a; --dark:#1C1C1C; }
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { height:100%; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--dark);
        }

        /* Left panel */
        .al-left {
            flex: 1;
            background: linear-gradient(135deg, #1a0008 0%, #2a0010 50%, #1C1C1C 100%);
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            padding: 60px 48px;
            position: relative; overflow: hidden;
        }

        .al-left::before {
            content:'';
            position:absolute; top:-100px; right:-100px;
            width:400px; height:400px;
            background: rgba(213,0,50,0.1);
            border-radius:50%;
        }

        .al-left::after {
            content:'';
            position:absolute; bottom:-80px; left:-80px;
            width:300px; height:300px;
            background: rgba(213,0,50,0.06);
            border-radius:50%;
        }

        .al-left-inner { position:relative; z-index:1; max-width:400px; }

        .al-logo {
            width:60px; height:60px;
            background: var(--red);
            border-radius:15px;
            display:flex; align-items:center; justify-content:center;
            font-size:24px; font-weight:900; color:white;
            box-shadow: 0 8px 24px rgba(213,0,50,0.4);
            margin-bottom:28px;
        }

        .al-left h2 {
            color:white; font-size:32px; font-weight:900;
            line-height:1.2; margin-bottom:14px;
            letter-spacing:-0.8px;
        }

        .al-left h2 span { color:var(--red); }

        .al-left p {
            color:rgba(255,255,255,0.4);
            font-size:14px; line-height:1.7;
            margin-bottom:36px;
        }

        .al-features { display:flex; flex-direction:column; gap:14px; }

        .al-feat {
            display:flex; align-items:center; gap:12px;
            color:rgba(255,255,255,0.5);
            font-size:13px;
        }

        .al-feat i {
            width:28px; height:28px;
            background:rgba(213,0,50,0.12);
            border-radius:7px;
            display:flex; align-items:center; justify-content:center;
            color:var(--red); font-size:12px;
            flex-shrink:0;
        }

        /* Right panel */
        .al-right {
            width: 480px; flex-shrink:0;
            background: white;
            display:flex; flex-direction:column;
            justify-content:center;
            padding: 60px 48px;
            overflow-y: auto;
        }

        .al-right .back-link {
            display:inline-flex; align-items:center; gap:7px;
            color:#aaa; font-size:12px; text-decoration:none;
            margin-bottom:36px;
            transition:color 0.2s;
        }

        .al-right .back-link:hover { color:var(--red); }

        .al-right .form-title {
            font-size:24px; font-weight:900;
            color:var(--dark); margin-bottom:4px;
            letter-spacing:-0.5px;
        }

        .al-right .form-sub {
            font-size:13px; color:#888;
            margin-bottom:32px;
        }

        .role-badge {
            display:inline-flex; align-items:center; gap:7px;
            background:rgba(213,0,50,0.06);
            border:1px solid rgba(213,0,50,0.15);
            color:var(--red);
            font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:0.8px;
            padding:5px 12px; border-radius:20px;
            margin-bottom:24px;
        }

        .f-group { margin-bottom:18px; }

        .f-group label {
            display:block;
            font-size:11px; font-weight:700;
            color:#555; text-transform:uppercase;
            letter-spacing:0.5px; margin-bottom:7px;
        }

        .f-wrap { position:relative; }

        .f-wrap .fi {
            position:absolute; left:13px; top:50%;
            transform:translateY(-50%);
            color:#bbb; font-size:13px; pointer-events:none;
        }

        .f-wrap input {
            width:100%;
            padding:12px 14px 12px 40px;
            border:1.5px solid #E0E0E0;
            border-radius:10px;
            font-size:13px; color:var(--dark);
            outline:none; background:#fafafa;
            transition:all 0.2s;
        }

        .f-wrap input:focus {
            border-color:var(--red);
            box-shadow:0 0 0 3px rgba(213,0,50,0.08);
            background:white;
        }

        .f-wrap .pw-eye {
            position:absolute; right:12px; top:50%;
            transform:translateY(-50%);
            background:none; border:none;
            color:#bbb; cursor:pointer; font-size:13px;
            padding:4px; transition:color 0.2s;
        }

        .f-wrap .pw-eye:hover { color:var(--red); }

        .f-row {
            display:flex; align-items:center;
            justify-content:space-between;
            margin-bottom:22px;
        }

        .f-row label {
            display:flex; align-items:center; gap:7px;
            font-size:12px; color:#888; cursor:pointer;
        }

        .f-row label input { accent-color:var(--red); }

        .f-row a {
            font-size:12px; color:var(--red);
            text-decoration:none; font-weight:700;
        }

        .f-row a:hover { text-decoration:underline; }

        .btn-login {
            width:100%; padding:13px;
            background:var(--red); color:white;
            border:none; border-radius:10px;
            font-size:14px; font-weight:700;
            cursor:pointer;
            display:flex; align-items:center; justify-content:center; gap:8px;
            transition:all 0.2s;
            box-shadow:0 4px 16px rgba(213,0,50,0.25);
        }

        .btn-login:hover {
            background:var(--red-dark);
            box-shadow:0 8px 24px rgba(213,0,50,0.4);
            transform:translateY(-1px);
        }

        .btn-login:disabled { opacity:0.7; cursor:not-allowed; transform:none; }

        .register-link {
            text-align:center; margin-top:24px;
            font-size:13px; color:#888;
        }

        .register-link a { color:var(--red); font-weight:700; text-decoration:none; }
        .register-link a:hover { text-decoration:underline; }

        /* Toast */
        .toast-stack { position:fixed; top:20px; right:20px; z-index:9999; display:flex; flex-direction:column; gap:10px; }
        .pru-toast { background:white; border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,0.15); padding:14px 18px; display:flex; align-items:flex-start; gap:12px; min-width:260px; border-left:4px solid var(--red); animation:toastIn 0.3s ease; }
        .pru-toast.success { border-left-color:#28a745; }
        .pru-toast.error   { border-left-color:#dc3545; }
        .pru-toast .ti { font-size:15px; margin-top:1px; }
        .pru-toast.success .ti { color:#28a745; }
        .pru-toast.error   .ti { color:#dc3545; }
        .pru-toast .tm { font-size:13px; color:#333; flex:1; }
        @keyframes toastIn { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }

        @media (max-width:768px) {
            body { flex-direction:column; }
            .al-left { display:none; }
            .al-right { width:100%; padding:40px 24px; }
        }
    </style>
</head>
<body>

<div class="al-left">
    <div class="al-left-inner">
        <div class="al-logo" style="font-size:18px;font-weight:900;letter-spacing:-1px;">eH</div>
        <h2>Welcome to <span>eHeart</span></h2>
        <p>PRU LIFE U.K. Agent Portal — your complete platform for insurance products, guidelines, services, and client support.</p>
        <div class="al-features">
            <div class="al-feat"><i class="fas fa-box-open"></i> Browse all insurance products</div>
            <div class="al-feat"><i class="fas fa-book"></i> Access underwriting guidelines</div>
            <div class="al-feat"><i class="fas fa-concierge-bell"></i> After-sales & claims support</div>
            <div class="al-feat"><i class="fas fa-comments"></i> Submit feedback to admin</div>
            <div class="al-feat"><i class="fas fa-external-link-alt"></i> Quick access to PRU portals</div>
        </div>
    </div>
</div>

<div class="al-right">
    <a href="../index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <div class="form-title">eHeart Sign In</div>
    <div class="form-sub">PRU LIFE U.K. Agent Portal — enter your credentials to continue</div>

    <div class="role-badge">
        <i class="fas fa-user-tie"></i> Agent Portal
    </div>

    <form id="loginForm" novalidate>
        <div class="f-group">
            <label>Agent Code or Email</label>
            <div class="f-wrap">
                <i class="fas fa-id-badge fi"></i>
                <input type="text" id="username" placeholder="e.g. AG-00123" required autocomplete="username">
            </div>
        </div>
        <div class="f-group">
            <label>Password</label>
            <div class="f-wrap">
                <i class="fas fa-lock fi"></i>
                <input type="password" id="password" placeholder="Enter your password" required autocomplete="current-password" style="padding-right:42px;">
                <button type="button" class="pw-eye" id="eyeBtn"><i class="fas fa-eye"></i></button>
            </div>
        </div>
        <div class="f-row">
            <label><input type="checkbox" id="rememberMe"> Remember me</label>
            <a href="#" onclick="showForgot();return false;">Forgot Password?</a>
        </div>
        <button type="submit" class="btn-login" id="loginBtn">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
    </form>

    <div class="register-link">
        New agent? <a href="../index.php#register">Create an account</a>
    </div>
</div>

<!-- Forgot Password Modal -->
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:2000;display:none;align-items:center;justify-content:center;padding:20px;" id="forgotOverlay">
    <div style="background:white;border-radius:16px;width:100%;max-width:400px;box-shadow:0 24px 64px rgba(0,0,0,0.3);">
        <div style="padding:20px 24px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between;">
            <h5 style="font-size:16px;font-weight:800;color:#1C1C1C;margin:0;display:flex;align-items:center;gap:8px;">
                <span style="width:30px;height:30px;background:rgba(213,0,50,0.08);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#D50032;font-size:13px;"><i class="fas fa-key"></i></span>
                Forgot Password
            </h5>
            <button onclick="hideForgot()" style="background:none;border:none;color:#aaa;cursor:pointer;font-size:16px;"><i class="fas fa-times"></i></button>
        </div>
        <div style="padding:24px;">
            <p style="font-size:13px;color:#888;margin-bottom:18px;line-height:1.6;">Enter your agent code or email address. The admin will be notified to reset your password.</p>
            <form id="forgotForm">
                <div class="f-group">
                    <label>Agent Code or Email</label>
                    <div class="f-wrap">
                        <i class="fas fa-id-badge fi"></i>
                        <input type="text" id="forgotCode" placeholder="e.g. AG-00123 or your email" required>
                    </div>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-paper-plane"></i> Send Request</button>
            </form>
        </div>
    </div>
</div>

<div class="toast-stack" id="toastStack"></div>

<script>
function showToast(msg, type='info') {
    const stack = document.getElementById('toastStack');
    const icons = {success:'fa-check-circle',error:'fa-times-circle',warning:'fa-exclamation-triangle',info:'fa-info-circle'};
    const t = document.createElement('div');
    t.className = `pru-toast ${type}`;
    t.innerHTML = `<i class="fas ${icons[type]||icons.info} ti"></i><div class="tm">${msg}</div>`;
    stack.appendChild(t);
    setTimeout(()=>{ t.style.transition='all 0.3s'; t.style.opacity='0'; t.style.transform='translateX(100%)'; setTimeout(()=>t.remove(),300); }, 4000);
}

document.getElementById('eyeBtn').addEventListener('click', function() {
    const inp = document.getElementById('password');
    const ico = this.querySelector('i');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
});

function showForgot() { document.getElementById('forgotOverlay').style.display='flex'; }
function hideForgot() { document.getElementById('forgotOverlay').style.display='none'; }

document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
    try {
        const res  = await fetch('../api/auth/login.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ username: document.getElementById('username').value, password: document.getElementById('password').value, role: 'agent' }) });
        const data = await res.json();
        if (data.success && data.role === 'agent') {
            showToast('Login successful! Redirecting...', 'success');
            setTimeout(() => window.location.href = 'dashboard.php', 800);
        } else if (data.success) {
            showToast('Please use the Admin login page.', 'error');
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
        } else {
            showToast(data.message, 'error');
            btn.disabled = false; btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
        }
    } catch (_) {
        showToast('Connection error. Please try again.', 'error');
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
    }
});

document.getElementById('forgotForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    try {
        const res  = await fetch('../api/password-requests/create.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ identifier: document.getElementById('forgotCode').value }) });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) { hideForgot(); e.target.reset(); }
    } catch (_) { showToast('Failed to send request.', 'error'); }
    btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
});
</script>
</body>
</html>
