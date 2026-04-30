<?php
/**
 * Comprehensive Login Fix Script
 * Run this file in browser to diagnose and fix login issues
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Login Fix Tool</title>";
echo "<style>
body { font-family: 'Segoe UI', sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
h1 { color: #0d6efd; }
h2 { color: #333; margin-top: 30px; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
.box { background: #fff; padding: 20px; border-radius: 8px; margin: 15px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.success { color: #198754; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
.warning { color: #fd7e14; font-weight: bold; }
.btn { display: inline-block; padding: 10px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; margin: 5px; }
.btn:hover { background: #0a58ca; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #f8f9fa; }
code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }
</style></head><body>";
echo "<h1>🔧 MediBase Login Fix Tool</h1>";

// ============================================================
// STEP 1: Check Database Connection
// ============================================================
echo "<h2>Step 1: Database Connection</h2>";
echo "<div class='box'>";

try {
    require_once __DIR__ . '/config/db.php';
    $db = getDB();
    echo "<span class='success'>✅ Database connected successfully!</span><br>";
    echo "Database: <code>" . DB_NAME . "</code><br>";
    echo "Host: <code>" . DB_HOST . ":" . DB_PORT . "</code>";
} catch (Exception $e) {
    echo "<span class='error'>❌ Database connection failed!</span><br>";
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<br><strong>To fix:</strong>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Create database: <code>CREATE DATABASE medibase_uni;</code></li>";
    echo "<li>Import the SQL file from phpMyAdmin</li>";
    echo "</ul>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// ============================================================
// STEP 2: Check if users exist
// ============================================================
echo "<h2>Step 2: User Records</h2>";
echo "<div class='box'>";

$users = $db->query("SELECT user_id, email, user_type, full_name FROM `user` ORDER BY user_id")->fetchAll();

if (empty($users)) {
    echo "<span class='warning'>⚠️ No users found in database!</span><br>";
    echo "<strong>Solution:</strong> Import the SQL file to populate seed data.<br>";
    echo "Import <code>medibase_enhanced.sql</code> (for v1) or <code>database/medibase_v2.sql</code> (for v2)";
} else {
    echo "<span class='success'>✅ Found " . count($users) . " user(s) in database</span><br>";
    echo "<table><tr><th>ID</th><th>Email</th><th>Type</th><th>Name</th></tr>";
    foreach ($users as $u) {
        echo "<tr><td>{$u['user_id']}</td><td>{$u['email']}</td><td>{$u['user_type']}</td><td>{$u['full_name']}</td></tr>";
    }
    echo "</table>";
}
echo "</div>";

// ============================================================
// STEP 3: Fix Password Hashes
// ============================================================
echo "<h2>Step 3: Password Verification</h2>";
echo "<div class='box'>";

$testPassword = 'Admin@1234';
$correctHash = password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 12]);

echo "Testing password: <code>$testPassword</code><br><br>";

$allFixed = true;
foreach ($users as $u) {
    $st = $db->prepare('SELECT password_hash FROM `user` WHERE user_id = ?');
    $st->execute([$u['user_id']]);
    $hash = $st->fetchColumn();
    
    $verified = password_verify($testPassword, $hash);
    
    if (!$verified) {
        echo "<span class='error'>❌ {$u['email']} - Password does NOT match</span><br>";
        
        // Fix the password
        $db->prepare('UPDATE `user` SET password_hash = ? WHERE user_id = ?')
           ->execute([$correctHash, $u['user_id']]);
        echo "   → Fixed! Updated hash.<br>";
        $allFixed = false;
    } else {
        echo "<span class='success'>✅ {$u['email']} - Password OK</span><br>";
    }
}

if ($allFixed && !empty($users)) {
    echo "<br><span class='success'>All passwords are correct!</span>";
} elseif (!empty($users)) {
    echo "<br><span class='success'>✅ All passwords have been fixed!</span>";
}
echo "</div>";

// ============================================================
// STEP 4: Test Login Flow
// ============================================================
echo "<h2>Step 4: Test Login</h2>";
echo "<div class='box'>";

if (!empty($users)) {
    $testUser = $users[0];
    echo "Testing login with: <code>{$testUser['email']}</code><br><br>";
    
    // Simulate login
    $st = $db->prepare('SELECT * FROM `user` WHERE email = ?');
    $st->execute([$testUser['email']]);
    $user = $st->fetch();
    
    if ($user) {
        $pwdMatch = password_verify($testPassword, $user['password_hash']);
        echo "User found: ✅<br>";
        echo "Password match: " . ($pwdMatch ? "✅ YES" : "❌ NO") . "<br>";
        
        if ($pwdMatch) {
            echo "<br><span class='success'>🎉 Login should work! Try logging in now.</span>";
        }
    }
} else {
    echo "<span class='warning'>No users to test</span>";
}
echo "</div>";

// ============================================================
// STEP 5: Quick Login Links
// ============================================================
echo "<h2>Step 5: Quick Actions</h2>";
echo "<div class='box'>";
echo "<a href='login.php' class='btn'>Go to Login Page</a>";
echo "<a href='?fix=1' class='btn' style='background:#fd7e14;'>Force Fix All Passwords</a>";
echo "<br><br>";
echo "<strong>Test Credentials:</strong><br>";
echo "<ul>";
foreach ($users as $u) {
    echo "<li>{$u['user_type']}: {$u['email']} / {$testPassword}</li>";
}
echo "</ul>";
echo "</div>";

// ============================================================
// Force fix if requested
// ============================================================
if (isset($_GET['fix'])) {
    echo "<h2>Force Fix Applied</h2>";
    echo "<div class='box'>";
    
    $newHash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);
    $db->prepare('UPDATE `user` SET password_hash = ?')->execute([$newHash]);
    
    echo "<span class='success'>✅ All passwords reset to: Admin@1234</span><br>";
    echo "New hash: <code>" . substr($newHash, 0, 30) . "...</code>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color:#666;text-align:center;'>MediBase University v2.0 - Login Fix Tool</p>";
echo "</body></html>";
?>