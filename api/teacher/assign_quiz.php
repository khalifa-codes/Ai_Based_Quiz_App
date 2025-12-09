<?php
/**
 * API Endpoint: Assign Quiz to Department
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
$quizId = isset($input['quiz_id']) ? (int)$input['quiz_id'] : 0;
$deptId = isset($input['dept_id']) ? $input['dept_id'] : null;

if ($quizId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
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
    
    // Verify quiz belongs to teacher
    $checkStmt = $conn->prepare("SELECT id FROM quizzes WHERE id = ? AND created_by = ?");
    $checkStmt->execute([$quizId, $teacherId]);
    if (!$checkStmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to assign this quiz']);
        exit();
    }
    
    // Update quiz organization_id
    if ($deptId && $deptId !== 'general' && is_numeric($deptId)) {
        $stmt = $conn->prepare("UPDATE quizzes SET organization_id = ?, updated_at = NOW() WHERE id = ? AND created_by = ?");
        $stmt->execute([$deptId, $quizId, $teacherId]);
    } else {
        // Unassign from department
        $stmt = $conn->prepare("UPDATE quizzes SET organization_id = NULL, updated_at = NOW() WHERE id = ? AND created_by = ?");
        $stmt->execute([$quizId, $teacherId]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Quiz assigned successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Assign Quiz Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error assigning quiz']);
}
?>

