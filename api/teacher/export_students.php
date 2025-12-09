<?php
/**
 * API Endpoint: Export Students to Excel/CSV
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
    
    // Fetch all students who have taken quizzes created by this teacher
    $stmt = $conn->prepare("
        SELECT DISTINCT
            s.id,
            s.name,
            s.email,
            s.student_id,
            o.name as organization_name,
            COUNT(DISTINCT qs.id) as quiz_count,
            s.created_at
        FROM students s
        INNER JOIN quiz_submissions qs ON qs.student_id = s.id
        INNER JOIN quizzes q ON q.id = qs.quiz_id
        LEFT JOIN organizations o ON o.id = s.organization_id
        WHERE q.created_by = ?
        GROUP BY s.id, s.name, s.email, s.student_id, o.name, s.created_at
        ORDER BY s.name ASC
    ");
    $stmt->execute([$teacherId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate CSV content
    $csvContent = "ID,Name,Email,Student ID,Organization,Quiz Count,Created Date\n";
    foreach ($students as $student) {
        $csvContent .= sprintf(
            "%d,%s,%s,%s,%s,%d,%s\n",
            $student['id'],
            '"' . str_replace('"', '""', $student['name']) . '"',
            $student['email'],
            $student['student_id'] ?? 'N/A',
            '"' . str_replace('"', '""', $student['organization_name'] ?? 'General') . '"',
            (int)($student['quiz_count'] ?? 0),
            date('Y-m-d H:i:s', strtotime($student['created_at']))
        );
    }
    
    // Return CSV data as base64 for download
    echo json_encode([
        'success' => true,
        'data' => base64_encode($csvContent),
        'filename' => 'students_export_' . date('Y-m-d_His') . '.csv',
        'content_type' => 'text/csv'
    ]);
    
} catch (Exception $e) {
    error_log('Export Students Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error exporting students']);
}
?>

