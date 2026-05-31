<?php
/**
 * eHeart Agent Sidebar — shared include
 * Requires: $user_name, $agent_code, $initials, $active_page
 */
$_base = (strpos($_SERVER['PHP_SELF'], '/agent/') !== false) ? '../' : '';

// Load avatar from DB for sidebar display
$_sidebar_avatar = '';
if (isset($_SESSION['user_id'])) {
    try {
        require_once $_base . 'api/config/database.php';
        $_sdb  = (new Database())->getConnection();
        $_sdb->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL");
        $_srow = $_sdb->prepare("SELECT avatar FROM users WHERE id=:id");
        $_srow->execute([':id' => $_SESSION['user_id']]);
        $_sdata = $_srow->fetch(PDO::FETCH_ASSOC);
        if (!empty($_sdata['avatar'])) {
            $_sidebar_avatar = $_base . 'api/auth/serve-avatar.php?file=' . urlencode($_sdata['avatar']);
        }
    } catch (Exception $e) {}
}
?>

<!-- ═══ MOBILE TOPBAR ═══════════════════════════════════════ -->
<header class="ad-topbar" id="adTopbar">
    <button class="ad-hamburger" id="adHamburger" onclick="toggleSidebar()" title="Toggle menu" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <div class="ad-topbar-brand">
        <div class="ad-brand-icon-sm">eH</div>
        <span>eHeart</span>
    </div>
    <div class="ad-topbar-right">
        <?php if ($_sidebar_avatar): ?>
        <img src="<?php echo htmlspecialchars($_sidebar_avatar); ?>" alt="Avatar"
             style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.3);">
        <?php else: ?>
        <div class="ad-topbar-avatar"><?php echo $initials; ?></div>
        <?php endif; ?>
    </div>
</header>

