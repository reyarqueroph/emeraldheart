<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_name   = $_SESSION['user_name'] ?? 'Agent';
$agent_code  = $_SESSION['agent_code'] ?? '';
$initials    = strtoupper(substr($user_name, 0, 1));
$active_page = 'services';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/agent-dashboard.css">
    <link rel="stylesheet" href="../assets/css/theme-toggle.css">
    <style>
        .guideline-pdf-viewer {
            width:100%; height:70vh; min-height:480px;
            border:none; border-radius:var(--radius-sm); background:#f0f0f0;
        }
        .pdf-loading-state {
            display:flex; flex-direction:column; align-items:center;
            justify-content:center; gap:12px; padding:60px;
            color:var(--pru-muted); text-align:center;
        }
        .pdf-loading-state i { font-size:40px; opacity:0.3; }
    </style>
</head>
<body class="agent-dash-body">

<?php include '../includes/agent-sidebar.php'; ?>

<div class="ad-main-wrap" id="adMainWrap">
    <main class="ad-content">

        <div class="page-header">
            <h2>Services</h2>
            <p>New business, after-sales services, forms, and claims.</p>
        </div>

        <div class="row g-4">
            <!-- Left Nav -->
            <div class="col-lg-3">
                <div class="guidelines-nav" id="servicesNav">
                    <div class="gnav-header"><i class="fas fa-list-ul" style="margin-right:8px;"></i>Categories</div>
                    <div style="padding:20px;text-align:center;color:rgba(255,255,255,0.3);font-size:12px;">
                        <i class="fas fa-spinner fa-spin"></i> Loading...
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="col-lg-9" id="servicesContent">
                <div class="pru-card">
                    <div class="card-body">
                        <div class="pdf-loading-state">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Loading services...</p>
                        </div>
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
let allSections = [];
let activeKey   = null;

const catLabels = {
    'new-business': 'New Business',
    'after-sales':  'After-Sales',
    'claims':       'Claims',
};
const catIcons = {
    'new-business': 'fa-file-signature',
    'after-sales':  'fa-headset',
    'claims':       'fa-file-invoice',
};

/* ── Load all sections from API ── */
fetch('../api/services/get.php')
    .then(r => r.json())
    .then(d => {
        if (!d.success) { showError('Failed to load services.'); return; }
        allSections = d.data;
        buildNav();
        // Check for ?section= or ?group= URL param from sidebar
        const params     = new URLSearchParams(window.location.search);
        const urlSection = params.get('section');
        const urlGroup   = params.get('group');

        if (urlSection && allSections.find(s => s.section_key === urlSection)) {
            showSection(urlSection);
        } else if (urlGroup) {
            // Show first section of the requested group
            const first = allSections.find(s => s.category === urlGroup);
            if (first) showSection(first.section_key);
            else if (allSections.length) showSection(allSections[0].section_key);
        } else if (allSections.length) {
            showSection(allSections[0].section_key);
        }
    })
    .catch(() => showError('Connection error.'));

function showError(msg) {
    document.getElementById('servicesContent').innerHTML = `<div class="pru-card"><div class="card-body"><div class="pdf-loading-state"><i class="fas fa-exclamation-circle"></i><p>${msg}</p></div></div></div>`;
}

/* ── Build left nav ── */
function buildNav() {
    const nav = document.getElementById('servicesNav');
    const cats = ['new-business','after-sales','claims'];
    let html = '<div class="gnav-header"><i class="fas fa-list-ul" style="margin-right:8px;"></i>Categories</div>';

    cats.forEach(cat => {
        const sections = allSections.filter(s => s.category === cat);
        if (!sections.length) return;
        html += `<div class="gnav-group-label">${catLabels[cat]}</div>`;
        sections.forEach(sec => {
            html += `<a href="#" class="gnav-item" id="gnav-${sec.section_key}"
                onclick="showSection('${sec.section_key}',this);return false;">
                <i class="fas ${esc(sec.icon)}"></i> ${esc(sec.title)}
            </a>`;
        });
    });

    nav.innerHTML = html;
}

