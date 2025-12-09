<?php
/**
 * API Endpoint: Delete Department
 * Removes a department/organization
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

$input = json_decode(file_get_contents('php://input'), true);
$deptId = $input['dept_id'] ?? null;

if (empty($deptId) || $deptId === 'general') {
    echo json_encode(['success' => false, 'message' => 'Invalid department ID']);
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
    
    // Check if department exists and has no active students/quizzes
    if (is_numeric($deptId)) {
        $checkStmt = $conn->prepare("
            SELECT COUNT(DISTINCT s.id) as student_count, COUNT(DISTINCT q.id) as quiz_count
            FROM organizations o
            LEFT JOIN students s ON s.organization_id = o.id
            LEFT JOIN quizzes q ON q.organization_id = o.id AND q.created_by = ?
            WHERE o.id = ?
        ");
        $checkStmt->execute([$teacherId, $deptId]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && ((int)$result['student_count'] > 0 || (int)$result['quiz_count'] > 0)) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete department with active students or quizzes']);
            exit();
        }
        
        // Delete the organization
        $deleteStmt = $conn->prepare("DELETE FROM organizations WHERE id = ?");
        $deleteStmt->execute([$deptId]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Department removed successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Delete Department Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting department: ' . $e->getMessage()]);
}
?>

