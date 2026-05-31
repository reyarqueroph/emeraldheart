<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Manage Guidelines';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<main class="pru-main">
    <div class="page-header">
        <h2>Manage Guidelines</h2>
        <p>Upload and manage PDF files for Underwriting and Policy Guidelines.</p>
    </div>

    <!-- Tabs -->
    <div style="display:flex;gap:8px;margin-bottom:20px;border-bottom:2px solid var(--pru-border);padding-bottom:0;">
        <button class="gtab-btn active" id="tab-underwriting" onclick="switchTab('underwriting')">
            <i class="fas fa-file-medical"></i> Underwriting Guidelines
        </button>
        <button class="gtab-btn" id="tab-policy" onclick="switchTab('policy')">
            <i class="fas fa-shield-alt"></i> Policy Guidelines
        </button>
    </div>

    <!-- Underwriting Table -->
    <div id="panel-underwriting" class="gtab-panel">
        <div class="table-wrapper">
            <div class="table-toolbar">
                <h5 style="margin-right:auto;">Underwriting Guidelines</h5>
            </div>
            <div class="table-scroll">
                <table class="pru-table">
                    <thead>
                        <tr><th>#</th><th>Title</th><th>Description</th><th>PDF Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="uwBody">
                        <tr><td colspan="5"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Policy Table -->
    <div id="panel-policy" class="gtab-panel" style="display:none;">
        <div class="table-wrapper">
            <div class="table-toolbar">
                <h5 style="margin-right:auto;">Policy Guidelines</h5>
            </div>
            <div class="table-scroll">
                <table class="pru-table">
                    <thead>
                        <tr><th>#</th><th>Title</th><th>Description</th><th>PDF Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="polBody">
                        <tr><td colspan="5"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Edit / Upload Modal -->
<div class="modal-overlay" id="editGuidelineModal">
    <div class="modal-box modal-lg">
        <div class="modal-head">
            <h5><i class="fas fa-file-pdf" style="color:var(--pru-red);margin-right:8px;"></i>Edit Guideline &amp; Upload PDF</h5>
            <button class="modal-close" onclick="closeModal('editGuidelineModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <input type="hidden" id="editGId">
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" id="editGTitle">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="editGDesc" rows="2"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">PDF File</label>
                        <div id="gPdfCurrentStatus" style="margin-bottom:8px;"></div>
                        <div class="pdf-upload-zone" id="gPdfDropZone">
                            <input type="file" id="gPdfFileInput" accept=".pdf" style="display:none;">
                            <div class="pdf-upload-inner" onclick="document.getElementById('gPdfFileInput').click()">
                                <i class="fas fa-file-pdf"></i>
                                <div class="puz-text">Click to upload or drag &amp; drop PDF</div>
                                <div class="puz-hint">PDF only · Max 20MB</div>
                            </div>
                            <div id="gPdfFileName" class="pdf-file-selected" style="display:none;"></div>
                        </div>
                        <div id="gPdfProgress" style="display:none;margin-top:8px;">
                            <div style="height:4px;background:#eee;border-radius:2px;overflow:hidden;">
                                <div id="gPdfBar" style="height:100%;background:var(--pru-red);width:0;transition:width 0.3s;border-radius:2px;"></div>
                            </div>
                            <div id="gPdfProgressText" style="font-size:11px;color:var(--pru-muted);margin-top:4px;">Uploading...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('editGuidelineModal')">Cancel</button>
            <button class="btn-pru" onclick="saveGuideline()"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </div>
</div>

<style>
.gtab-btn {
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 600;
    color: var(--pru-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: -2px;
    transition: all 0.2s;
}
.gtab-btn:hover { color: var(--pru-text); }
.gtab-btn.active { color: var(--pru-red); border-bottom-color: var(--pru-red); }

.pdf-upload-zone { border:2px dashed var(--pru-border);border-radius:var(--radius-md);transition:all 0.2s;overflow:hidden; }
.pdf-upload-zone:hover,.pdf-upload-zone.drag-over { border-color:var(--pru-red);background:rgba(213,0,50,0.02); }
.pdf-upload-inner { padding:24px;text-align:center;cursor:pointer; }
.pdf-upload-inner i { font-size:32px;color:var(--pru-red);margin-bottom:8px;display:block; }
.puz-text { font-size:13px;font-weight:600;color:var(--pru-text); }
.puz-hint { font-size:11px;color:var(--pru-muted);margin-top:4px; }
.pdf-file-selected { padding:12px 16px;background:rgba(213,0,50,0.04);border-top:1px solid rgba(213,0,50,0.1);display:flex;align-items:center;gap:10px;font-size:13px;color:var(--pru-text); }
.pdf-file-selected i { color:var(--pru-red); }
</style>

<script src="../assets/js/scripts.js"></script>
<script>
let allUW  = [];
let allPol = [];
let selectedPdf = null;
let activeTab   = 'underwriting';

/* ── Tab switching ── */
function switchTab(tab) {
    activeTab = tab;
    document.querySelectorAll('.gtab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.gtab-panel').forEach(p => p.style.display = 'none');
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('panel-' + tab).style.display = '';
    if (tab === 'underwriting' && !allUW.length)  loadSection('underwriting');
    if (tab === 'policy'       && !allPol.length)  loadSection('policy');
}

/* ── Load section ── */
function loadSection(section) {
    const bodyId = section === 'underwriting' ? 'uwBody' : 'polBody';
    fetch('../api/guidelines/get.php?section=' + section)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                if (section === 'underwriting') allUW  = d.data;
                else                            allPol = d.data;
                renderTable(d.data, bodyId);
            } else {
                document.getElementById(bodyId).innerHTML =
                    '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>' + (d.message||'Error') + '</p></div></td></tr>';
            }
        });
}

