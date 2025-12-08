<?php
/**
 * Admin Activity Tracker API
 * Updates last_activity timestamp in session
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Configure session to expire when browser closes
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

// CRITICAL: Use separate session name and path for admin to isolate from public sessions
session_name('ADMINSESSID');

// Get the base path for admin - detect from current script location
$scriptPath = $_SERVER['PHP_SELF'] ?? $_SERVER['SCRIPT_NAME'] ?? '';
// Extract the path up to and including /admin
if (preg_match('#(/[^/]*/admin|/admin)#', $scriptPath, $matches)) {
    $adminPath = $matches[1];
} else {
    // Fallback to /admin if pattern doesn't match
    $adminPath = '/admin';
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => $adminPath,
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
$hasSessionCookie = isset($_COOKIE[session_name()]) && !empty($_COOKIE[session_name()]);
$hasAdminSessionData = isset($_SESSION['admin_id']) || isset($_SESSION['admin_email']) || isset($_SESSION['admin_logged_in']);
$isAdminLoggedIn = $hasSessionCookie && $hasAdminSessionData;

// Check login timestamp
if ($isAdminLoggedIn && !isset($_SESSION['login_timestamp'])) {
    $isAdminLoggedIn = false;
}

// Check inactivity timeout (15 minutes)
if ($isAdminLoggedIn && isset($_SESSION['last_activity'])) {
    $inactivityTimeout = 900; // 15 minutes in seconds
    if (time() - $_SESSION['last_activity'] > $inactivityTimeout) {
        $isAdminLoggedIn = false;
    }
}

if (!$isAdminLoggedIn) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session expired'
    ]);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Update last activity timestamp
if (isset($data['action']) && $data['action'] === 'update_activity') {
    $_SESSION['last_activity'] = time();
    
    echo json_encode([
        'success' => true,
        'message' => 'Activity updated',
        'last_activity' => $_SESSION['last_activity']
    ]);
    exit;
}

// Invalid request
http_response_code(400);
echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
exit;

