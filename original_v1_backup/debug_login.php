<?php
// Debug login issue
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/app/helpers/auth.php';

$db = getDB();

// 1. Check what users exist
echo "<h2>Users in Database:</h2>";
$users = $db->query("SELECT user_id, email, password_hash, user_type FROM `user` LIMIT 10")->fetchAll();
foreach ($users as $u) {
    echo "ID: {$u['user_id']}, Email: {$u['email']}, Type: {$u['user_type']}<br>";
    echo "Hash: " . substr($u['password_hash'], 0, 20) . "...<br><br>";
}

// 2. Test with admin account
echo "<h2>Testing Admin Login:</h2>";
$testEmail = 'admin@medibase.bd';
$testPassword = 'Admin@1234';

$user = $db->prepare('SELECT * FROM `user` WHERE email = ?')->execute([$testEmail]);
$st = $db->prepare('SELECT * FROM `user` WHERE email = ?');
$st->execute([$testEmail]);
$user = $st->fetch();

if (!$user) {
    echo "❌ User not found with email: $testEmail<br>";
} else {
    echo "✅ User found: {$user['email']}<br>";
    echo "Stored hash: {$user['password_hash']}<br>";
    
    // Test password verification
    $matches = password_verify($testPassword, $user['password_hash']);
    echo "Password verification: " . ($matches ? "✅ PASS" : "❌ FAIL") . "<br>";
    
    // Try hashing the test password to see what we get
    $newHash = password_hash($testPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    echo "New hash generated: $newHash<br>";
    echo "New hash verifies: " . (password_verify($testPassword, $newHash) ? "✅ YES" : "❌ NO") . "<br>";
    
    // Check hash length
    echo "Stored hash length: " . strlen($user['password_hash']) . "<br>";
    echo "New hash length: " . strlen($newHash) . "<br>";
}
?>
