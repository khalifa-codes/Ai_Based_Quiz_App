<?php
/**
 * API Endpoint: Export Quizzes to Excel/CSV
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

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    
    // Fetch all quizzes for teacher
    $stmt = $conn->prepare("
        SELECT 
            q.id,
            q.title,
            q.subject,
            q.duration,
            q.total_questions,
            q.total_marks,
            q.status,
            q.created_at,
            COUNT(DISTINCT qs.id) as submission_count
        FROM quizzes q
        LEFT JOIN quiz_submissions qs ON q.id = qs.quiz_id
        WHERE q.created_by = ?
        GROUP BY q.id
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([$teacherId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate CSV content
    $csvContent = "ID,Title,Subject,Duration (min),Questions,Marks,Status,Submissions,Created Date\n";
    foreach ($quizzes as $quiz) {
        $csvContent .= sprintf(
            "%d,%s,%s,%d,%d,%d,%s,%d,%s\n",
            $quiz['id'],
            '"' . str_replace('"', '""', $quiz['title']) . '"',
            $quiz['subject'] ?? 'N/A',
            round($quiz['duration'] / 60),
            $quiz['total_questions'],
            $quiz['total_marks'],
            ucfirst($quiz['status']),
            $quiz['submission_count'],
            date('Y-m-d H:i:s', strtotime($quiz['created_at']))
        );
    }
    
    // Return CSV data as base64 for download
    echo json_encode([
        'success' => true,
        'data' => base64_encode($csvContent),
        'filename' => 'quizzes_export_' . date('Y-m-d_His') . '.csv',
        'content_type' => 'text/csv'
    ]);
    
} catch (Exception $e) {
    error_log('Export Quizzes Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error exporting quizzes']);
}
?>

