<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_name   = $_SESSION['user_name'] ?? 'Agent';
$agent_code  = $_SESSION['agent_code'] ?? '';
$initials    = strtoupper(substr($user_name, 0, 1));
$active_page = 'guidelines';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – Guidelines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/agent-dashboard.css">
    <link rel="stylesheet" href="../assets/css/theme-toggle.css">
    <style>
        .fatca-sub-item {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px;
            background: var(--pru-light);
            border-radius: var(--radius-sm);
            margin-bottom: 10px;
            border-left: 3px solid var(--pru-red);
            text-decoration: none;
            transition: all 0.2s;
        }
        .fatca-sub-item:hover {
            background: rgba(213,0,50,0.06);
            transform: translateX(3px);
        }
        .fatca-sub-item .fsi-icon {
            width: 36px; height: 36px;
            background: rgba(213,0,50,0.1);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            color: var(--pru-red); flex-shrink: 0;
        }
        .fatca-sub-item .fsi-text { flex: 1; }
        .fatca-sub-item .fsi-title { font-size: 13px; font-weight: 700; color: var(--pru-text); }
        .fatca-sub-item .fsi-desc  { font-size: 11px; color: var(--pru-muted); margin-top: 2px; }
        .fatca-sub-item .fsi-arrow { color: var(--pru-muted); font-size: 12px; }

        /* PDF viewer embed */
        .guideline-pdf-viewer {
            width: 100%; height: 70vh; min-height: 500px;
            border: none; border-radius: var(--radius-sm);
            background: #f0f0f0;
        }
        .pdf-loading-state {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; gap: 12px; padding: 60px;
            color: var(--pru-muted); text-align: center;
        }
        .pdf-loading-state i { font-size: 40px; opacity: 0.3; }
    </style>
</head>
<body class="agent-dash-body">

<?php include '../includes/agent-sidebar.php'; ?>

