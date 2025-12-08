<?php
/**
 * Student Authentication Check
 * Validates student session and handles inactivity timeout
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if student is logged in
$isStudentLoggedIn = false;

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
    // Verify no conflicting session data (should not have org_id or teacher_id)
    if (!isset($_SESSION['org_id']) && !isset($_SESSION['teacher_id'])) {
        // Check login timestamp
        if (isset($_SESSION['login_timestamp'])) {
            $loginTime = $_SESSION['login_timestamp'];
            $currentTime = time();
            $sessionDuration = $currentTime - $loginTime;
            
            // Session expires after 8 hours
            if ($sessionDuration > 28800) {
                // Session expired
                session_destroy();
                $isStudentLoggedIn = false;
            } else {
                // Check inactivity timeout (15 minutes)
                if (isset($_SESSION['last_activity'])) {
                    $inactivityTime = $currentTime - $_SESSION['last_activity'];
                    if ($inactivityTime > 900) { // 15 minutes
                        session_destroy();
                        $isStudentLoggedIn = false;
                    } else {
                        // Update last activity
                        $_SESSION['last_activity'] = $currentTime;
                        $isStudentLoggedIn = true;
                    }
                } else {
                    $_SESSION['last_activity'] = $currentTime;
                    $isStudentLoggedIn = true;
                }
            }
        } else {
            // No login timestamp, invalid session
            session_destroy();
            $isStudentLoggedIn = false;
        }
    } else {
        // Conflicting session data
        session_destroy();
        $isStudentLoggedIn = false;
    }
}

// Redirect if not logged in
if (!$isStudentLoggedIn) {
    // Calculate relative path for redirect
    $currentDir = dirname($_SERVER['PHP_SELF']);
    $depth = substr_count($currentDir, '/') - 1;
    $redirectPath = str_repeat('../', max(0, $depth)) . 'login.php';
    
    session_destroy();
    header("Location: " . $redirectPath);
    exit();
}

// Regenerate session ID periodically for security (every 5 minutes)
// Only regenerate if user is active (has recent activity) to avoid unnecessary operations
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 300) {
    // Only regenerate if user has been active recently (within last 15 minutes)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) < 900) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
    } else {
        // Update regeneration time even if not regenerating to avoid repeated checks
        $_SESSION['last_regeneration'] = time();
    }
}
?>

