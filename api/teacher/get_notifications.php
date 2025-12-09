<?php
/**
 * API Endpoint: Get Teacher Notifications (Real-time)
 * Returns unread and recent notifications for the teacher
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
    
    // Fetch unread notifications count
    $countStmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE teacher_id = ? AND (is_read = 0 OR is_read IS NULL)");
    $countStmt->execute([$teacherId]);
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $unreadCount = (int)($countResult['unread_count'] ?? 0);
    
    // Fetch recent notifications (last 5)
    $notifStmt = $conn->prepare("
        SELECT id, title, message, type, is_read, created_at 
        FROM notifications 
        WHERE teacher_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $notifStmt->execute([$teacherId]);
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format notifications
    $formattedNotifications = [];
    foreach ($notifications as $note) {
        $formattedNotifications[] = [
            'id' => (int)$note['id'],
            'title' => $note['title'] ?? 'Notification',
            'message' => $note['message'] ?? '',
            'type' => $note['type'] ?? 'info',
            'is_read' => (int)($note['is_read'] ?? 0) === 1,
            'created_at' => $note['created_at'] ?? '',
            'time_ago' => !empty($note['created_at']) ? getTimeAgo($note['created_at']) : ''
        ];
    }
    
    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount,
        'notifications' => $formattedNotifications
    ]);
    
} catch (Exception $e) {
    error_log('Get Notifications Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching notifications']);
}

function getTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $timestamp);
}
?>

