<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Manage Directories';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<main class="pru-main">
    <div class="page-header">
        <h2>Manage Directories</h2>
        <p>Add, edit, and manage Accredited Clinics and Email Directories.</p>
    </div>

    <!-- Tabs -->
    <div style="display:flex;gap:8px;margin-bottom:20px;border-bottom:2px solid var(--pru-border);padding-bottom:0;">
        <button class="gtab-btn active" id="tab-clinics" onclick="switchTab('clinics')">
            <i class="fas fa-clinic-medical"></i> Accredited Clinics
        </button>
        <button class="gtab-btn" id="tab-emails" onclick="switchTab('emails')">
            <i class="fas fa-envelope-open-text"></i> Email Directories
        </button>
    </div>

    <!-- Clinics Panel -->
    <div id="panel-clinics" class="gtab-panel">

        <!-- Clinic List PDF Card -->
        <div class="pru-card mb-4">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                <h5 style="margin:0;"><i class="fas fa-file-pdf" style="color:var(--pru-red);margin-right:8px;"></i>Accredited Clinics List PDF</h5>
                <small style="color:var(--pru-muted);">Upload a single PDF for the entire accredited clinics list</small>
            </div>
            <div class="card-body">
                <div id="clinicListPdfStatus" style="margin-bottom:12px;"></div>
                <div class="pdf-upload-zone" id="clinicListDropZone">
                    <input type="file" id="clinicListPdfInput" accept=".pdf" style="display:none;">
                    <div class="pdf-upload-inner" onclick="document.getElementById('clinicListPdfInput').click()">
                        <i class="fas fa-file-pdf"></i>
                        <div class="puz-text">Click to upload or drag &amp; drop the full clinic list PDF</div>
                        <div class="puz-hint">PDF only · Max 30MB · This replaces the existing file</div>
                    </div>
                    <div id="clinicListPdfName" class="pdf-file-selected" style="display:none;"></div>
                </div>
                <div id="clinicListProgress" style="display:none;margin-top:8px;">
                    <div style="height:4px;background:#eee;border-radius:2px;overflow:hidden;">
                        <div id="clinicListBar" style="height:100%;background:var(--pru-red);width:0;transition:width 0.3s;border-radius:2px;"></div>
                    </div>
                    <div id="clinicListProgressText" style="font-size:11px;color:var(--pru-muted);margin-top:4px;"></div>
                </div>
                <div style="margin-top:12px;display:flex;gap:8px;" id="clinicListActions" style="display:none;">
                    <button class="btn-pru btn-pru-sm" id="clinicListUploadBtn" onclick="uploadClinicListPdf()" style="display:none;">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <div class="table-toolbar">
                <h5 style="margin-right:auto;">Accredited Clinics</h5>
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="clinicSearch" placeholder="Search clinics..." oninput="filterTable('clinics')">
                </div>
                <button class="btn-pru btn-pru-sm" onclick="openAdd('clinics')">
                    <i class="fas fa-plus"></i> Add Clinic
                </button>
            </div>
            <div class="table-scroll">
                <table class="pru-table">
                    <thead>
                        <tr><th>Clinic / Hospital</th><th>Address</th><th>Region</th><th>Contact</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="clinicsBody">
                        <tr><td colspan="6"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Emails Panel -->
    <div id="panel-emails" class="gtab-panel" style="display:none;">
        <div class="table-wrapper">
            <div class="table-toolbar">
                <h5 style="margin-right:auto;">Email Directories</h5>
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="emailSearch" placeholder="Search departments..." oninput="filterTable('emails')">
                </div>
                <button class="btn-pru btn-pru-sm" onclick="openAdd('emails')">
                    <i class="fas fa-plus"></i> Add Email
                </button>
            </div>
            <div class="table-scroll">
                <table class="pru-table">
                    <thead>
                        <tr><th>Department</th><th>Email</th><th>Icon</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="emailsBody">
                        <tr><td colspan="5"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="dirModal">
    <div class="modal-box">
        <div class="modal-head">
            <h5 id="dirModalTitle"><i class="fas fa-plus-circle" style="color:var(--pru-red);margin-right:8px;"></i>Add Entry</h5>
            <button class="modal-close" onclick="closeModal('dirModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <input type="hidden" id="dirId">
            <input type="hidden" id="dirType">
            <input type="hidden" id="dirAction">

            <!-- Clinic fields -->
            <div id="clinicFields">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Clinic / Hospital Name *</label>
                            <input type="text" class="form-control" id="cName" placeholder="e.g. St. Luke's Medical Center">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Address *</label>
                            <input type="text" class="form-control" id="cAddress" placeholder="Full address">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Region *</label>
                            <select class="form-select" id="cRegion">
                                <option value="NCR">NCR</option>
                                <option value="Region I">Region I – Ilocos</option>
                                <option value="Region II">Region II – Cagayan Valley</option>
                                <option value="Region III">Region III – Central Luzon</option>
                                <option value="Region IV-A">Region IV-A – CALABARZON</option>
                                <option value="Region IV-B">Region IV-B – MIMAROPA</option>
                                <option value="Region V">Region V – Bicol</option>
                                <option value="Region VI">Region VI – Western Visayas</option>
                                <option value="Region VII">Region VII – Central Visayas</option>
                                <option value="Region VIII">Region VIII – Eastern Visayas</option>
                                <option value="Region IX">Region IX – Zamboanga Peninsula</option>
                                <option value="Region X">Region X – Northern Mindanao</option>
                                <option value="Region XI">Region XI – Davao</option>
                                <option value="Region XII">Region XII – SOCCSKSARGEN</option>
                                <option value="Region XIII">Region XIII – Caraga</option>
                                <option value="CAR">CAR – Cordillera</option>
                                <option value="BARMM">BARMM</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Contact Number *</label>
                            <input type="text" class="form-control" id="cContact" placeholder="e.g. (02) 8888-0000">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="cSort" value="0" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email fields -->
            <div id="emailFields" style="display:none;">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Department Name *</label>
                            <input type="text" class="form-control" id="eDept" placeholder="e.g. Customer Service">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="eEmail" placeholder="e.g. dept@prulifeuk.com.ph">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Icon <small style="color:var(--pru-muted)">(Font Awesome class)</small></label>
                            <select class="form-select" id="eIcon">
                                <option value="fa-envelope">fa-envelope (Default)</option>
                                <option value="fa-headset">fa-headset (Customer Service)</option>
                                <option value="fa-file-invoice">fa-file-invoice (Claims)</option>
                                <option value="fa-file-medical">fa-file-medical (Underwriting)</option>
                                <option value="fa-users">fa-users (Agency)</option>
                                <option value="fa-shield-alt">fa-shield-alt (Policy)</option>
                                <option value="fa-coins">fa-coins (Finance)</option>
                                <option value="fa-balance-scale">fa-balance-scale (Compliance)</option>
                                <option value="fa-laptop-code">fa-laptop-code (IT)</option>
                                <option value="fa-building">fa-building (Office)</option>
                                <option value="fa-phone">fa-phone (Phone)</option>
                                <option value="fa-briefcase">fa-briefcase (Business)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="eSort" value="0" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('dirModal')">Cancel</button>
            <button class="btn-pru" onclick="saveEntry()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<style>