<!-- ═══ SIDEBAR ═══════════════════════════════════════════ -->
<aside class="ad-sidebar" id="adSidebar">

    <div class="ad-sidebar-brand">
        <!-- Hamburger inside sidebar (desktop) -->
        <button class="ad-hamburger-desk" id="adHamburgerDesk" onclick="toggleSidebar()" title="Toggle sidebar" aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="ad-brand-icon">eH</div>
        <div class="ad-brand-text">
            <span class="ad-brand-name">eHeart</span>
            <span class="ad-brand-sub">Agent Portal</span>
        </div>
    </div>

    <!-- Agent pill -->
    <div class="ad-agent-pill">
        <?php if ($_sidebar_avatar): ?>
        <img src="<?php echo htmlspecialchars($_sidebar_avatar); ?>" alt="Avatar"
             style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid rgba(255,255,255,0.2);">
        <?php else: ?>
        <div class="ad-agent-avatar"><?php echo $initials; ?></div>
        <?php endif; ?>
        <div class="ad-agent-info">
            <div class="ad-agent-name"><?php echo htmlspecialchars($user_name); ?></div>
            <div class="ad-agent-code"><?php echo htmlspecialchars($agent_code); ?></div>
        </div>
    </div>

    <nav class="ad-nav">
        <div class="ad-nav-label">Main</div>

        <a href="<?php echo $_base; ?>agent/dashboard.php"
           class="ad-nav-item <?php echo $active_page==='dashboard'?'active':''; ?>"
           data-tooltip="Dashboard">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <!-- Products accordion -->
        <div class="ad-nav-item ad-has-sub <?php echo $active_page==='products'?'active sub-open':''; ?>"
             id="productsAccordion" onclick="toggleProductsMenu()" data-tooltip="Products">
            <i class="fas fa-box-open"></i>
            <span>Products</span>
            <i class="fas fa-chevron-down ad-chevron" id="productsChevron"
               style="<?php echo $active_page==='products'?'transform:rotate(180deg)':''; ?>"></i>
        </div>
        <div class="ad-sub-menu <?php echo $active_page==='products'?'open':''; ?>" id="productsSubMenu">
            <a class="ad-sub-item" href="<?php echo $_base; ?>agent/products.php?cat=all">
                <i class="fas fa-th-large"></i><span>All Products</span>
            </a>
            <a class="ad-sub-item" href="<?php echo $_base; ?>agent/products.php?cat=VUL">
                <i class="fas fa-chart-line"></i><span>VUL Plans</span>
            </a>
            <a class="ad-sub-item" href="<?php echo $_base; ?>agent/products.php?cat=Traditional+Life+Insurance">
                <i class="fas fa-shield-alt"></i><span>Traditional Plans</span>
            </a>
        </div>

        <!-- Stand-Alone Product — direct nav item -->
        <a href="<?php echo $_base; ?>agent/products.php?cat=Stand-Alone+Product"
           class="ad-nav-item <?php echo ($active_page==='products' && ($_GET['cat']??'')==='Stand-Alone+Product')?'active':''; ?>"
           data-tooltip="Stand-Alone Product">
            <i class="fas fa-user-shield"></i>
            <span>Stand-Alone Product</span>
        </a>

        <!-- Product Guides — direct nav item -->
        <a href="<?php echo $_base; ?>agent/products.php?cat=Product+Guides"
           class="ad-nav-item <?php echo ($active_page==='products' && ($_GET['cat']??'')==='Product+Guides')?'active':''; ?>"
           data-tooltip="Product Guides">
            <i class="fas fa-book-open"></i>
            <span>Product Guides</span>
        </a>

        <!-- Guidelines accordion -->
        <div class="ad-nav-item ad-has-sub <?php echo $active_page==='guidelines'?'active sub-open':''; ?>"
             id="guidelinesAccordion" onclick="toggleMenu('guidelines')" data-tooltip="Guidelines">
            <i class="fas fa-book"></i>
            <span>Guidelines</span>
            <i class="fas fa-chevron-down ad-chevron" id="guidelinesChevron"
               style="<?php echo $active_page==='guidelines'?'transform:rotate(180deg)':''; ?>"></i>
        </div>
        <div class="ad-sub-menu <?php echo $active_page==='guidelines'?'open':''; ?>" id="guidelinesSubMenu">
            <a class="ad-sub-item" href="<?php echo $_base; ?>agent/guidelines.php?group=underwriting">
                <i class="fas fa-file-medical"></i><span>Underwriting Guidelines</span>
            </a>
            <a class="ad-sub-item" href="<?php echo $_base; ?>agent/guidelines.php?group=policy">
                <i class="fas fa-shield-alt"></i><span>Policy Guidelines</span>
            </a>
        </div>

        <!-- Services accordion -->
        <div class="ad-nav-item ad-has-sub <?php echo $active_page==='services'?'active sub-open':''; ?>"
             id="servicesAccordion" onclick="toggleMenu('services')" data-tooltip="Services">
            <i class="fas fa-concierge-bell"></i>
            <span>Services</span>
            <i class="fas fa-chevron-down ad-chevron" id="servicesChevron"
               style="<?php echo $active_page==='services'?'transform:rotate(180deg)':''; ?>"></i>
        </div>
        <div class="ad-sub-menu <?php echo $active_page==='services'?'open':''; ?>" id="servicesSubMenu">
            <a class="ad-sub-item" href="<?php echo $_base; ?>agent/services.php?group=new-business">
                <i class="fas fa-file-signature"></i><span>New Business</span>
            </a>
            <a class="ad-sub-item" href="<?php echo $_base; ?>agent/services.php?group=after-sales">
                <i class="fas fa-headset"></i><span>After-Sales</span>
            </a>
            <a class="ad-sub-item" href="<?php echo $_base; ?>agent/services.php?group=claims">
                <i class="fas fa-file-invoice"></i><span>Claims</span>
            </a>
        </div>

        <!-- Submit Feedback shortcut -->
        <a href="#" class="ad-nav-item <?php echo $active_page==='feedback'?'active':''; ?>"
           data-tooltip="Submit Feedback" onclick="openFeedbackModal();return false;" id="navFeedbackBtn">
            <i class="fas fa-comment-dots"></i>
            <span>Submit Feedback</span>
        </a>

        <a href="<?php echo $_base; ?>agent/account.php"
           class="ad-nav-item <?php echo $active_page==='account'?'active':''; ?>"
           data-tooltip="Account">
            <i class="fas fa-user-circle"></i>
            <span>Account</span>
        </a>

        <div class="ad-nav-label">Resources</div>
        <a href="<?php echo $_base; ?>agent/email-directories.php"
           class="ad-nav-item <?php echo $active_page==='email-directories'?'active':''; ?>"
           data-tooltip="Email Directories">
            <i class="fas fa-envelope-open-text"></i>
            <span>Email Directories</span>
        </a>
        <a href="<?php echo $_base; ?>agent/accredited-clinics.php"
           class="ad-nav-item <?php echo $active_page==='accredited-clinics'?'active':''; ?>"
           data-tooltip="Accredited Clinics">
            <i class="fas fa-clinic-medical"></i>
            <span>Accredited Clinics</span>
        </a>

        <div class="ad-nav-label">Portals</div>
        <a href="https://pruexpertph.docebosaas.com/learn" target="_blank" class="ad-nav-item" data-tooltip="PruExpert">
            <i class="fas fa-graduation-cap"></i><span>PruExpert</span>
            <i class="fas fa-external-link-alt ad-ext-icon"></i>
        </a>
        <a href="https://www.prushoppe.com/" target="_blank" class="ad-nav-item" data-tooltip="PruShoppe">
            <i class="fas fa-shopping-cart"></i><span>PruShoppe</span>
            <i class="fas fa-external-link-alt ad-ext-icon"></i>
        </a>
        <a href="https://pruone.prulifeuk.com.ph/web/" target="_blank" class="ad-nav-item" data-tooltip="PruOne">
            <i class="fas fa-desktop"></i><span>PruOne</span>
            <i class="fas fa-external-link-alt ad-ext-icon"></i>
        </a>
        <a href="https://www.prulifeuk.com.ph/en/pruservices/" target="_blank" class="ad-nav-item" data-tooltip="PruServices">
            <i class="fas fa-cogs"></i><span>PruServices</span>
            <i class="fas fa-external-link-alt ad-ext-icon"></i>
        </a>
        <a href="https://pruforce.prulifeuk.com.ph/" target="_blank" class="ad-nav-item" data-tooltip="PruForce">
            <i class="fas fa-users-cog"></i><span>PruForce</span>
            <i class="fas fa-external-link-alt ad-ext-icon"></i>
        </a>
        <a href="https://prism.prulifeuk.com.ph/" target="_blank" class="ad-nav-item" data-tooltip="PRISM">
            <i class="fas fa-gem"></i><span>PRISM</span>
            <i class="fas fa-external-link-alt ad-ext-icon"></i>
        </a>
        <a href="https://www.joinpru.com.ph/" target="_blank" class="ad-nav-item" data-tooltip="JoinPru">
            <i class="fas fa-user-plus"></i><span>JoinPru</span>
            <i class="fas fa-external-link-alt ad-ext-icon"></i>
        </a>
        <a href="https://www.prulifeuk.com.ph/en/" target="_blank" class="ad-nav-item" data-tooltip="PruLife UK">
            <i class="fas fa-globe"></i><span>PruLife UK</span>
            <i class="fas fa-external-link-alt ad-ext-icon"></i>
        </a>

        <div class="ad-nav-label">About</div>
        <a href="#" class="ad-nav-item" data-tooltip="About eHeart" onclick="openAboutModal();return false;">
            <i class="fas fa-info-circle"></i><span>About eHeart</span>
        </a>
    </nav>

    <div class="ad-sidebar-footer">
        <a href="#" class="ad-nav-item ad-logout" data-logout data-role="agent" data-tooltip="Sign Out">
            <i class="fas fa-sign-out-alt"></i>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<!-- Mobile overlay -->
