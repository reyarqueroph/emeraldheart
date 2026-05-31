<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_name   = $_SESSION['user_name'] ?? 'Agent';
$agent_code  = $_SESSION['agent_code'] ?? '';
$initials    = strtoupper(substr($user_name, 0, 1));
$active_page = 'products';
$init_cat = urldecode($_GET['cat'] ?? 'all');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/chatbot.css">
    <link rel="stylesheet" href="../assets/css/pdf-viewer.css">
    <link rel="stylesheet" href="../assets/css/agent-dashboard.css">
    <link rel="stylesheet" href="../assets/css/theme-toggle.css">
</head>
<body class="agent-dash-body">

<?php include '../includes/agent-sidebar.php'; ?>

<div class="ad-main-wrap" id="adMainWrap">
    <main class="ad-content">

        <div class="ad-products-layout">

            <!-- Left: product list -->
            <div class="ad-products-panel">
                <div class="ad-panel-header">
                    <h3 class="ad-panel-title" id="productPanelTitle">All Products</h3>
                    
                    <!-- Google Drive Reference Links -->
                    <div id="productReferenceLinks" style="display:none;margin-bottom:12px;padding:10px;background:rgba(213,0,50,0.05);border-radius:8px;border-left:3px solid var(--pru-red);">
                        <div style="font-size:11px;font-weight:700;color:var(--pru-red);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">
                            <i class="fas fa-folder-open"></i> Additional References
                        </div>
                        <a id="productRefLink" href="#" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--pru-text);text-decoration:none;padding:4px 0;transition:color 0.2s;">
                            <i class="fab fa-google-drive" style="color:#4285F4;"></i>
                            <span id="productRefText">View Product References</span>
                            <i class="fas fa-external-link-alt" style="font-size:9px;opacity:0.5;margin-left:auto;"></i>
                        </a>
                    </div>
                    
                    <div class="ad-panel-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search products…" autocomplete="off">
                    </div>
                    <?php
                    $showPills = !in_array($init_cat, ['Stand-Alone+Product', 'Product+Guides', 'Stand-Alone Product', 'Product Guides']);
                    ?>
                    <?php if ($showPills): ?>
                    <div class="ad-cat-pills" id="filterBtns">
                        <button class="ad-cat-pill" onclick="filterCat('all',this)">All</button>
                        <button class="ad-cat-pill" onclick="filterCat('VUL',this)">VUL</button>
                        <button class="ad-cat-pill" onclick="filterCat('Traditional Life Insurance',this)">Traditional</button>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="ad-product-list" id="productsListEl">
                    <div class="ad-list-loading"><div class="pdf-spinner"></div><p>Loading products…</p></div>
                </div>
            </div>

            <!-- Right: PDF viewer -->
            <div class="ad-viewer-panel" id="adViewerPanel">
                <div class="ad-viewer-empty" id="adViewerEmpty">
                    <div class="ad-viewer-empty-icon"><i class="fas fa-file-pdf"></i></div>
                    <h4>Select a product</h4>
                    <p>Choose any product from the list to view its primer here.</p>
                </div>
                <div id="adViewerContent" style="display:none;flex-direction:column;">
                    <div class="ad-viewer-product-info" id="adViewerInfo"></div>
                    <div class="ad-viewer-pdf" id="adViewerPdf"></div>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Chatbot Widget -->