<div class="ad-main-wrap" id="adMainWrap">
    <main class="ad-content">

        <div class="page-header">
            <h2>Guidelines</h2>
            <p>Underwriting and policy guidelines for PRU Life U.K. agents.</p>
        </div>

        <div class="row g-4">
            <!-- Left Nav -->
            <div class="col-lg-3">
                <div class="guidelines-nav">
                    <div class="gnav-header"><i class="fas fa-list-ul" style="margin-right:8px;"></i>Categories</div>

                    <div class="gnav-group-label">Underwriting Guidelines</div>
                    <a href="#" class="gnav-item active" id="gnav-financial-uw"    onclick="showUWSection('financial-uw',this);return false;"><i class="fas fa-coins"></i> Financial Underwriting</a>
                    <a href="#" class="gnav-item"        id="gnav-occupational-uw" onclick="showUWSection('occupational-uw',this);return false;"><i class="fas fa-hard-hat"></i> Occupational Underwriting</a>
                    <a href="#" class="gnav-item"        id="gnav-height-weight"   onclick="showUWSection('height-weight',this);return false;"><i class="fas fa-weight"></i> Standard Height &amp; Weight</a>
                    <a href="#" class="gnav-item"        id="gnav-territorial-uw"  onclick="showUWSection('territorial-uw',this);return false;"><i class="fas fa-globe-asia"></i> Territorial Underwriting</a>

                    <div class="gnav-group-label" style="margin-top:10px;">Policy Guidelines</div>
                    <a href="#" class="gnav-item" id="gnav-fatca-guidelines"    onclick="showPolicySection('fatca-guidelines',this);return false;"><i class="fas fa-landmark"></i> FATCA Guidelines</a>
                    <a href="#" class="gnav-item" id="gnav-fatca-w9"            onclick="showPolicySection('fatca-w9',this);return false;"><i class="fas fa-file-pdf"></i> FATCA Form W9</a>
                    <a href="#" class="gnav-item" id="gnav-fatca-w8ben"         onclick="showPolicySection('fatca-w8ben',this);return false;"><i class="fas fa-file-pdf"></i> FATCA Form W8 BEN</a>
                    <a href="#" class="gnav-item" id="gnav-beneficiaries"       onclick="showPolicySection('beneficiaries',this);return false;"><i class="fas fa-users"></i> Acceptable Beneficiaries</a>
                    <a href="#" class="gnav-item" id="gnav-valid-ids"           onclick="showPolicySection('valid-ids',this);return false;"><i class="fas fa-id-card"></i> Acceptable Valid IDs</a>
                    <a href="#" class="gnav-item" id="gnav-non-medical"         onclick="showPolicySection('non-medical',this);return false;"><i class="fas fa-file-medical-alt"></i> Non-Medical Authority</a>
                    <a href="#" class="gnav-item" id="gnav-replacement"         onclick="showPolicySection('replacement',this);return false;"><i class="fas fa-exchange-alt"></i> Policy Replacement</a>
                </div>
            </div>

            <!-- Content -->
            <div class="col-lg-9">

                <!-- ══ UNDERWRITING PDF SECTIONS ══ -->
                <?php
                $uwSections = [
                    'financial-uw'    => ['fa-coins',      'Financial Underwriting'],
                    'occupational-uw' => ['fa-hard-hat',   'Occupational Underwriting'],
                    'height-weight'   => ['fa-weight',     'Standard Height & Weight'],
                    'territorial-uw'  => ['fa-globe-asia', 'Territorial Underwriting'],
                ];
                $first = true;
                foreach ($uwSections as $key => [$icon, $title]):
                ?>
                <div id="section-<?php echo $key; ?>" class="guideline-section" <?php echo $first ? '' : 'style="display:none;"'; ?>>
                    <?php if ($key === 'height-weight'): ?>
                    <!-- Height & Weight with Calculator -->
                    <div class="row g-3 mb-3">
                        <div class="col-lg-8">
                            <div class="pru-card">
                                <div class="card-header">
                                    <h5><i class="fas <?php echo $icon; ?>" style="color:var(--pru-red);margin-right:8px;"></i><?php echo $title; ?></h5>
                                </div>
                                <div class="card-body" id="uwcontent-<?php echo $key; ?>">
                                    <div class="pdf-loading-state">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Loading...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <!-- Calculator Widget -->
                            <div class="pru-card" style="position:sticky;top:20px;">
                                <div class="card-header" style="background:linear-gradient(135deg,#007DFF 0%,#0062CC 100%);color:white;">
                                    <h5 style="margin:0;font-size:14px;"><i class="fas fa-calculator" style="margin-right:8px;"></i>Health Calculator</h5>
                                </div>
                                <div class="card-body" style="padding:20px;">
                                    
                                    <!-- Weight Converter -->
                                    <div style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--pru-border);">
                                        <div style="font-size:12px;font-weight:700;color:var(--pru-text);margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                                            <i class="fas fa-weight" style="color:#007DFF;"></i> Weight Converter
                                        </div>
                                        <div style="margin-bottom:10px;">
                                            <label style="font-size:10px;font-weight:600;color:var(--pru-muted);text-transform:uppercase;display:block;margin-bottom:4px;">Kilograms (kg)</label>
                                            <input type="number" id="weightKg" placeholder="Enter kg" step="0.1" 
                                                style="width:100%;padding:8px 10px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;"
                                                oninput="convertWeight('kg')">
                                        </div>
                                        <div>
                                            <label style="font-size:10px;font-weight:600;color:var(--pru-muted);text-transform:uppercase;display:block;margin-bottom:4px;">Pounds (lbs)</label>
                                            <input type="number" id="weightLbs" placeholder="Enter lbs" step="0.1"
                                                style="width:100%;padding:8px 10px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;"
                                                oninput="convertWeight('lbs')">
                                        </div>
                                    </div>

                                    <!-- Height Converter -->
                                    <div style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--pru-border);">
                                        <div style="font-size:12px;font-weight:700;color:var(--pru-text);margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                                            <i class="fas fa-ruler-vertical" style="color:#007DFF;"></i> Height Converter
                                        </div>
                                        <div style="margin-bottom:10px;">
                                            <label style="font-size:10px;font-weight:600;color:var(--pru-muted);text-transform:uppercase;display:block;margin-bottom:4px;">Meters (m)</label>
                                            <input type="number" id="heightM" placeholder="Enter meters" step="0.01"
                                                style="width:100%;padding:8px 10px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;"
                                                oninput="convertHeight('m')">
                                        </div>
                                        <div style="margin-bottom:10px;">
                                            <label style="font-size:10px;font-weight:600;color:var(--pru-muted);text-transform:uppercase;display:block;margin-bottom:4px;">Centimeters (cm)</label>
                                            <input type="number" id="heightCm" placeholder="Enter cm" step="0.1"
                                                style="width:100%;padding:8px 10px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;"
                                                oninput="convertHeight('cm')">
                                        </div>
                                        <div>
                                            <label style="font-size:10px;font-weight:600;color:var(--pru-muted);text-transform:uppercase;display:block;margin-bottom:4px;">Inches (in)</label>
                                            <input type="number" id="heightIn" placeholder="Enter inches" step="0.1"
                                                style="width:100%;padding:8px 10px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;"
                                                oninput="convertHeight('in')">
                                        </div>
                                    </div>

                                    <!-- BMI Calculator -->
                                    <div>
                                        <div style="font-size:12px;font-weight:700;color:var(--pru-text);margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                                            <i class="fas fa-heartbeat" style="color:#007DFF;"></i> BMI Calculator
                                        </div>
                                        <button onclick="calculateBMI()" 
                                            style="width:100%;padding:10px;background:#007DFF;color:white;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;margin-bottom:12px;transition:background 0.2s;"
                                            onmouseover="this.style.background='#0062CC'" onmouseout="this.style.background='#007DFF'">
                                            <i class="fas fa-calculator"></i> Calculate BMI
                                        </button>
                                        <div id="bmiResult" style="display:none;padding:12px;border-radius:8px;text-align:center;">
                                            <div style="font-size:11px;color:rgba(255,255,255,0.8);margin-bottom:4px;">Your BMI</div>
                                            <div id="bmiValue" style="font-size:28px;font-weight:900;color:white;margin-bottom:6px;">--</div>
                                            <div id="bmiCategory" style="font-size:12px;font-weight:700;color:white;"></div>
                                        </div>
                                        <div style="font-size:10px;color:var(--pru-muted);margin-top:8px;line-height:1.4;">
                                            <strong>BMI Categories:</strong><br>
                                            &lt; 18.5: Underweight<br>
                                            18.5 - 24.9: Normal<br>
                                            25 - 29.9: Overweight<br>
                                            ≥ 30: Obese
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Regular Section -->
                    <div class="pru-card">
                        <div class="card-header">
                            <h5><i class="fas <?php echo $icon; ?>" style="color:var(--pru-red);margin-right:8px;"></i><?php echo $title; ?></h5>
                        </div>
                        <div class="card-body" id="uwcontent-<?php echo $key; ?>">
                            <div class="pdf-loading-state">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading...</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php $first = false; endforeach; ?>

                <!-- ══ POLICY PDF SECTIONS ══ -->
                <?php
                $policySections = [
                    'fatca-guidelines' => ['fa-landmark',        'FATCA Guidelines'],
                    'fatca-w9'         => ['fa-file-pdf',         'FATCA Form W9 – 27 May 2017'],
                    'fatca-w8ben'      => ['fa-file-pdf',         'FATCA Form W8 BEN (Individual) – 7 May 2017'],
                    'beneficiaries'    => ['fa-users',            'Acceptable Beneficiaries'],
                    'valid-ids'        => ['fa-id-card',          'Acceptable Valid IDs'],
                    'non-medical'      => ['fa-file-medical-alt', 'Non-Medical Authority'],
                    'replacement'      => ['fa-exchange-alt',     'Policy Replacement'],
                ];
                foreach ($policySections as $key => [$icon, $title]):
                ?>
                <div id="section-<?php echo $key; ?>" class="guideline-section" style="display:none;">
                    <div class="pru-card">
                        <div class="card-header">
                            <h5><i class="fas <?php echo $icon; ?>" style="color:var(--pru-red);margin-right:8px;"></i><?php echo $title; ?></h5>
                        </div>
                        <div class="card-body" id="polcontent-<?php echo $key; ?>">
                            <div class="pdf-loading-state">
                                <i class="fas fa-spinner fa-spin"></i>
                                <p>Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div><!-- end col-lg-9 -->
        </div><!-- end row -->
    </main>
