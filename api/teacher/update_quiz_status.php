<?php
/**
 * API Endpoint: Update Quiz Status (Published/Draft)
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
$quizId = isset($input['quiz_id']) ? (int)$input['quiz_id'] : 0;
$status = isset($input['status']) ? $input['status'] : '';

if ($quizId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
    exit();
}

if (!in_array($status, ['published', 'draft'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status. Must be "published" or "draft"']);
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
    
    // Verify quiz belongs to teacher
    $checkStmt = $conn->prepare("SELECT id FROM quizzes WHERE id = ? AND created_by = ?");
    $checkStmt->execute([$quizId, $teacherId]);
    if (!$checkStmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to update this quiz']);
        exit();
    }
    
    // Update quiz status
    $stmt = $conn->prepare("UPDATE quizzes SET status = ?, updated_at = NOW() WHERE id = ? AND created_by = ?");
    $stmt->execute([$status, $quizId, $teacherId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Quiz status updated successfully',
        'status' => $status
    ]);
    
} catch (Exception $e) {
    error_log('Update Quiz Status Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating quiz status']);
}
?>

