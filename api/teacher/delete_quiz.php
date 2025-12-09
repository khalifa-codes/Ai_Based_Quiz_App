<?php
/**
 * API Endpoint: Delete Quiz
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
    $checkStmt = $conn->prepare("SELECT id, title FROM quizzes WHERE id = ? AND created_by = ?");
    $checkStmt->execute([$quizId, $teacherId]);
    $quiz = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quiz) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this quiz']);
        exit();
    }
    
    // Check if quiz has submissions
    $submissionStmt = $conn->prepare("SELECT COUNT(*) as count FROM quiz_submissions WHERE quiz_id = ?");
    $submissionStmt->execute([$quizId]);
    $submissionCount = $submissionStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($submissionCount > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete quiz with existing submissions. Please archive it instead.',
            'has_submissions' => true
        ]);
        exit();
    }
    
    // Delete quiz (cascade will handle questions and options)
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ? AND created_by = ?");
    $stmt->execute([$quizId, $teacherId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Quiz deleted successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Delete Quiz Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting quiz']);
}
?>

