<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_admin = ($_SESSION['user_role'] ?? '') === 'admin';
$user_name = $_SESSION['user_name'] ?? 'User';
$agent_code = $_SESSION['agent_code'] ?? '';
$initials = strtoupper(substr($user_name, 0, 1));
?>
<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="pru-sidebar" id="pruSidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <div class="brand-icon" style="font-size:11px;font-weight:900;letter-spacing:-1px;">eH</div>
            <div class="brand-text">
                <h5>eHeart</h5>
                <small>PRU LIFE U.K. · <?php echo $is_admin ? 'Admin Panel' : 'Agent Portal'; ?></small>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($is_admin): ?>
        <div class="sidebar-section-label">Main</div>
        <a href="dashboard.php" data-label="Home" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> <span>Home</span>
        </a>

        <div class="sidebar-section-label">Management</div>
        <a href="agents.php" data-label="Agents" class="nav-link <?php echo $current_page === 'agents.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> <span>Agent Management</span>
        </a>
        <a href="products.php" data-label="Products" class="nav-link <?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box-open"></i> <span>Manage Products</span>
        </a>
        <a href="guidelines.php" data-label="Guidelines" class="nav-link <?php echo $current_page === 'guidelines.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> <span>Manage Guidelines</span>
        </a>
        <a href="directories.php" data-label="Directories" class="nav-link <?php echo $current_page === 'directories.php' ? 'active' : ''; ?>">
            <i class="fas fa-address-book"></i> <span>Manage Directories</span>
        </a>
        <a href="services.php" data-label="Services" class="nav-link <?php echo $current_page === 'services.php' ? 'active' : ''; ?>">
            <i class="fas fa-concierge-bell"></i> <span>Manage Services</span>
        </a>
        <a href="password-requests.php" data-label="Passwords" class="nav-link <?php echo $current_page === 'password-requests.php' ? 'active' : ''; ?>">
            <i class="fas fa-key"></i> <span>Password Requests</span>
            <span class="badge-count" id="sidebarPwCount" style="display:none"></span>
        </a>
        <a href="feedbacks.php" data-label="Feedbacks" class="nav-link <?php echo $current_page === 'feedbacks.php' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i> <span>Agent Feedbacks</span>
            <span class="badge-count" id="sidebarFbCount" style="display:none"></span>
        </a>
        <a href="announcements.php" data-label="Announcements" class="nav-link <?php echo $current_page === 'announcements.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> <span>Announcements</span>
        </a>

        <div class="sidebar-section-label">Data</div>
        <a href="export-data.php" data-label="Export" class="nav-link <?php echo $current_page === 'export-data.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-export"></i> <span>Export Data</span>
        </a>

        <div class="sidebar-section-label">Settings</div>
        <a href="account.php" data-label="Account" class="nav-link <?php echo $current_page === 'account.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-cog"></i> <span>Account Settings</span>
        </a>

        <div class="sidebar-section-label">PRU Portals</div>
        <a href="https://pruexpertph.docebosaas.com/learn" data-label="PruExpert" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-graduation-cap"></i> <span>PruExpert</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://www.prushoppe.com/" data-label="PruShoppe" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-shopping-cart"></i> <span>PruShoppe</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://pruone.prulifeuk.com.ph/web/" data-label="PruOne" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-desktop"></i> <span>PruOne</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://www.prulifeuk.com.ph/en/pruservices/" data-label="PruServices" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-cogs"></i> <span>PruServices</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://pruforce.prulifeuk.com.ph/" data-label="PruForce" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-users-cog"></i> <span>PruForce</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://prism.prulifeuk.com.ph/" data-label="PRISM" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-gem"></i> <span>PRISM</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://www.joinpru.com.ph/" data-label="JoinPru" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-user-plus"></i> <span>JoinPru</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://www.prulifeuk.com.ph/en/" data-label="PruLife UK" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-globe"></i> <span>PruLife UK</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>

        <div class="sidebar-section-label">About</div>
        <a href="#" data-label="About" class="nav-link" onclick="openAboutModal();return false;">
            <i class="fas fa-info-circle"></i> <span>About eHeart</span>
        </a>
        <?php else: ?>
        <div class="sidebar-section-label">Navigation</div>
        <a href="../agent/dashboard.php" data-label="Home" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> <span>Home</span>
        </a>
        <a href="../agent/products.php" data-label="Products" class="nav-link <?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box-open"></i> <span>Products</span>
        </a>
        <a href="../agent/guidelines.php" data-label="Guidelines" class="nav-link <?php echo $current_page === 'guidelines.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> <span>Guidelines</span>
        </a>
        <a href="../agent/services.php" data-label="Services" class="nav-link <?php echo $current_page === 'services.php' ? 'active' : ''; ?>">
            <i class="fas fa-concierge-bell"></i> <span>Services</span>
        </a>
        <a href="../agent/account.php" data-label="Account" class="nav-link <?php echo $current_page === 'account.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-circle"></i> <span>Account</span>
        </a>

        <div class="sidebar-section-label">PRU Portals</div>
        <a href="https://pruexpertph.docebosaas.com/learn" data-label="PruExpert" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-graduation-cap"></i> <span>PruExpert</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://www.prushoppe.com/" data-label="PruShoppe" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-shopping-cart"></i> <span>PruShoppe</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://pruone.prulifeuk.com.ph/web/" data-label="PruOne" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-desktop"></i> <span>PruOne</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://www.prulifeuk.com.ph/en/pruservices/" data-label="PruServices" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-cogs"></i> <span>PruServices</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://pruforce.prulifeuk.com.ph/" data-label="PruForce" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-users-cog"></i> <span>PruForce</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://prism.prulifeuk.com.ph/" data-label="PRISM" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-gem"></i> <span>PRISM</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://www.joinpru.com.ph/" data-label="JoinPru" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-user-plus"></i> <span>JoinPru</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>
        <a href="https://www.prulifeuk.com.ph/en/" data-label="PruLife UK" class="nav-link" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-globe"></i> <span>PruLife UK</span>
            <i class="fas fa-external-link-alt" style="font-size: 0.7rem; margin-left: auto; opacity: 0.6;"></i>
        </a>

        <div class="sidebar-section-label">About</div>
        <a href="#" data-label="About" class="nav-link" onclick="openAboutModal();return false;">
            <i class="fas fa-info-circle"></i> <span>About eHeart</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="#" data-label="Logout" class="nav-link" data-logout data-role="<?php echo $is_admin ? 'admin' : 'agent'; ?>">
            <i class="fas fa-sign-out-alt"></i> <span>Log Out</span>
        </a>
    </div>
