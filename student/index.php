<?php
/**
 * Student Module Entry Point
 * Redirects to dashboard if authenticated, otherwise to login
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if student is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
    // Redirect to dashboard
    header("Location: dashboard.php");
    exit();
} else {
    // Redirect to login
    header("Location: ../login.php");
    exit();
}
?>

