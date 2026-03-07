<?php
/**
 * Register a new user
 */
function registerUser($pdo, $name, $email, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $email, $hashedPassword]);
}

/**
 * Login a user
 */
function loginUser($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Prevent Session Fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'] ?? 'user'; // Phase 15 safe default
        
        // Handle Remember Me securely
        if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
            $token = hash_hmac('sha256', $user['id'] . $user['email'] . $user['password'], 'smart_archive_secret');
            setcookie('rem_usr', $user['id'], time() + (86400 * 30), "/");
            setcookie('rem_tok', $token, time() + (86400 * 30), "/");
        }

        return true;
    }
    return false;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
    setcookie('rem_usr', '', time() - 3600, "/");
    setcookie('rem_tok', '', time() - 3600, "/");
}

/**
 * Get user details by ID
 */
function getUserDetails($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, subscription_plan, storage_limit, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: dashboard.php?error=unauthorized");
        exit();
    }
}
/**
 * Log user activity
 */
function logActivity($pdo, $user_id, $action, $details = null) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $action, $details, $ip, $ua]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Escape HTML for output (XSS Protection)
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
