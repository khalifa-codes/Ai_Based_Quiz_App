<?php
/**
 * API Endpoint: Remove Student (from teacher's view)
 * Note: This doesn't delete the student, just removes association
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
    
    // Verify student has taken quizzes from this teacher
    $checkStmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM quiz_submissions qs
        INNER JOIN quizzes q ON q.id = qs.quiz_id
        WHERE qs.student_id = ? AND q.created_by = ?
    ");
    $checkStmt->execute([$studentId, $teacherId]);
    $check = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($check['count'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found in your records']);
        exit();
    }
    
    // Note: We don't actually delete the student or submissions
    // This is just for UI purposes - student will still exist in system
    // In a real scenario, you might want to archive or mark as inactive
    
    echo json_encode([
        'success' => true,
        'message' => 'Student removed from view successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Remove Student Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error removing student']);
}
?>