/* ── Show a section ── */
function showSection(key, linkEl) {
    activeKey = key;
    // Update nav active state
    document.querySelectorAll('.gnav-item').forEach(l => l.classList.remove('active'));
    const navLink = linkEl || document.getElementById('gnav-' + key);
    if (navLink) navLink.classList.add('active');

    const sec = allSections.find(s => s.section_key === key);
    if (!sec) return;

    const content = document.getElementById('servicesContent');

    // Claims sections → redirect to PRU Life website
    if (sec.category === 'claims') {
        const claimsUrl = sec.external_url || 'https://www.prulifeuk.com.ph/en/claims/';
        content.innerHTML = `
            <div class="pru-card">
                <div class="card-header">
                    <h5><i class="fas ${esc(sec.icon)}" style="color:var(--pru-red);margin-right:8px;"></i>${esc(sec.title)}</h5>
                </div>
                <div class="card-body">
                    ${sec.description ? `<p style="font-size:13px;color:var(--pru-muted);margin-bottom:16px;">${esc(sec.description)}</p>` : ''}
                    ${renderItems(sec.items, sec)}
                    <div style="margin-top:20px;padding:16px;background:rgba(213,0,50,0.04);border:1px solid rgba(213,0,50,0.15);border-radius:var(--radius-sm);">
                        <p style="font-size:13px;color:var(--pru-muted);margin:0 0 12px;">
                            <i class="fas fa-info-circle" style="color:var(--pru-red);margin-right:6px;"></i>
                            For complete claims information and to file a claim, visit the official PRU Life U.K. website.
                        </p>
                        <a href="${claimsUrl}" target="_blank" class="btn-pru">
                            <i class="fas fa-external-link-alt"></i> Go to PRU Life Claims Page
                        </a>
                    </div>
                </div>
            </div>`;
        return;
    }

    // Premium Payment → show items if any, plus external link
    if (sec.section_key === 'premium-payment') {
        const url = sec.external_url || 'https://www.prulifeuk.com.ph/en/policy-services-information/premium-payment-facilities/';
        content.innerHTML = `
            <div class="pru-card">
                <div class="card-header">
                    <h5><i class="fas ${esc(sec.icon)}" style="color:var(--pru-red);margin-right:8px;"></i>${esc(sec.title)}</h5>
                </div>
                <div class="card-body" ${sec.items && sec.items.length ? 'style="padding:0;"' : ''}>
                    ${sec.items && sec.items.length
                        ? renderFormsList(sec.items)
                        : `${sec.description ? `<p style="font-size:13px;color:var(--pru-muted);margin-bottom:20px;">${esc(sec.description)}</p>` : ''}`
                    }
                    <div style="padding:${sec.items && sec.items.length ? '16px 20px' : '0'};${sec.items && sec.items.length ? 'border-top:1px solid var(--pru-border);' : ''}">
                        <a href="${url}" target="_blank" class="btn-pru">
                            <i class="fas fa-external-link-alt"></i> View Premium Payment Facilities
                        </a>
                    </div>
                </div>
            </div>`;
        return;
    }

    // PruOne → steps + external link
    if (sec.section_key === 'pruone') {
        const url = sec.external_url || 'https://pruone.prulifeuk.com.ph';
        content.innerHTML = `
            <div class="pru-card">
                <div class="card-header">
                    <h5><i class="fas ${esc(sec.icon)}" style="color:var(--pru-red);margin-right:8px;"></i>${esc(sec.title)}</h5>
                </div>
                <div class="card-body">
                    ${sec.description ? `<p style="font-size:13px;color:var(--pru-muted);margin-bottom:16px;">${esc(sec.description)}</p>` : ''}
                    ${renderItems(sec.items, sec)}
                    <div style="margin-top:16px;">
                        <a href="${url}" target="_blank" class="btn-pru"><i class="fas fa-external-link-alt"></i> Go to PruOne</a>
                    </div>
                </div>
            </div>`;
        return;
    }

    // After-Sales Forms & Auto-Debit → list with PDF preview
    if (sec.section_key === 'after-sales-forms' || sec.section_key === 'auto-debit') {
        content.innerHTML = `
            <div class="pru-card">
                <div class="card-header">
                    <h5><i class="fas ${esc(sec.icon)}" style="color:var(--pru-red);margin-right:8px;"></i>${esc(sec.title)}</h5>
                    ${sec.description ? `<p style="font-size:12px;color:var(--pru-muted);margin:4px 0 0;">${esc(sec.description)}</p>` : ''}
                </div>
                <div class="card-body" style="padding:0;">
                    ${renderFormsList(sec.items)}
                </div>
            </div>`;
        return;
    }

    // Default: Manual / steps
    content.innerHTML = `
        <div class="pru-card">
            <div class="card-header">
                <h5><i class="fas ${esc(sec.icon)}" style="color:var(--pru-red);margin-right:8px;"></i>${esc(sec.title)}</h5>
            </div>
            <div class="card-body">
                ${sec.description ? `<p style="font-size:13px;color:var(--pru-muted);margin-bottom:16px;">${esc(sec.description)}</p>` : ''}
                ${renderItems(sec.items, sec)}
                ${sec.external_url ? `<div style="margin-top:16px;"><a href="${esc(sec.external_url)}" target="_blank" class="btn-pru"><i class="fas fa-external-link-alt"></i> Open Link</a></div>` : ''}
            </div>
        </div>`;
}

