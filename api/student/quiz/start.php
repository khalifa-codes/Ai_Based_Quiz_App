<?php
/**
 * Start Quiz API
 * Initializes quiz session, creates submission record, validates access
 * 
 * POST /api/student/quiz/start.php
 * Body: { "quiz_id": 1 }
 */

// Set headers first (before any output)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../../../config/database.php';
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
        'errors' => ['Please login to start quiz']
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
$rateLimit = checkRateLimit($studentId, 'student', 'start_quiz', 5, 60); // 5 requests per minute
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
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Handle JSON decode errors
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON format',
        'errors' => ['Request body must be valid JSON']
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Fallback: Check POST data if JSON is empty
if (empty($input) && !empty($_POST)) {
    $input = $_POST;
}

// Step 3.1: CSRF Protection (optional - for backward compatibility)
// Only validate if token is provided and not empty
if (isset($input['csrf_token']) && !empty($input['csrf_token'])) {
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
// If no CSRF token provided, continue (for backward compatibility)

// Step 3.3: Enhanced Input Validation & Sanitization
if (!isset($input['quiz_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input',
        'errors' => ['Quiz ID is required']
    ]);
    exit;
}

// Validate and sanitize quiz_id
$quizId = validateInteger($input['quiz_id'], 1);
if ($quizId === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid quiz ID',
        'errors' => ['Quiz ID must be a valid positive integer']
    ]);
    exit;
}

// Check for SQL injection attempts
if (isset($input['quiz_id']) && detectSQLInjection(strval($input['quiz_id']))) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input',
        'errors' => ['Suspicious input detected']
    ]);
    exit;
}

$studentId = intval($_SESSION['user_id']);

