<?php
// Fix password hashes - regenerate with correct Admin@1234 password
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/helpers/auth.php';

$db = getDB();

// Generate correct hash for Admin@1234
$correctHash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);

echo "<h2>🔧 Regenerating Password Hashes</h2>";
echo "Correct hash for 'Admin@1234': <code>$correctHash</code><br><br>";

// Update ALL users with the correct hash
$result = $db->prepare('UPDATE `user` SET password_hash = ?')->execute([$correctHash]);

echo "✅ Updated all user passwords!<br><br>";

// Verify the fix
echo "<h2>✅ Verifying Fix:</h2>";
$users = $db->query("SELECT email FROM `user` LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
foreach ($users as $email) {
    $st = $db->prepare('SELECT password_hash FROM `user` WHERE email = ?');
    $st->execute([$email]);
    $hash = $st->fetchColumn();
    $verified = password_verify('Admin@1234', $hash);
    echo "$email: " . ($verified ? "✅ PASS" : "❌ FAIL") . "<br>";
}

echo "<br><strong>All test accounts now use password: <code>Admin@1234</code></strong><br>";
echo "Try logging in now! 🚀";
?>
