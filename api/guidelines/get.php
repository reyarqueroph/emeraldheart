<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Create table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS guidelines (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section VARCHAR(100) NOT NULL,
        title VARCHAR(200) NOT NULL,
        description TEXT DEFAULT NULL,
        pdf_file VARCHAR(255) DEFAULT NULL,
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Seed Underwriting guidelines
    $uwCount = $db->query("SELECT COUNT(*) FROM guidelines WHERE section='underwriting'")->fetchColumn();
    if ($uwCount == 0) {
        $defaults = [
            ['underwriting', 'Financial Underwriting',    'Guidelines for assessing financial capacity and sum assured limits.', 1],
            ['underwriting', 'Occupational Underwriting', 'Risk classification based on the applicant\'s occupation.',           2],
            ['underwriting', 'Standard Height & Weight',  'BMI-based build assessment for underwriting decisions.',              3],
            ['underwriting', 'Territorial Underwriting',  'Guidelines for applicants based on residency and travel history.',    4],
        ];
        $ins = $db->prepare("INSERT INTO guidelines (section, title, description, sort_order) VALUES (?,?,?,?)");
        foreach ($defaults as $d) $ins->execute($d);
    }

    // Seed Policy guidelines
    $polCount = $db->query("SELECT COUNT(*) FROM guidelines WHERE section='policy'")->fetchColumn();
    if ($polCount == 0) {
        $defaults = [
            ['policy', 'FATCA Guidelines',                          'Overview of FATCA requirements and agent responsibilities.',                    1],
            ['policy', 'FATCA Form W9 – 27 May 2017',              'Request for Taxpayer Identification Number and Certification (US persons).',    2],
            ['policy', 'FATCA Form W8 BEN (Individual) – 7 May 2017', 'Certificate of Foreign Status of Beneficial Owner (non-US persons).',       3],
            ['policy', 'Acceptable Beneficiaries',                  'Guidelines on who may be named as policy beneficiaries.',                      4],
            ['policy', 'Acceptable Valid IDs',                      'List of government-issued IDs accepted for policy applications.',               5],
            ['policy', 'Non-Medical Authority',                     'Sum assured limits that do not require a medical examination.',                 6],
            ['policy', 'Policy Replacement',                        'Rules and requirements when replacing an existing policy.',                     7],
        ];
        $ins = $db->prepare("INSERT INTO guidelines (section, title, description, sort_order) VALUES (?,?,?,?)");
        foreach ($defaults as $d) $ins->execute($d);
    }

    $section = $_GET['section'] ?? 'underwriting';
    $stmt = $db->prepare("SELECT * FROM guidelines WHERE section=:s AND is_active=1 ORDER BY sort_order ASC, id ASC");
    $stmt->execute([':s' => $section]);

    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
