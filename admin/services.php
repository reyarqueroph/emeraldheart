<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Manage Services';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<main class="pru-main">
    <div class="page-header">
        <h2>Manage Services</h2>
        <p>Manage New Business, After-Sales, and Claims content for agents.</p>
    </div>

    <!-- Category Tabs -->
    <div style="display:flex;gap:4px;margin-bottom:24px;border-bottom:2px solid var(--pru-border);">
        <button class="svc-tab active" id="tab-new-business" onclick="switchCat('new-business')"><i class="fas fa-file-signature"></i> New Business</button>
        <button class="svc-tab" id="tab-after-sales"   onclick="switchCat('after-sales')"><i class="fas fa-headset"></i> After-Sales</button>
        <button class="svc-tab" id="tab-claims"        onclick="switchCat('claims')"><i class="fas fa-file-invoice"></i> Claims</button>
    </div>

    <div id="svcContent">
        <div style="text-align:center;padding:60px;color:var(--pru-muted);">
            <i class="fas fa-spinner fa-spin" style="font-size:28px;"></i>
            <p style="margin-top:12px;">Loading...</p>
        </div>
    </div>
</main>

<!-- Section Edit Modal -->
<div class="modal-overlay" id="sectionModal">
    <div class="modal-box">
        <div class="modal-head">
            <h5><i class="fas fa-edit" style="color:var(--pru-red);margin-right:8px;"></i>Edit Section</h5>
            <button class="modal-close" onclick="closeModal('sectionModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <input type="hidden" id="secId">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" id="secTitle">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Icon (FA class)</label>
                        <input type="text" class="form-control" id="secIcon" placeholder="fa-book">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="secDesc" rows="2"></textarea>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="form-label">External URL <small style="color:var(--pru-muted)">(optional — opens link button)</small></label>
                        <input type="url" class="form-control" id="secUrl" placeholder="https://...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="secSort" value="0" min="0">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('sectionModal')">Cancel</button>
            <button class="btn-pru" onclick="saveSection()"><i class="fas fa-save"></i> Save Section</button>
        </div>
    </div>
</div>

<!-- Item Add/Edit Modal -->
<div class="modal-overlay" id="itemModal">
    <div class="modal-box modal-lg">
        <div class="modal-head">
            <h5 id="itemModalTitle"><i class="fas fa-plus-circle" style="color:var(--pru-red);margin-right:8px;"></i>Add Item</h5>
            <button class="modal-close" onclick="closeModal('itemModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <input type="hidden" id="itemId">
            <input type="hidden" id="itemSectionId">
            <input type="hidden" id="itemAction">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" id="itemTitle">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select class="form-select" id="itemType" onchange="toggleItemFields()">
                            <option value="document">Document / Form</option>
                            <option value="step">Step</option>
                            <option value="link">External Link</option>
                            <option value="info">Info / Note</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Description / Notes</label>
                        <textarea class="form-control" id="itemDesc" rows="2"></textarea>
                    </div>
                </div>
                <div class="col-md-8" id="itemUrlRow">
                    <div class="form-group">
                        <label class="form-label">External URL <small style="color:var(--pru-muted)">(for link type)</small></label>
                        <input type="url" class="form-control" id="itemUrl" placeholder="https://...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="itemSort" value="0" min="0">
                    </div>
                </div>
                <!-- PDF Upload (shown after item is saved) -->
                <div class="col-12" id="pdfUploadRow" style="display:none;">
                    <div class="form-group">
                        <label class="form-label">PDF File</label>
                        <div id="itemPdfStatus" style="margin-bottom:8px;"></div>
                        <div class="pdf-upload-zone" id="itemPdfDrop">
                            <input type="file" id="itemPdfInput" accept=".pdf" style="display:none;">
                            <div class="pdf-upload-inner" onclick="document.getElementById('itemPdfInput').click()">
                                <i class="fas fa-file-pdf"></i>
                                <div class="puz-text">Click to upload or drag &amp; drop PDF</div>
                                <div class="puz-hint">PDF only · Max 20MB</div>
                            </div>
                            <div id="itemPdfName" class="pdf-file-selected" style="display:none;"></div>
                        </div>
                        <div id="itemPdfProgress" style="display:none;margin-top:8px;">
                            <div style="height:4px;background:#eee;border-radius:2px;overflow:hidden;">
                                <div id="itemPdfBar" style="height:100%;background:var(--pru-red);width:0;transition:width 0.3s;border-radius:2px;"></div>
                            </div>
                            <div id="itemPdfProgressText" style="font-size:11px;color:var(--pru-muted);margin-top:4px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('itemModal')">Cancel</button>
            <button class="btn-pru" onclick="saveItem()"><i class="fas fa-save"></i> Save Item</button>
        </div>
    </div>