</div>

<div class="toast-stack"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/scripts.js"></script>
<script>
// Map section key → guideline title (matches DB)
const uwTitleMap = {
    'financial-uw':    'Financial Underwriting',
    'occupational-uw': 'Occupational Underwriting',
    'height-weight':   'Standard Height & Weight',
    'territorial-uw':  'Territorial Underwriting',
};

const polTitleMap = {
    'fatca-guidelines': 'FATCA Guidelines',
    'fatca-w9':         'FATCA Form W9 – 27 May 2017',
    'fatca-w8ben':      'FATCA Form W8 BEN (Individual) – 7 May 2017',
    'beneficiaries':    'Acceptable Beneficiaries',
    'valid-ids':        'Acceptable Valid IDs',
    'non-medical':      'Non-Medical Authority',
    'replacement':      'Policy Replacement',
};

let uwData  = null;
let polData = null;

function showSection(id, link) {
    document.querySelectorAll('.guideline-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.gnav-item').forEach(l => l.classList.remove('active'));
    document.getElementById('section-' + id).style.display = '';
    if (link) link.classList.add('active');
}

function showUWSection(key, link) {
    showSection(key, link);
    loadUWContent(key);
}

function showPolicySection(key, link) {
    showSection(key, link);
    loadPolicyContent(key);
}

