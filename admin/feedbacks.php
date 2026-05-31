<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Agent Feedbacks';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="pru-main">
    <div class="page-header">
        <h2>Agent Feedbacks</h2>
        <p>View, reply to, and manage feedback messages from agents.</p>
    </div>

    <div class="table-wrapper">
        <div class="table-toolbar">
            <h5 style="margin-right:auto;">All Feedbacks</h5>
            <div class="table-search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search feedbacks...">
            </div>
        </div>
        
        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-user"></i> Agent</label>
                <select class="filter-select" id="filterAgent">
                    <option value="">All Agents</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-smile"></i> Mood</label>
                <select class="filter-select" id="filterMood">
                    <option value="">All Moods</option>
                    <option value="5">😄 Very Happy</option>
                    <option value="4">🙂 Happy</option>
                    <option value="3">😐 Neutral</option>
                    <option value="2">😟 Unhappy</option>
                    <option value="1">😢 Very Unhappy</option>
                    <option value="null">No Mood</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-tag"></i> Subject</label>
                <select class="filter-select" id="filterSubject">
                    <option value="">All Subjects</option>
                    <option value="Product Inquiry">Product Inquiry</option>
                    <option value="Technical Issue">Technical Issue</option>
                    <option value="Account Support">Account Support</option>
                    <option value="Payment Concern">Payment Concern</option>
                    <option value="Guidelines Question">Guidelines Question</option>
                    <option value="Portal Access">Portal Access</option>
                    <option value="Training Request">Training Request</option>
                    <option value="System Feedback">System Feedback</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label"><i class="fas fa-flag"></i> Status</label>
                <select class="filter-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="replied">Replied</option>
                </select>
            </div>
            <button class="btn-pru-outline btn-pru-sm" onclick="clearFilters()" style="margin-left:auto;">
                <i class="fas fa-redo"></i> Reset Filters
            </button>
        </div>
        
        <div class="table-scroll">
            <table class="pru-table">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Mood</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="feedbacksBody">
                    <tr><td colspan="7"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Reply Modal -->
<div class="modal-overlay" id="replyModal">
    <div class="modal-box">
        <div class="modal-head">
            <h5><i class="fas fa-reply" style="color:var(--pru-red);margin-right:8px;"></i>Reply to Feedback</h5>
            <button class="modal-close" onclick="closeModal('replyModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <input type="hidden" id="replyId">
            <div style="background:var(--pru-light);border-radius:var(--radius-sm);padding:14px;margin-bottom:16px;">
                <div style="font-size:12px;font-weight:700;color:var(--pru-muted);margin-bottom:4px;">SUBJECT</div>
                <div id="replySubject" style="font-size:14px;font-weight:600;color:var(--pru-text);"></div>
                <div style="font-size:12px;font-weight:700;color:var(--pru-muted);margin:10px 0 4px;">MESSAGE</div>
                <div id="replyMessage" style="font-size:13px;color:var(--pru-text);"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Your Reply</label>
                <textarea class="form-control" id="replyText" rows="4" placeholder="Type your reply here..." required></textarea>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('replyModal')">Cancel</button>
            <button class="btn-pru" onclick="submitReply()"><i class="fas fa-paper-plane"></i> Send Reply</button>
        </div>
    </div>
</div>

