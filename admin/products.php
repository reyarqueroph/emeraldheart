<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Manage Products';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<main class="pru-main">
    <div class="page-header">
        <h2>Manage Products</h2>
        <p>Add, edit, and attach PDF brochures to insurance products.</p>
    </div>

    <!-- Section Tabs -->
    <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--pru-border);">
        <button class="prod-tab active" id="ptab-main"       onclick="switchProdTab('main')">
            <i class="fas fa-box-open"></i> VUL &amp; Traditional
        </button>
        <button class="prod-tab"        id="ptab-standalone" onclick="switchProdTab('standalone')">
            <i class="fas fa-user-shield"></i> Stand-Alone Products
        </button>
        <button class="prod-tab"        id="ptab-guides"     onclick="switchProdTab('guides')">
            <i class="fas fa-book-open"></i> Product Guides
        </button>
    </div>

    <!-- VUL & Traditional Panel -->
    <div id="ppanel-main" class="ppanel">
        <div class="table-wrapper">
            <div class="table-toolbar">
                <h5 style="margin-right:auto;">VUL &amp; Traditional Products</h5>
                <div class="table-search"><i class="fas fa-search"></i>
                    <input type="text" id="searchMain" placeholder="Search..." oninput="loadSection('main')">
                </div>
                <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="fixCategories()">
                    <i class="fas fa-wrench"></i> Fix Categories
                </button>
                <button class="btn-pru btn-pru-sm" onclick="openAddModal('main')">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>
            <div class="table-scroll">
                <table class="pru-table">
                    <thead><tr><th>Product Name</th><th>Category</th><th>Age Range</th><th>Min.Premium</th><th>Payment</th><th>PDF</th><th>Actions</th></tr></thead>
                    <tbody id="bodyMain"><tr><td colspan="7"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stand-Alone Panel -->
    <div id="ppanel-standalone" class="ppanel" style="display:none;">
        <div class="table-wrapper">
            <div class="table-toolbar">
                <h5 style="margin-right:auto;">Stand-Alone Products</h5>
                <div class="table-search"><i class="fas fa-search"></i>
                    <input type="text" id="searchStandalone" placeholder="Search..." oninput="loadSection('standalone')">
                </div>
                <button class="btn-pru btn-pru-sm" onclick="openAddModal('standalone')">
                    <i class="fas fa-plus"></i> Add Stand-Alone Product
                </button>
            </div>
            <div class="table-scroll">
                <table class="pru-table">
                    <thead><tr><th>Product Name</th><th>Sub Category</th><th>Age Range</th><th>Min.Premium</th><th>Payment</th><th>PDF</th><th>Actions</th></tr></thead>
                    <tbody id="bodyStandalone"><tr><td colspan="7"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Product Guides Panel -->
    <div id="ppanel-guides" class="ppanel" style="display:none;">
        <div class="table-wrapper">
            <div class="table-toolbar">
                <h5 style="margin-right:auto;">Product Guides</h5>
                <div class="table-search"><i class="fas fa-search"></i>
                    <input type="text" id="searchGuides" placeholder="Search..." oninput="loadSection('guides')">
                </div>
                <button class="btn-pru btn-pru-sm" onclick="openAddModal('guides')">
                    <i class="fas fa-plus"></i> Add Product Guide
                </button>
            </div>
            <div class="table-scroll">
                <table class="pru-table">
                    <thead><tr><th>Guide Name</th><th>Sub Category</th><th>Description</th><th>PDF</th><th>Actions</th></tr></thead>
                    <tbody id="bodyGuides"><tr><td colspan="5"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addProductModal">
    <div class="modal-box modal-lg">
        <div class="modal-head">
            <h5 id="addModalTitle"><i class="fas fa-plus-circle" style="color:var(--pru-red);margin-right:8px;"></i>Add New Product</h5>
            <button class="modal-close" onclick="closeModal('addProductModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <!-- Hidden fields to carry tab context -->
            <input type="hidden" id="addSection"  value="main">
            <input type="hidden" id="addCatValue" value="VUL">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="addName" placeholder="Enter product name" required>
                    </div>
                </div>
                <div class="col-md-6" id="addCategoryRow">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" id="addCategoryDisplay" readonly
                               style="background:var(--pru-light);font-weight:600;color:var(--pru-red);">
                    </div>
                </div>
                <div class="col-md-6" id="addSubRow">
                    <div class="form-group">
                        <label class="form-label">Sub Category</label>
                        <input type="text" class="form-control" id="addSub" placeholder="e.g. Endowment, Term">
                    </div>
                </div>
                <div class="col-md-6" id="addPaymentRow">
                    <div class="form-group">
                        <label class="form-label">Payment Type</label>
                        <select class="form-select" id="addPayment">
                            <option value="Regular">Regular</option>
                            <option value="Limited">Limited</option>
                            <option value="Single">Single</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6" id="addAgeRow">
                    <div class="form-group">
                        <label class="form-label">Age Range</label>
                        <input type="text" class="form-control" id="addAge" value="7 days to 70 years old">
                    </div>
                </div>
                <div class="col-md-6" id="addPremiumRow">
                    <div class="form-group">
                        <label class="form-label">Min.Premium (₱)</label>
                        <input type="number" class="form-control" id="addPremium" step="0.01" min="0" value="0">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="addDesc" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('addProductModal')">Cancel</button>
            <button class="btn-pru" id="addSaveBtn" onclick="submitAddProduct()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal-overlay" id="editProductModal">
    <div class="modal-box modal-lg">
        <div class="modal-head">
            <h5><i class="fas fa-edit" style="color:var(--pru-red);margin-right:8px;"></i>Edit Product</h5>
            <button class="modal-close" onclick="closeModal('editProductModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner">
            <form id="editProductForm" novalidate>
                <input type="hidden" id="editId">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="editName">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="editCategory">
                                <option value="VUL">Variable Unit-Linked (VUL)</option>
                                <option value="Traditional Life Insurance">Traditional Life Insurance</option>
                                <option value="Stand-Alone Product">Stand-Alone Product</option>
                                <option value="Product Guides">Product Guides</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6" id="editSubRow">
                        <div class="form-group">
                            <label class="form-label">Sub Category</label>
                            <input type="text" class="form-control" id="editSub">
                        </div>
                    </div>
                    <div class="col-md-6" id="editPaymentRow">
                        <div class="form-group">
                            <label class="form-label">Payment Type</label>
                            <select class="form-select" id="editPayment">
                                <option value="Regular">Regular</option>
                                <option value="Limited">Limited</option>
                                <option value="Single">Single</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6" id="editAgeRow">
                        <div class="form-group">
                            <label class="form-label">Age Range</label>
                            <input type="text" class="form-control" id="editAge">
                        </div>
                    </div>
                    <div class="col-md-6" id="editPremiumRow">
                        <div class="form-group">
                            <label class="form-label">Min.Premium (₱)</label>
                            <input type="number" class="form-control" id="editPremium" step="0.01">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editDesc" rows="3"></textarea>
                        </div>
                    </div>
                    <!-- PDF Upload Section -->
                    <div class="col-12">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Product PDF / Brochure</label>
                            <div id="pdfCurrentStatus" style="margin-bottom:8px;"></div>
                            <div class="pdf-upload-zone" id="pdfDropZone">
                                <input type="file" id="pdfFileInput" accept=".pdf" style="display:none;">
                                <div class="pdf-upload-inner" onclick="document.getElementById('pdfFileInput').click()">
                                    <i class="fas fa-file-pdf"></i>
                                    <div class="puz-text">Click to upload or drag & drop PDF</div>
                                    <div class="puz-hint">PDF only · Max 10MB</div>
                                </div>
                                <div id="pdfFileName" class="pdf-file-selected" style="display:none;"></div>
                            </div>
                            <div id="pdfUploadProgress" style="display:none;margin-top:8px;">
                                <div style="height:4px;background:#eee;border-radius:2px;overflow:hidden;">
                                    <div id="pdfProgressBar" style="height:100%;background:var(--pru-red);width:0;transition:width 0.3s;border-radius:2px;"></div>
                                </div>
                                <div style="font-size:11px;color:var(--pru-muted);margin-top:4px;" id="pdfProgressText">Uploading...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('editProductModal')">Cancel</button>
            <button class="btn-pru" onclick="submitEditProduct()"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </div>
