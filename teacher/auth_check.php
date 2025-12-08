<?php
/**
 * Teacher Authentication Check
 * Include this file at the top of protected teacher pages
 */

// Prevent caching of teacher pages - force fresh authentication check
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

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

// CRITICAL: Check login timestamp - session must have been created during this browser session
if ($isTeacherLoggedIn && !isset($_SESSION['login_timestamp'])) {
    // Old session without login timestamp - invalidate it
    $isTeacherLoggedIn = false;
}

// CRITICAL: Inactivity timeout - session expires after 15 minutes of no activity
if ($isTeacherLoggedIn && isset($_SESSION['last_activity'])) {
    // If last activity was more than 15 minutes ago (900 seconds), invalidate session
    $inactivityTimeout = 900; // 15 minutes in seconds
    if (time() - $_SESSION['last_activity'] > $inactivityTimeout) {
        $isTeacherLoggedIn = false;
    } else {
        // Update last activity time on each page access
        $_SESSION['last_activity'] = time();
    }
}

if (!$isTeacherLoggedIn) {
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
    
    // Redirect to login page if not authenticated
    // Calculate correct path to login.php from current location
    $scriptPath = $_SERVER['PHP_SELF'];
    $scriptDir = dirname($scriptPath);
    
    // Extract path after /teacher/ to determine depth
    if (preg_match('#/teacher(/.*)?$#', $scriptDir, $matches)) {
        $pathAfterTeacher = isset($matches[1]) ? trim($matches[1], '/') : '';
        
        if (empty($pathAfterTeacher)) {
            // We're in teacher root
            $loginPath = '../login.php';
        } else {
            // We're in a subdirectory - count levels and build relative path
            $depth = substr_count($pathAfterTeacher, '/') + 1;
            $loginPath = str_repeat('../', $depth) . 'login.php';
        }
    } else {
        // Fallback - should not happen, but use relative path
        $loginPath = '../login.php';
    }
    
    header('Location: ' . $loginPath);
    exit;
}

// Session variables are set by the login API
// No need to set dummy values here - they come from actual login

