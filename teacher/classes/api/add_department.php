<?php
/**
 * API Endpoint: Add Department
 * Creates a new department/organization for the teacher
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$teacherId = (int)($_SESSION['user_id'] ?? 0);

if ($teacherId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$deptName = trim($_POST['dept_name'] ?? '');
$deptCode = trim($_POST['dept_code'] ?? '');
$deptDescription = trim($_POST['dept_description'] ?? '');

if (empty($deptName)) {
    echo json_encode(['success' => false, 'message' => 'Department name is required']);
    exit();
}

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    
    // Check if department with same name already exists
    $checkStmt = $conn->prepare("SELECT id FROM organizations WHERE name = ?");
    $checkStmt->execute([$deptName]);
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Department with this name already exists']);
        exit();
    }
    
    // Create new organization/department
    $stmt = $conn->prepare("
        INSERT INTO organizations (name, email, password, contact, address, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    // Generate a unique email for the department
    $deptEmail = strtolower(str_replace(' ', '', $deptName)) . '@department.local';
    $deptPassword = password_hash(uniqid(), PASSWORD_DEFAULT);
    $contact = $deptCode ? $deptCode : '';
    $address = $deptDescription ? $deptDescription : '';
    
    $stmt->execute([$deptName, $deptEmail, $deptPassword, $contact, $address]);
    $deptId = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Department created successfully',
        'department_id' => $deptId
    ]);
    
} catch (Exception $e) {
    error_log('Add Department Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error creating department: ' . $e->getMessage()]);
}
?>