<div id="chatbotWidget">
    <button id="chatbotToggle" title="Product Advisor">
        <i class="fas fa-robot"></i>
        <span class="chat-badge" id="chatBadge" style="display:none;">1</span>
    </button>
    <div id="chatbotPanel">
        <div class="chat-header">
            <div class="chat-header-info">
                <div class="chat-avatar"><i class="fas fa-robot"></i></div>
                <div>
                    <div class="chat-name">eHeart Advisor</div>
                    <div class="chat-status"><span class="status-dot"></span> Online</div>
                </div>
            </div>
            <button onclick="toggleChat()" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:16px;padding:0;"><i class="fas fa-times"></i></button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="chat-msg bot">
                <div class="msg-bubble">Hey! 👋 I'm your eHeart AI advisor - think of me as your personal Pru Life UK expert!<br><br>I'm not just a product finder - I'm a full insurance consultant. Ask me ANYTHING about:<br><br><strong>🏢 Pru Life UK</strong><br>Company info, history, why choose us<br><br><strong>📚 Insurance Basics</strong><br>What is life insurance? How does it work? Why do you need it?<br><br><strong>🎯 Products & Coverage</strong><br>VUL, Traditional, benefits, riders, coverage details<br><br><strong>💰 Premiums & Costs</strong><br>How much? What affects pricing? Payment options<br><br><strong>📋 Claims & Process</strong><br>How to file claims, what documents needed<br><br><strong>📈 Investment & Returns</strong><br>Fund performance, returns, growth potential<br><br><strong>Or let me find products for your client!</strong><br>Just tell me their age, budget, and payment preference.<br><br>So, what would you like to know today?</div>
            </div>
        </div>
        <div class="chat-progress-container" id="chatProgressContainer" style="display:none;">
            <div class="chat-progress-label">
                <i class="fas fa-tasks"></i>
                <span id="chatProgressText">Starting...</span>
            </div>
            <div class="chat-progress-bar-wrap">
                <div class="chat-progress-bar" id="chatProgressBar"></div>
            </div>
        </div>
        <div class="chat-suggestions" id="chatSuggestions">
            <button onclick="sendSuggestion('What is life insurance?')">❓ What is life insurance?</button>
            <button onclick="sendSuggestion('Show VUL products')">📊 VUL Products</button>
            <button onclick="sendSuggestion('How much does it cost?')">💰 Pricing</button>
            <button onclick="sendSuggestion('Compare VUL and Traditional')">⚖️ Compare</button>
        </div>
        <div class="chat-input-wrap">
            <input type="text" id="chatInput" placeholder="Describe your client's needs..." autocomplete="off">
            <button id="chatSendBtn" onclick="sendChatMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>

<div class="toast-stack"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/scripts.js"></script>
<script src="../assets/js/chatbot.js"></script>
<script src="../assets/js/pdf-viewer.js"></script>
<script>
let allProducts  = [];
let currentCat   = '<?php echo htmlspecialchars($init_cat); ?>';
let activeId     = null;

const catLabels = { all:'All Products', VUL:'VUL Plans', 'Traditional Life Insurance':'Traditional Plans', 'Stand-Alone Product':'Stand-Alone Product', 'Product Guides':'Product Guides' };

// Categories that have their own dedicated page (no pills, strict filter)
const dedicatedCats = ['Stand-Alone Product', 'Product Guides'];
const mainCats      = ['VUL', 'Traditional Life Insurance'];

function loadProducts() {
    fetch('../api/products/get.php').then(r => r.json()).then(d => {
        if (d.success) { allProducts = d.data; renderProducts(); }
    });
}

function renderProducts() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allProducts.filter(p => {
        const cat = (p.category || '').trim();
        // Dedicated pages: show only that exact category
        if (dedicatedCats.includes(currentCat)) {
            return cat === currentCat &&
                   (!q || p.product_name.toLowerCase().includes(q));
        }
        // All: only show main categories
        if (currentCat === 'all') {
            return mainCats.includes(cat) &&
                   (!q || p.product_name.toLowerCase().includes(q) || cat.toLowerCase().includes(q));
        }
        // VUL / Traditional explicit filter
        return cat === currentCat &&
               (!q || p.product_name.toLowerCase().includes(q) || cat.toLowerCase().includes(q));
    });
    
    const list = document.getElementById('productsListEl');
    if (!filtered.length) {
        list.innerHTML = '<div class="ad-list-empty"><i class="fas fa-box-open"></i><p>No products found</p></div>';
        return;
    }
    
    list.innerHTML = filtered.map(p => {
        const isProductGuide = (p.category || '').trim() === 'Product Guides';
        
        return `
        <div class="ad-product-item ${activeId == p.id ? 'active' : ''}" onclick="selectProduct(${p.id})" id="prod-item-${p.id}">
            <div class="ad-product-item-cat">${esc(p.category)}</div>
            <div class="ad-product-item-name">${esc(p.product_name)}</div>
            ${!isProductGuide ? `
            <div class="ad-product-item-meta">
                <span><i class="fas fa-user-clock"></i> ${esc(p.age_range)}</span>
                <span><i class="fas fa-coins"></i> ${formatPHP(p.min_premium_monthly)}</span>
                <span><i class="fas fa-calendar-alt"></i> ${esc(p.payment_type)} Pay</span>
            </div>` : ''}
            ${p.primer_file ? '<div class="ad-product-item-pdf"><i class="fas fa-file-pdf"></i> Product Primer Available</div>' : ''}
        </div>`
    }).join('');
}