.gtab-btn {
    background: none; border: none; border-bottom: 3px solid transparent;
    padding: 10px 20px; font-size: 13px; font-weight: 600;
    color: var(--pru-muted); cursor: pointer;
    display: flex; align-items: center; gap: 7px;
    margin-bottom: -2px; transition: all 0.2s;
}
.gtab-btn:hover { color: var(--pru-text); }
.gtab-btn.active { color: var(--pru-red); border-bottom-color: var(--pru-red); }

.pdf-upload-zone { border:2px dashed var(--pru-border);border-radius:var(--radius-md);transition:all 0.2s;overflow:hidden; }
.pdf-upload-zone:hover,.pdf-upload-zone.drag-over { border-color:var(--pru-red);background:rgba(213,0,50,0.02); }
.pdf-upload-inner { padding:20px;text-align:center;cursor:pointer; }
.pdf-upload-inner i { font-size:28px;color:var(--pru-red);margin-bottom:6px;display:block; }
.puz-text { font-size:13px;font-weight:600;color:var(--pru-text); }
.puz-hint { font-size:11px;color:var(--pru-muted);margin-top:3px; }
.pdf-file-selected { padding:10px 14px;background:rgba(213,0,50,0.04);border-top:1px solid rgba(213,0,50,0.1);display:flex;align-items:center;gap:10px;font-size:13px;color:var(--pru-text); }
.pdf-file-selected i { color:var(--pru-red); }
</style>

