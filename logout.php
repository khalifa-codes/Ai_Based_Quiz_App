<?php
/**
 * Public Logout Handler
 * Handles logout for Organization, Teacher, and Student roles
 * Admin logout is handled by admin/logout.php
 */

// Use default public session (PHPSESSID) - separate from admin sessions
// Admin sessions use ADMINSESSID and are handled by admin/logout.php
// No need to check admin sessions here since they use different session names

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine redirect URL based on role before destroying session
// Check for organization session first (org_id or org_email)
$redirectUrl = 'login.php';

if (isset($_SESSION['org_id']) || isset($_SESSION['org_email'])) {
    // Organization logout - redirect to login page
    $redirectUrl = 'login.php';
} elseif (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    // All roles redirect to public login page
    $redirectUrl = 'login.php';
}

// Destroy all session data
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Ensure proper redirect with absolute path
// Get the base URL path
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['PHP_SELF']);

// Build absolute URL for login
$basePath = rtrim($scriptPath, '/');
$loginUrl = $protocol . $host . $basePath . '/login.php';

// Redirect to login page
header('Location: ' . $loginUrl);
exit;
?>

