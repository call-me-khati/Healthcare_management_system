<?php
// config/app.php

define('APP_NAME',    'MediBase University Health');
define('APP_VERSION', '2.0.0');

// ── Auto-detect BASE_URL from the folder name ─────────────────
// Works regardless of what you renamed the folder to in htdocs/
if (!defined('BASE_URL')) {
    // Get the subfolder path up to /public/
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    // Remove everything from /public/ onward
    $base = preg_replace('#/public(/.*)?$#', '', $script);
    // Remove trailing slash
    define('BASE_URL', rtrim($base, '/'));
}

define('SESSION_NAME',     'mb_uni_sess');
define('SESSION_LIFETIME', 7200);   // 2 hours

define('ROLES', ['admin','doctor','nurse','student']);
define('PWD_MIN_LEN', 8);
define('TEMP_PWD_LEN', 10);