function filterCat(cat, btn) {
    currentCat = cat;
    document.querySelectorAll('.ad-cat-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('productPanelTitle').textContent = catLabels[cat] || cat;
    
    // Show/hide Google Drive reference links based on category
    const refLinksDiv = document.getElementById('productReferenceLinks');
    const refLink = document.getElementById('productRefLink');
    const refText = document.getElementById('productRefText');
    
    if (cat === 'Traditional Life Insurance') {
        refLinksDiv.style.display = 'block';
        refLink.href = 'https://drive.google.com/drive/folders/1GtxASCnmg92ogPobV_MpxSrPaikGeP1b';
        refText.textContent = 'View Traditional Products References';
    } else if (cat === 'VUL') {
        refLinksDiv.style.display = 'block';
        refLink.href = 'https://drive.google.com/drive/folders/1YB2o6N7Njdtac2o1x4gfk_-okjmaTqPL';
        refText.textContent = 'View VUL Products References';
    } else {
        refLinksDiv.style.display = 'none';
    }
    
    renderProducts();
}

function selectProduct(id) {
    const p = allProducts.find(x => x.id == id);
    if (!p) return;
    activeId = id;
    
    // Set current product context for chatbot
    window.currentProduct = p;
    
    // Update chatbot context
    if (typeof updateChatbotContext === 'function') {
        updateChatbotContext(p);
    }
    
    document.querySelectorAll('.ad-product-item').forEach(el => el.classList.remove('active'));
    const item = document.getElementById(`prod-item-${id}`);
    if (item) item.classList.add('active');

    document.getElementById('adViewerEmpty').style.display = 'none';
    const content = document.getElementById('adViewerContent');
    content.style.display = 'flex';

    document.getElementById('adViewerInfo').innerHTML = `
        <div class="ad-vinfo-name">${esc(p.product_name)}</div>
        <div class="ad-vinfo-meta">
            <span class="ad-vinfo-badge">${esc(p.category)}</span>
            ${(p.category || '').trim() !== 'Product Guides' ? `
            <span><i class="fas fa-user-clock"></i> ${esc(p.age_range)}</span>
            <span><i class="fas fa-coins"></i> ${formatPHP(p.min_premium_monthly)}</span>
            <span><i class="fas fa-calendar-alt"></i> ${esc(p.payment_type)} Pay</span>` : ''}
        </div>`;

    const pdfWrap = document.getElementById('adViewerPdf');
    if (p.primer_file) {
        const url = `../api/products/serve-pdf.php?file=${encodeURIComponent(p.primer_file)}`;
        const vid = `pdf-${id}`;
        pdfWrap.innerHTML = buildPdfViewer(url, vid, p.primer_file);
        initPdfViewer(vid, url);
    } else {
        pdfWrap.innerHTML = `<div class="ad-no-pdf"><i class="fas fa-file-slash"></i><p>No product primer attached.</p>${p.description ? `<p style="font-size:13px;color:var(--pru-text);max-width:400px;margin-top:8px;">${esc(p.description)}</p>` : ''}</div>`;
    }
    if (window.innerWidth < 900) document.getElementById('adViewerPanel').scrollIntoView({ behavior:'smooth' });
}

document.getElementById('searchInput').addEventListener('input', renderProducts);

// Expose for chatbot
window.getProducts = () => allProducts;
window.viewProductById = selectProduct;

// Init: set active pill from URL param
document.addEventListener('DOMContentLoaded', () => {
    const pills = document.querySelectorAll('.ad-cat-pill');
    pills.forEach(b => {
        const match = b.getAttribute('onclick')?.match(/'([^']+)'/);
        if (match && match[1] === currentCat) b.classList.add('active');
    });
    document.getElementById('productPanelTitle').textContent = catLabels[currentCat] || 'All Products';
    
    // Show Google Drive reference links on page load if applicable
    const refLinksDiv = document.getElementById('productReferenceLinks');
    const refLink = document.getElementById('productRefLink');
    const refText = document.getElementById('productRefText');
    
    if (currentCat === 'Traditional Life Insurance') {
        refLinksDiv.style.display = 'block';
        refLink.href = 'https://drive.google.com/drive/folders/1GtxASCnmg92ogPobV_MpxSrPaikGeP1b';
        refText.textContent = 'View Traditional Products References';
    } else if (currentCat === 'VUL') {
        refLinksDiv.style.display = 'block';
        refLink.href = 'https://drive.google.com/drive/folders/1YB2o6N7Njdtac2o1x4gfk_-okjmaTqPL';
        refText.textContent = 'View VUL Products References';
    }
    
    loadProducts();
});
</script>

<?php include '../includes/agent-footer.php'; ?>
