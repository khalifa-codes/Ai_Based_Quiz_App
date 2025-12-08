<?php
/**
 * Security Helper Functions
 * Provides rate limiting, CSRF protection, input validation, and session security
 */

// Ensure database function is available
require_once __DIR__ . '/../config/database.php';

/**
 * Check rate limit for a user action
 * 
 * @param int $userId User ID
 * @param string $userType User type: 'student', 'teacher', 'admin'
 * @param string $action Action name (e.g., 'submit_question', 'start_quiz')
 * @param int $maxRequests Maximum requests allowed
 * @param int $timeWindow Time window in seconds
 * @return array ['allowed' => bool, 'remaining' => int, 'reset_time' => int]
 */
function checkRateLimit($userId, $userType, $action, $maxRequests = 10, $timeWindow = 60) {
    try {
        $db = Database::getInstance();
        $db = $db->getConnection();
        
        // Clean old entries (older than time window)
        $stmt = $db->prepare("
            DELETE FROM rate_limits
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$timeWindow]);
        
        // Count requests in time window
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM rate_limits
            WHERE user_id = ? AND user_type = ? AND action = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute([$userId, $userType, $action, $timeWindow]);
        $result = $stmt->fetch();
        $count = intval($result['count']);
        
        if ($count >= $maxRequests) {
            // Rate limit exceeded
            // Get reset time (oldest request + time window)
            $stmt = $db->prepare("
                SELECT UNIX_TIMESTAMP(MIN(created_at)) + ? as reset_time
                FROM rate_limits
                WHERE user_id = ? AND user_type = ? AND action = ?
                AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
            ");
            $stmt->execute([$timeWindow, $userId, $userType, $action, $timeWindow]);
            $resetResult = $stmt->fetch();
            $resetTime = $resetResult ? intval($resetResult['reset_time']) : time() + $timeWindow;
            
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => $resetTime
            ];
        }
        
        // Log this request
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $db->prepare("
            INSERT INTO rate_limits (user_id, user_type, action, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $userType, $action, $ipAddress]);
        
        return [
            'allowed' => true,
            'remaining' => $maxRequests - $count - 1,
            'reset_time' => time() + $timeWindow
        ];
        
    } catch (Exception $e) {
        error_log("Rate limit check error: " . $e->getMessage());
        // On error, allow the request (fail open)
        return [
            'allowed' => true,
            'remaining' => $maxRequests,
            'reset_time' => time() + $timeWindow
        ];
    }
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return bool True if valid
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input string
 * 
 * @param string $input Input to sanitize
 * @return string Sanitized string
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    
    if (!is_string($input)) {
        return $input;
    }
    
    // Remove null bytes
    $input = str_replace("\0", '', $input);
    
    // Trim whitespace
    $input = trim($input);
    
    // Remove HTML tags (but preserve text content)
    $input = strip_tags($input);
    
    // Escape special characters for SQL (but we use prepared statements, so this is extra safety)
    // $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    return $input;
}

/**
 * Validate and sanitize integer input
 * 
 * @param mixed $value Value to validate
 * @param int $min Minimum value (optional)
 * @param int $max Maximum value (optional)
 * @return int|false Validated integer or false
 */
function validateInteger($value, $min = null, $max = null) {
    if (!is_numeric($value)) {
        return false;
    }
    
    $intValue = intval($value);
    
    if ($min !== null && $intValue < $min) {
        return false;
    }
    
    if ($max !== null && $intValue > $max) {
        return false;
    }
    
    return $intValue;
}

/**
 * Validate question ID belongs to quiz
 * 
 * @param int $questionId Question ID
 * @param int $quizId Quiz ID
 * @param PDO $db Database connection
 * @return bool True if valid
 */
