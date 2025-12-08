<?php
/**
 * Organization Login API
 * Handles organization authentication with database integration
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
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Include database connection inside try-catch
    require_once '../../config/database.php';
    
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
    
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Please provide both email and password'
        ]);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }
    
    // Start session after validation
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Query organization by email
    $stmt = $conn->prepare("
        SELECT id, name, email, password, contact, status, created_at
        FROM organizations
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $organization = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if organization exists
    if (!$organization) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
    // Check if account is active
    if ($organization['status'] !== 'active') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Your account is ' . $organization['status'] . '. Please contact support.'
        ]);
        exit;
    }
    
    // Verify password (plain text comparison as per requirement)
    if ($password !== $organization['password']) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
    
    // Clear any existing session data
    session_unset();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Create organization session
    $_SESSION['org_id'] = $organization['id'];
    $_SESSION['org_name'] = $organization['name'];
    $_SESSION['org_email'] = $organization['email'];
    $_SESSION['role'] = 'organization';
    $_SESSION['login_timestamp'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Remove password from response
    unset($organization['password']);
    
    // Clear output buffer before sending response
    ob_end_clean();
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful! Welcome back.',
        'redirectUrl' => 'organization/dashboard.php',
        'user' => $organization
    ]);
    exit();
    
} catch (PDOException $e) {
    // Log the full error for debugging
    error_log("Organization Login PDO Error: " . $e->getMessage());
    error_log("Organization Login PDO Error Trace: " . $e->getTraceAsString());
    
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
    error_log("Organization Login Error: " . $e->getMessage());
    error_log("Organization Login Error Trace: " . $e->getTraceAsString());
    
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
    error_log("Organization Login Fatal Error: " . $e->getMessage());
    error_log("Organization Login Fatal Error Trace: " . $e->getTraceAsString());
    
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
