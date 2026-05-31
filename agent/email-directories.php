<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_name   = $_SESSION['user_name'] ?? 'Agent';
$agent_code  = $_SESSION['agent_code'] ?? '';
$initials    = strtoupper(substr($user_name, 0, 1));
$active_page = 'email-directories';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – Email Directories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/agent-dashboard.css">
    <link rel="stylesheet" href="../assets/css/theme-toggle.css">
</head>
<body class="agent-dash-body">

<?php include '../includes/agent-sidebar.php'; ?>

<div class="ad-main-wrap" id="adMainWrap">
    <main class="ad-content">

        <div class="page-header">
            <h2>Email Directories</h2>
            <p>Key PRU Life U.K. department email contacts for agents.</p>
        </div>

        <!-- Search -->
        <div class="pru-card mb-4">
            <div class="card-body">
                <div style="position:relative;">
                    <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--pru-muted);font-size:13px;"></i>
                    <input type="text" id="emailSearch" class="form-control" placeholder="Search by department or email..." style="padding-left:36px;" oninput="filterEmails()">
                </div>
            </div>
        </div>

        <div class="pru-card">
            <div class="card-header"><h5><i class="fas fa-envelope-open-text" style="color:var(--pru-red);margin-right:8px;"></i>Department Email Contacts</h5></div>
            <div class="card-body" style="padding:0;" id="emailsContainer">
                <div style="padding:40px;text-align:center;color:var(--pru-muted);">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;"></i>
                    <p style="margin-top:10px;font-size:13px;">Loading...</p>
                </div>
            </div>
        </div>

    </main>
</div>

<div class="toast-stack"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/scripts.js"></script>
<script>
let allEmails = [];

fetch('../api/directories/get.php?type=emails')
    .then(r => r.json())
    .then(d => {
        if (!d.success) {
            document.getElementById('emailsContainer').innerHTML = '<div style="padding:40px;text-align:center;color:var(--pru-muted);"><i class="fas fa-exclamation-circle" style="font-size:24px;"></i><p style="margin-top:10px;font-size:13px;">Failed to load</p></div>';
            return;
        }
        allEmails = d.data;
        renderEmails(d.data);
    });

function renderEmails(items) {
    const container = document.getElementById('emailsContainer');
    if (!items.length) {
        container.innerHTML = '<div style="padding:40px;text-align:center;color:var(--pru-muted);"><i class="fas fa-envelope" style="font-size:24px;opacity:0.3;"></i><p style="margin-top:10px;font-size:13px;">No email directories found</p></div>';
        return;
    }
    container.innerHTML = items.map(e => `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--pru-border);"
             data-dept="${esc(e.department).toLowerCase()}" data-email="${esc(e.email).toLowerCase()}">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:38px;height:38px;background:rgba(213,0,50,0.08);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--pru-red);flex-shrink:0;">
                    <i class="fas ${esc(e.icon)}"></i>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--pru-text);">${esc(e.department)}</div>
                    <div style="font-size:12px;color:var(--pru-muted);">${esc(e.email)}</div>
                </div>
            </div>
            <a href="mailto:${esc(e.email)}" class="btn-pru-outline btn-pru-sm"><i class="fas fa-envelope"></i></a>
        </div>`).join('');
}

function filterEmails() {
    const q = document.getElementById('emailSearch').value.toLowerCase();
    const filtered = allEmails.filter(e =>
        e.department.toLowerCase().includes(q) || e.email.toLowerCase().includes(q)
    );
    renderEmails(filtered);
}
</script>
</body>
</html>