</div>

<style>
.prod-tab { background:none;border:none;border-bottom:3px solid transparent;padding:10px 20px;font-size:13px;font-weight:600;color:var(--pru-muted);cursor:pointer;display:flex;align-items:center;gap:7px;margin-bottom:-2px;transition:all 0.2s; }
.prod-tab:hover { color:var(--pru-text); }
.prod-tab.active { color:var(--pru-red);border-bottom-color:var(--pru-red); }
.pdf-upload-zone {
    border: 2px dashed var(--pru-border);
    border-radius: var(--radius-md);
    transition: all 0.2s;
    overflow: hidden;
}
.pdf-upload-zone:hover, .pdf-upload-zone.drag-over {
    border-color: var(--pru-red);
    background: rgba(213,0,50,0.02);
}
.pdf-upload-inner {
    padding: 24px;
    text-align: center;
    cursor: pointer;
}
.pdf-upload-inner i {
    font-size: 32px;
    color: var(--pru-red);
    margin-bottom: 8px;
    display: block;
}
.puz-text { font-size: 13px; font-weight: 600; color: var(--pru-text); }
.puz-hint { font-size: 11px; color: var(--pru-muted); margin-top: 4px; }
.pdf-file-selected {
    padding: 12px 16px;
    background: rgba(213,0,50,0.04);
    border-top: 1px solid rgba(213,0,50,0.1);
    display: flex; align-items: center; gap: 10px;
    font-size: 13px; color: var(--pru-text);
}
.pdf-file-selected i { color: var(--pru-red); }
</style>