/* ── Render table ── */
function renderTable(items, bodyId) {
    const tbody = document.getElementById(bodyId);
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><i class="fas fa-book"></i><p>No guidelines found</p></div></td></tr>';
        return;
    }
    tbody.innerHTML = items.map((g, i) => `
        <tr>
            <td>${i+1}</td>
            <td><strong>${esc(g.title)}</strong></td>
            <td style="font-size:12px;color:var(--pru-muted);max-width:260px;">${esc(g.description||'')}</td>
            <td>
                ${g.pdf_file
                    ? `<a href="../api/guidelines/serve-pdf.php?file=${encodeURIComponent(g.pdf_file)}" target="_blank" class="btn-pru btn-pru-sm" style="background:var(--pru-success);"><i class="fas fa-file-pdf"></i> View PDF</a>`
                    : `<span style="color:#ccc;font-size:12px;"><i class="fas fa-times-circle"></i> No PDF</span>`}
            </td>
            <td>
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="openEdit(${g.id})">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </td>
        </tr>`).join('');
}

/* ── Open edit modal ── */
function openEdit(id) {
    const all = activeTab === 'underwriting' ? allUW : allPol;
    const g   = all.find(x => x.id == id);
    if (!g) return;
    document.getElementById('editGId').value    = g.id;
    document.getElementById('editGTitle').value = g.title;
    document.getElementById('editGDesc').value  = g.description || '';
    selectedPdf = null;
    document.getElementById('gPdfFileName').style.display  = 'none';
    document.getElementById('gPdfProgress').style.display  = 'none';
    document.getElementById('gPdfBar').style.width         = '0';
    document.getElementById('gPdfCurrentStatus').innerHTML = g.pdf_file
        ? `<div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(40,167,69,0.06);border:1px solid rgba(40,167,69,0.15);border-radius:8px;font-size:12px;">
               <i class="fas fa-check-circle" style="color:var(--pru-success);"></i>
               <span>PDF attached: <strong>${esc(g.pdf_file)}</strong></span>
               <a href="../api/guidelines/serve-pdf.php?file=${encodeURIComponent(g.pdf_file)}" target="_blank" style="margin-left:auto;color:var(--pru-red);font-size:11px;font-weight:700;">View</a>
           </div>`
        : `<div style="font-size:12px;color:var(--pru-muted);padding:4px 0;">No PDF attached yet.</div>`;
    openModal('editGuidelineModal');
}

/* ── Save guideline ── */
async function saveGuideline() {
    const id = document.getElementById('editGId').value;
    if (selectedPdf) {
        const ok = await uploadGuidelinePdf(id);
        if (!ok) return;
    }
    const res    = await fetch('../api/guidelines/update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, title: document.getElementById('editGTitle').value, description: document.getElementById('editGDesc').value })
    });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) {
        closeModal('editGuidelineModal');
        // Reset cache and reload active tab
        if (activeTab === 'underwriting') { allUW  = []; loadSection('underwriting'); }
        else                              { allPol = []; loadSection('policy'); }
    }
}

/* ── Upload PDF ── */
async function uploadGuidelinePdf(id) {
    const prog = document.getElementById('gPdfProgress');
    const bar  = document.getElementById('gPdfBar');
    const txt  = document.getElementById('gPdfProgressText');
    prog.style.display = '';
    bar.style.width    = '30%';
    txt.textContent    = 'Uploading PDF...';
    txt.style.color    = 'var(--pru-muted)';

    const fd = new FormData();
    fd.append('guideline_id', id);
    fd.append('pdf_file', selectedPdf);

    try {
        bar.style.width = '70%';
        const res    = await fetch('../api/guidelines/upload-pdf.php', { method:'POST', body: fd });
        const result = await res.json();
        bar.style.width = '100%';
        if (result.success) {
            txt.textContent = 'PDF uploaded!';
            txt.style.color = 'var(--pru-success)';
            return true;
        } else {
            txt.textContent = result.message;
            txt.style.color = 'var(--pru-danger)';
            showToast(result.message, 'error');
            return false;
        }
    } catch(e) {
        txt.textContent = 'Upload failed';
        txt.style.color = 'var(--pru-danger)';
        return false;
    }
}

/* ── File input & drag-drop ── */
document.getElementById('gPdfFileInput').addEventListener('change', function() {
    if (this.files[0]) setGPdf(this.files[0]);
});

function setGPdf(file) {
    if (file.type !== 'application/pdf') { showToast('Only PDF files allowed', 'error'); return; }
    if (file.size > 20 * 1024 * 1024)   { showToast('File must be under 20MB', 'error'); return; }
    selectedPdf = file;
    const el = document.getElementById('gPdfFileName');
    el.style.display = 'flex';
    el.innerHTML = `<i class="fas fa-file-pdf"></i> ${esc(file.name)} <span style="margin-left:auto;color:var(--pru-muted);font-size:11px;">${(file.size/1024/1024).toFixed(2)} MB</span>`;
}

const dropZone = document.getElementById('gPdfDropZone');
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault(); dropZone.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) setGPdf(e.dataTransfer.files[0]);
});

/* ── Init ── */
loadSection('underwriting');
</script>

<?php include '../includes/footer.php'; ?>
