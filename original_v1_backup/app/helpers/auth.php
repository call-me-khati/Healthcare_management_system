<?php
// app/helpers/auth.php
require_once __DIR__ . '/../../config/app.php';

/* ── Session bootstrap ─────────────────────────────────── */
function startAppSession(): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function loginUser(array $user): void {
    startAppSession();
    session_regenerate_id(true);
    $_SESSION['user_id']        = (int)$user['user_id'];
    $_SESSION['email']          = $user['email'];
    $_SESSION['role']           = $user['user_type'];
    $_SESSION['is_first_login'] = (bool)$user['is_first_login'];
    $_SESSION['full_name']      = $user['full_name'] ?? '';
    $_SESSION['logged_in']      = true;
}

function logoutUser(): void {
    startAppSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ── Guards ────────────────────────────────────────────── */
function requireLogin(): void {
    startAppSession();
    if (empty($_SESSION['logged_in'])) {
        header('Location: ' . BASE_URL . '/public/login.php');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        include __DIR__ . '/../views/shared/403.php';
        exit;
    }
}

function requireProfileComplete(): void {
    requireLogin();
    if (!empty($_SESSION['is_first_login'])) {
        $allowed = [
            BASE_URL . '/public/complete-profile.php',
            BASE_URL . '/public/logout.php',
        ];
        $current = BASE_URL . '/public/' . basename($_SERVER['PHP_SELF']);
        if (!in_array($current, $allowed, true)) {
            header('Location: ' . BASE_URL . '/public/complete-profile.php');
            exit;
        }
    }
}

function currentUser(): array {
    startAppSession();
    return [
        'user_id'        => $_SESSION['user_id']        ?? null,
        'email'          => $_SESSION['email']           ?? '',
        'role'           => $_SESSION['role']            ?? '',
        'is_first_login' => $_SESSION['is_first_login']  ?? false,
        'full_name'      => $_SESSION['full_name']        ?? '',
    ];
}

/* ── Password helpers ──────────────────────────────────── */
function hashPwd(string $plain): string {
    return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
}
function verifyPwd(string $plain, string $hash): bool {
    return password_verify($plain, $hash);
}
function isStrongPassword(string $pwd): bool {
    return strlen($pwd) >= PWD_MIN_LEN
        && preg_match('/[A-Z]/', $pwd)
        && preg_match('/[a-z]/', $pwd)
        && preg_match('/[0-9]/', $pwd);
}
function generateTempPassword(): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#!';
    $pwd = '';
    for ($i = 0; $i < TEMP_PWD_LEN; $i++) $pwd .= $chars[random_int(0, strlen($chars)-1)];
    return $pwd;
}

/* ── CSRF ─────────────────────────────────────────────── */
function csrfToken(): string {
    startAppSession();
    if (empty($_SESSION['csrf_token']))
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verifyCsrf(): void {
    startAppSession();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

/* ── Flash messages ─────────────────────────────────────── */
function flash(string $key, string $message): void {
    startAppSession();
    $_SESSION['flash'][$key] = $message;
}
function getFlash(string $key): ?string {
    startAppSession();
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

/* ── Input helpers ──────────────────────────────────────── */
function clean(mixed $v): string {
    return htmlspecialchars(strip_tags(trim((string)$v)), ENT_QUOTES, 'UTF-8');
}
function postStr(string $key, string $default = ''): string {
    return clean($_POST[$key] ?? $default);
}
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/* ── Audit logging ──────────────────────────────────────── */
function auditLog(string $action, ?string $table = null, ?int $recordId = null,
                  mixed $oldVal = null, mixed $newVal = null): void {
    try {
        require_once __DIR__ . '/../../config/db.php';
        $db = getDB();
        $userId = $_SESSION['user_id'] ?? null;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? null;
        $db->prepare(
            'INSERT INTO `audit_log`
               (user_id, action, table_name, record_id, old_value, new_value, ip_address)
             VALUES (?,?,?,?,?,?,?)')
        ->execute([
            $userId, $action, $table, $recordId,
            $oldVal ? json_encode($oldVal) : null,
            $newVal ? json_encode($newVal) : null,
            $ip
        ]);
    } catch (Exception $e) { /* silent fail */ }
}
