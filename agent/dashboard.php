<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require_once '../api/config/database.php';
$db   = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_name   = $user['full_name']  ?? 'Agent';
$agent_code  = $user['agent_code'] ?? '';
$position    = $user['position']   ?? 'Agent';
$initials    = strtoupper(substr($user_name, 0, 1));
$active_page = 'dashboard';

// Position label & badge color
$posLabels = [
    'Agent' => ['Agent',           'rgba(213,0,50,0.1)',    '#D50032'],
    'OM'    => ['Office Manager',  'rgba(23,162,184,0.1)',  '#17a2b8'],
    'UM'    => ['Unit Manager',    'rgba(40,167,69,0.1)',   '#28a745'],
    'BM'    => ['Branch Manager',  'rgba(255,193,7,0.12)',  '#e6a800'],
];
$posInfo  = $posLabels[$position] ?? [$position, 'rgba(108,117,125,0.1)', '#6c757d'];
$posLabel = $posInfo[0];
$posBg    = $posInfo[1];
$posColor = $posInfo[2];

// Daily bible verses for financial advisors
$verses = [
    ['Proverbs 13:11',   '"Wealth gained hastily will dwindle, but whoever gathers little by little will increase it."'],
    ['Luke 16:10',       '"Whoever is faithful in a very little is also faithful in much."'],
    ['Proverbs 21:5',    '"The plans of the diligent lead surely to abundance, but everyone who is hasty comes only to poverty."'],
    ['Matthew 6:24',     '"No one can serve two masters... You cannot serve God and money."'],
    ['Proverbs 22:7',    '"The rich rules over the poor, and the borrower is the slave of the lender."'],
    ['Ecclesiastes 11:2','"Invest in seven ventures, yes, in eight; you do not know what disaster may come upon the land."'],
    ['Proverbs 3:9',     '"Honor the Lord with your wealth and with the firstfruits of all your produce."'],
    ['1 Timothy 6:17',   '"Command those who are rich... not to be arrogant nor to put their hope in wealth, which is so uncertain."'],
    ['Proverbs 11:14',   '"Where there is no guidance, a people falls, but in an abundance of counselors there is safety."'],
    ['Proverbs 27:23',   '"Know well the condition of your flocks, and give attention to your herds."'],
    ['Luke 14:28',       '"For which of you, desiring to build a tower, does not first sit down and count the cost?"'],
    ['Proverbs 6:6',     '"Go to the ant, O sluggard; consider her ways, and be wise."'],
    ['Deuteronomy 8:18', '"Remember the Lord your God, for it is he who gives you the ability to produce wealth."'],
    ['Proverbs 28:20',   '"A faithful man will abound with blessings, but whoever hastens to be rich will not go unpunished."'],
];
$verse = $verses[date('j') % count($verses)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/pdf-viewer.css">
    <link rel="stylesheet" href="../assets/css/agent-dashboard.css">
    <link rel="stylesheet" href="../assets/css/tutorial.css">
    <link rel="stylesheet" href="../assets/css/theme-toggle.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        .dash-clock {
            background: linear-gradient(135deg, var(--pru-dark) 0%, #2a0010 60%, var(--pru-red) 100%);
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            color: white;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
            margin-bottom: 20px;
        }
        .dash-clock-time {
            font-size: 36px; font-weight: 900; letter-spacing: -1px; line-height: 1;
            text-align: right;
        }
        .dash-clock-date { font-size: 13px; opacity: 0.65; margin-top: 4px; text-align: right; }
        .dash-clock-right { text-align: right; }
        .dash-pos-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 14px; border-radius: 20px;
            font-size: 12px; font-weight: 700;
        }

        .verse-card {
            background: white;
            border-left: 4px solid var(--pru-red);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .verse-text  { font-size: 14px; font-style: italic; color: var(--pru-text); line-height: 1.6; margin: 0 0 6px; }
        .verse-ref   { font-size: 12px; font-weight: 700; color: var(--pru-red); }

        .calendar-card {
            background: white;
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .calendar-card .card-header { padding: 14px 18px; }
        .announcement-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 12px 18px; border-bottom: 1px solid var(--pru-border);
            font-size: 13px; transition: background 0.2s;
        }
        .announcement-item:last-child { border-bottom: none; }
        .announcement-item:hover { background: var(--pru-light); }
        .announcement-icon {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0; margin-top: 2px;
        }
        .announcement-content { flex: 1; min-width: 0; }
        .announcement-title { font-weight: 700; color: var(--pru-text); margin-bottom: 4px; line-height: 1.3; }
        .announcement-message { color: var(--pru-muted); line-height: 1.4; margin-bottom: 6px; }
        .announcement-meta { display: flex; align-items: center; gap: 8px; font-size: 11px; color: var(--pru-muted); }
        .announcement-badge {
            padding: 2px 6px; border-radius: 10px; font-size: 10px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .badge-urgent { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .badge-event { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
        .badge-reminder { background: rgba(255, 193, 7, 0.1); color: #e6a800; }
        .badge-general { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
        .new-indicator {
            width: 8px; height: 8px; background: var(--pru-red); border-radius: 50%;
            position: absolute; top: -2px; right: -2px;
        }

        .portal-card {
            display: flex; flex-direction: column; align-items: center;
            padding: 16px 8px; border-radius: var(--radius-md);
            background: var(--pru-light); border: 1px solid var(--pru-border);
            text-decoration: none; transition: all 0.2s; position: relative;
        }
        .portal-card:hover { border-color: var(--pru-red); transform: translateY(-2px); box-shadow: 0 4px 16px rgba(213,0,50,0.1); }
        .portal-icon { font-size: 22px; margin-bottom: 8px; }
        .portal-name { font-size: 12px; font-weight: 700; color: var(--pru-text); text-align: center; }
        .portal-external { 
            position: absolute; top: 8px; right: 8px; 
            font-size: 10px; color: var(--pru-muted); opacity: 0.6;
        }
        
        .calendar-tabs {
            display: flex;
            border-bottom: 1px solid var(--pru-border);
            margin-bottom: 0;
        }
        
        .calendar-tab {
            padding: 10px 16px;
            background: none;
            border: none;
            font-size: 13px;
            font-weight: 600;
            color: var(--pru-muted);
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 2px solid transparent;
        }
        
        .calendar-tab.active {
            color: var(--pru-red);
            border-bottom-color: var(--pru-red);
        }
        
        .calendar-tab:hover {
            color: var(--pru-text);
        }
        
        .calendar-content {
            padding: 0;
        }
        
        .mini-calendar {
            padding: 15px;
        }
        
        .fc-event {
            border: none !important;
            border-radius: 4px !important;
            padding: 1px 4px !important;
            font-size: 10px !important;
            font-weight: 600 !important;
            margin-bottom: 1px !important;
        }
        
        .fc-event-urgent {
            background: #dc3545 !important;
            color: white !important;
        }
        
        .fc-event-event {
            background: #17a2b8 !important;
            color: white !important;
        }
        
        .fc-event-reminder {
            background: #ffc107 !important;
            color: #212529 !important;
        }
        
        .fc-event-general {
            background: #6c757d !important;
            color: white !important;
        }
        
        .fc-event-holiday {
            background: #28a745 !important;
            color: white !important;
        }
        
        .fc-event-birthday {
            background: #e83e8c !important;
            color: white !important;
        }
        
        .fc-daygrid-day-number {
            font-size: 12px !important;
        }
        
        .fc-col-header-cell {
            font-size: 11px !important;
        }
        
        .fc-toolbar {
            font-size: 12px !important;
        }
        
        .fc-toolbar-title {
            font-size: 14px !important;
            font-weight: 700 !important;
        }
        
        .fc-button {
            font-size: 11px !important;
            padding: 4px 8px !important;
        }
        
        /* Birthday/Events Styles */
        .event-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: linear-gradient(135deg, rgba(213,0,50,0.05) 0%, rgba(213,0,50,0.02) 100%);
            border-radius: 12px;
            border-left: 4px solid var(--pru-red);
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        
        .event-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(213,0,50,0.1);
        }
        
        .event-item.today {
            background: linear-gradient(135deg, rgba(255,193,7,0.15) 0%, rgba(255,193,7,0.05) 100%);
            border-left-color: #ffc107;
            animation: pulse 2s infinite;
        }
        
        .event-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .event-item.today .event-icon {
            animation: bounce 1s infinite;
        }
        
        .event-content {
            flex: 1;
            min-width: 0;
        }
        
        .event-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--pru-text);
            margin-bottom: 4px;
        }
        
        .event-subtitle {
            font-size: 12px;
            color: var(--pru-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .event-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            background: var(--pru-red);
            color: white;
        }
        
        .event-item.today .event-badge {
            background: #ffc107;
            color: #212529;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.95; }
        }
        
        /* Feeling Check Modal */
        .feeling-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }
        
        .feeling-modal {
            background: white;
            border-radius: 24px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            animation: slideUp 0.4s ease;
        }
        
        .feeling-header {
            background: linear-gradient(135deg, var(--pru-red) 0%, #a00028 100%);
            padding: 32px 28px;
            color: white;
            text-align: center;
        }
        
        .feeling-header h2 {
            font-size: 24px;
            font-weight: 800;
            margin: 0 0 8px;
            letter-spacing: -0.5px;
        }
        
        .feeling-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }
        
        .feeling-body {
            padding: 32px 28px;
        }
        
        .feeling-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .feeling-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 20px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        
        .feeling-option:hover {
            border-color: var(--pru-red);
            background: rgba(213, 0, 50, 0.05);
            transform: translateY(-2px);
        }
        
        .feeling-option.selected {
            border-color: var(--pru-red);
            background: rgba(213, 0, 50, 0.1);
            box-shadow: 0 4px 16px rgba(213, 0, 50, 0.2);
        }
        
        .feeling-emoji {
            font-size: 48px;
            line-height: 1;
        }
        
        .feeling-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--pru-text);
        }
        
        .feeling-message {
            padding: 16px;
            background: rgba(213, 0, 50, 0.05);
            border-radius: 12px;
            border-left: 4px solid var(--pru-red);
            margin-bottom: 24px;
            display: none;
        }
        
        .feeling-message.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .feeling-message p {
            font-size: 14px;
            color: var(--pru-text);
            margin: 0;
            line-height: 1.6;
        }
        
        .feeling-footer {
            display: flex;
            gap: 12px;
        }
        
        .feeling-btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .feeling-btn-skip {
            background: #f5f5f5;
            color: #555;
        }
        
        .feeling-btn-skip:hover {
            background: #e0e0e0;
        }
        
        .feeling-btn-submit {
            background: var(--pru-red);
            color: white;
            box-shadow: 0 4px 14px rgba(213, 0, 50, 0.3);
        }
        
        .feeling-btn-submit:hover {
            background: #a00028;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(213, 0, 50, 0.4);
        }
        
        .feeling-btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="agent-dash-body">

<?php include '../includes/agent-sidebar.php'; ?>
<div class="ad-main-wrap" id="adMainWrap">

    <!-- ── HOME VIEW ──────────────────────────────────────── -->
    <main class="ad-content" id="viewHome">

        <!-- Clock + Position Banner -->
        <div class="dash-clock">
            <div>
                <div style="font-size:15px;font-weight:800;margin-bottom:6px;"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="dash-pos-badge" style="background:<?php echo $posBg; ?>;color:<?php echo $posColor; ?>;">
                    <i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($posLabel); ?>
                </div>
                <div style="font-size:11px;opacity:0.5;margin-top:6px;"><?php echo htmlspecialchars($agent_code); ?></div>
            </div>
            <div class="dash-clock-right">
                <div class="dash-clock-time" id="clockTime">--:--:--</div>
                <div class="dash-clock-date" id="clockDate">Loading...</div>
            </div>
        </div>

        <!-- Bible Verse of the Day -->
        <div class="verse-card">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                <i class="fas fa-bible" style="color:var(--pru-red);"></i>
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:var(--pru-muted);">Verse of the Day</span>
            </div>
            <p class="verse-text"><?php echo htmlspecialchars($verse[1]); ?></p>
            <div class="verse-ref">— <?php echo htmlspecialchars($verse[0]); ?></div>
        </div>

        <!-- Quick Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="ad-quick-card" onclick="openProductView('all')">
                    <div class="ad-quick-icon" style="background:rgba(213,0,50,0.08);color:var(--pru-red);"><i class="fas fa-box-open"></i></div>
                    <div class="ad-quick-label">Products</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <a href="guidelines.php" class="ad-quick-card">
                    <div class="ad-quick-icon" style="background:rgba(40,167,69,0.08);color:var(--pru-success);"><i class="fas fa-book"></i></div>
                    <div class="ad-quick-label">Guidelines</div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a href="services.php" class="ad-quick-card">
                    <div class="ad-quick-icon" style="background:rgba(23,162,184,0.08);color:var(--pru-info);"><i class="fas fa-concierge-bell"></i></div>
                    <div class="ad-quick-label">Services</div>
                </a>
            </div>
            <div class="col-6 col-lg-3">
                <a href="account.php" class="ad-quick-card">
                    <div class="ad-quick-icon" style="background:rgba(255,193,7,0.1);color:#e6a800;"><i class="fas fa-user-circle"></i></div>
                    <div class="ad-quick-label">My Account</div>
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Upcoming Events (Birthdays) -->
            <div class="col-lg-12">
                <div class="pru-card" id="upcomingEventsCard" style="display:none;">
                    <div class="card-header">
                        <h5><i class="fas fa-birthday-cake" style="color:var(--pru-red);margin-right:8px;"></i>Upcoming Events</h5>
                    </div>
                    <div class="card-body" id="upcomingEventsBody">
                        <!-- Birthday notifications will be inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Admin Calendar & Announcements -->
            <div class="col-lg-6">
                <div class="calendar-card pru-card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar-alt" style="color:var(--pru-red);margin-right:8px;"></i>Admin Updates & Calendar</h5>
                    </div>
                    <div class="calendar-tabs">
                        <button class="calendar-tab active" onclick="switchCalendarTab('announcements', this)">
                            <i class="fas fa-bullhorn"></i> Announcements
                        </button>
                        <button class="calendar-tab" onclick="switchCalendarTab('calendar', this)">
                            <i class="fas fa-calendar"></i> Calendar
                        </button>
                    </div>
                    <div class="calendar-content">
                        <div id="announcementsTab" class="tab-content">
                            <div id="calendarBody">
                                <div style="padding:20px;text-align:center;color:var(--pru-muted);font-size:13px;">
                                    <i class="fas fa-spinner fa-spin"></i> Loading announcements...
                                </div>
                            </div>
                        </div>
                        <div id="calendarTab" class="tab-content" style="display:none;">
                            <div class="mini-calendar">
                                <div id="agentCalendar"></div>
                            </div>
                        </div>
                    </div>
                    <div style="padding:10px 18px;font-size:10px;color:var(--pru-muted);border-top:1px solid var(--pru-border);">
                        <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                        Updates from admin team. Last checked: <span id="calendarUpdated">—</span>
                    </div>
                </div>
            </div>

            <!-- PRU Portals -->
            <div class="col-lg-6">
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
                            ];
                            foreach ($portals as [$name, $url, $icon, $color]):
                            ?>
                            <div class="col-4">
                                <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer" class="portal-card">
                                    <div class="portal-icon"><i class="fas <?php echo $icon; ?>" style="color:<?php echo $color; ?>;"></i></div>
                                    <div class="portal-name"><?php echo $name; ?></div>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- ── PRODUCTS VIEW ──────────────────────────────────── -->
    <main class="ad-content" id="viewProducts" style="display:none;">
        <div class="ad-products-layout">
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
                        <input type="text" id="dashSearchInput" placeholder="Search products…" autocomplete="off">
                    </div>
                    <div class="ad-cat-pills" id="dashFilterBtns">
                        <button class="ad-cat-pill active" onclick="dashFilterCat('all',this)">All</button>
                        <button class="ad-cat-pill" onclick="dashFilterCat('VUL',this)">VUL</button>
                        <button class="ad-cat-pill" onclick="dashFilterCat('Traditional Life Insurance',this)">Traditional</button>
                        <button class="ad-cat-pill" onclick="dashFilterCat('Stand-Alone Product',this)">Stand-Alone</button>
                        <button class="ad-cat-pill" onclick="dashFilterCat('Product Guides',this)">Guides</button>
                    </div>
                </div>
                <div class="ad-product-list" id="dashProductList">
                    <div class="ad-list-loading"><div class="pdf-spinner"></div><p>Loading products…</p></div>
                </div>
            </div>
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

