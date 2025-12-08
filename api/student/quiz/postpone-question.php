<?php
/**
 * Postpone Question API
 * Mark a question as postponed for later
 * 
 * POST /api/student/quiz/postpone-question.php
 * Body: { "submission_id": 123, "question_id": 1, "answer": "2" }
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
        'errors' => ['Please login to postpone question']
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
$rateLimit = checkRateLimit($studentId, 'student', 'postpone_question', 10, 60); // 10 requests per minute
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
if (!isset($input['submission_id']) || !isset($input['question_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields',
        'errors' => ['submission_id and question_id are required']
    ]);
    exit;
}

// Validate and sanitize IDs
$submissionId = validateInteger($input['submission_id'], 1);
$questionId = validateInteger($input['question_id'], 1);

if ($submissionId === false || $questionId === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input',
        'errors' => ['Submission ID and Question ID must be valid positive integers']
    ]);
    exit;
}

// Sanitize answer if provided
$answer = isset($input['answer']) ? sanitizeInput(trim($input['answer'])) : null;

// Check for SQL injection attempts
if ($answer && detectSQLInjection($answer)) {
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
            'errors' => ['Submission not found or quiz already submitted']
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
    
    // Validate time
    $startTime = strtotime($submission['started_at']);
    $currentTime = time();
    $elapsedTime = $currentTime - $startTime;
    
    if ($elapsedTime > $submission['duration']) {
        // Time expired
        $stmt = $db->prepare("
            UPDATE quiz_submissions
            SET status = 'auto_submitted',
                submitted_at = NOW(),
                time_taken = ?,
                auto_submitted = TRUE,
                auto_submit_reason = 'time_expired'
            WHERE id = ?
        ");
        $stmt->execute([$elapsedTime, $submissionId]);
        $db->commit();
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Time expired',
            'errors' => ['Quiz time has expired'],
            'data' => ['auto_submitted' => true]
        ]);
        exit;
    }
    
    // Step 3.3: Validate question belongs to quiz using helper
    if (!validateQuestionBelongsToQuiz($questionId, $submission['quiz_id'], $db)) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid question',
            'errors' => ['Question does not belong to this quiz']
        ]);
        exit;
    }
    
    // Check if already submitted (not postponed)
    $stmt = $db->prepare("
        SELECT id, is_postponed FROM student_answers
        WHERE submission_id = ? AND question_id = ? AND is_postponed = FALSE
    ");
    $stmt->execute([$submissionId, $questionId]);
    $existingSubmitted = $stmt->fetch();
    
    if ($existingSubmitted) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Question already submitted',
            'errors' => ['This question has already been submitted and cannot be postponed']
        ]);
        exit;
    }
    
    // Check if already postponed
    $stmt = $db->prepare("
        SELECT id FROM student_answers
        WHERE submission_id = ? AND question_id = ? AND is_postponed = TRUE
    ");
    $stmt->execute([$submissionId, $questionId]);
    $existingPostponed = $stmt->fetch();
    
    if ($existingPostponed) {
        // Update answer if provided
        if ($answer !== null) {
            $stmt = $db->prepare("
                UPDATE student_answers
                SET answer_value = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$answer, $existingPostponed['id']]);
        }
    } else {
        // Insert new postponed answer
        $stmt = $db->prepare("
            INSERT INTO student_answers (submission_id, question_id, answer_value, is_postponed)
            VALUES (?, ?, ?, TRUE)
        ");
        $stmt->execute([$submissionId, $questionId, $answer ?? '']);
    }
    
    // Get next unanswered question (excluding submitted and postponed)
    $nextQuestion = null;
    $stmt = $db->prepare("
        SELECT q.id, q.question_text, q.question_type, q.question_order, q.marks, q.max_marks, q.criteria
        FROM questions q
        WHERE q.quiz_id = ?
          AND q.id NOT IN (
            SELECT sa.question_id
            FROM student_answers sa
            WHERE sa.submission_id = ?
          )
        ORDER BY q.question_order ASC
        LIMIT 1
    ");
    $stmt->execute([$submission['quiz_id'], $submissionId]);
    $nextQuestion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$nextQuestion) {
        // Fallback to earliest postponed question
        $stmt = $db->prepare("
            SELECT q.id, q.question_text, q.question_type, q.question_order, q.marks, q.max_marks, q.criteria
            FROM questions q
            INNER JOIN student_answers sa ON sa.question_id = q.id
            WHERE sa.submission_id = ?
              AND sa.is_postponed = TRUE
            ORDER BY q.question_order ASC
            LIMIT 1
        ");
        $stmt->execute([$submissionId]);
        $nextQuestion = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if ($nextQuestion) {
        if ($nextQuestion['question_type'] === 'multiple_choice' || $nextQuestion['question_type'] === 'true_false') {
            $stmt = $db->prepare("
                SELECT option_text, option_value, is_correct, option_order
                FROM question_options
                WHERE question_id = ?
                ORDER BY option_order ASC, option_value ASC
            ");
            $stmt->execute([$nextQuestion['id']]);
            $nextQuestion['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $nextQuestion['options'] = [];
        }
        
        if ($nextQuestion['criteria']) {
            $nextQuestion['criteria'] = json_decode($nextQuestion['criteria'], true);
        }
    }
    
    // Count postponed
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM student_answers
        WHERE submission_id = ? AND is_postponed = TRUE
    ");
    $stmt->execute([$submissionId]);
    $postponedCount = intval($stmt->fetch()['count']);
    
    // Count submitted
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM student_answers
        WHERE submission_id = ? AND is_postponed = FALSE
    ");
    $stmt->execute([$submissionId]);
    $submittedCount = intval($stmt->fetch()['count']);
    
    // Get total questions
    $stmt = $db->prepare("
        SELECT total_questions FROM quizzes WHERE id = ?
    ");
    $stmt->execute([$submission['quiz_id']]);
    $totalQuestions = intval($stmt->fetch()['total_questions']);
    
    $db->commit();
    
    // Step 3.4: Update session activity
    $_SESSION['last_activity'] = time();
    
    // Regenerate CSRF token after successful operation
    generateCSRFToken();
    
    echo json_encode([
        'success' => true,
        'message' => 'Question postponed successfully',
        'data' => [
            'next_question' => $nextQuestion,
            'postponed_count' => $postponedCount,
            'submitted_count' => $submittedCount,
            'total_questions' => $totalQuestions,
            'time_remaining' => max(0, $submission['duration'] - $elapsedTime)
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Database error in postpone-question.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'errors' => ['Failed to postpone question']
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Error in postpone-question.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'errors' => ['Failed to postpone question']
    ]);
}
?>

