<?php
/**
 * Finalize Quiz API
 * Finalize and submit entire quiz
 * 
 * POST /api/student/quiz/finalize.php
 * Body: { "submission_id": 123 }
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include required files
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../student/auth_check.php';
require_once __DIR__ . '/../../../includes/security_helpers.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'errors' => ['Only POST method is allowed']
    ]);
    exit;
}

// Check authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized',
        'errors' => ['Please login to finalize quiz']
    ]);
    exit;
}

// Step 3.4: Enhanced Session Security
$sessionCheck = validateSessionSecurity();
if (!$sessionCheck['valid']) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session expired',
        'errors' => [$sessionCheck['message']]
    ]);
    exit;
}

// Step 3.2: Rate Limiting
$studentId = intval($_SESSION['user_id']);
$rateLimit = checkRateLimit($studentId, 'student', 'finalize_quiz', 3, 60); // 3 requests per minute
if (!$rateLimit['allowed']) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Rate limit exceeded',
        'errors' => ['Too many requests. Please wait before trying again.'],
        'data' => [
            'reset_time' => $rateLimit['reset_time']
        ]
    ]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Step 3.1: CSRF Protection (optional - for backward compatibility)
if (isset($input['csrf_token'])) {
    if (!validateCSRFToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token',
            'errors' => ['Security token validation failed']
        ]);
        exit;
    }
}

// Step 3.3: Enhanced Input Validation & Sanitization
if (!isset($input['submission_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing submission_id',
        'errors' => ['submission_id is required']
    ]);
    exit;
}

// Validate and sanitize submission_id
$submissionId = validateInteger($input['submission_id'], 1);
if ($submissionId === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input',
        'errors' => ['Submission ID must be a valid positive integer']
    ]);
    exit;
}

// Check for SQL injection attempts
if (detectSQLInjection(strval($submissionId))) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input',
        'errors' => ['Suspicious input detected']
    ]);
    exit;
}

try {
    $dbInstance = Database::getInstance();
    $db = $dbInstance->getConnection();
    $db->beginTransaction();
    
    // Step 3.3: Validate submission ownership using helper
    $submission = validateSubmissionOwnership($submissionId, $studentId, $db);
    
    if (!$submission || $submission['status'] !== 'in_progress') {
        $db->rollBack();
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid submission',
            'errors' => ['Submission not found or already submitted']
        ]);
        exit;
    }
    
    // Get quiz details
    $stmt = $db->prepare("SELECT duration FROM quizzes WHERE id = ?");
    $stmt->execute([$submission['quiz_id']]);
    $quiz = $stmt->fetch();
    if (!$quiz) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Quiz not found',
            'errors' => ['Quiz does not exist']
        ]);
        exit;
    }
    $submission['duration'] = $quiz['duration'];
    
    // Calculate time taken
    $startTime = strtotime($submission['started_at']);
    $currentTime = time();
    $timeTaken = $currentTime - $startTime;
    
    // Ensure time taken doesn't exceed duration
    $timeTaken = min($timeTaken, $submission['duration']);
    
    // Calculate results for MCQ questions
    $stmt = $db->prepare("
        SELECT sa.question_id, sa.answer_value, q.marks, qo.is_correct
        FROM student_answers sa
        INNER JOIN questions q ON sa.question_id = q.id
        LEFT JOIN question_options qo ON q.id = qo.question_id AND sa.answer_value = qo.option_value
        WHERE sa.submission_id = ? AND sa.is_postponed = FALSE
        AND q.question_type IN ('multiple_choice', 'true_false')
    ");
    $stmt->execute([$submissionId]);
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalScore = 0;
    $totalMarks = 0;
    
    foreach ($answers as $answer) {
        $totalMarks += intval($answer['marks']);
        if ($answer['is_correct']) {
            $totalScore += intval($answer['marks']);
        }
    }
    
    // Calculate percentage
    $percentage = $totalMarks > 0 ? ($totalScore / $totalMarks) * 100 : 0;
    
    // Update submission status with results
    $stmt = $db->prepare("
        UPDATE quiz_submissions
        SET status = 'submitted',
            submitted_at = NOW(),
            time_taken = ?,
            total_score = ?,
            percentage = ?
        WHERE id = ?
    ");
    $stmt->execute([$timeTaken, $totalScore, $percentage, $submissionId]);
    
    // Step 3.4: Clear session variables securely
    unset($_SESSION['current_submission_id']);
    unset($_SESSION['quiz_start_time']);
    $_SESSION['last_activity'] = time();
    
    // Regenerate CSRF token after successful operation
    generateCSRFToken();
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Quiz submitted successfully',
        'data' => [
            'submission_id' => $submissionId,
            'quiz_id' => $submission['quiz_id'],
            'time_taken' => $timeTaken,
            'redirect_url' => "/student/quizzes/submit_quiz.php?quiz_id={$submission['quiz_id']}"
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Database error in finalize.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'errors' => ['Failed to finalize quiz']
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error in finalize.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'errors' => ['Failed to finalize quiz']
    ]);
}
?>