</div>

<style>
.svc-tab {
    background:none;border:none;border-bottom:3px solid transparent;
    padding:10px 20px;font-size:13px;font-weight:600;color:var(--pru-muted);
    cursor:pointer;display:flex;align-items:center;gap:7px;
    margin-bottom:-2px;transition:all 0.2s;
}
.svc-tab:hover{color:var(--pru-text);}
.svc-tab.active{color:var(--pru-red);border-bottom-color:var(--pru-red);}

.svc-section-card {
    background:white;border:1px solid var(--pru-border);border-radius:var(--radius-lg);
    margin-bottom:20px;overflow:hidden;
}
.svc-section-head {
    display:flex;align-items:center;gap:12px;padding:16px 20px;
    background:var(--pru-light);border-bottom:1px solid var(--pru-border);
}
.svc-section-icon {
    width:38px;height:38px;background:rgba(213,0,50,0.1);border-radius:var(--radius-sm);
    display:flex;align-items:center;justify-content:center;color:var(--pru-red);flex-shrink:0;
}
.svc-section-info{flex:1;}
.svc-section-title{font-size:15px;font-weight:700;color:var(--pru-text);margin:0;}
.svc-section-desc{font-size:12px;color:var(--pru-muted);margin:2px 0 0;}
.svc-section-actions{display:flex;gap:8px;align-items:center;}