<script>
let allProducts = [];
let selectedPdfFile = null;
let activeTab = 'main';

const tabConfig = {
    main:       { cats:['VUL','Traditional Life Insurance'], body:'bodyMain',       search:'searchMain',       cols:7 },
    standalone: { cats:['Stand-Alone Product'],              body:'bodyStandalone', search:'searchStandalone', cols:7 },
    guides:     { cats:['Product Guides'],                   body:'bodyGuides',     search:'searchGuides',     cols:5 },
};

function switchProdTab(tab) {
    activeTab = tab;
    document.querySelectorAll('.prod-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.ppanel').forEach(p => p.style.display = 'none');
    document.getElementById('ptab-' + tab).classList.add('active');
    document.getElementById('ppanel-' + tab).style.display = '';
    loadSection(tab);
}

function loadSection(tab) {
    const startTime = performance.now(); // Performance monitoring
    const cfg  = tabConfig[tab];
    const q    = document.getElementById(cfg.search)?.value || '';
    const body = document.getElementById(cfg.body);
    body.innerHTML = `<tr><td colspan="${cfg.cols}"><div class="empty-state"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div></td></tr>`;

    // Add timeout to prevent infinite loading
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

    // Fetch products with timeout
    fetch(`../api/products/get.php?search=${encodeURIComponent(q)}&limit=100`, {
        signal: controller.signal
    })
        .then(r => {
            clearTimeout(timeoutId);
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(d => {
            const loadTime = performance.now() - startTime;
            console.log(`Products loaded in ${loadTime.toFixed(2)}ms`);
            
            if (!d.success) {
                body.innerHTML = `<tr><td colspan="${cfg.cols}"><div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>${d.message||'Failed to load'}</p></div></td></tr>`;
                return;
            }
            allProducts = d.data;
            
            // Filter by this tab's categories (case-insensitive trim) - optimized
            const products = d.data.filter(p => {
                const productCat = (p.category || '').trim().toLowerCase();
                return cfg.cats.some(c => c.toLowerCase() === productCat);
            });
            
            renderSection(tab, products);
        })
        .catch(err => {
            clearTimeout(timeoutId);
            console.error('Load section error:', err);
            let errorMsg = 'Connection error';
            if (err.name === 'AbortError') {
                errorMsg = 'Request timed out - please try again';
            }
            body.innerHTML = `<tr><td colspan="${cfg.cols}"><div class="empty-state"><i class="fas fa-exclamation-circle"></i><p>${errorMsg}</p></div></td></tr>`;
        });
}

function renderSection(tab, products) {
    const cfg   = tabConfig[tab];
    const tbody = document.getElementById(cfg.body);
    if (!products.length) {
        tbody.innerHTML = `<tr><td colspan="${cfg.cols}"><div class="empty-state"><i class="fas fa-box-open"></i><p>No products found</p></div></td></tr>`;
        return;
    }
    if (tab === 'guides') {
        tbody.innerHTML = products.map(p => `
            <tr>
                <td><strong>${esc(p.product_name)}</strong></td>
                <td style="font-size:12px;color:var(--pru-muted);">${esc(p.sub_category||'—')}</td>
                <td style="font-size:12px;color:var(--pru-muted);max-width:260px;">${esc((p.description||'').substring(0,80))}${(p.description||'').length>80?'…':''}</td>
                <td>${p.primer_file?`<a href="../api/products/serve-pdf.php?file=${encodeURIComponent(p.primer_file)}" target="_blank" class="btn-pru btn-pru-sm" style="background:var(--pru-success);"><i class="fas fa-file-pdf"></i> View</a>`:`<span style="color:#ccc;font-size:12px;">None</span>`}</td>
                <td>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="openEdit(${p.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-danger);margin-left:4px;" onclick="deleteProduct(${p.id},'${esc(p.product_name)}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`).join('');
    } else {
        tbody.innerHTML = products.map(p => `
            <tr>
                <td><strong>${esc(p.product_name)}</strong></td>
                <td><span class="badge-status" style="background:rgba(213,0,50,0.08);color:var(--pru-red);">${esc(p.category||'—')}</span></td>
                <td>${esc(p.age_range)}</td>
                <td>${formatPHP(p.min_premium_monthly)}</td>
                <td>${esc(p.payment_type)}</td>
                <td>${p.primer_file?`<a href="../api/products/serve-pdf.php?file=${encodeURIComponent(p.primer_file)}" target="_blank" class="btn-pru btn-pru-sm" style="background:var(--pru-success);"><i class="fas fa-file-pdf"></i> View</a>`:`<span style="color:#ccc;font-size:12px;">None</span>`}</td>
                <td>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-info);" onclick="openEdit(${p.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-pru btn-pru-sm" style="background:var(--pru-danger);margin-left:4px;" onclick="deleteProduct(${p.id},'${esc(p.product_name)}')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`).join('');
    }
}

function openAddModal(tab) {
    const catMap = {
        main:       'VUL',               // default; user can pick VUL or Traditional via a select shown below
        standalone: 'Stand-Alone Product',
        guides:     'Product Guides',
    };

    // For main tab, show a select; for others, show read-only display
    const isMain  = tab === 'main';
    const isGuide = tab === 'guides';
    const cat     = catMap[tab];

    document.getElementById('addSection').value  = tab;
    document.getElementById('addCatValue').value = cat;

    // Category display
    if (isMain) {
        document.getElementById('addCategoryRow').innerHTML = `
            <div class="form-group">
                <label class="form-label">Category *</label>
                <select class="form-select" id="addCategorySelect" onchange="handleAddCategoryChange(this.value)">
                    <option value="VUL">Variable Unit-Linked (VUL)</option>
                    <option value="Traditional Life Insurance">Traditional Life Insurance</option>
                </select>
            </div>`;
        document.getElementById('addCatValue').value = 'VUL';
    } else {
        document.getElementById('addCategoryRow').innerHTML = `
            <div class="form-group">
                <label class="form-label">Category</label>
                <input type="text" class="form-control" value="${cat}" readonly
                       style="background:var(--pru-light);font-weight:600;color:var(--pru-red);">
            </div>`;
    }

    const titles = { main:'Add VUL / Traditional Product', standalone:'Add Stand-Alone Product', guides:'Add Product Guide' };
    document.getElementById('addModalTitle').innerHTML =
        `<i class="fas fa-plus-circle" style="color:var(--pru-red);margin-right:8px;"></i>${titles[tab]}`;

    // Show/hide fields
    document.getElementById('addSubRow').style.display = isGuide ? 'none' : '';
    document.getElementById('addPaymentRow').style.display = isGuide ? 'none' : '';
    document.getElementById('addAgeRow').style.display     = isGuide ? 'none' : '';
    document.getElementById('addPremiumRow').style.display = isGuide ? 'none' : '';

    // Clear fields
    document.getElementById('addName').value    = '';
    document.getElementById('addSub').value     = '';
    document.getElementById('addDesc').value    = '';
    document.getElementById('addAge').value     = '7 days to 70 years old';
    document.getElementById('addPremium').value = '0';
    document.getElementById('addPayment').value = 'Regular';

    openModal('addProductModal');
}

function handleAddCategoryChange(category) {
    // Update the hidden field
    document.getElementById('addCatValue').value = category;
    
    // Show/hide fields based on category
    const isGuide = category === 'Product Guides';
    document.getElementById('addSubRow').style.display = isGuide ? 'none' : '';
    document.getElementById('addPaymentRow').style.display = isGuide ? 'none' : '';
    document.getElementById('addAgeRow').style.display = isGuide ? 'none' : '';
    document.getElementById('addPremiumRow').style.display = isGuide ? 'none' : '';
}

async function submitAddProduct() {
    const section  = document.getElementById('addSection').value;
    const category = document.getElementById('addCatValue').value;
    const name     = document.getElementById('addName').value.trim();

    if (!name)     { showToast('Product name is required', 'warning'); return; }
    if (!category) { showToast('Category is missing', 'warning'); return; }

    // For Product Guides, use default values for hidden fields
    const isProductGuide = category === 'Product Guides';

    const data = {
        product_name:        name,
        category:            category,
        sub_category:        isProductGuide ? '' : document.getElementById('addSub').value.trim(),
        payment_type:        isProductGuide ? 'Regular' : (document.getElementById('addPayment').value || 'Regular'),
        age_range:           isProductGuide ? '' : document.getElementById('addAge').value.trim(),
        min_premium_monthly: isProductGuide ? 0 : (parseFloat(document.getElementById('addPremium').value) || 0),
        description:         document.getElementById('addDesc').value.trim(),
    };

    const btn = document.getElementById('addSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    try {
        const res    = await fetch('../api/products/create.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(data)
        });
        const result = await res.json();
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) {
            closeModal('addProductModal');
            loadSection(section);
        }
    } catch(e) {
        showToast('Failed to save. Please try again.', 'error');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save"></i> Save';
}

function openEdit(id) {
    const p = allProducts.find(x => x.id == id);
    if (!p) return;

    // Map legacy values to valid ones
    const legacyMap = {
        'personal accident'  : 'Stand-Alone Product',
        'stand-alone'        : 'Stand-Alone Product',
        'stand alone'        : 'Stand-Alone Product',
        'product guide'      : 'Product Guides',
        'traditional'        : 'Traditional Life Insurance',
    };
    const validCats = ['VUL', 'Traditional Life Insurance', 'Stand-Alone Product', 'Product Guides'];
    const raw = (p.category || '').trim();
    const cat = validCats.includes(raw) ? raw : (legacyMap[raw.toLowerCase()] || 'VUL');

    document.getElementById('editId').value      = p.id;
    document.getElementById('editName').value    = p.product_name;
    document.getElementById('editSub').value     = p.sub_category || '';
    document.getElementById('editPayment').value = p.payment_type || 'Regular';
    document.getElementById('editAge').value     = p.age_range || '';
    document.getElementById('editPremium').value = p.min_premium_monthly || 0;
    document.getElementById('editDesc').value    = p.description || '';

    // Force-set the category select by iterating options
    const sel = document.getElementById('editCategory');
    let matched = false;
    for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value === cat) {
            sel.selectedIndex = i;
            matched = true;
            break;
        }
    }
    if (!matched) sel.selectedIndex = 0; // fallback to VUL

    // Show/hide fields based on category
    const isProductGuide = cat === 'Product Guides';
    
    // Hide these fields for Product Guides, show for others
    document.getElementById('editSubRow').style.display = isProductGuide ? 'none' : '';
    document.getElementById('editPaymentRow').style.display = isProductGuide ? 'none' : '';
    document.getElementById('editAgeRow').style.display = isProductGuide ? 'none' : '';
    document.getElementById('editPremiumRow').style.display = isProductGuide ? 'none' : '';

    // Add event listener to category dropdown to toggle fields when changed
    sel.onchange = function() {
        const selectedCategory = this.options[this.selectedIndex].value;
        const isGuide = selectedCategory === 'Product Guides';
        
        document.getElementById('editSubRow').style.display = isGuide ? 'none' : '';
        document.getElementById('editPaymentRow').style.display = isGuide ? 'none' : '';
        document.getElementById('editAgeRow').style.display = isGuide ? 'none' : '';
        document.getElementById('editPremiumRow').style.display = isGuide ? 'none' : '';
    };

    selectedPdfFile = null;
    document.getElementById('pdfFileName').style.display      = 'none';
    document.getElementById('pdfUploadProgress').style.display = 'none';
    document.getElementById('pdfProgressBar').style.width     = '0';
    document.getElementById('pdfCurrentStatus').innerHTML = p.primer_file
        ? `<div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(40,167,69,0.06);border:1px solid rgba(40,167,69,0.15);border-radius:8px;font-size:12px;">
               <i class="fas fa-check-circle" style="color:var(--pru-success);"></i>
               <span>PDF: <strong>${esc(p.primer_file)}</strong></span>
               <a href="../api/products/serve-pdf.php?file=${encodeURIComponent(p.primer_file)}" target="_blank" style="margin-left:auto;color:var(--pru-red);font-size:11px;font-weight:700;">View</a>
           </div>`
        : `<div style="font-size:12px;color:var(--pru-muted);padding:4px 0;">No PDF attached yet.</div>`;
    openModal('editProductModal');
}

