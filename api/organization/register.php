<?php
/**
 * Organization Registration API
 * Handles organization registration with database integration
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Include database connection
require_once '../../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$contact = trim($input['contact'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirmPassword'] ?? '';

// Basic validation
if (empty($name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Check password match
if ($password !== $confirmPassword) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Passwords do not match'
    ]);
    exit;
}

// Password length validation
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long'
    ]);
    exit;
}

try {
    // Get database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM organizations WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered. Please use a different email or login.'
        ]);
        exit;
    }
    
    // Insert organization into database
    $stmt = $conn->prepare("
        INSERT INTO organizations (name, email, password, contact, status, created_at)
        VALUES (?, ?, ?, ?, 'active', NOW())
    ");
    
    $result = $stmt->execute([
        $name,
        $email,
        $password,  // Storing plain password as requested (no hashing)
        $contact
    ]);
    
    if ($result) {
        $organizationId = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Organization registered successfully! Please login to continue.',
            'redirectUrl' => '../login.php',
            'data' => [
                'id' => $organizationId,
                'name' => $name,
                'email' => $email
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to register organization. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Organization Registration Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log("Organization Registration Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during registration. Please try again.'
    ]);
}
?>

