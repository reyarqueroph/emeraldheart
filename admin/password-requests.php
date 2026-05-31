<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Password Requests';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="pru-main">
    <div class="page-header">
        <h2>Password Requests</h2>
        <p>Review and process agent password reset requests.</p>
    </div>

    <div class="table-wrapper">
        <div class="table-toolbar">
            <h5 style="margin-right:auto;">All Requests</h5>
            <div class="table-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search requests...">
            </div>
        </div>
        <div class="table-scroll">
            <table class="pru-table">
                <thead>
                    <tr>
                        <th>Agent Code</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsBody">
                    <tr><td colspan="6"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Approve Modal -->
<div class="modal-overlay" id="approveModal">
    <div class="modal-box">
        <div class="modal-head">
            <h5><i class="fas fa-check-circle" style="color:var(--pru-success);margin-right:8px;"></i>Approve Password Reset</h5>
            <button class="modal-close" onclick="closeModal('approveModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <p style="font-size:13px;color:var(--pru-muted);margin-bottom:16px;">Set a new password for this agent. They will need to change it after logging in.</p>
            <input type="hidden" id="approveId">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="text" class="form-control" id="newPassword" value="Pru@2024!">
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('approveModal')">Cancel</button>
            <button class="btn-pru" style="background:var(--pru-success);" onclick="submitApprove()">
                <i class="fas fa-check"></i> Approve & Reset
            </button>
        </div>
    </div>
</div>

<!-- Load scripts.js first so esc() and formatDate() are available -->
<script src="../assets/js/scripts.js"></script>

<script>
let allRequests = [];

function loadRequests() {
    fetch('../api/password-requests/get.php')
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                allRequests = d.data;
                renderRequests(allRequests);
            } else {
                document.getElementById('requestsBody').innerHTML =
                    '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>' + (d.message || 'Failed to load') + '</p></div></td></tr>';
            }
        })
        .catch(() => {
            document.getElementById('requestsBody').innerHTML =
                '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Connection error</p></div></td></tr>';
        });
}

function renderRequests(requests) {
    const tbody = document.getElementById('requestsBody');
    if (!requests.length) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-key"></i><p>No password requests</p></div></td></tr>';
        return;
    }
    tbody.innerHTML = requests.map(r => `
        <tr>
            <td><strong>${esc(r.agent_code)}</strong></td>
            <td>${esc(r.full_name)}</td>
            <td>${esc(r.email)}</td>
            <td><span class="badge-status badge-${r.status}">${esc(r.status)}</span></td>
            <td>${formatDate(r.created_at)}</td>
            <td>
                ${r.status === 'pending' ? `
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-success);" onclick="openApprove(${r.id})">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-danger);margin-left:4px;" onclick="declineRequest(${r.id})">
                    <i class="fas fa-times"></i> Decline
                </button>` : `<span style="color:var(--pru-muted);font-size:12px;">Processed</span>`}
            </td>
        </tr>`).join('');
}

document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    renderRequests(allRequests.filter(r =>
        (r.agent_code||'').toLowerCase().includes(q) ||
        (r.full_name||'').toLowerCase().includes(q) ||
        (r.email||'').toLowerCase().includes(q)
    ));
});

function openApprove(id) {
    document.getElementById('approveId').value = id;
    openModal('approveModal');
}

async function submitApprove() {
    const res    = await fetch('../api/password-requests/approve.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: document.getElementById('approveId').value, new_password: document.getElementById('newPassword').value }) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) { closeModal('approveModal'); loadRequests(); }
}

async function declineRequest(id) {
    if (!confirm('Decline this password request?')) return;
    const res    = await fetch('../api/password-requests/decline.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id }) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) loadRequests();
}

loadRequests();
</script>

<?php include '../includes/footer.php'; ?>
