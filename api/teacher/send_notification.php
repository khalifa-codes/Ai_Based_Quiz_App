<?php
/**
 * API Endpoint: Send Notification to Students
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
$title = isset($input['title']) ? trim($input['title']) : '';
$message = isset($input['message']) ? trim($input['message']) : '';
$type = isset($input['type']) ? trim($input['type']) : 'announcement';
$recipients = isset($input['recipients']) ? $input['recipients'] : [];
$quizId = isset($input['quiz_id']) ? (int)$input['quiz_id'] : null;
$priority = isset($input['priority']) ? trim($input['priority']) : 'medium';

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Notification title is required']);
    exit();
}

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Notification message is required']);
    exit();
}

if (empty($recipients) || !is_array($recipients)) {
    echo json_encode(['success' => false, 'message' => 'At least one recipient is required']);
    exit();
}

// Validate type
$allowedTypes = ['announcement', 'exam', 'result', 'info', 'warning', 'error'];
if (!in_array($type, $allowedTypes)) {
    $type = 'announcement';
}

// Validate priority
$allowedPriorities = ['low', 'medium', 'high'];
if (!in_array($priority, $allowedPriorities)) {
    $priority = 'medium';
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
    
    // Verify quiz belongs to teacher if quiz_id is provided
    if ($quizId && $quizId > 0) {
        $quizStmt = $conn->prepare("SELECT id FROM quizzes WHERE id = ? AND created_by = ?");
        $quizStmt->execute([$quizId, $teacherId]);
        if (!$quizStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
            exit();
        }
    }
    
    // Get student IDs based on recipients
    $studentIds = [];
    
    if (in_array('all', $recipients)) {
        // Get all students who have taken quizzes from this teacher
        $stmt = $conn->prepare("
            SELECT DISTINCT s.id 
            FROM students s
            INNER JOIN quiz_submissions qs ON qs.student_id = s.id
            INNER JOIN quizzes q ON q.id = qs.quiz_id
            WHERE q.created_by = ?
        ");
        $stmt->execute([$teacherId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $row) {
            $studentIds[] = (int)$row['id'];
        }
    } else {
        // Get students from specific departments/organizations
        foreach ($recipients as $recipient) {
            if (is_numeric($recipient)) {
                // Organization ID
                $stmt = $conn->prepare("SELECT id FROM students WHERE organization_id = ?");
                $stmt->execute([(int)$recipient]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($results as $row) {
                    $studentIds[] = (int)$row['id'];
                }
            }
        }
    }
    
    // Remove duplicates
    $studentIds = array_unique($studentIds);
    
    if (empty($studentIds)) {
        echo json_encode(['success' => false, 'message' => 'No students found for the selected recipients']);
        exit();
    }
    
    // Check if notifications table exists, create if not
    try {
        $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
        if ($tableCheck->rowCount() == 0) {
            // Create notifications table
            $createTableSQL = "
                CREATE TABLE IF NOT EXISTS notifications (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    teacher_id INT NOT NULL,
                    student_id INT DEFAULT NULL,
                    quiz_id INT DEFAULT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    type ENUM('announcement', 'exam', 'result', 'info', 'warning', 'error') DEFAULT 'announcement',
                    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                    is_read TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_teacher_id (teacher_id),
                    INDEX idx_student_id (student_id),
                    INDEX idx_quiz_id (quiz_id),
                    INDEX idx_is_read (is_read),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $conn->exec($createTableSQL);
        }
    } catch (Exception $e) {
        error_log('Table check/create error: ' . $e->getMessage());
        // Continue anyway, will fail with better error message
    }
    
    // Insert notifications for each student
    $insertStmt = $conn->prepare("
        INSERT INTO notifications (teacher_id, student_id, quiz_id, title, message, type, priority, is_read, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
    ");
    
    $successCount = 0;
    $failedCount = 0;
    
    foreach ($studentIds as $studentId) {
        try {
            $insertStmt->execute([
                $teacherId,
                $studentId,
                $quizId ?: null,
                $title,
                $message,
                $type,
                $priority
            ]);
            $successCount++;
        } catch (PDOException $e) {
            $errorInfo = $insertStmt->errorInfo();
            error_log("Failed to insert notification for student {$studentId}: " . $e->getMessage());
            error_log("SQL Error Code: " . ($errorInfo[0] ?? 'N/A'));
            error_log("SQL Error: " . ($errorInfo[2] ?? 'N/A'));
            $failedCount++;
        } catch (Exception $e) {
            error_log("Failed to insert notification for student {$studentId}: " . $e->getMessage());
            $failedCount++;
        }
    }
    
    if ($successCount > 0) {
        echo json_encode([
            'success' => true,
            'message' => "Notification sent successfully to {$successCount} student(s)" . ($failedCount > 0 ? " ({$failedCount} failed)" : ""),
            'sent_count' => $successCount,
            'failed_count' => $failedCount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send notification to any students. Please check database connection and table structure. Error details logged.',
            'sent_count' => 0,
            'failed_count' => $failedCount
        ]);
    }
    
} catch (Exception $e) {
    error_log('Send Notification Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error sending notification: ' . $e->getMessage()
    ]);
}
?>

