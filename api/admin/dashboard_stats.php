<?php
/**
 * Admin Dashboard Statistics API
 * Returns real-time statistics for charts and metrics
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$period = $_GET['period'] ?? '7days'; // 7days, 30days, 90days

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Calculate date range
    $days = 7;
    if ($period === '30days') $days = 30;
    elseif ($period === '90days') $days = 90;
    
    $dateFrom = date('Y-m-d', strtotime("-$days days"));
    
    // User Growth Data (last 7/30/90 days)
    $growthData = [
        'labels' => [],
        'organizations' => [],
        'students' => [],
        'teachers' => []
    ];
    
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $growthData['labels'][] = date('M d', strtotime($date));
        
        // Count organizations created on this date
        $stmt = $conn->prepare("SELECT COUNT(*) FROM organizations WHERE DATE(created_at) = ?");
        $stmt->execute([$date]);
        $growthData['organizations'][] = (int)$stmt->fetchColumn();
        
        // Count students created on this date
        $stmt = $conn->prepare("SELECT COUNT(*) FROM students WHERE DATE(created_at) = ?");
        $stmt->execute([$date]);
        $growthData['students'][] = (int)$stmt->fetchColumn();
        
        // Count teachers created on this date
        $stmt = $conn->prepare("SELECT COUNT(*) FROM teachers WHERE DATE(created_at) = ?");
        $stmt->execute([$date]);
        $growthData['teachers'][] = (int)$stmt->fetchColumn();
    }
    
    // Quiz Activity Data
    $stmt = $conn->query("
        SELECT 
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived
        FROM quizzes
    ");
    $quizActivity = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $quizActivityData = [
        'labels' => ['Published', 'Draft', 'Archived'],
        'values' => [
            (int)($quizActivity['published'] ?? 0),
            (int)($quizActivity['draft'] ?? 0),
            (int)($quizActivity['archived'] ?? 0)
        ]
    ];
    
    // Revenue/Activity Data (using quiz submissions as activity metric)
    $revenueData = [
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        'values' => []
    ];
    
    $currentYear = date('Y');
    for ($month = 1; $month <= 12; $month++) {
        $monthStart = "$currentYear-$month-01";
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        
        // Count quiz submissions in this month (as activity metric)
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM quiz_submissions 
            WHERE DATE(submitted_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$monthStart, $monthEnd]);
        $revenueData['values'][] = (int)$stmt->fetchColumn();
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'userGrowth' => $growthData,
            'quizActivity' => $quizActivityData,
            'revenue' => $revenueData
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Dashboard Stats Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching statistics']);
}
?>