function validateQuestionBelongsToQuiz($questionId, $quizId, $db) {
    try {
        $stmt = $db->prepare("
            SELECT id FROM questions
            WHERE id = ? AND quiz_id = ?
        ");
        $stmt->execute([$questionId, $quizId]);
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        error_log("Question validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate submission belongs to student
 * 
 * @param int $submissionId Submission ID
 * @param int $studentId Student ID
 * @param PDO $db Database connection
 * @return array|false Submission data or false
 */
function validateSubmissionOwnership($submissionId, $studentId, $db) {
    try {
        $stmt = $db->prepare("
            SELECT id, quiz_id, student_id, status, started_at
            FROM quiz_submissions
            WHERE id = ? AND student_id = ?
        ");
        $stmt->execute([$submissionId, $studentId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Submission validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check for SQL injection patterns
 * 
 * @param string $input Input to check
 * @return bool True if suspicious
 */
function detectSQLInjection($input) {
    if (!is_string($input)) {
        return false;
    }
    
    $suspiciousPatterns = [
        '/(\bUNION\b.*\bSELECT\b)/i',
        '/(\bSELECT\b.*\bFROM\b)/i',
        '/(\bINSERT\b.*\bINTO\b)/i',
        '/(\bUPDATE\b.*\bSET\b)/i',
        '/(\bDELETE\b.*\bFROM\b)/i',
        '/(\bDROP\b.*\bTABLE\b)/i',
        '/(\bEXEC\b|\bEXECUTE\b)/i',
        '/(\bSCRIPT\b)/i',
        '/(\bJAVASCRIPT\b)/i',
        '/(\bONLOAD\b|\bONERROR\b)/i',
        '/(--|\#|\/\*|\*\/)/', // SQL comments
        '/(\bOR\b.*=.*=)/i',
        '/(\bAND\b.*=.*=)/i'
    ];
    
    foreach ($suspiciousPatterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Enhanced session validation
 * 
 * @return array ['valid' => bool, 'message' => string]
 */
function validateSessionSecurity() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if session exists
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return ['valid' => false, 'message' => 'Session not found'];
    }
    
    // Check session timeout (15 minutes inactivity)
    if (isset($_SESSION['last_activity'])) {
        $inactivityTime = time() - $_SESSION['last_activity'];
        if ($inactivityTime > 900) { // 15 minutes
            return ['valid' => false, 'message' => 'Session expired'];
        }
    }
    
    // Check login timestamp (8 hours max)
    if (isset($_SESSION['login_timestamp'])) {
        $sessionAge = time() - $_SESSION['login_timestamp'];
        if ($sessionAge > 28800) { // 8 hours
            return ['valid' => false, 'message' => 'Session expired'];
        }
    }
    
    // Regenerate session ID periodically (every 5 minutes)
    if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    return ['valid' => true, 'message' => 'Session valid'];
}

/**
 * Validate email format
 * 
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get client IP address
 * 
 * @return string IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Step 7.2: Log action to audit trail
 * 
 * @param string $action Action name (e.g., 'quiz_start', 'question_submit', 'security_violation')
 * @param string $resourceType Resource type (e.g., 'quiz', 'question', 'submission')
 * @param int|null $resourceId Resource ID
 * @param array|null $details Additional details as JSON
 * @param int|null $userId User ID (defaults to session user_id)
 * @param string|null $userType User type (defaults to session role)
 * @return bool Success status
 */
function logAction($action, $resourceType = null, $resourceId = null, $details = null, $userId = null, $userType = null) {
    try {
        // Get user info from session if not provided
        if ($userId === null) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        if ($userType === null) {
            $userType = $_SESSION['role'] ?? 'student';
        }
        
        $db = getDB();
        $ipAddress = getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Convert details to JSON if array
        $detailsJson = null;
        if ($details !== null) {
            $detailsJson = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        $stmt = $db->prepare("
            INSERT INTO audit_logs (user_id, user_type, action, resource_type, resource_id, ip_address, user_agent, details)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $userType,
            $action,
            $resourceType,
            $resourceId,
            $ipAddress,
            $userAgent,
            $detailsJson
        ]);
        
        return true;
    } catch (Exception $e) {
        // Log to error log if database logging fails
        error_log("Failed to log action to audit_logs: " . $e->getMessage());
        return false;
    }
}

/**
 * Step 7.1: Handle API error with logging
 * 
 * @param string $message User-friendly error message
 * @param array $errors Array of error details
 * @param int $httpCode HTTP status code
 * @param string|null $logMessage Log message (if different from user message)
 * @param array|null $logDetails Additional details for logging
 * @param string|null $action Action name for audit log
 * @return void (exits script)
 */
function handleAPIError($message, $errors = [], $httpCode = 500, $logMessage = null, $logDetails = null, $action = null) {
    // Step 7.1: Log error server-side
    $logMsg = $logMessage ?? $message;
    error_log("API Error: " . $logMsg . " | Errors: " . json_encode($errors));
    
    // Step 7.2: Log to audit trail if action provided
    if ($action !== null) {
        logAction($action, null, null, array_merge(['error' => $logMsg, 'errors' => $errors], $logDetails ?? []));
    }
    
    // Step 7.1: Return user-friendly error message
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Step 7.1: Handle network/connection errors
 * 
 * @param Exception $e Exception object
 * @param string $context Context where error occurred
 * @return void (exits script)
 */
function handleNetworkError($e, $context = 'API') {
    $message = $e->getMessage();
    
    // Step 7.1: Log network error
    error_log("Network error in {$context}: " . $message);
    
    // Step 7.1: Return user-friendly message
    handleAPIError(
        'Network connection failed. Please check your internet connection and try again.',
        ['Connection error occurred'],
        503,
        "Network error in {$context}: " . $message,
        ['exception' => $message],
        'network_error'
    );
}

/**
 * Step 7.1: Handle time expiration errors
 * 
 * @param int $submissionId Submission ID
 * @param string $context Context where error occurred
 * @return void (exits script)
 */
function handleTimeExpirationError($submissionId, $context = 'API') {
    // Step 7.2: Log time expiration
    logAction('time_expired', 'submission', $submissionId, ['context' => $context]);
    
    // Step 7.1: Return user-friendly message
    handleAPIError(
        'Time has expired. Your quiz has been automatically submitted.',
        ['Time limit exceeded'],
        400,
        "Time expired for submission {$submissionId} in {$context}",
        ['submission_id' => $submissionId],
        'time_expired'
    );
}

/**
 * Step 7.1: Handle validation errors
 * 
 * @param array $errors Validation errors
 * @param string $context Context where error occurred
 * @return void (exits script)
 */
function handleValidationError($errors, $context = 'API') {
    // Step 7.2: Log validation error
    logAction('validation_error', null, null, ['context' => $context, 'errors' => $errors]);
    
    // Step 7.1: Return user-friendly message
    handleAPIError(
        'Validation failed. Please check your input and try again.',
        $errors,
        400,
        "Validation error in {$context}: " . json_encode($errors),
        ['errors' => $errors],
        'validation_error'
    );
}

