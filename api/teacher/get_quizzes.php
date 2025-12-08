<?php
/**
 * Get Quizzes API Endpoint
 * Returns all quizzes created by the logged-in teacher
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $teacherId = (int)$_SESSION['user_id'];
    
    // Get filter parameters
    $statusFilter = $_GET['status'] ?? 'all';
    $typeFilter = $_GET['type'] ?? 'all';
    $searchQuery = $_GET['search'] ?? '';
    
    // Build query
    $query = "
        SELECT 
            q.id,
            q.title,
            q.subject,
            q.description,
            q.duration,
            q.total_questions,
            q.total_marks,
            q.status,
            q.ai_provider,
            q.ai_model,
            q.created_at,
            q.updated_at,
            COUNT(DISTINCT qs.id) as submission_count
        FROM quizzes q
        LEFT JOIN quiz_submissions qs ON q.id = qs.quiz_id
        WHERE q.created_by = ?
    ";
    
    $params = [$teacherId];
    
    // Add status filter
    if ($statusFilter !== 'all') {
        if ($statusFilter === 'active') {
            $query .= " AND q.status = 'published'";
        } elseif ($statusFilter === 'inactive') {
            $query .= " AND q.status = 'archived'";
        } else {
            $query .= " AND q.status = ?";
            $params[] = $statusFilter;
        }
    }
    
    // Add search filter
    if (!empty($searchQuery)) {
        $query .= " AND (q.title LIKE ? OR q.subject LIKE ?)";
        $searchTerm = "%$searchQuery%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $query .= " GROUP BY q.id ORDER BY q.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Determine quiz type for each quiz (check if it has subjective questions)
    foreach ($quizzes as &$quiz) {
        $quizId = $quiz['id'];
        
        // Check question types
        $typeStmt = $conn->prepare("
            SELECT DISTINCT question_type 
            FROM questions 
            WHERE quiz_id = ?
        ");
        $typeStmt->execute([$quizId]);
        $questionTypes = $typeStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('subjective', $questionTypes)) {
            $quiz['type'] = 'subjective';
        } else {
            $quiz['type'] = 'objective';
        }
        
        // Format duration (convert seconds to minutes)
        $quiz['duration_minutes'] = round($quiz['duration'] / 60);
        
        // Format dates
        $quiz['created_at_formatted'] = date('M d, Y', strtotime($quiz['created_at']));
    }
    
    echo json_encode([
        'success' => true,
        'data' => $quizzes,
        'count' => count($quizzes)
    ]);
    
} catch (PDOException $e) {
    error_log("Get Quizzes Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Get Quizzes Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
}
?>