<div class="toast-stack"></div>

<!-- ── FEELING CHECK MODAL ── -->
<div id="feelingModalOverlay" class="feeling-modal-overlay" style="display:none;">
    <div class="feeling-modal">
        <div class="feeling-header">
            <h2>How are you feeling today?</h2>
            <p>Let's start your day with a quick check-in</p>
        </div>
        <div class="feeling-body">
            <div class="feeling-options">
                <div class="feeling-option" onclick="selectFeeling('great', this)">
                    <div class="feeling-emoji">😊</div>
                    <div class="feeling-label">Great</div>
                </div>
                <div class="feeling-option" onclick="selectFeeling('good', this)">
                    <div class="feeling-emoji">🙂</div>
                    <div class="feeling-label">Good</div>
                </div>
                <div class="feeling-option" onclick="selectFeeling('okay', this)">
                    <div class="feeling-emoji">😐</div>
                    <div class="feeling-label">Okay</div>
                </div>
                <div class="feeling-option" onclick="selectFeeling('tired', this)">
                    <div class="feeling-emoji">😴</div>
                    <div class="feeling-label">Tired</div>
                </div>
                <div class="feeling-option" onclick="selectFeeling('stressed', this)">
                    <div class="feeling-emoji">😰</div>
                    <div class="feeling-label">Stressed</div>
                </div>
                <div class="feeling-option" onclick="selectFeeling('sad', this)">
                    <div class="feeling-emoji">😔</div>
                    <div class="feeling-label">Sad</div>
                </div>
            </div>
            
            <div id="feelingMessage" class="feeling-message">
                <p id="feelingMessageText"></p>
            </div>
            
            <div class="feeling-footer">
                <button class="feeling-btn feeling-btn-skip" onclick="skipFeelingCheck()">
                    <i class="fas fa-times"></i> Skip
                </button>
                <button class="feeling-btn feeling-btn-submit" id="feelingSubmitBtn" onclick="submitFeelingCheck()" disabled>
                    <i class="fas fa-check"></i> Continue
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="../assets/js/scripts.js"></script>
<script src="../assets/js/pdf-viewer.js"></script>
<script src="../assets/js/tutorial.js"></script>
<script src="../assets/js/theme-toggle.js"></script>
<script>
/* ── Real-time clock ── */
function updateClock() {
    const now  = new Date();
    const time = now.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12: true });
    const date = now.toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    document.getElementById('clockTime').textContent = time;
    document.getElementById('clockDate').textContent = date;
}
updateClock();
setInterval(updateClock, 1000);

