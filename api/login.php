<?php
/**
 * Login API Endpoint
 * Handles login for Teacher and Student roles with database integration
 */

// Start output buffering to prevent any output before JSON
ob_start();

// Set error reporting (disable display, enable logging)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Include database connection inside try-catch
    require_once '../config/database.php';
    
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        throw new Exception('No input data received');
    }
    
    $input = json_decode($rawInput, true);
    
    // Check for JSON parsing errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON format: ' . json_last_error_msg());
    }
    
    // Validate input
    if (!isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    $role = $input['role'] ?? 'teacher';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }
    
    // Validate role
    if (!in_array($role, ['teacher', 'student'])) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        exit();
    }
    
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Query user based on role
    if ($role === 'teacher') {
        $stmt = $conn->prepare("
            SELECT id, name, email, password_hash, organization_id, created_at
            FROM teachers
            WHERE email = ?
        ");
    } else {
        // Student
        $stmt = $conn->prepare("
            SELECT id, name, email, password_hash, student_id, organization_id, created_at
            FROM students
            WHERE email = ?
        ");
    }
    
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists
    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit();
    }
    
    // Verify password (plain text comparison as per requirement)
    if ($password !== $user['password_hash']) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit();
    }
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // CRITICAL: Clear any Organization session data to prevent conflicts
    // Student/Teacher use user_id, email, role
    // Organization uses org_id, org_email, org_name
    unset($_SESSION['org_id']);
    unset($_SESSION['org_email']);
    unset($_SESSION['org_name']);
    
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    
    // Set Student/Teacher session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $role;
    $_SESSION['login_timestamp'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Set role-specific session variables
    if ($role === 'teacher') {
        $_SESSION['teacher_id'] = $user['id'];
        if (isset($user['organization_id'])) {
            $_SESSION['organization_id'] = $user['organization_id'];
        }
    } elseif ($role === 'student') {
        $_SESSION['student_id'] = $user['id'];
        if (isset($user['student_id'])) {
            $_SESSION['student_id_number'] = $user['student_id'];
        }
        if (isset($user['organization_id'])) {
            $_SESSION['organization_id'] = $user['organization_id'];
        }
    }
    
    // Determine redirect URL based on role
    $redirectUrl = '';
    if ($role === 'teacher') {
        $redirectUrl = 'teacher/dashboard.php';
    } elseif ($role === 'student') {
        $redirectUrl = 'student/dashboard.php';
    }
    
    // Remove password from response
    unset($user['password_hash']);
    
    // Clear output buffer before sending response
    ob_end_clean();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful! Welcome back.',
        'redirectUrl' => $redirectUrl,
        'user' => $user
    ]);
    exit();
    
} catch (PDOException $e) {
    // Log the full error for debugging
    error_log("Login PDO Error: " . $e->getMessage());
    error_log("Login PDO Error Trace: " . $e->getTraceAsString());
    
    // Clear any output
    ob_end_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.',
        'error' => (ini_get('display_errors') ? $e->getMessage() : null)
    ]);
    exit();
    
} catch (Exception $e) {
    // Log the full error for debugging
    error_log("Login Error: " . $e->getMessage());
    error_log("Login Error Trace: " . $e->getTraceAsString());
    
    // Clear any output
    ob_end_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during login. Please try again.',
        'error' => (ini_get('display_errors') ? $e->getMessage() : null)
    ]);
    exit();
    
} catch (Error $e) {
    // Catch PHP 7+ Error exceptions (fatal errors)
    error_log("Login Fatal Error: " . $e->getMessage());
    error_log("Login Fatal Error Trace: " . $e->getTraceAsString());
    
    // Clear any output
    ob_end_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A system error occurred. Please contact support.',
        'error' => (ini_get('display_errors') ? $e->getMessage() : null)
    ]);
    exit();
}
?>
