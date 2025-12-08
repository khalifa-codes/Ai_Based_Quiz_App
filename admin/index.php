<?php
/**
 * Admin Module Entry Point
 * Redirects to login if not authenticated, otherwise to dashboard
 */

// Configure session to expire when browser closes
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);

// CRITICAL: Use separate session name and path for admin to isolate from public sessions
session_name('ADMINSESSID'); // Separate session name for admin

// Get the base path for admin - detect from current script location
$scriptPath = $_SERVER['PHP_SELF'];
// Extract the path up to and including /admin
if (preg_match('#(/[^/]*/admin|/admin)#', $scriptPath, $matches)) {
    $adminPath = $matches[1];
} else {
    // Fallback to /admin if pattern doesn't match
    $adminPath = '/admin';
}

session_set_cookie_params([
    'lifetime' => 0, // Expires when browser closes
    'path' => $adminPath, // Only accessible in /admin path (dynamically calculated)
    'domain' => '',
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// CRITICAL: After session_start(), explicitly set cookie with lifetime 0 to ensure it expires on browser close
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), session_id(), 0, // 0 = expires when browser closes
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// CRITICAL: Check if session cookie actually exists in the request
$sessionCookieName = session_name();
$hasSessionCookie = isset($_COOKIE[$sessionCookieName]) && !empty($_COOKIE[$sessionCookieName]);
$hasAdminSessionData = isset($_SESSION['admin_id']) || isset($_SESSION['admin_email']) || isset($_SESSION['admin_logged_in']);

// Check if admin is logged in AND session cookie exists
$isAdminLoggedIn = $hasSessionCookie && $hasAdminSessionData;

// CRITICAL: Check login timestamp - session must have been created during this browser session
if ($isAdminLoggedIn && !isset($_SESSION['login_timestamp'])) {
    // Old session without login timestamp - invalidate it
    $isAdminLoggedIn = false;
}

// Also check if session has last activity and if it's too old
if ($isAdminLoggedIn && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > 3600) {
        $isAdminLoggedIn = false;
    } else {
        $_SESSION['last_activity'] = time();
    }
}

if ($isAdminLoggedIn) {
    // Redirect to dashboard if already logged in
    header('Location: dashboard.php');
    exit;
} else {
    // Clear any residual session data
    $_SESSION = array();
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    // Redirect to admin login page if not authenticated
    header('Location: login.php');
    exit;
}

