<?php
// config/db.php  —  Database connection
// ── Edit these values to match your MySQL setup ───────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'medibase_uni');   // Must match the imported SQL database name
define('DB_USER',    'root');
define('DB_PASS',    '');               // XAMPP default: blank. WAMP: blank. Change if needed.
define('DB_PORT',    '3306');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = "mysql:host=".DB_HOST.";port=".DB_PORT
          .";dbname=".DB_NAME.";charset=".DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Show a helpful error instead of a cryptic PHP crash
        http_response_code(500);
        die('<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:30px;
             border:1px solid #fca5a5;border-radius:10px;background:#fef2f2;color:#991b1b">
             <h2>⚠️ Database Connection Failed</h2>
             <p style="margin-top:10px"><strong>Error:</strong> '.htmlspecialchars($e->getMessage()).'</p>
             <hr style="margin:16px 0;border-color:#fecaca">
             <p><strong>Fix:</strong> Open <code>config/db.php</code> and check:</p>
             <ul style="margin-top:8px;line-height:2">
               <li>DB_NAME is <code>medibase_uni</code> (imported from medibase_enhanced.sql)</li>
               <li>DB_USER is <code>root</code></li>
               <li>DB_PASS is blank for standard XAMPP</li>
               <li>MySQL/MariaDB is running in XAMPP Control Panel</li>
             </ul>
        </div>');
    }
    return $pdo;
}