function renderPdfSection(container, item) {
    if (!item) {
        container.innerHTML = '<div class="pdf-loading-state"><i class="fas fa-file-pdf"></i><p>No content available yet.</p><p style="font-size:11px;color:var(--pru-muted);">Contact your administrator.</p></div>';
        return;
    }
    if (item.pdf_file) {
        const pdfUrl = '../api/guidelines/serve-pdf.php?file=' + encodeURIComponent(item.pdf_file);
        container.innerHTML = `
            <div style="margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <p style="font-size:13px;color:var(--pru-muted);margin:0;">${item.description || ''}</p>
                <a href="${pdfUrl}" target="_blank" class="btn-pru btn-pru-sm"><i class="fas fa-external-link-alt"></i> Open in new tab</a>
            </div>
            <iframe src="${pdfUrl}" class="guideline-pdf-viewer" title="${item.title}"></iframe>`;
    } else {
        container.innerHTML = `
            <div class="pdf-loading-state">
                <i class="fas fa-file-pdf"></i>
                <p style="font-weight:700;color:var(--pru-text);">${item.title}</p>
                <p>${item.description || 'No PDF has been uploaded for this guideline yet.'}</p>
                <p style="font-size:11px;color:var(--pru-muted);">Please check back later or contact your administrator.</p>
            </div>`;
    }
}

async function loadUWContent(key) {
    const container = document.getElementById('uwcontent-' + key);
    if (!container) return;
    if (!uwData) {
        try {
            const res = await fetch('../api/guidelines/get.php?section=underwriting');
            const d   = await res.json();
            if (d.success) uwData = d.data;
        } catch(e) {}
    }
    if (!uwData) { container.innerHTML = '<div class="pdf-loading-state"><i class="fas fa-exclamation-circle"></i><p>Failed to load</p></div>'; return; }
    const item = uwData.find(g => g.title === uwTitleMap[key]);
    renderPdfSection(container, item);
}

async function loadPolicyContent(key) {
    const container = document.getElementById('polcontent-' + key);
    if (!container) return;
    if (!polData) {
        try {
            const res = await fetch('../api/guidelines/get.php?section=policy');
            const d   = await res.json();
            if (d.success) polData = d.data;
        } catch(e) {}
    }
    if (!polData) { container.innerHTML = '<div class="pdf-loading-state"><i class="fas fa-exclamation-circle"></i><p>Failed to load</p></div>'; return; }
    const item = polData.find(g => g.title === polTitleMap[key]);
    renderPdfSection(container, item);
}

