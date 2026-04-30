<?php
// fix_password.php - Run this once to fix the admin password
require_once __DIR__ . '/config/db.php';

$newPassword = 'Admin@1234';
$hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $db = getDB();
    
    // Update all user passwords to Admin@1234
    $stmt = $db->prepare("UPDATE `user` SET password_hash = ?");
    $stmt->execute([$hash]);
    
    echo "Passwords updated successfully!<br>";
    echo "New hash: " . $hash . "<br>";
    echo "<br>You can now login with:<br>";
    echo "- Email: admin@medibase.bd<br>";
    echo "- Password: Admin@1234<br>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}