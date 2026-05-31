<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}
$page_title = 'Export Data';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="pru-main">
    <div class="page-header">
        <h2>Export Data</h2>
        <p>Download system data as CSV files for reporting and analysis.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="pru-card text-center" style="padding:32px 24px;">
                <div style="width:64px;height:64px;background:rgba(213,0,50,0.08);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;color:var(--pru-red);">
                    <i class="fas fa-users"></i>
                </div>
                <h5 style="font-weight:700;margin-bottom:8px;">Agents Data</h5>
                <p style="font-size:13px;color:var(--pru-muted);margin-bottom:20px;">Export all agent records including codes, names, emails, positions, and status.</p>
                <a href="../api/export/export-data.php?type=agents" class="btn-pru" style="display:inline-flex;">
                    <i class="fas fa-download"></i> Export Agents
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="pru-card text-center" style="padding:32px 24px;">
                <div style="width:64px;height:64px;background:rgba(40,167,69,0.08);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;color:var(--pru-success);">
                    <i class="fas fa-box-open"></i>
                </div>
                <h5 style="font-weight:700;margin-bottom:8px;">Products Data</h5>
                <p style="font-size:13px;color:var(--pru-muted);margin-bottom:20px;">Export all product records including names, categories, premiums, and payment types.</p>
                <a href="../api/export/export-data.php?type=products" class="btn-pru" style="display:inline-flex;background:var(--pru-success);">
                    <i class="fas fa-download"></i> Export Products
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="pru-card text-center" style="padding:32px 24px;">
                <div style="width:64px;height:64px;background:rgba(23,162,184,0.08);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;color:var(--pru-info);">
                    <i class="fas fa-comments"></i>
                </div>
                <h5 style="font-weight:700;margin-bottom:8px;">Feedbacks Data</h5>
                <p style="font-size:13px;color:var(--pru-muted);margin-bottom:20px;">Export all agent feedback records including subjects, messages, and admin replies.</p>
                <a href="../api/export/export-data.php?type=feedbacks" class="btn-pru" style="display:inline-flex;background:var(--pru-info);">
                    <i class="fas fa-download"></i> Export Feedbacks
                </a>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