async function submitEditProduct() {
    const id  = document.getElementById('editId').value;
    const sel = document.getElementById('editCategory');
    const category = sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex].value : '';

    if (!id) { showToast('No product ID', 'error'); return; }
    if (!category) { showToast('Please select a category', 'warning'); return; }

    if (selectedPdfFile) {
        const ok = await uploadPdf(id);
        if (!ok) return;
    }

    // For Product Guides, use default values for hidden fields
    const isProductGuide = category === 'Product Guides';
    
    const payload = {
        id:                  parseInt(id),
        product_name:        document.getElementById('editName').value,
        category:            category,
        sub_category:        isProductGuide ? '' : document.getElementById('editSub').value,
        payment_type:        isProductGuide ? 'Regular' : document.getElementById('editPayment').value,
        age_range:           isProductGuide ? '' : document.getElementById('editAge').value,
        min_premium_monthly: isProductGuide ? 0 : (parseFloat(document.getElementById('editPremium').value) || 0),
        description:         document.getElementById('editDesc').value
    };

    console.log('Sending update:', JSON.stringify(payload));

    const res    = await fetch('../api/products/update.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload)
    });
    const result = await res.json();
    console.log('Update response:', result);

    if (result.success) {
        showToast('Saved! Category: ' + result.saved_category, 'success');
        closeModal('editProductModal');
        loadSection(activeTab);
    } else {
        showToast(result.message || 'Update failed', 'error');
    }
}