.svc-item-row {
    display:flex;align-items:center;gap:12px;padding:12px 20px;
    border-bottom:1px solid var(--pru-border);
}
.svc-item-row:last-child{border-bottom:none;}
.svc-item-num {
    width:26px;height:26px;background:rgba(213,0,50,0.08);border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:11px;font-weight:700;color:var(--pru-red);flex-shrink:0;
}
.svc-item-info{flex:1;}
.svc-item-title{font-size:13px;font-weight:700;color:var(--pru-text);}
.svc-item-desc{font-size:12px;color:var(--pru-muted);margin-top:2px;}
.svc-item-badges{display:flex;gap:6px;margin-top:4px;flex-wrap:wrap;}
.svc-badge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;text-transform:uppercase;}
.svc-badge-doc{background:rgba(23,162,184,0.1);color:var(--pru-info);}
.svc-badge-step{background:rgba(40,167,69,0.1);color:var(--pru-success);}
.svc-badge-link{background:rgba(213,0,50,0.08);color:var(--pru-red);}
.svc-badge-info{background:rgba(255,193,7,0.1);color:#e6a800;}
.svc-badge-pdf{background:rgba(40,167,69,0.1);color:var(--pru-success);}

.pdf-upload-zone{border:2px dashed var(--pru-border);border-radius:var(--radius-md);transition:all 0.2s;overflow:hidden;}
.pdf-upload-zone:hover,.pdf-upload-zone.drag-over{border-color:var(--pru-red);background:rgba(213,0,50,0.02);}
.pdf-upload-inner{padding:20px;text-align:center;cursor:pointer;}
.pdf-upload-inner i{font-size:28px;color:var(--pru-red);margin-bottom:6px;display:block;}
.puz-text{font-size:13px;font-weight:600;color:var(--pru-text);}
.puz-hint{font-size:11px;color:var(--pru-muted);margin-top:3px;}
.pdf-file-selected{padding:10px 14px;background:rgba(213,0,50,0.04);border-top:1px solid rgba(213,0,50,0.1);display:flex;align-items:center;gap:10px;font-size:13px;color:var(--pru-text);}
.pdf-file-selected i{color:var(--pru-red);}
</style>

<script src="../assets/js/scripts.js"></script>
<script>
let allSections  = [];
let activeCat    = 'new-business';
let selectedPdf  = null;
let pendingItemId = null; // item id waiting for PDF upload

/* ── Load all data once ── */
async function loadAll() {
    const res = await fetch('../api/services/get.php');
    const d   = await res.json();
    if (d.success) { allSections = d.data; renderCat(activeCat); }
    else document.getElementById('svcContent').innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>${d.message||'Error loading'}</p></div>`;
}

/* ── Switch category tab ── */
function switchCat(cat) {
    activeCat = cat;
    document.querySelectorAll('.svc-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + cat).classList.add('active');
    renderCat(cat);
}

/* ── Render sections for a category ── */
function renderCat(cat) {
    const sections = allSections.filter(s => s.category === cat);
    const el = document.getElementById('svcContent');
    if (!sections.length) { el.innerHTML = '<div class="empty-state"><i class="fas fa-folder-open"></i><p>No sections found</p></div>'; return; }

    el.innerHTML = sections.map(sec => `
        <div class="svc-section-card" id="sec-card-${sec.id}">
            <div class="svc-section-head">
                <div class="svc-section-icon"><i class="fas ${esc(sec.icon)}"></i></div>
                <div class="svc-section-info">
                    <div class="svc-section-title">${esc(sec.title)}</div>
                    ${sec.description ? `<div class="svc-section-desc">${esc(sec.description)}</div>` : ''}
                    ${sec.external_url ? `<div style="font-size:11px;color:var(--pru-red);margin-top:2px;"><i class="fas fa-link"></i> ${esc(sec.external_url)}</div>` : ''}
                </div>
                <div class="svc-section-actions">
                    <span class="badge-status badge-${sec.is_active=='1'?'active':'inactive'}" style="cursor:pointer;font-size:11px;" onclick="toggleSection(${sec.id})">${sec.is_active=='1'?'Active':'Inactive'}</span>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="openEditSection(${sec.id})"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn-pru btn-pru-sm" onclick="openAddItem(${sec.id})"><i class="fas fa-plus"></i> Add Item</button>
                </div>
            </div>
            <div id="items-${sec.id}">
                ${renderItems(sec.items, sec.id)}
            </div>
        </div>`).join('');
}

function renderItems(items, secId) {
    if (!items || !items.length) return `<div style="padding:20px;text-align:center;color:var(--pru-muted);font-size:13px;"><i class="fas fa-inbox" style="opacity:0.3;"></i><p style="margin-top:6px;">No items yet. Click "Add Item" to add one.</p></div>`;
    return items.map((item, i) => `
        <div class="svc-item-row" id="item-row-${item.id}">
            <div class="svc-item-num">${i+1}</div>
            <div class="svc-item-info">
                <div class="svc-item-title">${esc(item.title)}</div>
                ${item.description ? `<div class="svc-item-desc">${esc(item.description)}</div>` : ''}
                <div class="svc-item-badges">
                    <span class="svc-badge svc-badge-${item.item_type}">${item.item_type}</span>
                    ${item.pdf_file ? `<span class="svc-badge svc-badge-pdf"><i class="fas fa-file-pdf"></i> PDF attached</span>` : ''}
                    ${item.external_url ? `<span class="svc-badge svc-badge-link"><i class="fas fa-link"></i> Link</span>` : ''}
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
                ${item.pdf_file ? `<a href="../api/services/serve-pdf.php?file=${encodeURIComponent(item.pdf_file)}" target="_blank" class="btn-pru btn-pru-sm" style="background:var(--pru-success);"><i class="fas fa-file-pdf"></i></a>` : ''}
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="openEditItem(${secId},${item.id})"><i class="fas fa-edit"></i></button>
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-danger);" onclick="deleteItem(${item.id},'${esc(item.title)}',${secId})"><i class="fas fa-trash"></i></button>
            </div>
        </div>`).join('');
}

/* ── Section edit ── */
function openEditSection(id) {
    const sec = allSections.find(s => s.id == id);
    if (!sec) return;
    document.getElementById('secId').value    = sec.id;
    document.getElementById('secTitle').value = sec.title;
    document.getElementById('secIcon').value  = sec.icon;
    document.getElementById('secDesc').value  = sec.description || '';
    document.getElementById('secUrl').value   = sec.external_url || '';
    document.getElementById('secSort').value  = sec.sort_order;
    openModal('sectionModal');
}

async function saveSection() {
    const res    = await fetch('../api/services/save.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'update', target:'section', id:document.getElementById('secId').value, title:document.getElementById('secTitle').value, icon:document.getElementById('secIcon').value, description:document.getElementById('secDesc').value, external_url:document.getElementById('secUrl').value||null, sort_order:document.getElementById('secSort').value }) });
    const result = await res.json();
    showToast(result.message, result.success?'success':'error');
    if (result.success) { closeModal('sectionModal'); await loadAll(); }
}

async function toggleSection(id) {
    const res    = await fetch('../api/services/save.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'toggle', target:'section', id }) });
    const result = await res.json();
    showToast(result.message, result.success?'success':'error');
    if (result.success) await loadAll();
}

/* ── Item add/edit ── */
function openAddItem(secId) {
    document.getElementById('itemId').value        = '';
    document.getElementById('itemSectionId').value = secId;
    document.getElementById('itemAction').value    = 'create';
    document.getElementById('itemModalTitle').innerHTML = '<i class="fas fa-plus-circle" style="color:var(--pru-red);margin-right:8px;"></i>Add Item';
    document.getElementById('itemTitle').value = '';
    document.getElementById('itemDesc').value  = '';
    document.getElementById('itemUrl').value   = '';
    document.getElementById('itemSort').value  = '0';
    document.getElementById('itemType').value  = 'document';
    document.getElementById('pdfUploadRow').style.display = 'none';
    document.getElementById('itemPdfName').style.display  = 'none';
    document.getElementById('itemPdfProgress').style.display = 'none';
    selectedPdf = null;
    toggleItemFields();
    openModal('itemModal');
}

function openEditItem(secId, itemId) {
    const sec  = allSections.find(s => s.id == secId);
    const item = sec?.items?.find(i => i.id == itemId);
    if (!item) return;
    document.getElementById('itemId').value        = item.id;
    document.getElementById('itemSectionId').value = secId;
    document.getElementById('itemAction').value    = 'update';
    document.getElementById('itemModalTitle').innerHTML = '<i class="fas fa-edit" style="color:var(--pru-red);margin-right:8px;"></i>Edit Item';
    document.getElementById('itemTitle').value = item.title;
    document.getElementById('itemDesc').value  = item.description || '';
    document.getElementById('itemUrl').value   = item.external_url || '';
    document.getElementById('itemSort').value  = item.sort_order;
    document.getElementById('itemType').value  = item.item_type;
    selectedPdf = null;
    document.getElementById('itemPdfName').style.display     = 'none';
    document.getElementById('itemPdfProgress').style.display = 'none';
    document.getElementById('pdfUploadRow').style.display    = '';
    document.getElementById('itemPdfStatus').innerHTML = item.pdf_file
        ? `<div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(40,167,69,0.06);border:1px solid rgba(40,167,69,0.15);border-radius:8px;font-size:12px;">
               <i class="fas fa-check-circle" style="color:var(--pru-success);"></i>
               <span>PDF: <strong>${esc(item.pdf_file)}</strong></span>
               <a href="../api/services/serve-pdf.php?file=${encodeURIComponent(item.pdf_file)}" target="_blank" style="margin-left:auto;color:var(--pru-red);font-size:11px;font-weight:700;">View</a>
           </div>`
        : `<div style="font-size:12px;color:var(--pru-muted);">No PDF attached yet.</div>`;
    toggleItemFields();
    openModal('itemModal');
}

function toggleItemFields() {
    const type = document.getElementById('itemType').value;
    document.getElementById('itemUrlRow').style.display = type === 'link' ? '' : 'none';
}

async function saveItem() {
    const action = document.getElementById('itemAction').value;
    const id     = document.getElementById('itemId').value;
    const secId  = document.getElementById('itemSectionId').value;
    const title  = document.getElementById('itemTitle').value.trim();
    if (!title) { showToast('Title is required.', 'warning'); return; }

    const payload = { action, target:'item', id: id ? parseInt(id) : undefined, section_id: parseInt(secId), title, description: document.getElementById('itemDesc').value, external_url: document.getElementById('itemUrl').value || null, item_type: document.getElementById('itemType').value, sort_order: parseInt(document.getElementById('itemSort').value)||0 };

    const res    = await fetch('../api/services/save.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const result = await res.json();

    if (!result.success) { showToast(result.message, 'error'); return; }

    // Upload PDF if selected
    const savedId = result.id || parseInt(id);
    if (selectedPdf && savedId) {
        await uploadItemPdf(savedId);
    }

    showToast(result.message, 'success');
    closeModal('itemModal');
    await loadAll();
}

async function uploadItemPdf(itemId) {
    const prog = document.getElementById('itemPdfProgress');
    const bar  = document.getElementById('itemPdfBar');
    const txt  = document.getElementById('itemPdfProgressText');
    prog.style.display = '';
    bar.style.width    = '30%';
    txt.textContent    = 'Uploading PDF...';
    txt.style.color    = 'var(--pru-muted)';

    const fd = new FormData();
    fd.append('item_id', itemId);
    fd.append('pdf_file', selectedPdf);

    try {
        bar.style.width = '70%';
        const res    = await fetch('../api/services/upload-pdf.php', { method:'POST', body: fd });
        const result = await res.json();
        bar.style.width = '100%';
        txt.textContent = result.success ? 'PDF uploaded!' : result.message;
        txt.style.color = result.success ? 'var(--pru-success)' : 'var(--pru-danger)';
        if (!result.success) showToast(result.message, 'error');
    } catch(e) {
        txt.textContent = 'Upload failed';
        txt.style.color = 'var(--pru-danger)';
    }
}

async function deleteItem(id, name, secId) {
    if (!confirm(`Delete "${name}"?`)) return;
    const res    = await fetch('../api/services/save.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'delete', target:'item', id }) });
    const result = await res.json();
    showToast(result.message, result.success?'success':'error');
    if (result.success) await loadAll();
}

/* ── PDF file input ── */
document.getElementById('itemPdfInput').addEventListener('change', function() { if (this.files[0]) setItemPdf(this.files[0]); });
function setItemPdf(file) {
    if (file.type !== 'application/pdf') { showToast('Only PDF files allowed','error'); return; }
    if (file.size > 20*1024*1024) { showToast('File must be under 20MB','error'); return; }
    selectedPdf = file;
    const el = document.getElementById('itemPdfName');
    el.style.display = 'flex';
    el.innerHTML = `<i class="fas fa-file-pdf"></i> ${esc(file.name)} <span style="margin-left:auto;color:var(--pru-muted);font-size:11px;">${(file.size/1024/1024).toFixed(2)} MB</span>`;
}
const dropZone = document.getElementById('itemPdfDrop');
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('drag-over'); if (e.dataTransfer.files[0]) setItemPdf(e.dataTransfer.files[0]); });

loadAll();
</script>

<?php include '../includes/footer.php'; ?>
