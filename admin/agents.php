<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Agent Management';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="pru-main">
    <div class="page-header">
        <h2>Agent Management</h2>
        <p>Manage all registered agents in the system.</p>
    </div>

    <div class="table-wrapper">
        <div class="table-toolbar">
            <h5 style="margin-right:auto;">All Agents</h5>
            <div class="table-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search by name, code or email...">
            </div>
            <button class="btn-pru btn-pru-sm" onclick="openModal('addAgentModal')">
                <i class="fas fa-plus"></i> Add Agent
            </button>
        </div>
        <div class="table-scroll">
            <table class="pru-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Agent Code</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Last Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="agentsBody">
                    <tr><td colspan="9"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add Agent Modal -->
<div class="modal-overlay" id="addAgentModal">
    <div class="modal-box modal-lg">
        <div class="modal-head">
            <h5><i class="fas fa-user-plus" style="color:var(--pru-red);margin-right:8px;"></i>Add New Agent</h5>
            <button class="modal-close" onclick="closeModal('addAgentModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <form id="addAgentForm" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Agent Code *</label>
                            <input type="text" class="form-control" name="agent_code" required placeholder="e.g. AG-00123">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <select class="form-select" name="position">
                                <option value="Agent">Agent</option>
                                <option value="OM">OM – Office Manager</option>
                                <option value="UM">UM – Unit Manager</option>
                                <option value="BM">BM – Branch Manager</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Password <small style="color:var(--pru-muted)">(default: password)</small></label>
                            <input type="password" class="form-control" name="password" placeholder="Leave blank for default">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('addAgentModal')">Cancel</button>
            <button class="btn-pru" onclick="submitAddAgent()"><i class="fas fa-save"></i> Save Agent</button>
        </div>
    </div>
</div>

<!-- Edit Agent Modal -->
<div class="modal-overlay" id="editAgentModal">
    <div class="modal-box modal-lg">
        <div class="modal-head">
            <h5><i class="fas fa-user-edit" style="color:var(--pru-red);margin-right:8px;"></i>Edit Agent</h5>
            <button class="modal-close" onclick="closeModal('editAgentModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <form id="editAgentForm" novalidate>
                <input type="hidden" id="editId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editName">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <select class="form-select" id="editPosition">
                                <option value="Agent">Agent</option>
                                <option value="OM">OM – Office Manager</option>
                                <option value="UM">UM – Unit Manager</option>
                                <option value="BM">BM – Branch Manager</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="editStatus">
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">New Password <small style="color:var(--pru-muted)">(leave blank to keep)</small></label>
                            <input type="password" class="form-control" id="editPassword">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('editAgentModal')">Cancel</button>
            <button class="btn-pru" onclick="submitEditAgent()"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </div>
</div>

<script>
let allAgents = [];

function loadAgents() {
    fetch('../api/agents/get.php').then(r => r.json()).then(d => {
        if (d.success) { allAgents = d.data; renderAgents(allAgents); }
    });
}

function renderAgents(agents) {
    const tbody = document.getElementById('agentsBody');
    if (!agents.length) {
        tbody.innerHTML = '<tr><td colspan="9"><div class="empty-state"><i class="fas fa-users"></i><p>No agents found</p></div></td></tr>';
        return;
    }

    const paymentBadge = ps => {
        const map = {
            verified: ['#28a745', 'Verified'],
            paid:     ['#17a2b8', 'Paid – Pending'],
            pending:  ['#ffc107', 'Submitted'],
            unpaid:   ['#dc3545', 'Unpaid'],
        };
        const [color, label] = map[ps] || ['#6c757d', ps || 'N/A'];
        return `<span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:${color}22;color:${color};">${label}</span>`;
    };

    const getAvatar = (agent) => {
        if (agent.avatar) {
            return `<img src="../api/auth/serve-avatar.php?file=${encodeURIComponent(agent.avatar)}" alt="${esc(agent.full_name)}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--pru-red);">`;
        } else {
            const initials = agent.full_name ? agent.full_name.charAt(0).toUpperCase() : 'A';
            return `<div style="width:40px;height:40px;border-radius:50%;background:var(--pru-red);color:white;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;">${initials}</div>`;
        }
    };

    tbody.innerHTML = agents.map(a => `
        <tr>
            <td>${getAvatar(a)}</td>
            <td><strong>${esc(a.agent_code)}</strong></td>
            <td>${esc(a.full_name)}</td>
            <td>${esc(a.email)}</td>
            <td>${esc(a.position)}</td>
            <td><span class="badge-status badge-${a.status}">${esc(a.status)}</span></td>
            <td>${paymentBadge(a.payment_status)}</td>
            <td>${formatDate(a.last_active)}</td>
            <td style="white-space:nowrap;">
                ${a.status === 'pending' ? `
                <button class="btn-pru btn-pru-sm" style="background:#28a745;margin-right:4px;" onclick="approveAgent(${a.id},'${esc(a.full_name)}')">
                    <i class="fas fa-check"></i> Approve
                </button>` : ''}
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="openEdit(${a.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-danger);margin-left:4px;" onclick="deleteAgent(${a.id},'${esc(a.full_name)}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`).join('');
}

document.getElementById('searchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    renderAgents(allAgents.filter(a =>
        (a.full_name||'').toLowerCase().includes(q) ||
        (a.agent_code||'').toLowerCase().includes(q) ||
        (a.email||'').toLowerCase().includes(q)
    ));
});

async function submitAddAgent() {
    const form = document.getElementById('addAgentForm');
    const fd   = new FormData(form);
    const data = Object.fromEntries(fd);
    const res  = await fetch('../api/agents/create.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) { closeModal('addAgentModal'); form.reset(); loadAgents(); }
}

function openEdit(id) {
    const a = allAgents.find(x => x.id == id);
    if (!a) return;
    document.getElementById('editId').value       = a.id;
    document.getElementById('editName').value     = a.full_name;
    document.getElementById('editEmail').value    = a.email;
    document.getElementById('editPosition').value = a.position;
    document.getElementById('editStatus').value   = a.status;
    document.getElementById('editPassword').value = '';
    openModal('editAgentModal');
}

async function submitEditAgent() {
    const data = {
        id: document.getElementById('editId').value,
        full_name: document.getElementById('editName').value,
        email: document.getElementById('editEmail').value,
        position: document.getElementById('editPosition').value,
        status: document.getElementById('editStatus').value,
        password: document.getElementById('editPassword').value
    };
    const res    = await fetch('../api/agents/update.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) { closeModal('editAgentModal'); loadAgents(); }
}

async function deleteAgent(id, name) {
    if (!confirm(`Delete agent "${name}"? This cannot be undone.`)) return;
    const res    = await fetch(`../api/agents/delete.php?id=${id}`, { method:'DELETE' });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) loadAgents();
}

async function approveAgent(id, name) {
    if (!confirm(`Approve agent "${name}"?\n\nThis will set their status to Active and create a payment record in Payments for verification.`)) return;
    const res    = await fetch('../api/agents/update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status: 'active' })
    });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) loadAgents();
}
</script>

<script src="../assets/js/scripts.js"></script>
<script>
loadAgents();
</script>

<?php include '../includes/footer.php'; ?>