/* ── Calendar tab switching ── */
let agentCalendar = null;
let calendarAnnouncements = [];

function switchCalendarTab(tab, button) {
    // Update tab buttons
    document.querySelectorAll('.calendar-tab').forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');
    
    // Show/hide content
    document.getElementById('announcementsTab').style.display = tab === 'announcements' ? 'block' : 'none';
    document.getElementById('calendarTab').style.display = tab === 'calendar' ? 'block' : 'none';
    
    // Initialize calendar if switching to calendar tab
    if (tab === 'calendar' && !agentCalendar) {
        initializeAgentCalendar();
    }
}

function initializeAgentCalendar() {
    const calendarEl = document.getElementById('agentCalendar');
    agentCalendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next',
            center: 'title',
            right: 'today'
        },
        height: 350,
        events: [],
        eventDisplay: 'block',
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        eventDidMount: function(info) {
            const type = info.event.extendedProps.event_type;
            info.el.classList.add(`fc-event-${type}`);
            
            // Add tooltip
            info.el.title = info.event.extendedProps.description || info.event.title;
        }
    });
    
    agentCalendar.render();
    updateAgentCalendarEvents();
}

function updateAgentCalendarEvents() {
    if (!agentCalendar) return;
    
    const events = [];
    
    // Add announcements to calendar
    calendarAnnouncements.forEach(announcement => {
        const startDate = announcement.start_date || announcement.created_at.split(' ')[0];
        const endDate = announcement.end_date || startDate;
        
        events.push({
            id: `announcement-${announcement.id}`,
            title: announcement.title,
            start: startDate,
            end: endDate,
            extendedProps: {
                event_type: announcement.announcement_type,
                description: announcement.message
            }
        });
    });
    
    // Load and add birthdays to calendar
    fetch('../api/agents/get-all-birthdays.php')
        .then(r => r.json())
        .then(result => {
            if (result.success && result.data) {
                result.data.forEach(birthday => {
                    events.push({
                        id: birthday.id,
                        title: birthday.title,
                        start: birthday.start,
                        allDay: birthday.allDay,
                        extendedProps: {
                            event_type: 'birthday',
                            description: birthday.description
                        }
                    });
                });
            }
            
            // Add Philippine holidays
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const holidays = getAllPhilippineHolidays(currentYear);
            
            holidays.forEach(holiday => {
                events.push({
                    id: `holiday-${holiday.date}`,
                    title: holiday.name,
                    start: holiday.date,
                    allDay: true,
                    extendedProps: {
                        event_type: 'holiday',
                        description: `Philippine Holiday: ${holiday.name}`
                    }
                });
            });
            
            agentCalendar.removeAllEvents();
            agentCalendar.addEventSource(events);
        })
        .catch(error => {
            console.error('Failed to load birthdays:', error);
            
            // Still add holidays even if birthdays fail
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const holidays = getAllPhilippineHolidays(currentYear);
            
            holidays.forEach(holiday => {
                events.push({
                    id: `holiday-${holiday.date}`,
                    title: holiday.name,
                    start: holiday.date,
                    allDay: true,
                    extendedProps: {
                        event_type: 'holiday',
                        description: `Philippine Holiday: ${holiday.name}`
                    }
                });
            });
            
            agentCalendar.removeAllEvents();
            agentCalendar.addEventSource(events);
        });
}

