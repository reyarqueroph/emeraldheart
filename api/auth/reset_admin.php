<?php
// ============================================================
// SETUP UTILITY — Run once, then DELETE this file
// ============================================================
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$accounts = [
    [
        'agent_code' => 'ADMIN001',
        'username'   => 'admin',
        'email'      => 'admin@prulifeuk.com.ph',
        'password'   => 'admin123',
        'full_name'  => 'System Administrator',
        'position'   => 'Admin',
    ],
    [
        'agent_code' => 'EHEART001',
        'username'   => 'eheart_admin',
        'email'      => 'eheart@prulifeuk.com.ph',
        'password'   => 'eHeart@2024!',
        'full_name'  => 'eHeart Administrator',
        'position'   => 'Admin',
    ],
];

// Delete existing admin accounts and re-insert with fresh hashes
$db->exec("DELETE FROM users WHERE role = 'admin'");

$stmt = $db->prepare("INSERT INTO users 
    (agent_code, username, email, password, full_name, position, role, status)
    VALUES (:ac, :un, :em, :pw, :fn, :pos, 'admin', 'active')");

foreach ($accounts as $acc) {
    $stmt->execute([
        ':ac'  => $acc['agent_code'],
        ':un'  => $acc['username'],
        ':em'  => $acc['email'],
        ':pw'  => password_hash($acc['password'], PASSWORD_DEFAULT),
        ':fn'  => $acc['full_name'],
        ':pos' => $acc['position'],
    ]);
}

// Also insert test agent if not exists
$chk = $db->prepare("SELECT id FROM users WHERE agent_code = 'AG-00001'");
$chk->execute();
if ($chk->rowCount() === 0) {
    $db->prepare("INSERT INTO users (agent_code, username, email, password, full_name, position, role, status)
        VALUES ('AG-00001','testagent','testagent@prulifeuk.com.ph',:pw,'Test Agent','Agent','agent','active')")
        ->execute([':pw' => password_hash('agent123', PASSWORD_DEFAULT)]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>eHeart – Setup</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f6fa; margin: 0; padding: 40px 20px; }
        .card { background: white; border-radius: 14px; padding: 32px; max-width: 520px; margin: 0 auto; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        .logo { width: 48px; height: 48px; background: #D50032; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 900; color: white; letter-spacing: -1px; margin-bottom: 20px; }
        h2 { color: #1C1C1C; font-size: 20px; font-weight: 800; margin: 0 0 6px; }
        p  { color: #777; font-size: 13px; margin: 0 0 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th { background: #f5f6fa; padding: 10px 14px; font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; border-bottom: 1px solid #eee; }
        tbody td { padding: 12px 14px; font-size: 13px; border-bottom: 1px solid #f5f5f5; color: #333; }
        tbody tr:last-child td { border-bottom: none; }
        code { background: #f5f6fa; padding: 2px 8px; border-radius: 5px; font-size: 13px; color: #D50032; font-weight: 700; }
        .warn { background: #fff8f0; border: 1px solid #ffd0a0; border-radius: 10px; padding: 14px 16px; font-size: 12px; color: #a05000; margin-top: 20px; display: flex; gap: 10px; align-items: flex-start; }
        .btn { display: inline-flex; align-items: center; gap: 8px; background: #D50032; color: white; padding: 11px 22px; border-radius: 9px; text-decoration: none; font-size: 13px; font-weight: 700; margin-top: 20px; }
        .btn:hover { background: #a8002a; }
        .check { color: #28a745; font-size: 18px; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">eH</div>
    <h2>✓ Setup Complete</h2>
    <p>Admin accounts have been created with fresh password hashes.</p>

    <table>
        <thead>
            <tr><th>Account</th><th>Username</th><th>Password</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>System Admin</td>
                <td><code>admin</code></td>
                <td><code>admin123</code></td>
            </tr>
            <tr>
                <td>eHeart Admin</td>
                <td><code>eheart_admin</code></td>
                <td><code>eHeart@2024!</code></td>
            </tr>
            <tr>
                <td>Test Agent</td>
                <td><code>AG-00001</code></td>
                <td><code>agent123</code></td>
            </tr>
        </tbody>
    </table>

    <div class="warn">
        ⚠️ <div><strong>Delete this file immediately after use!</strong><br>
        <code>pru_life_system/api/auth/reset_admin.php</code></div>
    </div>

    <a href="../../index.php" class="btn">→ Go to Login</a>
</div>
</body>
</html>
