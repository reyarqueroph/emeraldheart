<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    // Create tables if not exist
    $db->exec("CREATE TABLE IF NOT EXISTS accredited_clinics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        address VARCHAR(300) NOT NULL,
        region VARCHAR(100) NOT NULL,
        contact VARCHAR(100) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS email_directories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        department VARCHAR(200) NOT NULL,
        icon VARCHAR(100) DEFAULT 'fa-envelope',
        email VARCHAR(200) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed clinics if empty
    $cc = $db->query("SELECT COUNT(*) FROM accredited_clinics")->fetchColumn();
    if ($cc == 0) {
        $ins = $db->prepare("INSERT INTO accredited_clinics (name, address, region, contact, sort_order) VALUES (?,?,?,?,?)");
        $clinics = [
            ["St. Luke's Medical Center",                    "279 E. Rodriguez Sr. Blvd., Quezon City",  "NCR",        "(02) 8723-0101", 1],
            ["Makati Medical Center",                        "2 Amorsolo St., Makati City",               "NCR",        "(02) 8888-8999", 2],
            ["The Medical City",                             "Ortigas Ave., Pasig City",                  "NCR",        "(02) 8988-1000", 3],
            ["Asian Hospital and Medical Center",            "Filinvest Corporate City, Alabang",         "NCR",        "(02) 8771-9000", 4],
            ["Cardinal Santos Medical Center",               "10 Wilson St., San Juan City",              "NCR",        "(02) 8727-0001", 5],
            ["Jose R. Reyes Memorial Medical Center",        "Rizal Ave., Manila",                        "NCR",        "(02) 8711-9491", 6],
            ["Ospital ng Maynila",                           "Quirino Ave., Manila",                      "NCR",        "(02) 8524-6061", 7],
            ["Angeles University Foundation Medical Center", "MacArthur Highway, Angeles City",           "Region III", "(045) 625-2000", 8],
            ["Batangas Medical Center",                      "Kumintang Ibaba, Batangas City",            "Region IV-A","(043) 723-7777", 9],
            ["Chong Hua Hospital",                           "Don Mariano Cui St., Cebu City",            "Region VII", "(032) 255-8000", 10],
            ["Davao Doctors Hospital",                       "E. Quirino Ave., Davao City",               "Region XI",  "(082) 222-8000", 11],
        ];
        foreach ($clinics as $c) $ins->execute($c);
    }

    // Seed email directories if empty
    $ec = $db->query("SELECT COUNT(*) FROM email_directories")->fetchColumn();
    if ($ec == 0) {
        $ins = $db->prepare("INSERT INTO email_directories (department, icon, email, sort_order) VALUES (?,?,?,?)");
        $emails = [
            ["Customer Service",      "fa-headset",       "customerservice@prulifeuk.com.ph", 1],
            ["Claims Department",     "fa-file-invoice",  "claims@prulifeuk.com.ph",          2],
            ["Underwriting",          "fa-file-medical",  "underwriting@prulifeuk.com.ph",    3],
            ["Agency Services",       "fa-users",         "agencyservices@prulifeuk.com.ph",  4],
            ["Policy Services",       "fa-shield-alt",    "policyservices@prulifeuk.com.ph",  5],
            ["Finance / Billing",     "fa-coins",         "finance@prulifeuk.com.ph",         6],
            ["Compliance",            "fa-balance-scale", "compliance@prulifeuk.com.ph",      7],
            ["IT / Technical Support","fa-laptop-code",   "itsupport@prulifeuk.com.ph",       8],
        ];
        foreach ($emails as $e) $ins->execute($e);
    }

    $type = $_GET['type'] ?? 'clinics';

    if ($type === 'clinics') {
        $stmt = $db->query("SELECT * FROM accredited_clinics WHERE is_active=1 ORDER BY sort_order ASC, id ASC");
    } else {
        $stmt = $db->query("SELECT * FROM email_directories WHERE is_active=1 ORDER BY sort_order ASC, id ASC");
    }

    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
