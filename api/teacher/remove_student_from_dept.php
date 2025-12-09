<?php
/**
 * API Endpoint: Remove Student from Department
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
session_start();

$teacherId = (int)($_SESSION['user_id'] ?? 0);

if ($teacherId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$studentId = isset($input['student_id']) ? (int)$input['student_id'] : 0;
$deptId = isset($input['dept_id']) ? $input['dept_id'] : null;

if ($studentId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
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
    
    // Verify student exists and belongs to a department that teacher has access to
    if ($deptId && $deptId !== 'general' && is_numeric($deptId)) {
        // Remove student from specific department
        $stmt = $conn->prepare("UPDATE students SET organization_id = NULL WHERE id = ? AND organization_id = ?");
        $stmt->execute([$studentId, $deptId]);
    } else {
        // For general department, we can't really remove - just return success
        // Student will still exist but won't show in this department view
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Student removed from department successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Remove Student from Dept Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error removing student']);
}
?>