/* ── Render items (steps / documents) ── */
function renderItems(items, sec) {
    if (!items || !items.length) return '<p style="font-size:13px;color:var(--pru-muted);">No items available.</p>';

    return items.map((item, i) => {
        if (item.item_type === 'step') {
            return `<div style="display:flex;gap:16px;padding:14px 0;border-bottom:1px solid var(--pru-border);">
                <div style="width:36px;height:36px;background:var(--pru-red);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:white;flex-shrink:0;">${i+1}</div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:var(--pru-text);margin-bottom:4px;">${esc(item.title)}</div>
                    ${item.description ? `<div style="font-size:13px;color:var(--pru-muted);">${esc(item.description)}</div>` : ''}
                    ${item.pdf_file ? `<a href="../api/services/serve-pdf.php?file=${encodeURIComponent(item.pdf_file)}" target="_blank" class="btn-pru btn-pru-sm" style="margin-top:8px;"><i class="fas fa-file-pdf"></i> View PDF</a>` : ''}
                </div>
            </div>`;
        }
        if (item.item_type === 'document') {
            return `<div style="display:flex;align-items:flex-start;gap:14px;padding:12px 0;border-bottom:1px solid var(--pru-border);">
                <div style="width:36px;height:36px;background:rgba(213,0,50,0.08);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--pru-red);flex-shrink:0;"><i class="fas fa-file-alt"></i></div>
                <div style="flex:1;">
                    <div style="font-size:13px;font-weight:700;color:var(--pru-text);">${esc(item.title)}</div>
                    ${item.description ? `<div style="font-size:12px;color:var(--pru-muted);margin-top:2px;">${esc(item.description)}</div>` : ''}
                </div>
                ${item.pdf_file ? `<a href="../api/services/serve-pdf.php?file=${encodeURIComponent(item.pdf_file)}" target="_blank" class="btn-pru btn-pru-sm" style="background:var(--pru-success);flex-shrink:0;"><i class="fas fa-file-pdf"></i> View</a>` : ''}
            </div>`;
        }
        if (item.item_type === 'link') {
            return `<div style="padding:12px 0;border-bottom:1px solid var(--pru-border);">
                <a href="${esc(item.external_url||'#')}" target="_blank" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--pru-red);">
                    <i class="fas fa-external-link-alt"></i>
                    <span style="font-size:13px;font-weight:700;">${esc(item.title)}</span>
                </a>
                ${item.description ? `<div style="font-size:12px;color:var(--pru-muted);margin-top:4px;padding-left:24px;">${esc(item.description)}</div>` : ''}
            </div>`;
        }
        // info
        return `<div style="padding:12px;background:rgba(213,0,50,0.04);border-radius:var(--radius-sm);margin-bottom:8px;">
            <div style="font-size:13px;font-weight:700;color:var(--pru-text);margin-bottom:4px;">${esc(item.title)}</div>
            ${item.description ? `<div style="font-size:12px;color:var(--pru-muted);">${esc(item.description)}</div>` : ''}
        </div>`;
    }).join('');
}

