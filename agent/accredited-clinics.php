<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_name   = $_SESSION['user_name'] ?? 'Agent';
$agent_code  = $_SESSION['agent_code'] ?? '';
$initials    = strtoupper(substr($user_name, 0, 1));
$active_page = 'accredited-clinics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – Accredited Clinics</title>
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
            <h2>Accredited Clinics</h2>
            <p>PRU Life U.K. accredited medical examination clinics and hospitals.</p>
        </div>

        <!-- Clinic List PDF Banner -->
        <div id="clinicListPdfBanner" style="display:none;" class="pru-card mb-4">
            <div class="card-body" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <div style="width:42px;height:42px;background:rgba(213,0,50,0.1);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-file-pdf" style="color:var(--pru-red);font-size:18px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14px;font-weight:700;color:var(--pru-text);">Full Accredited Clinics List</div>
                    <div style="font-size:12px;color:var(--pru-muted);">View the complete list of PRU Life U.K. accredited clinics as a PDF document.</div>
                </div>
                <div style="display:flex;gap:8px;">
                    <button id="clinicListPreviewBtn" onclick="previewClinicListPdf()" class="btn-pru">
                        <i class="fas fa-eye"></i> Preview Full List
                    </button>
                    <a id="clinicListDownloadBtn" href="#" target="_blank" class="btn-pru" style="background:var(--pru-success);">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="pru-card mb-4">
            <div class="card-body">
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    <div style="position:relative;flex:1;min-width:200px;">
                        <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--pru-muted);font-size:13px;"></i>
                        <input type="text" id="clinicSearch" class="form-control" placeholder="Search by clinic name or address..." style="padding-left:36px;" oninput="filterClinics()">
                    </div>
                    <select id="regionFilter" class="form-control" style="max-width:200px;" onchange="filterClinics()">
                        <option value="">All Regions</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="pru-card">
            <div class="card-header"><h5><i class="fas fa-clinic-medical" style="color:var(--pru-red);margin-right:8px;"></i>Accredited Clinics List</h5></div>
            <div class="card-body" style="padding:0;">
                <div style="overflow-x:auto;">
                    <table class="pru-table">
                        <thead>
                            <tr><th>Clinic / Hospital</th><th>Address</th><th>Region</th><th>Contact</th></tr>
                        </thead>
                        <tbody id="clinicsBody">
                            <tr><td colspan="4"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<div class="toast-stack"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/scripts.js"></script>
<script>
let allClinics = [];
let clinicListPdfFile = null;

// Load clinic list PDF
fetch('../api/directories/clinic-list-pdf.php?action=get')
    .then(r => r.json())
    .then(d => {
        if (d.success && d.pdf_file) {
            clinicListPdfFile = d.pdf_file;
            const url = '../api/directories/serve-pdf.php?file=' + encodeURIComponent(d.pdf_file);
            document.getElementById('clinicListPdfBanner').style.display = '';
            document.getElementById('clinicListDownloadBtn').href = url;
        }
    });

fetch('../api/directories/get.php?type=clinics')
    .then(r => r.json())
    .then(d => {
        if (!d.success) { document.getElementById('clinicsBody').innerHTML = '<tr><td colspan="4"><div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>Failed to load</p></div></td></tr>'; return; }
        allClinics = d.data;

        // Populate region filter dynamically
        const regions = [...new Set(d.data.map(c => c.region))].sort();
        const sel = document.getElementById('regionFilter');
        regions.forEach(r => { const o = document.createElement('option'); o.value = r; o.textContent = r; sel.appendChild(o); });

        renderClinics(d.data);
    });

function renderClinics(items) {
    const tbody = document.getElementById('clinicsBody');
    if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><i class="fas fa-clinic-medical"></i><p>No clinics found</p></div></td></tr>';
        return;
    }
    tbody.innerHTML = items.map(c => `
        <tr data-name="${esc(c.name).toLowerCase()}" data-region="${esc(c.region)}">
            <td><strong>${esc(c.name)}</strong></td>
            <td style="font-size:12px;color:var(--pru-muted);">${esc(c.address)}</td>
            <td><span class="badge-status badge-pending" style="font-size:11px;">${esc(c.region)}</span></td>
            <td style="font-size:12px;">${esc(c.contact)}</td>
        </tr>`).join('');
}

function filterClinics() {
    const search = document.getElementById('clinicSearch').value.toLowerCase();
    const region = document.getElementById('regionFilter').value;
    const filtered = allClinics.filter(c =>
        (!search || c.name.toLowerCase().includes(search) || c.address.toLowerCase().includes(search)) &&
        (!region || c.region === region)
    );
    renderClinics(filtered);
}

function previewClinicListPdf() {
    if (!clinicListPdfFile) return;
    const url = '../api/directories/serve-pdf.php?file=' + encodeURIComponent(clinicListPdfFile);
    document.getElementById('pdfModalFrame').src = url;
    document.getElementById('pdfModalOpenBtn').href = url;
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
    <div style="background:white;width:100%;max-width:960px;margin:auto;display:flex;flex-direction:column;height:100vh;max-height:100vh;">
        <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid #e0e0e0;flex-shrink:0;background:white;">
            <div style="width:32px;height:32px;background:rgba(213,0,50,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#D50032;flex-shrink:0;">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:14px;font-weight:700;color:#1C1C1C;">Accredited Clinics List</div>
                <div style="font-size:11px;color:#aaa;">PDF Preview</div>
            </div>
            <a id="pdfModalOpenBtn" href="#" target="_blank" class="btn-pru btn-pru-sm" style="flex-shrink:0;">
                <i class="fas fa-external-link-alt"></i> Open in new tab
            </a>
            <button onclick="closePdfModal()" style="background:none;border:none;color:#aaa;cursor:pointer;font-size:20px;padding:4px 8px;border-radius:6px;flex-shrink:0;" title="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <iframe id="pdfModalFrame" src="" style="flex:1;border:none;background:#525659;" title="PDF Preview"></iframe>
    </div>
</div>
</body>
</html>
