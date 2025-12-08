<?php
/**
 * Teacher Activity Tracker API
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

// Check if teacher is logged in
// CRITICAL: Only check for Teacher-specific session variables
// Organization sessions use org_id/org_email, which are different
$isTeacherLoggedIn = isset($_SESSION['teacher_id']) || (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher');

// SECURITY: Ensure no Organization session data exists (extra validation)
// If org_id exists but teacher_id doesn't, this is an Organization session, not Teacher
if (isset($_SESSION['org_id']) && !isset($_SESSION['teacher_id']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher')) {
    $isTeacherLoggedIn = false;
}

// Check login timestamp
if ($isTeacherLoggedIn && !isset($_SESSION['login_timestamp'])) {
    $isTeacherLoggedIn = false;
}

// Check inactivity timeout (15 minutes)
if ($isTeacherLoggedIn && isset($_SESSION['last_activity'])) {
    $inactivityTimeout = 900; // 15 minutes in seconds
    if (time() - $_SESSION['last_activity'] > $inactivityTimeout) {
        $isTeacherLoggedIn = false;
    }
}

if (!$isTeacherLoggedIn) {
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

