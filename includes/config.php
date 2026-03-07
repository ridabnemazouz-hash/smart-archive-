<?php
// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
session_start();
define('SESSION_TIMEOUT', 1800); 
if (isset($_SESSION['last_activity'])) {
    $elapsed = time() - $_SESSION['last_activity'];
    if ($elapsed > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start(); // Start fresh session
        $_SESSION['timeout_message'] = 'Your session has expired due to inactivity.';
    }
}
$_SESSION['last_activity'] = time();
require_once __DIR__ . '/lang.php';
if (isset($_GET['lang'])) {
    $allowed_langs = ['fr', 'ar', 'en'];
    if (in_array($_GET['lang'], $allowed_langs)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
}

// Default Language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smartarchive');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    // Disable emulated prepares for better security
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error internally but show generic message
    error_log("Database Connection Error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}

// Auto-login via Remember Me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['rem_usr']) && isset($_COOKIE['rem_tok'])) {
    $rem_usr = $_COOKIE['rem_usr'];
    $rem_tok = $_COOKIE['rem_tok'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$rem_usr]);
        $user = $stmt->fetch();
        if ($user) {
            $expected_tok = hash_hmac('sha256', $user['id'] . $user['email'] . $user['password'], 'smart_archive_secret');
            if (hash_equals($expected_tok, $rem_tok)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';
            } else {
                setcookie('rem_usr', '', time() - 3600, "/");
                setcookie('rem_tok', '', time() - 3600, "/");
            }
        }
    } catch(PDOException $e) {
        // Ignore during auto-login
    }
}
?>
