<?php
/**
 * Admin Authentication Check
 * Include this file at the top of protected admin pages
 */

// Configure session to expire when browser closes
ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session cookie
ini_set('session.use_only_cookies', 1); // Only use cookies for session management
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.gc_maxlifetime', 3600); // Session data lifetime (1 hour max, but cookie expires on browser close)

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
    'lifetime' => 0, // CRITICAL: 0 means cookie expires when browser closes
    'path' => $adminPath, // Only accessible in /admin path
    'domain' => '',
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Prevent caching of admin pages - force fresh authentication check
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// CRITICAL: Check if session cookie actually exists in the request
// If browser was closed, the cookie won't exist even if session file exists on server
$sessionCookieName = session_name();
$hasSessionCookie = isset($_COOKIE[$sessionCookieName]) && !empty($_COOKIE[$sessionCookieName]);

// Check if admin is logged in AND session cookie exists AND has required session variables
$hasAdminSessionData = isset($_SESSION['admin_id']) || isset($_SESSION['admin_email']) || isset($_SESSION['admin_logged_in']);
$isAdminLoggedIn = $hasSessionCookie && $hasAdminSessionData;

// CRITICAL: Check login timestamp - session must have been created during this browser session
if ($isAdminLoggedIn) {
    // Check if login_timestamp exists (set during login)
    if (!isset($_SESSION['login_timestamp'])) {
        // Old session without login timestamp - invalidate it
        $isAdminLoggedIn = false;
    } else {
        // Check if session is too old (more than 8 hours - should never happen with browser-close sessions, but safety check)
        $sessionAge = time() - $_SESSION['login_timestamp'];
        if ($sessionAge > 28800) { // 8 hours
            $isAdminLoggedIn = false;
        }
    }
}

// CRITICAL: Implement 1-minute cooldown for re-access after tab closure
// This checks if the user is trying to access a page too soon after a potential tab closure
// Skip cooldown check if last_page_access is 0 (initial value from login) or not set
if ($isAdminLoggedIn && isset($_SESSION['last_page_access']) && $_SESSION['last_page_access'] > 0) {
    $timeSinceLastAccess = time() - $_SESSION['last_page_access'];
    // If the gap is between 5 seconds and 60 seconds, it suggests a tab was closed and reopened quickly
    // This is a heuristic to prevent immediate re-access without full re-login
    if ($timeSinceLastAccess >= 5 && $timeSinceLastAccess <= 60) {
        $isAdminLoggedIn = false;
    }
}

// Update last page access time for session tracking (only if still logged in)
if ($isAdminLoggedIn) {
    $_SESSION['last_page_access'] = time();
}

// CRITICAL: Inactivity timeout - session expires after 15 minutes of no activity
if ($isAdminLoggedIn && isset($_SESSION['last_activity'])) {
    // If last activity was more than 15 minutes ago (900 seconds), invalidate session
    $inactivityTimeout = 900; // 15 minutes in seconds
    if (time() - $_SESSION['last_activity'] > $inactivityTimeout) {
        $isAdminLoggedIn = false;
    } else {
        // Update last activity time on each page access
        $_SESSION['last_activity'] = time();
    }
}

// CRITICAL: IP address validation - session must be from same IP (prevents session hijacking)
if ($isAdminLoggedIn && isset($_SESSION['ip_address'])) {
    $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($currentIP !== $_SESSION['ip_address']) {
        // IP address changed - invalidate session for security
        $isAdminLoggedIn = false;
    }
}

if (!$isAdminLoggedIn) {
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
    
    // Destroy the session
    session_destroy();
    
    // Redirect to admin login page if not authenticated
    // Calculate correct path to login.php from current location
    $scriptPath = $_SERVER['PHP_SELF'];
    $scriptDir = dirname($scriptPath);
    
    // Extract path after /admin/ to determine depth
    if (preg_match('#/admin(/.*)?$#', $scriptDir, $matches)) {
        $pathAfterAdmin = isset($matches[1]) ? trim($matches[1], '/') : '';
        
        if (empty($pathAfterAdmin)) {
            // We're in admin root
            $loginPath = 'login.php';
        } else {
            // We're in a subdirectory - count levels and build relative path
            $depth = substr_count($pathAfterAdmin, '/') + 1;
            $loginPath = str_repeat('../', $depth) . 'login.php';
        }
    } else {
        // Fallback - should not happen, but use relative path
        $loginPath = 'login.php';
    }
    
    header('Location: ' . $loginPath);
    exit;
}

// Session variables are set by the login API
// No need to set dummy values here - they come from actual login

