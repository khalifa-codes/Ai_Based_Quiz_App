<?php
/**
 * Get Quiz Result with AI Evaluation
 * Returns quiz submission results including AI evaluation data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : (isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0);

if ($quizId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid quiz ID is required']);
    exit();
}

$studentId = (int)$_SESSION['user_id'];

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../student/includes/student_data_helper.php';

try {
    $db = Database::getInstance()->getConnection();

    $submissionStmt = $db->prepare("
        SELECT id
        FROM quiz_submissions
        WHERE quiz_id = ? AND student_id = ?
        ORDER BY COALESCE(submitted_at, started_at) DESC
        LIMIT 1
    ");
    $submissionStmt->execute([$quizId, $studentId]);
    $submissionRow = $submissionStmt->fetch(PDO::FETCH_ASSOC);

    if (!$submissionRow) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Quiz submission not found']);
        exit();
    }

    $detail = fetchSubmissionDetail($db, (int)$submissionRow['id'], $studentId);
    if (!$detail) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Result details unavailable']);
        exit();
    }

    $responseData = [
        'quiz' => [
            'id' => $detail['submission']['quiz_id'],
            'title' => $detail['submission']['title'],
            'subject' => $detail['submission']['subject'],
            'total_marks' => $detail['submission']['total_marks'],
            'total_questions' => $detail['submission']['total_questions'],
        ],
        'submission' => $detail['submission'],
        'summary' => $detail['summary'],
        'questions' => $detail['questions'],
    ];

    echo json_encode(['success' => true, 'data' => $responseData], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('get_quiz_result error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch result.']);
}
?>

