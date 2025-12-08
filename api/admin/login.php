<?php
/**
 * Admin Login API Endpoint
 * Handles admin authentication
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

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required'
    ]);
    exit;
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = $data['password'];
$role = isset($data['role']) ? $data['role'] : 'admin';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// ===== BACKEND INTEGRATION POINT =====
// Replace this section with your actual database authentication logic
// Example:
// 
// $admin = getAdminByEmail($email);
// if ($admin && password_verify($password, $admin['password'])) {
//     // Set session
//     $_SESSION['admin_id'] = $admin['id'];
//     $_SESSION['admin_email'] = $admin['email'];
//     $_SESSION['admin_role'] = 'admin';
//     
//     echo json_encode([
//         'success' => true,
//         'message' => 'Login successful',
//         'redirectUrl' => '../admin/dashboard.php',
//         'user' => [
//             'id' => $admin['id'],
//             'email' => $admin['email'],
//             'name' => $admin['name']
//         ]
//     ]);
//     exit;
// }

// For now, simulate a successful login for testing
// TODO: Replace with actual database authentication

// Dummy credentials for testing (remove in production)
$dummyAdminEmail = 'hamza@gmail.com';
$dummyAdminPassword = 'Hamza123';

if ($email === $dummyAdminEmail && $password === $dummyAdminPassword) {
    // Configure session to expire when browser closes
    ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
    ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session cookie
    ini_set('session.use_only_cookies', 1); // Only use cookies for session management
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    
    // CRITICAL: Use separate session name and path for admin to isolate from public sessions
    session_name('ADMINSESSID'); // Separate session name for admin
    
    // Set session cookie path to /admin so it's accessible from all admin pages
    // The API is at /api/admin/ but the cookie needs to be accessible from /admin/ pages
    $adminPath = '/admin';
    
    session_set_cookie_params([
        'lifetime' => 0, // Expires when browser closes
        'path' => $adminPath, // Cookie accessible from /admin path
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    // Start session for authentication
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    
    // CRITICAL: After regenerating session ID, explicitly set cookie with lifetime 0
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), 0, // 0 = expires when browser closes
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // Set session variables (in production, get these from database)
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_name'] = 'Admin User';
    $_SESSION['admin_role'] = 'admin';
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['login_timestamp'] = time(); // CRITICAL: Track when login occurred
    $_SESSION['last_activity'] = time(); // Track last activity time
    $_SESSION['last_page_access'] = 0; // Initialize - will be set on first page access
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? ''; // Track IP for security
    
    // Simulate successful login
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirectUrl' => 'dashboard.php',
        'user' => [
            'id' => 1,
            'email' => $email,
            'name' => 'Admin User',
            'role' => 'admin'
        ]
    ]);
    exit;
}

// Invalid credentials
http_response_code(401);
echo json_encode([
    'success' => false,
    'message' => 'Invalid email or password'
]);
exit;

