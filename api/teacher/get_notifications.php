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
    
    // For teachers, count unique notifications they sent (grouped to avoid duplicates)
    // Since teachers send notifications, badge count shows unique notifications sent
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as unread_count 
        FROM (
            SELECT DISTINCT title, message, type, DATE(created_at) as date_sent
            FROM notifications 
            WHERE teacher_id = ? AND (is_read = 0 OR is_read IS NULL)
        ) as unique_notifs
    ");
    $countStmt->execute([$teacherId]);
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $unreadCount = (int)($countResult['unread_count'] ?? 0);
    
    // Fetch recent unique notifications (last 5) - notifications sent by this teacher
    // Group by title, message, type, and date to show unique notifications
    $notifStmt = $conn->prepare("
        SELECT 
            MIN(id) as id,
            title, 
            message, 
            type, 
            MIN(is_read) as is_read,
            MAX(created_at) as created_at,
            COUNT(*) as recipient_count
        FROM notifications 
        WHERE teacher_id = ? 
        GROUP BY title, message, type, DATE(created_at)
        ORDER BY MAX(created_at) DESC 
        LIMIT 5
    ");
    $notifStmt->execute([$teacherId]);
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format notifications
    $formattedNotifications = [];
    foreach ($notifications as $note) {
        $recipientCount = (int)($note['recipient_count'] ?? 1);
        $message = $note['message'] ?? '';
        if ($recipientCount > 1) {
            $message .= " (Sent to {$recipientCount} students)";
        }
        
        $formattedNotifications[] = [
            'id' => (int)$note['id'],
            'title' => $note['title'] ?? 'Notification',
            'message' => $message,
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