<div class="ad-overlay" id="adOverlay" onclick="toggleSidebar()"></div>

<!-- Floating restore button (desktop, shown when sidebar is hidden) -->
<button class="ad-sidebar-restore" id="adSidebarRestore" onclick="toggleSidebar()" title="Show sidebar" aria-label="Show sidebar">
    <span></span>
    <span></span>
    <span></span>
</button>

<!-- ═══ QUICK FEEDBACK MODAL ═══════════════════════════════ -->
<div class="modal-overlay" id="quickFeedbackModal" style="z-index:2100;">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-head">
            <h5><i class="fas fa-comment-dots" style="color:var(--pru-red);margin-right:8px;"></i>Feedback</h5>
            <button class="modal-close" onclick="closeModal('quickFeedbackModal')"><i class="fas fa-times"></i></button>
        </div>

        <!-- Tabs -->
        <div style="display:flex;border-bottom:1px solid var(--pru-border,#e0e0e0);padding:0 20px;">
            <button class="fb-tab active" id="fbTabSubmit" onclick="switchFbTab('submit')">
                <i class="fas fa-paper-plane"></i> Submit
            </button>
            <button class="fb-tab" id="fbTabInbox" onclick="switchFbTab('inbox')">
                <i class="fas fa-inbox"></i> My Feedbacks
                <span id="fbUnreadBadge" style="display:none;background:#D50032;color:white;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:5px;"></span>
            </button>
        </div>

        <!-- Submit Tab -->
        <div id="fbPanelSubmit" class="modal-body-inner">
            <form id="quickFeedbackForm" novalidate>
                <div class="form-group">
                    <label class="form-label">How are you feeling today? *</label>
                    <div class="emoji-rating-container">
                        <input type="radio" name="moodRating" id="mood1" value="1" required>
                        <label for="mood1" class="emoji-option" title="Very Unhappy">😢</label>
                        
                        <input type="radio" name="moodRating" id="mood2" value="2" required>
                        <label for="mood2" class="emoji-option" title="Unhappy">😟</label>
                        
                        <input type="radio" name="moodRating" id="mood3" value="3" required>
                        <label for="mood3" class="emoji-option" title="Neutral">😐</label>
                        
                        <input type="radio" name="moodRating" id="mood4" value="4" required>
                        <label for="mood4" class="emoji-option" title="Happy">🙂</label>
                        
                        <input type="radio" name="moodRating" id="mood5" value="5" required>
                        <label for="mood5" class="emoji-option" title="Very Happy">😄</label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject *</label>
                    <select class="form-control" id="qfSubject" required>
                        <option value="">-- Select a subject --</option>
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
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Message *</label>
                    <textarea class="form-control" id="qfMessage" rows="4" placeholder="Describe your feedback or concern..." required></textarea>
                </div>
            </form>
        </div>
        <div id="fbPanelSubmitFoot" class="modal-foot">
            <button class="btn-pru-outline" onclick="closeModal('quickFeedbackModal')">Cancel</button>
            <button class="btn-pru" onclick="submitQuickFeedback()"><i class="fas fa-paper-plane"></i> Send Feedback</button>
        </div>

        <!-- Inbox Tab -->
        <div id="fbPanelInbox" style="display:none;">
            <div id="fbInboxList" style="max-height:420px;overflow-y:auto;padding:12px 20px;">
                <div style="text-align:center;padding:40px;color:#aaa;">
                    <i class="fas fa-spinner fa-spin" style="font-size:24px;"></i>
                    <p style="margin-top:10px;font-size:13px;">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.fb-tab {
    background: none; border: none; border-bottom: 3px solid transparent;
    padding: 12px 16px; font-size: 13px; font-weight: 600;
    color: #888; cursor: pointer; display: flex; align-items: center; gap: 6px;
    margin-bottom: -1px; transition: all 0.2s;
}
.fb-tab:hover { color: #333; }
.fb-tab.active { color: #D50032; border-bottom-color: #D50032; }

.fb-inbox-item {
    border: 1px solid #e8e8e8; border-radius: 10px;
    padding: 14px 16px; margin-bottom: 10px;
    background: white; transition: box-shadow 0.2s;
}
.fb-inbox-item:hover { box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
.fb-inbox-item.has-reply { border-left: 3px solid #28a745; }
.fb-inbox-item.pending   { border-left: 3px solid #e6a800; }
.fb-subject { font-size: 13px; font-weight: 700; color: #1C1C1C; margin-bottom: 4px; }
.fb-message { font-size: 12px; color: #777; margin-bottom: 8px; line-height: 1.5; }
.fb-meta    { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 11px; color: #aaa; }
.fb-reply-box {
    margin-top: 10px; padding: 10px 12px;
    background: #f0faf3; border-radius: 8px;
    border-left: 3px solid #28a745;
}
.fb-reply-label { font-size: 10px; font-weight: 700; color: #28a745; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.fb-reply-text  { font-size: 12px; color: #333; line-height: 1.5; }

/* Emoji Rating Styles */
.emoji-rating-container {
    display: flex; gap: 8px; justify-content: center;
    padding: 12px 0; background: #f8f9fa; border-radius: 8px;
}
.emoji-rating-container input[type="radio"] {
    display: none;
}
.emoji-option {
    font-size: 32px; cursor: pointer; transition: all 0.2s;
    opacity: 0.4; filter: grayscale(100%);
    padding: 8px; border-radius: 8px;
}
.emoji-option:hover {
    opacity: 0.7; filter: grayscale(50%); transform: scale(1.1);
}
.emoji-rating-container input[type="radio"]:checked + .emoji-option {
    opacity: 1; filter: grayscale(0%); transform: scale(1.2);
    background: rgba(213, 0, 50, 0.1);
}
.fb-mood-emoji {
    font-size: 16px; margin-right: 4px;
}
</style>

<script>
/* ── Sidebar toggle (hamburger) ──────────────── */
function toggleSidebar() {
    const sidebar  = document.getElementById('adSidebar');
    const overlay  = document.getElementById('adOverlay');
    const wrap     = document.getElementById('adMainWrap');
    const restore  = document.getElementById('adSidebarRestore');
    const isMobile = window.innerWidth <= 900;

    if (isMobile) {
        const isOpen = sidebar.classList.contains('open');
        sidebar.classList.toggle('open', !isOpen);
        overlay.classList.toggle('show', !isOpen);
    } else {
        const isHidden = sidebar.classList.contains('hidden');
        sidebar.classList.toggle('hidden', !isHidden);
        if (wrap)    wrap.classList.toggle('sidebar-hidden', !isHidden);
        if (restore) restore.classList.toggle('visible', !isHidden);
        localStorage.setItem('adSidebarHidden', !isHidden ? '1' : '0');
    }
}

/* Restore saved state on load (desktop only) */
(function() {
    if (window.innerWidth > 900) {
        const sidebar = document.getElementById('adSidebar');
        const wrap    = document.getElementById('adMainWrap');
        const restore = document.getElementById('adSidebarRestore');
        if (localStorage.getItem('adSidebarHidden') === '1') {
            sidebar.classList.add('hidden');
            if (wrap)    wrap.classList.add('sidebar-hidden');
            if (restore) restore.classList.add('visible');
        }
    }
})();

/* ── Generic accordion toggle ────────────────── */
function toggleMenu(id) {
    const sidebar = document.getElementById('adSidebar');
    if (sidebar.classList.contains('hidden')) {
        const urls = {
            products:   '<?php echo $_base; ?>agent/products.php',
            guidelines: '<?php echo $_base; ?>agent/guidelines.php',
            services:   '<?php echo $_base; ?>agent/services.php',
        };
        if (urls[id]) window.location.href = urls[id];
        return;
    }
    const sub  = document.getElementById(id + 'SubMenu');
    const chev = document.getElementById(id + 'Chevron');
    const acc  = document.getElementById(id + 'Accordion');
    if (!sub) return;
    const open = sub.classList.toggle('open');
    if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
    if (acc)  acc.classList.toggle('sub-open', open);
}

/* ── Products accordion (kept for compatibility) ── */
function toggleProductsMenu() { toggleMenu('products'); }

/* ── Quick Feedback ──────────────────────────── */
function openFeedbackModal() {
    openModal('quickFeedbackModal');
    loadFbInbox(); // always refresh inbox when opening
}

function switchFbTab(tab) {
    document.querySelectorAll('.fb-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('fbTabSubmit').classList.toggle('active', tab === 'submit');
    document.getElementById('fbTabInbox').classList.toggle('active', tab === 'inbox');
    document.getElementById('fbPanelSubmit').style.display     = tab === 'submit' ? '' : 'none';
    document.getElementById('fbPanelSubmitFoot').style.display = tab === 'submit' ? '' : 'none';
    document.getElementById('fbPanelInbox').style.display      = tab === 'inbox'  ? '' : 'none';
    if (tab === 'inbox') loadFbInbox();
}

async function loadFbInbox() {
    const list = document.getElementById('fbInboxList');
    if (!list) return;
    try {
        const base = window.location.pathname.includes('/agent/') ? '../' : '';
        const res  = await fetch(base + 'api/feedbacks/get.php');
        const d    = await res.json();
        if (!d.success || !d.data.length) {
            list.innerHTML = '<div style="text-align:center;padding:40px;color:#aaa;"><i class="fas fa-comments" style="font-size:28px;opacity:0.3;"></i><p style="margin-top:10px;font-size:13px;">No feedbacks yet</p></div>';
            return;
        }
        // Count unread replies
        const unread = d.data.filter(f => f.status === 'replied').length;
        const badge  = document.getElementById('fbUnreadBadge');
        if (badge) {
            if (unread > 0) { badge.textContent = unread; badge.style.display = ''; }
            else badge.style.display = 'none';
        }
        list.innerHTML = d.data.map(f => {
            const moodEmojis = { '1': '😢', '2': '😟', '3': '😐', '4': '🙂', '5': '😄' };
            const moodEmoji = f.mood_rating ? moodEmojis[f.mood_rating] || '' : '';
            return `
            <div class="fb-inbox-item ${f.status === 'replied' ? 'has-reply' : 'pending'}">
                <div class="fb-subject">${moodEmoji ? `<span class="fb-mood-emoji">${moodEmoji}</span>` : ''}${esc(f.subject)}</div>
                <div class="fb-message">${esc(f.message)}</div>
                <div class="fb-meta">
                    <span class="badge-status badge-${f.status === 'replied' ? 'replied' : 'pending'}" style="font-size:10px;">${f.status === 'replied' ? 'Replied' : 'Pending'}</span>
                    <span>${formatDate(f.created_at)}</span>
                </div>
                ${f.admin_reply ? `
                <div class="fb-reply-box">
                    <div class="fb-reply-label"><i class="fas fa-reply"></i> Admin Reply</div>
                    <div class="fb-reply-text">${esc(f.admin_reply)}</div>
                </div>` : ''}
            </div>`;
        }).join('');
    } catch(e) {
        list.innerHTML = '<div style="text-align:center;padding:40px;color:#aaa;font-size:13px;">Failed to load feedbacks.</div>';
    }
}

async function submitQuickFeedback() {
    const subject = document.getElementById('qfSubject').value.trim();
    const message = document.getElementById('qfMessage').value.trim();
    const moodRating = document.querySelector('input[name="moodRating"]:checked');
    
    if (!moodRating) { showToast('Please select how you are feeling today.', 'warning'); return; }
    if (!subject) { showToast('Please select a subject.', 'warning'); return; }
    if (!message) { showToast('Please enter your message.', 'warning'); return; }

    const btn = document.querySelector('#fbPanelSubmitFoot .btn-pru');
    btn.disabled = true;
    try {
        const base = window.location.pathname.includes('/agent/') ? '../' : '';
        const res    = await fetch(base + 'api/feedbacks/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                subject, 
                message,
                mood_rating: moodRating.value
            })
        });
        const result = await res.json();
        showToast(result.message, result.success ? 'success' : 'error');
        if (result.success) {
            document.getElementById('quickFeedbackForm').reset();
            switchFbTab('inbox'); // switch to inbox after sending
        }
    } catch(e) { showToast('Failed to send feedback.', 'error'); }
    btn.disabled = false;
}
</script>