<style>
.filter-bar {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    padding: 16px 20px;
    background: var(--pru-light, #f8f9fa);
    border-bottom: 1px solid var(--pru-border, #e0e0e0);
    flex-wrap: wrap;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 160px;
}
.filter-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--pru-muted, #666);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.filter-select {
    padding: 8px 12px;
    border: 1px solid var(--pru-border, #ddd);
    border-radius: 6px;
    font-size: 13px;
    background: white;
    color: var(--pru-text, #333);
    cursor: pointer;
    transition: all 0.2s;
}
.filter-select:hover {
    border-color: var(--pru-red, #D50032);
}
.filter-select:focus {
    outline: none;
    border-color: var(--pru-red, #D50032);
    box-shadow: 0 0 0 3px rgba(213, 0, 50, 0.1);
}
@media (max-width: 768px) {
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .filter-group {
        width: 100%;
    }
}
</style>

<script>
let allFeedbacks = [];

function loadFeedbacks() {
    fetch('../api/feedbacks/get.php').then(r => r.json()).then(d => {
        if (d.success) { 
            allFeedbacks = d.data; 
            populateAgentFilter();
            applyFilters();
        }
    });
}

function populateAgentFilter() {
    const agentFilter = document.getElementById('filterAgent');
    const uniqueAgents = [...new Set(allFeedbacks.map(f => JSON.stringify({
        name: f.full_name,
        code: f.agent_code
    })))].map(s => JSON.parse(s));
    
    // Sort by name
    uniqueAgents.sort((a, b) => a.name.localeCompare(b.name));
    
    // Clear existing options except "All Agents"
    agentFilter.innerHTML = '<option value="">All Agents</option>';
    
    // Add agent options
    uniqueAgents.forEach(agent => {
        const option = document.createElement('option');
        option.value = agent.code;
        option.textContent = `${agent.name} (${agent.code})`;
        agentFilter.appendChild(option);
    });
}

function renderFeedbacks(feedbacks) {
    const tbody = document.getElementById('feedbacksBody');
    if (!feedbacks.length) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="fas fa-comments"></i><p>No feedbacks found</p></div></td></tr>';
        return;
    }
    
    const moodEmojis = { '1': '😢', '2': '😟', '3': '😐', '4': '🙂', '5': '😄' };
    const moodLabels = { '1': 'Very Unhappy', '2': 'Unhappy', '3': 'Neutral', '4': 'Happy', '5': 'Very Happy' };
    
    tbody.innerHTML = feedbacks.map(f => {
        const moodEmoji = f.mood_rating ? moodEmojis[f.mood_rating] || '-' : '-';
        const moodLabel = f.mood_rating ? moodLabels[f.mood_rating] || '' : '';
        return `
        <tr>
            <td>
                <strong>${esc(f.full_name)}</strong><br>
                <small style="color:var(--pru-muted);">${esc(f.agent_code)}</small>
            </td>
            <td style="text-align:center;font-size:24px;" title="${moodLabel}">${moodEmoji}</td>
            <td>${esc(f.subject)}</td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(f.message)}</td>
            <td><span class="badge-status badge-${f.status === 'replied' ? 'replied' : 'pending'}">${esc(f.status)}</span></td>
            <td>${formatDate(f.created_at)}</td>
            <td>
                <button class="btn-pru btn-pru-sm" onclick="openReply(${f.id},'${esc(f.subject).replace(/'/g,"\\'")}','${esc(f.message).replace(/'/g,"\\'")}')">
                    <i class="fas fa-reply"></i>
                </button>
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-danger);margin-left:4px;" onclick="deleteFeedback(${f.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

// Apply filters function
function applyFilters() {
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();
    const agentFilter = document.getElementById('filterAgent').value;
    const moodFilter = document.getElementById('filterMood').value;
    const subjectFilter = document.getElementById('filterSubject').value;
    const statusFilter = document.getElementById('filterStatus').value;
    
    let filtered = allFeedbacks.filter(f => {
        // Search filter
        const matchesSearch = !searchQuery || 
            (f.full_name||'').toLowerCase().includes(searchQuery) ||
            (f.subject||'').toLowerCase().includes(searchQuery) ||
            (f.message||'').toLowerCase().includes(searchQuery) ||
            (f.agent_code||'').toLowerCase().includes(searchQuery);
        
        // Agent filter
        const matchesAgent = !agentFilter || f.agent_code === agentFilter;
        
        // Mood filter
        const matchesMood = !moodFilter || 
            (moodFilter === 'null' ? !f.mood_rating : f.mood_rating == moodFilter);
        
        // Subject filter
        const matchesSubject = !subjectFilter || f.subject === subjectFilter;
        
        // Status filter
        const matchesStatus = !statusFilter || f.status === statusFilter;
        
        return matchesSearch && matchesAgent && matchesMood && matchesSubject && matchesStatus;
    });
    
    renderFeedbacks(filtered);
    updateFilterCount(filtered.length);
}

function updateFilterCount(count) {
    const toolbar = document.querySelector('.table-toolbar h5');
    if (count === allFeedbacks.length) {
        toolbar.textContent = 'All Feedbacks';
    } else {
        toolbar.textContent = `Feedbacks (${count} of ${allFeedbacks.length})`;
    }
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterAgent').value = '';
    document.getElementById('filterMood').value = '';
    document.getElementById('filterSubject').value = '';
    document.getElementById('filterStatus').value = '';
    applyFilters();
}

// Event listeners for filters
document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('filterAgent').addEventListener('change', applyFilters);
document.getElementById('filterMood').addEventListener('change', applyFilters);
document.getElementById('filterSubject').addEventListener('change', applyFilters);
document.getElementById('filterStatus').addEventListener('change', applyFilters);

function openReply(id, subject, message) {
    document.getElementById('replyId').value      = id;
    document.getElementById('replySubject').textContent = subject;
    document.getElementById('replyMessage').textContent = message;
    document.getElementById('replyText').value    = '';
    openModal('replyModal');
}

async function submitReply() {
    const res    = await fetch('../api/feedbacks/reply.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: document.getElementById('replyId').value, reply: document.getElementById('replyText').value }) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) { closeModal('replyModal'); loadFeedbacks(); }
}

async function deleteFeedback(id) {
    if (!confirm('Delete this feedback?')) return;
    const res    = await fetch(`../api/feedbacks/delete.php?id=${id}`, { method:'DELETE' });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) loadFeedbacks();
}
</script>

<script src="../assets/js/scripts.js"></script>
<script>
loadFeedbacks();
</script>

<?php include '../includes/footer.php'; ?>