<script src="../assets/js/scripts.js"></script>
<script>
let allClinics = [];
let allEmails  = [];
let activeTab  = 'clinics';

/* ── Tab switching ── */
function switchTab(tab) {
    activeTab = tab;
    document.querySelectorAll('.gtab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.gtab-panel').forEach(p => p.style.display = 'none');
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('panel-' + tab).style.display = '';
    if (tab === 'clinics' && !allClinics.length) loadData('clinics');
    if (tab === 'emails'  && !allEmails.length)  loadData('emails');
}

/* ── Load data ── */
function loadData(type) {
    const bodyId = type === 'clinics' ? 'clinicsBody' : 'emailsBody';
    const cols   = type === 'clinics' ? 6 : 5;
    fetch('../api/directories/get.php?type=' + (type === 'clinics' ? 'clinics' : 'emails'))
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                if (type === 'clinics') allClinics = d.data;
                else                   allEmails  = d.data;
                renderTable(type, d.data);
            } else {
                document.getElementById(bodyId).innerHTML =
                    `<tr><td colspan="${cols}"><div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>${d.message||'Error'}</p></div></td></tr>`;
            }
        });
}

/* ── Render tables ── */
function renderTable(type, items) {
    if (type === 'clinics') {
        const tbody = document.getElementById('clinicsBody');
        if (!items.length) { tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="fas fa-clinic-medical"></i><p>No clinics found</p></div></td></tr>'; return; }
        tbody.innerHTML = items.map(c => `
            <tr data-name="${esc(c.name).toLowerCase()}" data-region="${esc(c.region)}">
                <td><strong>${esc(c.name)}</strong></td>
                <td style="font-size:12px;color:var(--pru-muted);">${esc(c.address)}</td>
                <td><span class="badge-status badge-pending" style="font-size:11px;">${esc(c.region)}</span></td>
                <td style="font-size:12px;">${esc(c.contact)}</td>
                <td>
                    <span class="badge-status badge-${c.is_active=='1'?'active':'inactive'}" style="cursor:pointer;" onclick="toggleEntry('clinics',${c.id})">
                        ${c.is_active=='1'?'Active':'Inactive'}
                    </span>
                </td>
                <td>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="openEdit('clinics',${c.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-danger);margin-left:4px;" onclick="deleteEntry('clinics',${c.id},'${esc(c.name)}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`).join('');
    } else {
        const tbody = document.getElementById('emailsBody');
        if (!items.length) { tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-envelope"></i><p>No email entries found</p></div></td></tr>'; return; }
        tbody.innerHTML = items.map(e => `
            <tr data-dept="${esc(e.department).toLowerCase()}">
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;background:rgba(213,0,50,0.08);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--pru-red);">
                            <i class="fas ${esc(e.icon)}"></i>
                        </div>
                        <strong>${esc(e.department)}</strong>
                    </div>
                </td>
                <td><a href="mailto:${esc(e.email)}" style="color:var(--pru-red);font-size:13px;">${esc(e.email)}</a></td>
                <td style="font-size:12px;color:var(--pru-muted);">${esc(e.icon)}</td>
                <td>
                    <span class="badge-status badge-${e.is_active=='1'?'active':'inactive'}" style="cursor:pointer;" onclick="toggleEntry('emails',${e.id})">
                        ${e.is_active=='1'?'Active':'Inactive'}
                    </span>
                </td>
                <td>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="openEdit('emails',${e.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-danger);margin-left:4px;" onclick="deleteEntry('emails',${e.id},'${esc(e.department)}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`).join('');
    }
}

/* ── Filter ── */
function filterTable(type) {
    if (type === 'clinics') {
        const q = document.getElementById('clinicSearch').value.toLowerCase();
        document.querySelectorAll('#clinicsBody tr[data-name]').forEach(r => {
            r.style.display = r.dataset.name.includes(q) ? '' : 'none';
        });
    } else {
        const q = document.getElementById('emailSearch').value.toLowerCase();
        document.querySelectorAll('#emailsBody tr[data-dept]').forEach(r => {
            r.style.display = r.dataset.dept.includes(q) ? '' : 'none';
        });
    }
}

/* ── Open Add modal ── */
function openAdd(type) {
    document.getElementById('dirId').value     = '';
    document.getElementById('dirType').value   = type;
    document.getElementById('dirAction').value = 'create';
    document.getElementById('dirModalTitle').innerHTML =
        `<i class="fas fa-plus-circle" style="color:var(--pru-red);margin-right:8px;"></i>Add ${type === 'clinics' ? 'Clinic' : 'Email Directory'}`;
    document.getElementById('clinicFields').style.display = type === 'clinics' ? '' : 'none';
    document.getElementById('emailFields').style.display  = type === 'emails'  ? '' : 'none';
    // Clear fields
    ['cName','cAddress','cContact'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('cSort').value = '0';
    ['eDept','eEmail'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('eSort').value = '0';
    document.getElementById('eIcon').value = 'fa-envelope';
    openModal('dirModal');
}

/* ── Open Edit modal ── */
function openEdit(type, id) {
    const items = type === 'clinics' ? allClinics : allEmails;
    const item  = items.find(x => x.id == id);
    if (!item) return;
    document.getElementById('dirId').value     = id;
    document.getElementById('dirType').value   = type;
    document.getElementById('dirAction').value = 'update';
    document.getElementById('dirModalTitle').innerHTML =
        `<i class="fas fa-edit" style="color:var(--pru-red);margin-right:8px;"></i>Edit ${type === 'clinics' ? 'Clinic' : 'Email Directory'}`;
    document.getElementById('clinicFields').style.display = type === 'clinics' ? '' : 'none';
    document.getElementById('emailFields').style.display  = type === 'emails'  ? '' : 'none';
    if (type === 'clinics') {
        document.getElementById('cName').value    = item.name;
        document.getElementById('cAddress').value = item.address;
        document.getElementById('cRegion').value  = item.region;
        document.getElementById('cContact').value = item.contact;
        document.getElementById('cSort').value    = item.sort_order;
    } else {
        document.getElementById('eDept').value  = item.department;
        document.getElementById('eEmail').value = item.email;
        document.getElementById('eIcon').value  = item.icon;
        document.getElementById('eSort').value  = item.sort_order;
    }
    openModal('dirModal');
}

/* ── Save entry ── */
async function saveEntry() {
    const type   = document.getElementById('dirType').value;
    const action = document.getElementById('dirAction').value;
    const id     = document.getElementById('dirId').value;

    let payload = { action, type, id: id ? parseInt(id) : undefined };

    if (type === 'clinics') {
        const name    = document.getElementById('cName').value.trim();
        const address = document.getElementById('cAddress').value.trim();
        const contact = document.getElementById('cContact').value.trim();
        if (!name || !address || !contact) { showToast('Please fill in all required fields.', 'warning'); return; }
        payload = { ...payload, name, address, region: document.getElementById('cRegion').value, contact, sort_order: parseInt(document.getElementById('cSort').value)||0 };
    } else {
        const dept  = document.getElementById('eDept').value.trim();
        const email = document.getElementById('eEmail').value.trim();
        if (!dept || !email) { showToast('Please fill in all required fields.', 'warning'); return; }
        payload = { ...payload, department: dept, email, icon: document.getElementById('eIcon').value, sort_order: parseInt(document.getElementById('eSort').value)||0 };
    }

    const res    = await fetch('../api/directories/save.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        closeModal('dirModal');
        if (type === 'clinics') { allClinics = []; loadData('clinics'); }
        else                    { allEmails  = []; loadData('emails'); }
    }
}

/* ── Toggle active ── */
async function toggleEntry(type, id) {
    const res    = await fetch('../api/directories/save.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'toggle', type, id }) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        if (type === 'clinics') { allClinics = []; loadData('clinics'); }
        else                    { allEmails  = []; loadData('emails'); }
    }
}

/* ── Delete ── */
async function deleteEntry(type, id, name) {
    if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
    const res    = await fetch('../api/directories/save.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'delete', type, id }) });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        if (type === 'clinics') { allClinics = []; loadData('clinics'); }
        else                    { allEmails  = []; loadData('emails'); }
    }
}

/* ── Init ── */
loadData('clinics');
loadClinicListPdf();

/* ── Clinic List PDF ── */
let selectedClinicListPdf = null;

function loadClinicListPdf() {
    fetch('../api/directories/clinic-list-pdf.php?action=get')
        .then(r => r.json())
        .then(d => {
            const status = document.getElementById('clinicListPdfStatus');
            if (d.success && d.pdf_file) {
                status.innerHTML = `
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(40,167,69,0.06);border:1px solid rgba(40,167,69,0.2);border-radius:8px;font-size:13px;">
                        <i class="fas fa-check-circle" style="color:var(--pru-success);"></i>
                        <span>Current PDF: <strong>${esc(d.pdf_file)}</strong></span>
                        <a href="../api/directories/serve-pdf.php?file=${encodeURIComponent(d.pdf_file)}" target="_blank" class="btn-pru btn-pru-sm" style="margin-left:auto;">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                        <button onclick="deleteClinicListPdf()" class="btn-pru btn-pru-sm" style="background:var(--pru-danger);">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>`;
            } else {
                status.innerHTML = `<div style="font-size:12px;color:var(--pru-muted);padding:4px 0;"><i class="fas fa-info-circle" style="margin-right:6px;"></i>No clinic list PDF uploaded yet.</div>`;
            }
        });
}

document.getElementById('clinicListPdfInput').addEventListener('change', function() {
    if (this.files[0]) setClinicListPdf(this.files[0]);
});

function setClinicListPdf(file) {
    if (file.type !== 'application/pdf') { showToast('Only PDF files allowed', 'error'); return; }
    if (file.size > 30 * 1024 * 1024)   { showToast('File must be under 30MB', 'error'); return; }
    selectedClinicListPdf = file;
    const el = document.getElementById('clinicListPdfName');
    el.style.display = 'flex';
    el.innerHTML = `<i class="fas fa-file-pdf"></i> ${esc(file.name)} <span style="margin-left:auto;color:var(--pru-muted);font-size:11px;">${(file.size/1024/1024).toFixed(2)} MB</span>`;
    document.getElementById('clinicListUploadBtn').style.display = '';
    uploadClinicListPdf();
}

async function uploadClinicListPdf() {
    if (!selectedClinicListPdf) return;
    const prog = document.getElementById('clinicListProgress');
    const bar  = document.getElementById('clinicListBar');
    const txt  = document.getElementById('clinicListProgressText');
    prog.style.display = '';
    bar.style.width    = '30%';
    txt.textContent    = 'Uploading...';
    txt.style.color    = 'var(--pru-muted)';

    const fd = new FormData();
    fd.append('action', 'upload');
    fd.append('pdf_file', selectedClinicListPdf);

    try {
        bar.style.width = '70%';
        const res    = await fetch('../api/directories/clinic-list-pdf.php?action=upload', { method:'POST', body: fd });
        const result = await res.json();
        bar.style.width = '100%';
        if (result.success) {
            txt.textContent = 'Uploaded successfully!';
            txt.style.color = 'var(--pru-success)';
            showToast(result.message, 'success');
            selectedClinicListPdf = null;
            document.getElementById('clinicListPdfName').style.display = 'none';
            document.getElementById('clinicListUploadBtn').style.display = 'none';
            setTimeout(() => { prog.style.display = 'none'; loadClinicListPdf(); }, 1500);
        } else {
            txt.textContent = result.message;
            txt.style.color = 'var(--pru-danger)';
            showToast(result.message, 'error');
        }
    } catch(e) {
        txt.textContent = 'Upload failed';
        txt.style.color = 'var(--pru-danger)';
    }
}

async function deleteClinicListPdf() {
    if (!confirm('Remove the clinic list PDF?')) return;
    const res    = await fetch('../api/directories/clinic-list-pdf.php?action=delete', { method:'POST' });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) loadClinicListPdf();
}

const clDropZone = document.getElementById('clinicListDropZone');
clDropZone.addEventListener('dragover',  e => { e.preventDefault(); clDropZone.classList.add('drag-over'); });
clDropZone.addEventListener('dragleave', () => clDropZone.classList.remove('drag-over'));
clDropZone.addEventListener('drop', e => {
    e.preventDefault(); clDropZone.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) setClinicListPdf(e.dataTransfer.files[0]);
});
</script>

<?php include '../includes/footer.php'; ?>