/* ── Enhanced Philippine Holidays ── */
function getAllPhilippineHolidays(year) {
    const holidays = [
        // Fixed holidays
        { date: `${year}-01-01`, name: "New Year's Day" },
        { date: `${year}-02-25`, name: "EDSA People Power Revolution" },
        { date: `${year}-04-09`, name: "Araw ng Kagitingan" },
        { date: `${year}-05-01`, name: "Labor Day" },
        { date: `${year}-06-12`, name: "Independence Day" },
        { date: `${year}-08-21`, name: "Ninoy Aquino Day" },
        { date: `${year}-08-29`, name: "National Heroes Day" },
        { date: `${year}-11-01`, name: "All Saints' Day" },
        { date: `${year}-11-02`, name: "All Souls' Day" },
        { date: `${year}-11-30`, name: "Bonifacio Day" },
        { date: `${year}-12-25`, name: "Christmas Day" },
        { date: `${year}-12-30`, name: "Rizal Day" },
        
        // Variable holidays for 2026 (in production, calculate these properly)
        ...(year === 2026 ? [
            { date: `${year}-04-17`, name: "Maundy Thursday" },
            { date: `${year}-04-18`, name: "Good Friday" },
            { date: `${year}-04-19`, name: "Black Saturday" },
        ] : [])
    ];
    
    return holidays;
}
async function loadCalendar() {
    try {
        const response = await fetch('../api/announcements/get.php?limit=5');
        const result = await response.json();
        
        const body = document.getElementById('calendarBody');
        
        if (result.success && result.data.length > 0) {
            calendarAnnouncements = result.data; // Store for calendar view
            
            body.innerHTML = result.data.map(announcement => {
                const iconClass = {
                    'urgent': 'fa-exclamation-triangle',
                    'event': 'fa-calendar-day', 
                    'reminder': 'fa-bell',
                    'general': 'fa-info-circle'
                }[announcement.announcement_type] || 'fa-info-circle';
                
                const iconColor = {
                    'urgent': '#dc3545',
                    'event': '#17a2b8',
                    'reminder': '#e6a800', 
                    'general': '#6c757d'
                }[announcement.announcement_type] || '#6c757d';
                
                return `
                    <div class="announcement-item">
                        <div class="announcement-icon" style="background:${iconColor}20;color:${iconColor};">
                            <i class="fas ${iconClass}"></i>
                            ${announcement.is_new ? '<div class="new-indicator"></div>' : ''}
                        </div>
                        <div class="announcement-content">
                            <div class="announcement-title">${escapeHtml(announcement.title)}</div>
                            <div class="announcement-message">${escapeHtml(announcement.message)}</div>
                            <div class="announcement-meta">
                                <span class="announcement-badge badge-${announcement.announcement_type}">
                                    ${announcement.announcement_type}
                                </span>
                                <span><i class="fas fa-clock"></i> ${announcement.formatted_date}</span>
                                ${announcement.created_by_name ? `<span><i class="fas fa-user"></i> ${escapeHtml(announcement.created_by_name)}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Update calendar events if calendar is initialized
            if (agentCalendar) {
                updateAgentCalendarEvents();
            }
        } else {
            calendarAnnouncements = [];
            body.innerHTML = `
                <div style="padding:20px;text-align:center;color:var(--pru-muted);font-size:13px;">
                    <i class="fas fa-calendar-check" style="font-size:24px;margin-bottom:8px;opacity:0.5;"></i>
                    <p>No announcements at this time</p>
                    <p style="font-size:11px;margin-top:8px;">Check back later for updates from the admin team.</p>
                </div>
            `;
        }
        
        document.getElementById('calendarUpdated').textContent = new Date().toLocaleTimeString('en-PH', { 
            hour:'2-digit', minute:'2-digit', hour12: true 
        });
        
    } catch (error) {
        console.error('Calendar load error:', error);
        document.getElementById('calendarBody').innerHTML = `
            <div style="padding:20px;text-align:center;color:var(--pru-muted);font-size:13px;">
                <i class="fas fa-exclamation-triangle" style="color:#dc3545;margin-bottom:8px;"></i>
                <p>Failed to load announcements</p>
            </div>
        `;
    }
}

/* ── Philippine Holidays ── */
function getPhilippineHolidays(year, month) {
    const holidays = [
        // Fixed holidays
        { month: 1, day: 1, name: "New Year's Day" },
        { month: 4, day: 9, name: "Araw ng Kagitingan (Day of Valor)" },
        { month: 5, day: 1, name: "Labor Day" },
        { month: 6, day: 12, name: "Independence Day" },
        { month: 8, day: 21, name: "Ninoy Aquino Day" },
        { month: 8, day: 29, name: "National Heroes Day" },
        { month: 11, day: 30, name: "Bonifacio Day" },
        { month: 12, day: 25, name: "Christmas Day" },
        { month: 12, day: 30, name: "Rizal Day" },
        
        // Variable holidays (approximate dates - in production, use proper calculation)
        ...(year === 2026 ? [
            { month: 2, day: 25, name: "EDSA People Power Revolution Anniversary" },
            { month: 4, day: 17, name: "Maundy Thursday" },
            { month: 4, day: 18, name: "Good Friday" },
            { month: 4, day: 19, name: "Black Saturday" },
            { month: 11, day: 1, name: "All Saints' Day" },
            { month: 11, day: 2, name: "All Souls' Day" },
        ] : [])
    ];
    
    return holidays
        .filter(h => h.month === month)
        .map(h => ({
            date: `${h.month}/${h.day}/${year}`,
            name: h.name
        }))
        .sort((a, b) => new Date(a.date) - new Date(b.date));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadCalendar();
setInterval(loadCalendar, 5 * 60 * 1000); // refresh every 5 minutes

/* ── Birthday Notifications ── */
async function loadBirthdayNotifications() {
    try {
        const response = await fetch('../api/agents/get-birthdays.php');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const card = document.getElementById('upcomingEventsCard');
            const body = document.getElementById('upcomingEventsBody');
            
            body.innerHTML = result.data.map(event => {
                const isToday = event.is_today;
                const isSelf = event.is_current_user;
                
                let title, subtitle, icon;
                
                if (isToday && isSelf) {
                    title = '🎉 Happy Birthday!';
                    subtitle = 'Wishing you a wonderful day filled with joy and success!';
                    icon = '🎂';
                } else if (isToday) {
                    title = `${escapeHtml(event.name)}'s Birthday`;
                    subtitle = `${event.agent_code ? escapeHtml(event.agent_code) + ' • ' : ''}Celebrate with your colleague today!`;
                    icon = '🎂';
                } else {
                    const daysText = event.days_until === 1 ? 'Tomorrow' : `In ${event.days_until} days`;
                    title = `${escapeHtml(event.name)}'s Birthday`;
                    subtitle = `${event.agent_code ? escapeHtml(event.agent_code) + ' • ' : ''}${escapeHtml(event.formatted_date)} • ${daysText}`;
                    icon = '🎂';
                }
                
                return `
                    <div class="event-item ${isToday ? 'today' : ''}">
                        <div class="event-icon">${icon}</div>
                        <div class="event-content">
                            <div class="event-title">${title}</div>
                            <div class="event-subtitle">${subtitle}</div>
                        </div>
                        ${isToday ? '<span class="event-badge"><i class="fas fa-star"></i> Today</span>' : ''}
                    </div>
                `;
            }).join('');
            
            card.style.display = 'block';
        } else {
            document.getElementById('upcomingEventsCard').style.display = 'none';
        }
    } catch (error) {
        console.error('Failed to load birthday notifications:', error);
        document.getElementById('upcomingEventsCard').style.display = 'none';
    }
}

loadBirthdayNotifications();
setInterval(loadBirthdayNotifications, 10 * 60 * 1000); // refresh every 10 minutes

/* ── Products ── */
let allProducts    = [];
let dashCurrentCat = 'all';
let activeProductId = null;

function showView(id) {
    document.querySelectorAll('.ad-content').forEach(v => v.style.display = 'none');
    document.getElementById(id).style.display = 'block';
}

function openProductView(cat) {
    dashCurrentCat = cat || 'all';
    showView('viewProducts');
    document.getElementById('productPanelTitle').textContent = catLabel(dashCurrentCat);
    document.querySelectorAll('.ad-cat-pill').forEach(b => {
        b.classList.toggle('active', b.getAttribute('onclick')?.includes(`'${dashCurrentCat}'`));
    });
    
    // Show/hide Google Drive reference links based on category
    const refLinksDiv = document.getElementById('productReferenceLinks');
    const refLink = document.getElementById('productRefLink');
    const refText = document.getElementById('productRefText');
    
    if (dashCurrentCat === 'Traditional Life Insurance') {
        refLinksDiv.style.display = 'block';
        refLink.href = 'https://drive.google.com/drive/folders/1GtxASCnmg92ogPobV_MpxSrPaikGeP1b';
        refText.textContent = 'View Traditional Products References';
    } else if (dashCurrentCat === 'VUL') {
        refLinksDiv.style.display = 'block';
        refLink.href = 'https://drive.google.com/drive/folders/1YB2o6N7Njdtac2o1x4gfk_-okjmaTqPL';
        refText.textContent = 'View VUL Products References';
    } else {
        refLinksDiv.style.display = 'none';
    }
    
    if (allProducts.length) renderDashProducts();
    else loadDashProducts();
}

function catLabel(cat) {
    return { all:'All Products', VUL:'VUL Plans', 'Traditional Life Insurance':'Traditional Plans', 'Stand-Alone Product':'Stand-Alone Product', 'Product Guides':'Product Guides' }[cat] || cat;
}

function loadDashProducts() {
    document.getElementById('dashProductList').innerHTML = '<div class="ad-list-loading"><div class="pdf-spinner"></div><p>Loading…</p></div>';
    fetch('../api/products/get.php').then(r => r.json()).then(d => {
        if (d.success) { allProducts = d.data; renderDashProducts(); }
    });
}

function renderDashProducts() {
    const q = document.getElementById('dashSearchInput').value.toLowerCase();
    const filtered = allProducts.filter(p => {
        const cat = (p.category || '').trim();
        return (dashCurrentCat === 'all' || cat === dashCurrentCat) &&
               (!q || p.product_name.toLowerCase().includes(q) || cat.toLowerCase().includes(q));
    });
    const list = document.getElementById('dashProductList');
    if (!filtered.length) {
        list.innerHTML = '<div class="ad-list-empty"><i class="fas fa-box-open"></i><p>No products found</p></div>';
        return;
    }
    list.innerHTML = filtered.map(p => {
        const isProductGuide = (p.category || '').trim() === 'Product Guides';
        const metaHtml = isProductGuide ? 
            '<span><i class="fas fa-book"></i> Product Guide</span>' :
            `<span><i class="fas fa-user-clock"></i> ${esc(p.age_range)}</span>
             <span><i class="fas fa-coins"></i> ${formatPHP(p.min_premium_monthly)}/mo</span>`;
        
        return `
        <div class="ad-product-item ${activeProductId == p.id ? 'active' : ''}" onclick="selectProduct(${p.id})" id="prod-item-${p.id}">
            <div class="ad-product-item-cat">${esc(p.category)}</div>
            <div class="ad-product-item-name">${esc(p.product_name)}</div>
            <div class="ad-product-item-meta">
                ${metaHtml}
            </div>
            ${p.primer_file ? '<div class="ad-product-item-pdf"><i class="fas fa-file-pdf"></i> Product Primer Available</div>' : ''}
        </div>`;
    }).join('');
}

function dashFilterCat(cat, btn) {
    dashCurrentCat = cat;
    document.querySelectorAll('.ad-cat-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('productPanelTitle').textContent = catLabel(cat);
    
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
    
    renderDashProducts();
}

document.getElementById('dashSearchInput').addEventListener('input', renderDashProducts);

function selectProduct(id) {
    const p = allProducts.find(x => x.id == id);
    if (!p) return;
    activeProductId = id;
    document.querySelectorAll('.ad-product-item').forEach(el => el.classList.remove('active'));
    const item = document.getElementById(`prod-item-${id}`);
    if (item) item.classList.add('active');
    document.getElementById('adViewerEmpty').style.display = 'none';
    const content = document.getElementById('adViewerContent');
    content.style.display = 'flex';
    
    const isProductGuide = (p.category || '').trim() === 'Product Guides';
    const metaHtml = isProductGuide ?
        `<span class="ad-vinfo-badge">${esc(p.category)}</span>
         <span><i class="fas fa-book"></i> Product Guide</span>` :
        `<span class="ad-vinfo-badge">${esc(p.category)}</span>
         <span><i class="fas fa-user-clock"></i> ${esc(p.age_range)}</span>
         <span><i class="fas fa-coins"></i> Min. ${formatPHP(p.min_premium_monthly)}/mo</span>
         <span><i class="fas fa-calendar-alt"></i> ${esc(p.payment_type)} Pay</span>`;
    
    document.getElementById('adViewerInfo').innerHTML = `
        <div class="ad-vinfo-name">${esc(p.product_name)}</div>
        <div class="ad-vinfo-meta">
            ${metaHtml}
        </div>`;
    const pdfWrap = document.getElementById('adViewerPdf');
    if (p.primer_file) {
        const url = `../api/products/serve-pdf.php?file=${encodeURIComponent(p.primer_file)}`;
        const vid = `dash-pdf-${id}`;
        pdfWrap.innerHTML = buildPdfViewer(url, vid, p.primer_file);
        initPdfViewer(vid, url);
    } else {
        pdfWrap.innerHTML = `<div class="ad-no-pdf"><i class="fas fa-file-slash"></i><p>No product primer attached.</p>${p.description ? `<p style="font-size:13px;color:var(--pru-text);max-width:400px;margin-top:8px;">${esc(p.description)}</p>` : ''}</div>`;
    }
    if (window.innerWidth < 900) document.getElementById('adViewerPanel').scrollIntoView({ behavior:'smooth' });
}

/* ── Feeling Check Modal ── */
let selectedFeeling = null;

function initFeelingCheck() {
    // Check if feeling check was already done today
    const lastCheck = localStorage.getItem('lastFeelingCheck');
    const today = new Date().toDateString();
    
    if (lastCheck === today) {
        // Already checked today, skip
        return;
    }
    
    // Show feeling check modal after a short delay
    setTimeout(() => {
        document.getElementById('feelingModalOverlay').style.display = 'flex';
    }, 1000);
}

function selectFeeling(feeling, element) {
    selectedFeeling = feeling;
    
    // Update UI
    document.querySelectorAll('.feeling-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
    
    // Enable submit button
    document.getElementById('feelingSubmitBtn').disabled = false;
    
    // Show personalized message
    const messages = {
        'great': '🌟 That\'s wonderful! Keep that positive energy going. You\'re going to have an amazing day!',
        'good': '😊 Great to hear! Let\'s make today even better. You\'ve got this!',
        'okay': '👍 That\'s alright! Sometimes okay is perfectly fine. Take it one step at a time.',
        'tired': '💪 We all have those days. Remember to take breaks and stay hydrated. You\'re doing great!',
        'stressed': '🧘 Take a deep breath. You\'re stronger than you think. Break tasks into smaller steps and tackle them one by one.',
        'sad': '💙 It\'s okay to not be okay. Remember, tough times don\'t last, but tough people do. We\'re here for you!'
    };
    
    const messageDiv = document.getElementById('feelingMessage');
    const messageText = document.getElementById('feelingMessageText');
    messageText.textContent = messages[feeling] || 'Thank you for sharing!';
    messageDiv.classList.add('show');
}

function skipFeelingCheck() {
    document.getElementById('feelingModalOverlay').style.display = 'none';
    // Don't save to localStorage so it shows again next time
}

async function submitFeelingCheck() {
    if (!selectedFeeling) return;
    
    const btn = document.getElementById('feelingSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    try {
        // Save feeling to backend (optional - you can create an API endpoint for this)
        // For now, just save to localStorage
        localStorage.setItem('lastFeelingCheck', new Date().toDateString());
        localStorage.setItem('todayFeeling', selectedFeeling);
        
        // Show success message
        showToast('Thank you for sharing! Have a great day! 🌟', 'success');
        
        // Close modal
        setTimeout(() => {
            document.getElementById('feelingModalOverlay').style.display = 'none';
        }, 500);
        
    } catch (error) {
        console.error('Failed to save feeling:', error);
        showToast('Thank you for sharing!', 'success');
        document.getElementById('feelingModalOverlay').style.display = 'none';
    }
}

// Initialize feeling check on page load
document.addEventListener('DOMContentLoaded', () => {
    initFeelingCheck();
});

// ── Welcome Popup (First Login) ──
if (!sessionStorage.getItem('welcomeShown')) {
    setTimeout(() => {
        const welcomeOverlay = document.createElement('div');
        welcomeOverlay.id = 'welcomeOverlay';
        welcomeOverlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.85);backdrop-filter:blur(8px);z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px;animation:fadeIn 0.4s ease;';
        
        welcomeOverlay.innerHTML = `
            <div style="background:white;border-radius:24px;max-width:500px;width:100%;box-shadow:0 32px 80px rgba(0,0,0,0.4);animation:slideUp 0.5s ease;overflow:hidden;">
                <div style="background:linear-gradient(135deg,#1C1C1C 0%,#2a0010 60%,#D50032 100%);padding:40px 32px;text-align:center;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-50px;right:-50px;width:150px;height:150px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
                    <div style="position:absolute;bottom:-30px;left:-30px;width:100px;height:100px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
                    <div style="width:90px;height:90px;background:rgba(255,255,255,0.15);border-radius:22px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;position:relative;animation:pulse 2s infinite;">
                        <span style="font-size:40px;font-weight:900;color:white;letter-spacing:-2px;">eH</span>
                    </div>
                    <h2 style="font-size:28px;font-weight:900;color:white;margin:0 0 8px;letter-spacing:-0.5px;">Welcome to</h2>
                    <h3 style="font-size:32px;font-weight:900;color:white;margin:0 0 12px;letter-spacing:-1px;">Emerald Heart</h3>
                    <p style="font-size:14px;color:rgba(255,255,255,0.85);margin:0;line-height:1.6;">Official Website · PRU Life U.K.</p>
                </div>
                <div style="padding:32px;text-align:center;">
                    <p style="font-size:15px;color:#555;line-height:1.7;margin:0 0 24px;">
                        Your complete platform for managing insurance products, tracking performance, and connecting with PRU Life U.K. resources.
                    </p>
                    <button onclick="closeWelcome()" style="width:100%;padding:14px;background:#D50032;color:white;border:none;border-radius:12px;font-size:15px;font-weight:700;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 16px rgba(213,0,50,0.3);" onmouseover="this.style.background='#a8002a';this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#D50032';this.style.transform='translateY(0)'">
                        <i class="fas fa-arrow-right" style="margin-right:8px;"></i> Get Started
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(welcomeOverlay);
        sessionStorage.setItem('welcomeShown', 'true');
        
        window.closeWelcome = function() {
            welcomeOverlay.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => welcomeOverlay.remove(), 300);
        };
    }, 500);
}

</script>

<?php include '../includes/agent-footer.php'; ?>
