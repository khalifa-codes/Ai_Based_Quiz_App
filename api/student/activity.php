<?php
/**
 * Student Activity API
 * Updates last_activity timestamp for student sessions
 */

header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if student is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Verify no conflicting session data
if (isset($_SESSION['org_id']) || isset($_SESSION['teacher_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid session']);
    exit();
}

// Check login timestamp
if (isset($_SESSION['login_timestamp'])) {
    $loginTime = $_SESSION['login_timestamp'];
    $currentTime = time();
    $sessionDuration = $currentTime - $loginTime;
    
    // Session expires after 8 hours
    if ($sessionDuration > 28800) {
        session_destroy();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Session expired']);
        exit();
    }
}

// Check inactivity timeout
if (isset($_SESSION['last_activity'])) {
    $inactivityTime = $currentTime - $_SESSION['last_activity'];
    if ($inactivityTime > 900) { // 15 minutes
        session_destroy();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Session expired due to inactivity']);
        exit();
    }
}

// Update last activity
$_SESSION['last_activity'] = time();

// Return success
echo json_encode(['success' => true, 'message' => 'Activity updated']);
?>

