<?php
require_once __DIR__ . '/config/db.php';

$db = getDB();
$user = $db->query("SELECT password_hash FROM `user` WHERE email = 'admin@medibase.bd'")->fetch();

if (password_verify('Admin@1234', $user['password_hash'])) {
    echo "Password verification works!\n";
} else {
    echo "Still not working\n";
}
?>