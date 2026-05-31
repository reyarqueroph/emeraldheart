<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
require_once '../config/database.php';

try {
    $database = new Database();
    $db       = $database->getConnection();

    // ── Create tables ──────────────────────────────────────────────
    $db->exec("CREATE TABLE IF NOT EXISTS service_sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_key VARCHAR(100) NOT NULL UNIQUE,
        title VARCHAR(200) NOT NULL,
        icon VARCHAR(100) DEFAULT 'fa-file-alt',
        category ENUM('new-business','after-sales','claims') NOT NULL,
        description TEXT DEFAULT NULL,
        external_url VARCHAR(500) DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS service_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        description TEXT DEFAULT NULL,
        pdf_file VARCHAR(255) DEFAULT NULL,
        external_url VARCHAR(500) DEFAULT NULL,
        item_type ENUM('step','document','link','info') DEFAULT 'document',
        sort_order INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (section_id) REFERENCES service_sections(id) ON DELETE CASCADE
    )");

    // ── Seed default sections ──────────────────────────────────────
    $cnt = $db->query("SELECT COUNT(*) FROM service_sections")->fetchColumn();
    if ($cnt == 0) {
        $ins = $db->prepare("INSERT INTO service_sections (section_key,title,icon,category,description,external_url,sort_order) VALUES (?,?,?,?,?,?,?)");
        $sections = [
            ['manual',            'Manual',                      'fa-book',           'new-business', 'Step-by-step guide for processing new business applications manually.',                                    null,                                                                                    1],
            ['pruone',            'PruOne',                      'fa-chart-pie',      'new-business', 'Digital platform for submitting new business applications online.',                                       'https://pruone.prulifeuk.com.ph',                                                       2],
            ['auto-debit',        'Auto-Debit',                  'fa-sync-alt',       'new-business', 'Auto-debit arrangement forms and instructions for automatic premium payment.',                            null,                                                                                    3],
            ['premium-payment',   'Premium Payment Facilities',  'fa-credit-card',    'after-sales',  'View all available premium payment channels and instructions.',                                           'https://www.prulifeuk.com.ph/en/policy-services-information/premium-payment-facilities/', 4],
            ['after-sales-forms', 'Common After-Sales Forms',    'fa-file-alt',       'after-sales',  'Downloadable forms for common after-sales transactions.',                                                 null,                                                                                    5],
            ['death-claim',       'Death Claim',                 'fa-cross',          'claims',       'Requirements and process for filing a death claim.',                                                      'https://www.prulifeuk.com.ph/en/claims/',                                               6],
            ['medical-claim',     'Medical Claim',               'fa-hospital',       'claims',       'Requirements and process for filing a medical/hospitalization claim.',                                    'https://www.prulifeuk.com.ph/en/claims/',                                               7],
            ['ci-claim',          'Critical Illness Claim',      'fa-heartbeat',      'claims',       'Requirements and process for filing a critical illness claim.',                                           'https://www.prulifeuk.com.ph/en/claims/',                                               7],
            ['tpd-claim',         'TPD & Accidental Disablement','fa-wheelchair',     'claims',       'Requirements for Total Permanent Disability and Accidental Disablement claims.',                         'https://www.prulifeuk.com.ph/en/claims/',                                               8],
        ];
        foreach ($sections as $s) $ins->execute($s);

        // Seed items for Manual
        $secId = $db->lastInsertId();
        // Get manual section id
        $manualId = $db->query("SELECT id FROM service_sections WHERE section_key='manual'")->fetchColumn();
        $insItem  = $db->prepare("INSERT INTO service_items (section_id,title,description,item_type,sort_order) VALUES (?,?,?,?,?)");
        $manualSteps = [
            ['Complete the Application Form',    'Ensure all sections of the application form are filled out accurately and completely by the client.',                    'step', 1],
            ['Gather Required Documents',        'Collect valid IDs, proof of income, and any other required supporting documents.',                                       'step', 2],
            ['Health Declaration',               'Client must answer all health questions truthfully. Medical exam may be required based on age and sum assured.',         'step', 3],
            ['Submit to Branch / Head Office',   'Submit the complete application package to the nearest PRU Life U.K. branch or through your unit manager.',             'step', 4],
            ['Follow Up on Application Status',  'Monitor the application status and coordinate with underwriting for any additional requirements.',                       'step', 5],
            ['Policy Delivery',                  'Upon approval, deliver the policy to the client and explain the coverage, benefits, and exclusions.',                   'step', 6],
        ];
        foreach ($manualSteps as $step) $insItem->execute(array_merge([$manualId], $step));

        // Seed PruOne steps
        $pruoneId = $db->query("SELECT id FROM service_sections WHERE section_key='pruone'")->fetchColumn();
        $pruoneSteps = [
            ['Log in to PruOne',           'Access PruOne at pruone.prulifeuk.com.ph using your agent credentials.',                                    'step', 1],
            ['Create New Application',     'Click "New Business" and select the appropriate product for your client.',                                  'step', 2],
            ['Fill Out e-Application',     'Complete all required fields including client information, health declaration, and beneficiary details.',    'step', 3],
            ['Upload Documents',           'Upload scanned copies of valid IDs and other required documents.',                                          'step', 4],
            ['Submit for Underwriting',    'Review and submit the application. Track status directly on the PruOne dashboard.',                         'step', 5],
        ];
        foreach ($pruoneSteps as $step) $insItem->execute(array_merge([$pruoneId], $step));

        // Seed After-Sales Forms
        $formsId = $db->query("SELECT id FROM service_sections WHERE section_key='after-sales-forms'")->fetchColumn();
        $forms = [
            'Additional Beneficiary Form','Agency Movement Form','Agent Confidential Information',
            'Amendment of Application Form','Contest Appeal Form','Change of Servicing Agent',
            'Customer Information Update','Irrevocable Beneficiary Form','KYC for Beneficial Owner',
            'KYP for Third Party Payor','Mode of Release Form','Policy Amendment Request Form',
            'Policy Surrender Form','Reinstatement (Form & Checklist)',
            'Request for Printed Copy of Policy','Specimen Signature Form',
        ];
        foreach ($forms as $i => $f) {
            $insItem->execute([$formsId, $f, 'Downloadable form for '.$f.'.', 'document', $i+1]);
        }

        // Seed claim docs
        $claimDocs = [
            'death-claim'   => [
                ['Claim Notification Form',                  'Duly accomplished and signed by the claimant/beneficiary.'],
                ['Original Policy Contract',                 'Submit the original policy document.'],
                ['Certified True Copy of Death Certificate', 'Issued by the Philippine Statistics Authority (PSA).'],
                ['Valid ID of Claimant',                     'Government-issued photo ID of the beneficiary filing the claim.'],
                ['Proof of Relationship',                    'Birth certificate, marriage certificate, or other legal documents.'],
                ['Medical Records (if applicable)',          'Hospital records, attending physician\'s statement for illness-related deaths.'],
                ['Police Report / Autopsy Report',           'Required for accidental or unnatural deaths.'],
            ],
            'medical-claim' => [
                ['Claim Notification Form',              'Duly accomplished and signed by the insured or authorized representative.'],
                ['Attending Physician\'s Statement',     'Completed by the treating physician with diagnosis and treatment details.'],
                ['Hospital / Medical Bills',             'Original official receipts and itemized billing statements.'],
                ['Laboratory and Diagnostic Results',    'All test results, imaging reports, and other diagnostic documents.'],
                ['Discharge Summary',                    'Hospital discharge summary for in-patient claims.'],
                ['Valid ID of Claimant',                 'Government-issued photo ID of the insured or authorized claimant.'],
            ],
            'ci-claim'      => [
                ['Claim Notification Form',          'Duly accomplished and signed by the insured.'],
                ['Attending Physician\'s Statement', 'Must confirm the diagnosis of the covered critical illness.'],
                ['Pathology / Biopsy Report',        'Required for cancer and tumor-related claims.'],
                ['Complete Medical Records',         'All relevant hospital records, test results, and treatment history.'],
                ['Valid ID of Claimant',             'Government-issued photo ID of the insured.'],
            ],
            'tpd-claim'     => [
                ['Claim Notification Form',          'Duly accomplished and signed by the insured or authorized representative.'],
                ['Attending Physician\'s Statement', 'Must confirm the nature and permanence of the disability.'],
                ['Complete Medical Records',         'All hospital records, diagnostic results, and treatment history.'],
                ['Police Report',                    'Required for accidental disablement claims.'],
                ['Proof of Disability',              'PWD ID, government certification, or specialist assessment.'],
                ['Valid ID of Claimant',             'Government-issued photo ID of the insured or authorized claimant.'],
            ],
        ];
        foreach ($claimDocs as $key => $docs) {
            $sid = $db->query("SELECT id FROM service_sections WHERE section_key='$key'")->fetchColumn();
            foreach ($docs as $i => [$t, $d]) $insItem->execute([$sid, $t, $d, 'document', $i+1]);
        }
    }

    // ── Ensure Auto-Debit section exists (for existing installs) ──
    $db->exec("INSERT IGNORE INTO service_sections (section_key,title,icon,category,description,external_url,sort_order)
               VALUES ('auto-debit','Auto-Debit','fa-sync-alt','new-business','Auto-debit arrangement forms and instructions for automatic premium payment.',NULL,3)");

    // ── Return data ────────────────────────────────────────────────
    $sections = $db->query("SELECT * FROM service_sections WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
    $items    = $db->query("SELECT * FROM service_items WHERE is_active=1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Group items by section_id
    $grouped = [];
    foreach ($items as $item) $grouped[$item['section_id']][] = $item;
    foreach ($sections as &$sec) $sec['items'] = $grouped[$sec['id']] ?? [];

    echo json_encode(['success' => true, 'data' => $sections]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