try {
    $dbInstance = Database::getInstance();
    $db = $dbInstance->getConnection();
    $db->beginTransaction();
    
    // Check if quiz exists and is accessible
    $stmt = $db->prepare("
        SELECT id, title, subject, duration, total_questions, status, ai_provider, ai_model
        FROM quizzes
        WHERE id = ? AND status = 'published'
    ");
    $stmt->execute([$quizId]);
    $quiz = $stmt->fetch();
    
    if (!$quiz) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Quiz not found or not available',
            'errors' => ['The quiz you are trying to start does not exist or is not published.']
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    // Check if student already has a submission
    $stmt = $db->prepare("
        SELECT id, started_at, submitted_at, status
        FROM quiz_submissions
        WHERE quiz_id = ? AND student_id = ?
        ORDER BY started_at DESC
        LIMIT 1
    ");
    $stmt->execute([$quizId, $studentId]);
    $existingSubmission = $stmt->fetch();
    
    if ($existingSubmission) {
        // Check if already submitted
        if ($existingSubmission['status'] === 'submitted' || 
            $existingSubmission['status'] === 'auto_submitted') {
            $db->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Quiz already submitted',
                'errors' => ['You have already submitted this quiz. You cannot start it again.']
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        
        // Resume existing submission
        $submissionId = $existingSubmission['id'];
        $startTime = strtotime($existingSubmission['started_at']);
        if ($startTime === false) {
            $startTime = time(); // Fallback if parsing fails
        }
        
        // Check if time expired
        $elapsed = time() - $startTime;
        if ($elapsed > $quiz['duration']) {
            // Time expired - mark as auto submitted
            $stmt = $db->prepare("
                UPDATE quiz_submissions
                SET status = 'auto_submitted',
                    submitted_at = NOW(),
                    time_taken = ?,
                    auto_submitted = TRUE,
                    auto_submit_reason = 'time_expired'
                WHERE id = ?
            ");
            $stmt->execute([$elapsed, $submissionId]);
            $db->commit();
            
            // Step 7.2: Log time expiration
            logAction('time_expired', 'submission', $submissionId, [
                'quiz_id' => $quizId,
                'elapsed_time' => $elapsed,
                'duration' => $quiz['duration']
            ]);
            
            // Step 7.1: Handle time expiration errors
            handleTimeExpirationError($submissionId, 'start_quiz');
        }
    } else {
        // Create new submission
        $startTime = time();
        $stmt = $db->prepare("
            INSERT INTO quiz_submissions (quiz_id, student_id, started_at, status)
            VALUES (?, ?, FROM_UNIXTIME(?), 'in_progress')
        ");
        $stmt->execute([$quizId, $studentId, $startTime]);
        $submissionId = $db->lastInsertId();
    }
    
    // Get questions for this quiz
    $stmt = $db->prepare("
        SELECT q.id, q.question_text, q.question_type, q.question_order, q.marks, q.max_marks, q.criteria
        FROM questions q
        WHERE q.quiz_id = ?
        ORDER BY q.question_order ASC
    ");
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll();
    
    // Get options for each question
    foreach ($questions as &$question) {
        if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false') {
            $stmt = $db->prepare("
                SELECT id, option_text, option_value, is_correct
                FROM question_options
                WHERE question_id = ?
                ORDER BY option_order ASC, option_value ASC
            ");
            $stmt->execute([$question['id']]);
            $question['options'] = $stmt->fetchAll();
        } else {
            $question['options'] = [];
        }
        
        // Parse criteria if exists
        if ($question['criteria']) {
            $question['criteria'] = json_decode($question['criteria'], true);
        }
    }
    
    // Get already submitted answers
    $stmt = $db->prepare("
        SELECT question_id, answer_value, is_postponed
        FROM student_answers
        WHERE submission_id = ?
    ");
    $stmt->execute([$submissionId]);
    $submittedAnswers = [];
    $postponedQuestions = [];
    while ($row = $stmt->fetch()) {
        $submittedAnswers[$row['question_id']] = $row['answer_value'];
        if ($row['is_postponed']) {
            $postponedQuestions[] = $row['question_id'];
        }
    }
    
    // Step 3.4: Store submission ID in session securely
    $_SESSION['current_submission_id'] = $submissionId;
    $_SESSION['quiz_start_time'] = $startTime;
    $_SESSION['last_activity'] = time(); // Update activity
    
    // Regenerate CSRF token after successful operation
    generateCSRFToken();
    
    $db->commit();
    
    // Step 7.2: Log quiz start action
    logAction('quiz_start', 'quiz', $quizId, [
        'submission_id' => $submissionId,
        'resumed' => isset($existingSubmission)
    ]);
    
    // Calculate time remaining
    $elapsed = time() - $startTime;
    $timeRemaining = max(0, $quiz['duration'] - $elapsed);
    
    echo json_encode([
        'success' => true,
        'message' => 'Quiz started successfully',
        'data' => [
            'submission_id' => $submissionId,
            'quiz_id' => $quizId,
            'quiz_title' => $quiz['title'],
            'quiz_subject' => $quiz['subject'],
            'start_time' => $startTime,
            'duration' => $quiz['duration'],
            'time_remaining' => $timeRemaining,
            'total_questions' => $quiz['total_questions'],
            'ai_provider' => $quiz['ai_provider'] ?? 'gemini',
            'ai_model' => $quiz['ai_model'] ?? null,
            'questions' => $questions,
            'submitted_answers' => $submittedAnswers,
            'postponed_questions' => $postponedQuestions
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    // Step 7.1: Handle database errors with logging
    handleAPIError(
        'Database error occurred. Please try again.',
        ['Failed to start quiz'],
        500,
        "Database error in start.php: " . $e->getMessage(),
        ['exception' => $e->getMessage(), 'quiz_id' => $quizId ?? null],
        'quiz_start_error'
    );
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    // Step 7.1: Handle general errors with logging
    handleAPIError(
        'An error occurred. Please try again.',
        ['Failed to start quiz'],
        500,
        "Error in start.php: " . $e->getMessage(),
        ['exception' => $e->getMessage(), 'quiz_id' => $quizId ?? null],
        'quiz_start_error'
    );
}
?>

