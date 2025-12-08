<?php
/**
 * Organization Activity Tracker API
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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if organization is logged in
// CRITICAL: Only check for Organization-specific session variables
// Student/Teacher sessions use user_id/role, which are different
$isOrgLoggedIn = isset($_SESSION['org_id']) || isset($_SESSION['org_email']);

// SECURITY: Ensure no Student/Teacher session data exists (extra validation)
// If user_id exists but org_id doesn't, this is a Student/Teacher session, not Organization
if (isset($_SESSION['user_id']) && !isset($_SESSION['org_id'])) {
    $isOrgLoggedIn = false;
}

// Check login timestamp
if ($isOrgLoggedIn && !isset($_SESSION['login_timestamp'])) {
    $isOrgLoggedIn = false;
}

// Check inactivity timeout (15 minutes)
if ($isOrgLoggedIn && isset($_SESSION['last_activity'])) {
    $inactivityTimeout = 900; // 15 minutes in seconds
    if (time() - $_SESSION['last_activity'] > $inactivityTimeout) {
        $isOrgLoggedIn = false;
    }
}

if (!$isOrgLoggedIn) {
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

// Update last activity timestamp (only if session is still valid)
if (isset($data['action']) && $data['action'] === 'update_activity') {
    // Update last activity time
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

