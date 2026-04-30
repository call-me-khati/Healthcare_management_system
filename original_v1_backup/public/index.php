<?php
// public/index.php
// Auto-detect base URL for redirect
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$base   = rtrim(dirname($script), '/');
header('Location: ' . $base . '/login.php');
exit;
