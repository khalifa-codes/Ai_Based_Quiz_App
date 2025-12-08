<?php
/**
 * Admin Logout
 * Destroys session and redirects to login page
 */

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

// Prevent caching of logout page
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

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

// Regenerate session ID to prevent session fixation
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
}

// Output JavaScript to clear client-side storage before redirect
// This ensures complete logout even if browser back button is used
echo '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Logging out...</title>
</head>
<body>
    <script>
        // Clear all sessionStorage and localStorage admin data
        sessionStorage.removeItem("admin_id");
        sessionStorage.removeItem("admin_email");
        sessionStorage.removeItem("admin_name");
        sessionStorage.removeItem("admin_role");
        
        // Prevent back button from working
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.go(1);
        };
        
        // Redirect to login page
        window.location.replace("./login.php");
    </script>
    <p>Logging out...</p>
</body>
</html>';
exit;