</aside>

<!-- Topbar -->
<header class="pru-topbar">
    <button class="topbar-hamburger" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Collapse sidebar" aria-label="Collapse sidebar">
        <i class="fas fa-sidebar" id="collapseIcon"></i>
    </button>
    <div style="display:flex;align-items:center;gap:8px;margin-right:auto;">
        <div style="width:28px;height:28px;background:#D50032;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;color:white;letter-spacing:-0.5px;flex-shrink:0;">eH</div>
        <div>
            <div style="font-size:13px;font-weight:800;color:#1C1C1C;line-height:1;">eHeart</div>
            <div style="font-size:10px;color:#aaa;line-height:1;">PRU LIFE U.K.</div>
        </div>
    </div>

    <div class="topbar-search">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search..." id="globalSearch" autocomplete="off">
    </div>

    <div class="topbar-right">
        <div class="topbar-user">
            <div class="user-avatar"><?php echo $initials; ?></div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-role"><?php echo $is_admin ? 'Administrator · eHeart' : htmlspecialchars($agent_code); ?></div>
            </div>
        </div>
    </div>
</header>

<?php if ($is_admin): ?>
<script>
// Load sidebar badge counts
fetch('../api/stats/get.php').then(r => r.json()).then(d => {
    if (!d.success) return;
    if (d.password_requests > 0) {
        const el = document.getElementById('sidebarPwCount');
        if (el) { el.textContent = d.password_requests; el.style.display = ''; }
    }
    if (d.agent_feedbacks > 0) {
        const el = document.getElementById('sidebarFbCount');
        if (el) { el.textContent = d.agent_feedbacks; el.style.display = ''; }
    }
});
</script>
<?php endif; ?>