async function uploadPdf(productId) {
    const prog = document.getElementById('pdfUploadProgress');
    const bar  = document.getElementById('pdfProgressBar');
    const txt  = document.getElementById('pdfProgressText');
    prog.style.display = '';
    bar.style.width = '30%';
    txt.textContent = 'Uploading PDF...';

    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('primer_file', selectedPdfFile);

    try {
        bar.style.width = '70%';
        const res    = await fetch('../api/products/upload-pdf.php', { method:'POST', body: fd });
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
    } catch (e) {
        txt.textContent = 'Upload failed';
        txt.style.color = 'var(--pru-danger)';
        return false;
    }
}

async function deleteProduct(id, name) {
    if (!confirm(`Delete product "${name}"?`)) return;
    const res    = await fetch(`../api/products/delete.php?id=${id}`, { method:'DELETE' });
    const result = await res.json();
    showToast(result.message, result.success ? 'success' : 'error');
    if (result.success) loadSection(activeTab);
}

// PDF file input
document.getElementById('pdfFileInput').addEventListener('change', function() {
    if (this.files[0]) setPdfFile(this.files[0]);
});

function setPdfFile(file) {
    if (file.type !== 'application/pdf') { showToast('Only PDF files allowed', 'error'); return; }
    if (file.size > 10 * 1024 * 1024)   { showToast('File must be under 10MB', 'error'); return; }
    selectedPdfFile = file;
    const el = document.getElementById('pdfFileName');
    el.style.display = 'flex';
    el.innerHTML = `<i class="fas fa-file-pdf"></i> ${esc(file.name)} <span style="margin-left:auto;color:var(--pru-muted);font-size:11px;">${(file.size/1024/1024).toFixed(2)} MB</span>`;
}

// Drag & drop
const dropZone = document.getElementById('pdfDropZone');
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault(); dropZone.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) setPdfFile(e.dataTransfer.files[0]);
});

async function fixCategories() {
    const btn = document.querySelector('[onclick="fixCategories()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fixing...';
    
    try {
        const res = await fetch('../api/products/fix-categories.php');
        const result = await res.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            loadSection(activeTab); // Reload current section
        } else {
            showToast(result.message || 'Fix failed', 'error');
        }
    } catch(e) { 
        console.error('Fix categories error:', e);
        showToast('Fix failed: ' + e.message, 'error'); 
    }
    
    btn.disabled = false;
    btn.innerHTML = originalText;
}

loadSection('main');
</script>

<?php include '../includes/footer.php'; ?>