// Load first UW section on page load
document.addEventListener('DOMContentLoaded', () => {
    const urlSection = new URLSearchParams(window.location.search).get('section');
    const urlGroup   = new URLSearchParams(window.location.search).get('group');
    const uwKeys  = ['financial-uw','occupational-uw','height-weight','territorial-uw'];
    const polKeys = ['fatca-guidelines','fatca-w9','fatca-w8ben','beneficiaries','valid-ids','non-medical','replacement'];

    if (urlSection && uwKeys.includes(urlSection)) {
        const link = document.getElementById('gnav-' + urlSection);
        showUWSection(urlSection, link);
    } else if (urlSection && polKeys.includes(urlSection)) {
        const link = document.getElementById('gnav-' + urlSection);
        showPolicySection(urlSection, link);
    } else if (urlGroup === 'policy') {
        // Open first policy section
        const link = document.getElementById('gnav-fatca-guidelines');
        showPolicySection('fatca-guidelines', link);
    } else {
        // Default: first UW section
        loadUWContent('financial-uw');
    }
});

// ── Health Calculator Functions ──

// Weight Converter
function convertWeight(from) {
    const kgInput = document.getElementById('weightKg');
    const lbsInput = document.getElementById('weightLbs');
    
    if (from === 'kg') {
        const kg = parseFloat(kgInput.value);
        if (!isNaN(kg) && kg > 0) {
            lbsInput.value = (kg * 2.20462).toFixed(2);
        } else {
            lbsInput.value = '';
        }
    } else if (from === 'lbs') {
        const lbs = parseFloat(lbsInput.value);
        if (!isNaN(lbs) && lbs > 0) {
            kgInput.value = (lbs / 2.20462).toFixed(2);
        } else {
            kgInput.value = '';
        }
    }
}

// Height Converter
function convertHeight(from) {
    const mInput = document.getElementById('heightM');
    const cmInput = document.getElementById('heightCm');
    const inInput = document.getElementById('heightIn');
    
    if (from === 'm') {
        const m = parseFloat(mInput.value);
        if (!isNaN(m) && m > 0) {
            cmInput.value = (m * 100).toFixed(1);
            inInput.value = (m * 39.3701).toFixed(1);
        } else {
            cmInput.value = '';
            inInput.value = '';
        }
    } else if (from === 'cm') {
        const cm = parseFloat(cmInput.value);
        if (!isNaN(cm) && cm > 0) {
            mInput.value = (cm / 100).toFixed(2);
            inInput.value = (cm * 0.393701).toFixed(1);
        } else {
            mInput.value = '';
            inInput.value = '';
        }
    } else if (from === 'in') {
        const inches = parseFloat(inInput.value);
        if (!isNaN(inches) && inches > 0) {
            mInput.value = (inches / 39.3701).toFixed(2);
            cmInput.value = (inches * 2.54).toFixed(1);
        } else {
            mInput.value = '';
            cmInput.value = '';
        }
    }
}

// BMI Calculator
function calculateBMI() {
    const weightKg = parseFloat(document.getElementById('weightKg').value);
    const heightM = parseFloat(document.getElementById('heightM').value);
    
    if (!weightKg || weightKg <= 0) {
        showToast('Please enter weight in kilograms', 'warning');
        return;
    }
    
    if (!heightM || heightM <= 0) {
        showToast('Please enter height in meters', 'warning');
        return;
    }
    
    // Calculate BMI = weight (kg) / height (m)²
    const bmi = weightKg / (heightM * heightM);
    
    // Determine category and color
    let category, bgColor;
    if (bmi < 18.5) {
        category = 'Underweight';
        bgColor = '#17a2b8'; // info blue
    } else if (bmi >= 18.5 && bmi < 25) {
        category = 'Normal Weight';
        bgColor = '#28a745'; // success green
    } else if (bmi >= 25 && bmi < 30) {
        category = 'Overweight';
        bgColor = '#ffc107'; // warning yellow
    } else {
        category = 'Obese';
        bgColor = '#dc3545'; // danger red
    }
    
    // Display result
    const resultDiv = document.getElementById('bmiResult');
    const valueDiv = document.getElementById('bmiValue');
    const categoryDiv = document.getElementById('bmiCategory');
    
    resultDiv.style.display = 'block';
    resultDiv.style.background = bgColor;
    valueDiv.textContent = bmi.toFixed(1);
    categoryDiv.textContent = category;
    
    // Scroll to result
    resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

</script>

<?php include '../includes/agent-footer.php'; ?>
