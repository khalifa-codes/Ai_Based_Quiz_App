<?php
/**
 * API Endpoint: Delete Notification
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
$notificationId = isset($input['notification_id']) ? (int)$input['notification_id'] : 0;

if ($notificationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
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
    
    // Get notification details first
    $getStmt = $conn->prepare("SELECT title, message, type, DATE(created_at) as date_sent FROM notifications WHERE id = ? AND teacher_id = ?");
    $getStmt->execute([$notificationId, $teacherId]);
    $notification = $getStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notification) {
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        exit();
    }
    
    // Delete all notifications with same title, message, type, and date
    // This handles grouped notifications properly
    $stmt = $conn->prepare("
        DELETE FROM notifications 
        WHERE teacher_id = ? 
        AND title = ? 
        AND message = ? 
        AND type = ? 
        AND DATE(created_at) = DATE(?)
    ");
    $stmt->execute([
        $teacherId,
        $notification['title'],
        $notification['message'],
        $notification['type'],
        $notification['date_sent']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification deleted successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Delete Notification Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting notification']);
}
?>

