<?php
/**
 * Check AI Evaluation Status
 * Returns whether AI evaluation is complete for a submission
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if student is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get submission ID
$submissionId = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : 0;

if ($submissionId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid submission ID']);
    exit();
}

require_once __DIR__ . '/../../../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $studentId = intval($_SESSION['user_id']);
    
    // Check if submission exists and belongs to student
    $stmt = $conn->prepare("
        SELECT id, status, total_ai_marks, ai_provider 
        FROM quiz_submissions 
        WHERE id = ? AND student_id = ?
    ");
    $stmt->execute([$submissionId, $studentId]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Submission not found']);
        exit();
    }
    
    // Check if there are subjective questions that need evaluation
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM student_answers sa
        INNER JOIN questions q ON sa.question_id = q.id
        WHERE sa.submission_id = ? 
        AND q.question_type = 'subjective'
        AND sa.answer_value IS NOT NULL
        AND sa.answer_value != ''
    ");
    $stmt->execute([$submissionId]);
    $subjectiveCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Check if AI evaluations exist
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM ai_evaluations
        WHERE submission_id = ?
    ");
    $stmt->execute([$submissionId]);
    $evaluationCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Evaluation is complete if:
    // 1. No subjective questions, OR
    // 2. All subjective questions have been evaluated
    $evaluationComplete = ($subjectiveCount == 0) || ($evaluationCount > 0 && $evaluationCount >= $subjectiveCount);
    
    echo json_encode([
        'success' => true,
        'submission_id' => $submissionId,
        'submission_status' => $submission['status'],
        'subjective_questions_count' => intval($subjectiveCount),
        'evaluations_count' => intval($evaluationCount),
        'evaluation_complete' => $evaluationComplete,
        'has_ai_marks' => !empty($submission['total_ai_marks'])
    ]);
    
} catch (Exception $e) {
    error_log("Error checking evaluation status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error checking evaluation status'
    ]);
}
?>

