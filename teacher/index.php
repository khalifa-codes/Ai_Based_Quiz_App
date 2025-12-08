<?php
/**
 * Teacher Module Entry Point
 * Redirects to login if not authenticated, or dashboard if authenticated
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if teacher is logged in
$isTeacherLoggedIn = isset($_SESSION['teacher_id']) || (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher');

if ($isTeacherLoggedIn) {
    // Teacher is logged in, redirect to dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Teacher is not logged in, redirect to login
    header('Location: ../login.php');
    exit;
}
?>