/* ── Render after-sales / auto-debit forms list with PDF preview ── */
function renderFormsList(items) {
    if (!items || !items.length) return '<div style="padding:24px;text-align:center;color:var(--pru-muted);font-size:13px;"><i class="fas fa-inbox" style="font-size:28px;opacity:0.3;display:block;margin-bottom:8px;"></i>No forms available yet.</div>';
    return items.map(item => `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 20px;border-bottom:1px solid var(--pru-border);">
            <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                <div style="width:36px;height:36px;background:rgba(213,0,50,0.08);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--pru-red);flex-shrink:0;">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div style="min-width:0;">
                    <div style="font-size:13px;font-weight:600;color:var(--pru-text);">${esc(item.title)}</div>
                    ${item.description ? `<div style="font-size:11px;color:var(--pru-muted);margin-top:1px;">${esc(item.description)}</div>` : ''}
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;margin-left:12px;">
                ${item.pdf_file
                    ? `<button onclick="previewPdf('../api/services/serve-pdf.php?file=${encodeURIComponent(item.pdf_file)}','${esc(item.title).replace(/'/g,"\\'")}');"
                               class="btn-pru btn-pru-sm" style="background:var(--pru-red);">
                           <i class="fas fa-eye"></i> Preview
                       </button>
                       <a href="../api/services/serve-pdf.php?file=${encodeURIComponent(item.pdf_file)}"
                          download target="_blank"
                          class="btn-pru btn-pru-sm" style="background:var(--pru-success);" title="Download">
                           <i class="fas fa-download"></i>
                       </a>`
                    : `<button onclick="previewNoPdf('${esc(item.title).replace(/'/g,"\\'")}');"
                               class="btn-pru btn-pru-sm" style="background:var(--pru-muted);opacity:0.7;">
                           <i class="fas fa-eye"></i> Preview
                       </button>
                       <a href="${item.external_url || 'https://www.prulifeuk.com.ph/en/forms'}"
                          target="_blank" class="btn-pru-outline btn-pru-sm" title="Open on PRU Life website">
                           <i class="fas fa-external-link-alt"></i>
                       </a>`}
            </div>
        </div>`).join('');
}

/* ── PDF Preview ── */
function previewPdf(url, title) {
    document.getElementById('pdfModalTitle').textContent = title;
    document.getElementById('pdfModalFrame').src = url;
    document.getElementById('pdfModalOpenBtn').href = url;
    document.getElementById('pdfModalOpenBtn').style.display = '';
    document.getElementById('pdfModalFrame').style.display = '';
    document.getElementById('pdfModalNoPdf').style.display = 'none';
    document.getElementById('pdfPreviewModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function previewNoPdf(title) {
    document.getElementById('pdfModalTitle').textContent = title;
    document.getElementById('pdfModalFrame').src = '';
    document.getElementById('pdfModalFrame').style.display = 'none';
    document.getElementById('pdfModalOpenBtn').style.display = 'none';
    document.getElementById('pdfModalNoPdf').style.display = 'flex';
    document.getElementById('pdfPreviewModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closePdfModal() {
    document.getElementById('pdfPreviewModal').classList.remove('show');
    document.getElementById('pdfModalFrame').src = '';
    document.body.style.overflow = '';
}
</script>

<!-- PDF Preview Modal -->
<div class="modal-overlay" id="pdfPreviewModal" style="z-index:2200;padding:0;align-items:stretch;">
    <div style="background:white;width:100%;max-width:900px;margin:auto;display:flex;flex-direction:column;height:100vh;max-height:100vh;">
        <!-- Header -->
        <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid #e0e0e0;flex-shrink:0;background:white;">
            <div style="width:32px;height:32px;background:rgba(213,0,50,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#D50032;flex-shrink:0;">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div id="pdfModalTitle" style="font-size:14px;font-weight:700;color:#1C1C1C;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                <div style="font-size:11px;color:#aaa;">PDF Preview</div>
            </div>
            <a id="pdfModalOpenBtn" href="#" target="_blank" class="btn-pru btn-pru-sm" style="flex-shrink:0;">
                <i class="fas fa-external-link-alt"></i> Open in new tab
            </a>
            <button onclick="closePdfModal()" style="background:none;border:none;color:#aaa;cursor:pointer;font-size:20px;padding:4px 8px;border-radius:6px;flex-shrink:0;transition:color 0.2s;" title="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- PDF iframe -->
        <iframe id="pdfModalFrame" src="" style="flex:1;border:none;background:#525659;" title="PDF Preview"></iframe>
        <!-- No PDF state -->
        <div id="pdfModalNoPdf" style="display:none;flex:1;flex-direction:column;align-items:center;justify-content:center;gap:16px;padding:40px;text-align:center;background:#f8f8f8;">
            <div style="width:72px;height:72px;background:rgba(213,0,50,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-file-pdf" style="font-size:28px;color:#D50032;opacity:0.5;"></i>
            </div>
            <div>
                <div style="font-size:16px;font-weight:700;color:#1C1C1C;margin-bottom:6px;">PDF Not Yet Available</div>
                <div style="font-size:13px;color:#888;max-width:300px;line-height:1.6;">This form has not been uploaded yet. Please check back later or contact your administrator.</div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/agent-footer.php'; ?>
