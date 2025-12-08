<?php
/**
 * Submit Question API
 * Submit a single question answer with server-side validation
 * 
 * POST /api/student/quiz/submit-question.php
 * Body: { "submission_id": 123, "question_id": 1, "answer": "2", "timestamp": 1704067300 }
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
        'errors' => ['Please login to submit answer']
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
$rateLimit = checkRateLimit($studentId, 'student', 'submit_question', 10, 60); // 10 requests per minute
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
if (!isset($input['submission_id']) || !isset($input['question_id']) || !isset($input['answer'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields',
        'errors' => ['submission_id, question_id, and answer are required']
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

// Sanitize and validate answer
$answer = isset($input['answer']) ? sanitizeInput($input['answer']) : '';

// Check for SQL injection attempts
if (detectSQLInjection($answer) || detectSQLInjection(strval($submissionId)) || detectSQLInjection(strval($questionId))) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input',
        'errors' => ['Suspicious input detected']
    ]);
    exit;
}

// Validate answer is not empty (but allow whitespace-only for subjective questions that might be auto-saved)
// We'll check this more leniently - only reject if it's completely empty
$answerTrimmed = trim($answer);
if ($answerTrimmed === '' && strlen($answer) === 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid answer',
        'errors' => ['Answer cannot be empty']
    ]);
    exit;
}
// Use trimmed answer for processing
$answer = $answerTrimmed;

try {
    $dbInstance = Database::getInstance();
    $db = $dbInstance->getConnection();
    $db->beginTransaction();
    
    // Step 3.3: Validate submission ownership using helper
    $submission = validateSubmissionOwnership($submissionId, $studentId, $db);
    
    if (!$submission) {
        $db->rollBack();
        // Step 7.1: Handle validation errors with logging
        handleValidationError(
            ['Submission not found or access denied'],
            'submit_question'
        );
    }
    
    // Get quiz details
    $stmt = $db->prepare("SELECT duration FROM quizzes WHERE id = ?");
    $stmt->execute([$submission['quiz_id']]);
    $quiz = $stmt->fetch();
    if (!$quiz) {
        $db->rollBack();
        // Step 7.1: Handle validation errors with logging
        handleValidationError(
            ['Quiz does not exist'],
            'submit_question'
        );
    }
    $submission['duration'] = $quiz['duration'];
    
    // Check if already submitted
    if ($submission['status'] !== 'in_progress') {
        $db->rollBack();
        // Step 7.1: Handle validation errors with logging
        handleValidationError(
            ['This quiz has already been submitted'],
            'submit_question'
        );
    }
    
    // Validate time (server-side check)
    $startTime = strtotime($submission['started_at']);
    $currentTime = time();
    $elapsedTime = $currentTime - $startTime;
    
    if ($elapsedTime > $submission['duration']) {
        // Time expired - auto submit
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
        
        // Step 7.2: Log time expiration
        logAction('time_expired', 'submission', $submissionId, [
            'quiz_id' => $submission['quiz_id'],
            'question_id' => $questionId ?? null,
            'elapsed_time' => $elapsedTime,
            'duration' => $submission['duration']
        ]);
        
        // Step 7.1: Handle time expiration errors
        handleTimeExpirationError($submissionId, 'submit_question');
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
    
    // Get question type
    $stmt = $db->prepare("SELECT question_type FROM questions WHERE id = ?");
    $stmt->execute([$questionId]);
    $question = $stmt->fetch();
    if (!$question) {
        $db->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Question not found',
            'errors' => ['Question does not exist']
        ]);
        exit;
    }
    
    // Validate answer format for multiple choice
    if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false') {
        $stmt = $db->prepare("
            SELECT option_value FROM question_options
            WHERE question_id = ? AND option_value = ?
        ");
        $stmt->execute([$questionId, $answer]);
        if (!$stmt->fetch()) {
            $db->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid answer',
                'errors' => ['Answer does not match any valid option']
            ]);
            exit;
        }
    }
    
    // Check if answer already exists (submitted or postponed)
    $stmt = $db->prepare("
        SELECT id, is_postponed
        FROM student_answers
        WHERE submission_id = ? AND question_id = ?
    ");
    $stmt->execute([$submissionId, $questionId]);
    $existingAnswer = $stmt->fetch();
    
    if ($existingAnswer) {
        if (!$existingAnswer['is_postponed']) {
            $db->rollBack();
            handleValidationError(
                ['This question has already been submitted'],
                'submit_question'
            );
        }
        
        // Update postponed answer to submitted
        $stmt = $db->prepare("
            UPDATE student_answers
            SET answer_value = ?, submitted_at = NOW(), is_postponed = FALSE
            WHERE id = ?
        ");
        $stmt->execute([$answer, $existingAnswer['id']]);
    } else {
        // Insert new answer
        $stmt = $db->prepare("
            INSERT INTO student_answers (submission_id, question_id, answer_value, is_postponed)
            VALUES (?, ?, ?, FALSE)
        ");
        $stmt->execute([$submissionId, $questionId, $answer]);
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
        // Fallback to earliest postponed question (if any)
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
    
    // Count submitted questions
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
    
    // Step 7.2: Log question submit action
    logAction('question_submit', 'question', $questionId, [
        'submission_id' => $submissionId,
        'quiz_id' => $submission['quiz_id'],
        'submitted_count' => $submittedCount,
        'total_questions' => $totalQuestions
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Question submitted successfully',
        'data' => [
            'next_question' => $nextQuestion,
            'submitted_count' => $submittedCount,
            'total_questions' => $totalQuestions,
            'all_submitted' => ($submittedCount >= $totalQuestions),
            'time_remaining' => max(0, $submission['duration'] - $elapsedTime)
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    // Step 7.1: Handle database errors with logging
    handleAPIError(
        'Database error occurred. Please try again.',
        ['Failed to submit question'],
        500,
        "Database error in submit-question.php: " . $e->getMessage(),
        ['exception' => $e->getMessage(), 'submission_id' => $submissionId ?? null, 'question_id' => $questionId ?? null],
        'question_submit_error'
    );
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    // Step 7.1: Handle general errors with logging
    handleAPIError(
        'An error occurred. Please try again.',
        ['Failed to submit question'],
        500,
        "Error in submit-question.php: " . $e->getMessage(),
        ['exception' => $e->getMessage(), 'submission_id' => $submissionId ?? null, 'question_id' => $questionId ?? null],
        'question_submit_error'
    );
}
?>

