<?php
/**
 * Teacher Registration API
 * Handles teacher registration with database integration
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
$firstName = trim($input['firstName'] ?? '');
$lastName = trim($input['lastName'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$organizationId = isset($input['organization_id']) ? intval($input['organization_id']) : null;

// Basic validation
if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
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
    $stmt = $conn->prepare("SELECT id FROM teachers WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered. Please use a different email or login.'
        ]);
        exit;
    }
    
    // Insert teacher into database
    $name = $firstName . ' ' . $lastName;
    $stmt = $conn->prepare("
        INSERT INTO teachers (name, email, password_hash, organization_id, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $name,
        $email,
        $password,  // Storing plain password as requested (no hashing)
        $organizationId
    ]);
    
    if ($result) {
        $teacherId = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Teacher registered successfully! Please login to continue.',
            'redirectUrl' => '../login.php',
            'data' => [
                'id' => $teacherId,
                'name' => $name,
                'email' => $email
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to register teacher. Please try again.'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Teacher Registration Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    error_log("Teacher Registration Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during registration. Please try again.'
    ]);
}
?>

