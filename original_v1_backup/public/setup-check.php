<?php
/**
 * MediBase Setup Checker
 * Visit: http://localhost/medibase_uni/public/setup-check.php
 * DELETE this file after confirming everything works!
 */
$results = [];

// 1. PHP version
$phpOk = version_compare(PHP_VERSION, '7.4', '>=');
$results[] = [$phpOk, 'PHP Version', PHP_VERSION . ($phpOk ? ' ✓' : ' — needs 7.4+')];

// 2. PDO MySQL extension
$pdoOk = extension_loaded('pdo_mysql');
$results[] = [$pdoOk, 'PDO MySQL Extension', $pdoOk ? 'Enabled' : 'MISSING — enable pdo_mysql in php.ini'];

// 3. Sessions
session_start();
$_SESSION['test'] = 1;
$sessOk = isset($_SESSION['test']);
$results[] = [$sessOk, 'PHP Sessions', $sessOk ? 'Working' : 'Not working'];

// 4. Database connection
require_once __DIR__ . '/../config/db.php';
try {
    $db = getDB();
    $results[] = [true, 'Database Connection', 'Connected to ' . DB_NAME . ' on ' . DB_HOST];

    // 5. Check tables exist
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['user','doctors','nurses','students','appointments','availability',
                 'consultations','medicines','lab_tests','follow_ups','notifications','feedback','audit_log'];
    $missing = array_diff($required, $tables);
    if (empty($missing)) {
        $results[] = [true,  'Database Tables', count($tables).' tables found — all required tables present'];
    } else {
        $results[] = [false, 'Database Tables', 'MISSING tables: ' . implode(', ', $missing) . ' — re-import medibase_enhanced.sql'];
    }

    // 6. Check seed data
    $userCount = $db->query("SELECT COUNT(*) FROM `user`")->fetchColumn();
    $results[] = [$userCount > 0, 'Seed Data', $userCount > 0 ? "$userCount users found" : 'No users — re-import medibase_enhanced.sql'];

} catch (Exception $e) {
    $results[] = [false, 'Database Connection', $e->getMessage()];
    $results[] = [false, 'Database Tables', 'Skipped — fix DB connection first'];
    $results[] = [false, 'Seed Data',       'Skipped'];
}

// 7. BASE_URL auto-detect
require_once __DIR__ . '/../config/app.php';
$results[] = [!empty(BASE_URL) || BASE_URL === '', 'BASE_URL Auto-detected', BASE_URL === '' ? '/ (root install)' : BASE_URL];

// 8. Write permissions (sessions)
$tmpOk = is_writable(sys_get_temp_dir());
$results[] = [$tmpOk, 'Temp Directory Writable', $tmpOk ? sys_get_temp_dir() : 'Not writable — check server permissions'];

$allOk = !in_array(false, array_column($results, 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MediBase Setup Check</title>
<style>
body{font-family:system-ui,sans-serif;max-width:700px;margin:50px auto;padding:0 20px;background:#f8fafc;color:#1e293b}
h1{font-size:22px;margin-bottom:4px}
.sub{color:#64748b;font-size:14px;margin-bottom:28px}
.card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden}
.row{display:flex;align-items:center;gap:12px;padding:13px 18px;border-bottom:1px solid #f1f5f9;font-size:14px}
.row:last-child{border-bottom:none}
.icon{font-size:18px;flex-shrink:0;width:24px;text-align:center}
.label{font-weight:600;flex:0 0 200px}
.value{color:#475569;flex:1}
.banner{padding:16px 20px;border-radius:10px;font-size:14px;font-weight:600;margin-bottom:20px;text-align:center}
.ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.fail{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.login-box{margin-top:24px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:20px}
table{width:100%;border-collapse:collapse;font-size:13px}
th{text-align:left;padding:8px 12px;background:#f8fafc;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.05em}
td{padding:8px 12px;border-top:1px solid #f1f5f9}
code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px}
.warn{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;font-size:13px;margin-top:20px;color:#92400e}
</style>
</head>
<body>
<h1>🏥 MediBase Setup Check</h1>
<p class="sub">Verifying your installation is correct</p>

<div class="banner <?= $allOk ? 'ok' : 'fail' ?>">
  <?= $allOk ? '✅ All checks passed! Your installation is ready.' : '❌ Some checks failed. See details below.' ?>
</div>

<div class="card">
  <?php foreach ($results as [$ok, $label, $value]): ?>
  <div class="row">
    <div class="icon"><?= $ok ? '✅' : '❌' ?></div>
    <div class="label"><?= htmlspecialchars($label) ?></div>
    <div class="value"><?= htmlspecialchars($value) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<?php if ($allOk): ?>
<div class="login-box">
  <strong style="font-size:15px">🚀 Ready! Go to: <a href="<?= BASE_URL ?>/public/login.php"><?= BASE_URL ?>/public/login.php</a></strong>
  <table style="margin-top:14px">
    <tr><th>Role</th><th>Email</th><th>Password</th></tr>
    <tr><td>Admin</td><td><code>admin@medibase.bd</code></td><td><code>Admin@1234</code></td></tr>
    <tr><td>Doctor</td><td><code>dr.ayesha@medibase.bd</code></td><td><code>Admin@1234</code></td></tr>
    <tr><td>Nurse</td><td><code>nurse.salma@medibase.bd</code></td><td><code>Admin@1234</code></td></tr>
    <tr><td>Student</td><td><code>farida@student.uni</code></td><td><code>Admin@1234</code></td></tr>
  </table>
</div>
<?php endif; ?>

<div class="warn">
  ⚠️ <strong>Delete this file after setup!</strong> 
  Remove <code>public/setup-check.php</code> once you confirm everything works. 
  It exposes system information.
</div>
</body>
</html>
