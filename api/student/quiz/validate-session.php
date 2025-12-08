<?php
/**
 * Validate Session API
 * Validate quiz session and time (called by security script)
 * 
 * POST /api/student/quiz/validate-session.php
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
        'valid' => false,
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
        'valid' => false,
        'message' => 'Unauthorized',
        'errors' => ['Please login']
    ]);
    exit;
}

// Step 3.4: Enhanced Session Security
$sessionCheck = validateSessionSecurity();
if (!$sessionCheck['valid']) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Session expired',
        'errors' => [$sessionCheck['message']]
    ]);
    exit;
}

// Step 3.2: Rate Limiting (higher limit for validation)
$studentId = intval($_SESSION['user_id']);
$rateLimit = checkRateLimit($studentId, 'student', 'validate_session', 30, 60); // 30 requests per minute
if (!$rateLimit['allowed']) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'valid' => false,
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

// Step 3.3: Enhanced Input Validation & Sanitization
if (!isset($input['submission_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'valid' => false,
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
        'valid' => false,
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
        'valid' => false,
        'message' => 'Invalid input',
        'errors' => ['Suspicious input detected']
    ]);
    exit;
}

try {
    $dbInstance = Database::getInstance();
    $db = $dbInstance->getConnection();
    
    $stmt = $db->prepare("
        SELECT qs.id, qs.started_at, qs.status, qs.submitted_at, q.duration
        FROM quiz_submissions qs
        JOIN quizzes q ON qs.quiz_id = q.id
        WHERE qs.id = ? AND qs.student_id = ?
    ");
    $stmt->execute([$submissionId, $studentId]);
    $submission = $stmt->fetch();
    
    if (!$submission) {
        echo json_encode([
            'success' => false,
            'valid' => false,
            'message' => 'Invalid submission',
            'errors' => ['Submission not found']
        ]);
        exit;
    }
    
    if ($submission['status'] !== 'in_progress') {
        echo json_encode([
            'success' => true,
            'valid' => false,
            'message' => 'Quiz already submitted',
            'data' => [
                'submission_id' => $submissionId,
                'status' => $submission['status'],
                'submitted_at' => $submission['submitted_at']
            ]
        ]);
        exit;
    }
    
    // Calculate time remaining
    $startTime = strtotime($submission['started_at']);
    $currentTime = time();
    $elapsed = $currentTime - $startTime;
    $timeRemaining = max(0, $submission['duration'] - $elapsed);
    
    // Check if time expired
    if ($timeRemaining <= 0) {
        // Auto submit if time expired
        try {
            $db->beginTransaction();
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
            
            echo json_encode([
                'success' => true,
                'valid' => false,
                'message' => 'Time expired',
                'data' => [
                    'submission_id' => $submissionId,
                    'status' => 'auto_submitted',
                    'time_expired' => true
                ]
            ]);
            exit;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error auto-submitting expired quiz: " . $e->getMessage());
        }
    }
    
    // Step 3.4: Update session activity
    $_SESSION['last_activity'] = time();
    
    echo json_encode([
        'success' => true,
        'valid' => true,
        'time_remaining' => $timeRemaining,
        'elapsed' => $elapsed,
        'data' => [
            'submission_id' => $submissionId,
            'status' => $submission['status'],
            'duration' => $submission['duration']
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    error_log("Database error in validate-session.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Internal server error',
        'errors' => ['Failed to validate session']
    ]);
} catch (Exception $e) {
    error_log("Error in validate-session.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Internal server error',
        'errors' => ['Failed to validate session']
    ]);
}
?>

