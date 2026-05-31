<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Dashboard';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="pru-main">
    <div class="page-header">
        <div style="flex:1;">
            <h2>Dashboard</h2>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Here's what's happening today.</p>
        </div>
        <!-- Clock Widget -->
        <div style="background:linear-gradient(135deg,var(--pru-dark) 0%,#2a0010 60%,var(--pru-red) 100%);border-radius:12px;padding:16px 20px;color:white;min-width:280px;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div>
                    <div style="font-size:11px;opacity:0.6;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:4px;">Current Time</div>
                    <div id="adminClockTime" style="font-size:24px;font-weight:900;letter-spacing:-1px;line-height:1;">--:--:--</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:11px;opacity:0.6;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:4px;">Today</div>
                    <div id="adminClockDate" style="font-size:12px;font-weight:600;opacity:0.85;">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-value" id="statAgents">–</div>
                    <div class="stat-label">Total Active Agents</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
                <div class="stat-info">
                    <div class="stat-value" id="statActive">–</div>
                    <div class="stat-label">Active Today</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-key"></i></div>
                <div class="stat-info">
                    <div class="stat-value" id="statPwReq">–</div>
                    <div class="stat-label">Password Requests</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-comments"></i></div>
                <div class="stat-info">
                    <div class="stat-value" id="statFeedbacks">–</div>
                    <div class="stat-label">Pending Feedbacks</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="pru-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-th" style="color:var(--pru-red);margin-right:8px;"></i>Quick Links</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="agents.php" class="portal-card">
                        <div class="portal-icon"><i class="fas fa-users" style="color:var(--pru-red);"></i></div>
                        <div class="portal-name">Agents</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="products.php" class="portal-card">
                        <div class="portal-icon"><i class="fas fa-box-open" style="color:#e67e22;"></i></div>
                        <div class="portal-name">Products</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="password-requests.php" class="portal-card">
                        <div class="portal-icon"><i class="fas fa-key" style="color:#f39c12;"></i></div>
                        <div class="portal-name">Pw Requests</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="feedbacks.php" class="portal-card">
                        <div class="portal-icon"><i class="fas fa-comments" style="color:var(--pru-info);"></i></div>
                        <div class="portal-name">Feedbacks</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="export-data.php" class="portal-card">
                        <div class="portal-icon"><i class="fas fa-file-export" style="color:var(--pru-success);"></i></div>
                        <div class="portal-name">Export Data</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="https://www.prulifeuk.com.ph" target="_blank" class="portal-card">
                        <div class="portal-icon"><i class="fas fa-globe" style="color:#8e44ad;"></i></div>
                        <div class="portal-name">PruLife UK</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- PRU Portals -->
    <div class="pru-card">
        <div class="card-header">
            <h5><i class="fas fa-external-link-alt" style="color:var(--pru-red);margin-right:8px;"></i>PRU Portals</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php
                $portals = [
                    ['PruExpert',   'https://pruexpertph.docebosaas.com/learn',  'fa-graduation-cap', '#2980b9'],
                    ['PruShoppe',   'https://www.prushoppe.com/',               'fa-shopping-cart',  '#e67e22'],
                    ['PruOne',      'https://pruone.prulifeuk.com.ph/web/',     'fa-desktop',        '#27ae60'],
                    ['PruServices', 'https://www.prulifeuk.com.ph/en/pruservices/', 'fa-cogs',      '#16a085'],
                    ['PruForce',    'https://pruforce.prulifeuk.com.ph/',       'fa-users-cog',      '#f39c12'],
                    ['PRISM',       'https://prism.prulifeuk.com.ph/',          'fa-gem',            '#9333ea'],
                    ['JoinPru',     'https://www.joinpru.com.ph/',              'fa-user-plus',      '#D50032'],
                    ['PruLife UK',  'https://www.prulifeuk.com.ph/en/',         'fa-globe',          '#8e44ad'],
                ];
                foreach ($portals as [$name, $url, $icon, $color]):
                ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer" class="portal-card">
                        <div class="portal-icon"><i class="fas <?php echo $icon; ?>" style="color:<?php echo $color; ?>;"></i></div>
                        <div class="portal-name"><?php echo $name; ?></div>
                        <div class="portal-external"><i class="fas fa-external-link-alt"></i></div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<script>
// ── Real-time Clock ──
function updateAdminClock() {
    const now  = new Date();
    const time = now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12: true });
    const date = now.toLocaleDateString('en-PH', { weekday:'long', month:'short', day:'numeric', year:'numeric' });
    const timeEl = document.getElementById('adminClockTime');
    const dateEl = document.getElementById('adminClockDate');
    if (timeEl) timeEl.textContent = time;
    if (dateEl) dateEl.textContent = date;
}
updateAdminClock();
setInterval(updateAdminClock, 1000);

// ── Load Stats ──
fetch('../api/stats/get.php').then(r => r.json()).then(d => {
    if (!d.success) return;
    document.getElementById('statAgents').textContent   = d.total_agents;
    document.getElementById('statActive').textContent   = d.active_today;
    document.getElementById('statPwReq').textContent    = d.password_requests;
    document.getElementById('statFeedbacks').textContent = d.agent_feedbacks;
});

</script>

<?php include '../includes/footer.php'; ?>
