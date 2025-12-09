<?php
/**
 * API Endpoint: Export Departments to Excel/CSV
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
    
    // Fetch all departments
    $stmt = $conn->prepare("
        SELECT 
            o.id as organization_id,
            o.name as department_name,
            o.created_at,
            COUNT(DISTINCT s.id) as student_count,
            COUNT(DISTINCT q.id) as quiz_count,
            GROUP_CONCAT(DISTINCT q.subject SEPARATOR ', ') as subjects
        FROM organizations o
        LEFT JOIN students s ON s.organization_id = o.id
        LEFT JOIN quizzes q ON q.created_by = ? AND (q.organization_id = o.id OR q.organization_id IS NULL)
        WHERE o.status = 'active'
        GROUP BY o.id, o.name, o.created_at
        ORDER BY o.name ASC
    ");
    $stmt->execute([$teacherId]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generate CSV content
    $csvContent = "ID,Department Name,Students,Examinations,Subjects,Created Date\n";
    foreach ($departments as $dept) {
        $csvContent .= sprintf(
            "%d,%s,%d,%d,%s,%s\n",
            $dept['organization_id'] ?? 'N/A',
            '"' . str_replace('"', '""', $dept['department_name']) . '"',
            (int)($dept['student_count'] ?? 0),
            (int)($dept['quiz_count'] ?? 0),
            '"' . str_replace('"', '""', $dept['subjects'] ?? 'N/A') . '"',
            date('Y-m-d H:i:s', strtotime($dept['created_at']))
        );
    }
    
    // Return CSV data as base64 for download
    echo json_encode([
        'success' => true,
        'data' => base64_encode($csvContent),
        'filename' => 'departments_export_' . date('Y-m-d_His') . '.csv',
        'content_type' => 'text/csv'
    ]);
    
} catch (Exception $e) {
    error_log('Export Departments Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error exporting departments']);
}
?>

