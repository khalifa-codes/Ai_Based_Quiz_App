<?php
/**
 * API Endpoint: Export Quiz Results to Excel/CSV
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
session_start();

$teacherId = (int)($_SESSION['user_id'] ?? 0);
$format = $_GET['format'] ?? 'excel';

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
    
    // Fetch all results for teacher's quizzes
    $stmt = $conn->prepare("
        SELECT 
            qs.id,
            s.name as student_name,
            s.student_id as student_id_code,
            q.title as quiz_title,
            q.subject,
            qs.total_score,
            qs.percentage,
            qs.status,
            qs.submitted_at,
            qs.ai_provider
        FROM quiz_submissions qs
        INNER JOIN quizzes q ON q.id = qs.quiz_id
        INNER JOIN students s ON s.id = qs.student_id
        WHERE q.created_by = ? AND qs.status IN ('submitted', 'auto_submitted')
        ORDER BY qs.submitted_at DESC
    ");
    $stmt->execute([$teacherId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate CSV content
    $csvContent = "ID,Student Name,Student ID,Examination,Subject,Score,Percentage,Status,Submitted Date,AI Evaluated\n";
    foreach ($results as $result) {
        $statusText = $result['percentage'] >= 60 ? 'Passed' : ($result['percentage'] >= 40 ? 'Average' : 'Failed');
        $csvContent .= sprintf(
            "%d,%s,%s,%s,%s,%.2f,%.1f,%s,%s,%s\n",
            $result['id'],
            '"' . str_replace('"', '""', $result['student_name']) . '"',
            $result['student_id_code'] ?? 'N/A',
            '"' . str_replace('"', '""', $result['quiz_title']) . '"',
            $result['subject'] ?? 'N/A',
            $result['total_score'],
            $result['percentage'],
            $statusText,
            date('Y-m-d H:i:s', strtotime($result['submitted_at'])),
            !empty($result['ai_provider']) ? 'Yes' : 'No'
        );
    }
    
    // Return CSV data as base64 for download
    echo json_encode([
        'success' => true,
        'data' => base64_encode($csvContent),
        'filename' => 'quiz_results_export_' . date('Y-m-d_His') . '.csv',
        'content_type' => 'text/csv'
    ]);
    
} catch (Exception $e) {
    error_log('Export Results Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error exporting results']);
}
?>

