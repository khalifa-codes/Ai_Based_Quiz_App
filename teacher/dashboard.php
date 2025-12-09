<?php 
require_once 'auth_check.php';
require_once __DIR__ . '/../includes/branding_loader.php';
require_once __DIR__ . '/../config/database.php';

$teacherId = (int)($_SESSION['user_id'] ?? 0);

// Initialize stats
$stats = [
    'total_students' => 0,
    'students_this_month' => 0,
    'active_examinations' => 0,
    'active_now' => 0,
    'completed_examinations' => 0,
    'completed_this_week' => 0,
    'average_score' => 0,
    'score_change' => 0
];

$recentActivity = [];
$chartData = [
    'performance' => [],
    'success_rate' => []
];

$notifications = [];
$notificationCount = 0;

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    
    // Get total students count
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT s.id) as total,
               COUNT(DISTINCT CASE WHEN s.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN s.id END) as this_month
        FROM students s
        INNER JOIN quizzes q ON q.created_by = ?
        WHERE EXISTS (
            SELECT 1 FROM quiz_submissions qs 
            WHERE qs.student_id = s.id AND qs.quiz_id = q.id
        )
    ");
    $stmt->execute([$teacherId]);
    $studentData = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_students'] = (int)($studentData['total'] ?? 0);
    $stats['students_this_month'] = (int)($studentData['this_month'] ?? 0);
    
    // Get active examinations (published quizzes)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total,
               COUNT(CASE WHEN q.status = 'published' AND (q.end_date IS NULL OR q.end_date >= NOW()) THEN 1 END) as active_now
        FROM quizzes q
        WHERE q.created_by = ? AND q.status = 'published'
    ");
    $stmt->execute([$teacherId]);
    $examData = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['active_examinations'] = (int)($examData['total'] ?? 0);
    $stats['active_now'] = (int)($examData['active_now'] ?? 0);
    
    // Get completed examinations (quizzes with submissions)
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT q.id) as total,
               COUNT(DISTINCT CASE WHEN qs.submitted_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK) THEN q.id END) as this_week
        FROM quizzes q
        INNER JOIN quiz_submissions qs ON qs.quiz_id = q.id
        WHERE q.created_by = ? AND qs.status IN ('submitted', 'auto_submitted')
    ");
    $stmt->execute([$teacherId]);
    $completedData = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['completed_examinations'] = (int)($completedData['total'] ?? 0);
    $stats['completed_this_week'] = (int)($completedData['this_week'] ?? 0);
    
    // Get average score
    $stmt = $conn->prepare("
        SELECT 
            AVG(qs.percentage) as avg_score,
            AVG(CASE WHEN qs.submitted_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN qs.percentage END) as current_avg,
            AVG(CASE WHEN qs.submitted_at < DATE_SUB(NOW(), INTERVAL 1 MONTH) AND qs.submitted_at >= DATE_SUB(NOW(), INTERVAL 2 MONTH) THEN qs.percentage END) as previous_avg
        FROM quiz_submissions qs
        INNER JOIN quizzes q ON q.id = qs.quiz_id
        WHERE q.created_by = ? AND qs.status IN ('submitted', 'auto_submitted') AND qs.percentage IS NOT NULL
    ");
    $stmt->execute([$teacherId]);
    $scoreData = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['average_score'] = round((float)($scoreData['avg_score'] ?? 0), 1);
    $currentAvg = (float)($scoreData['current_avg'] ?? 0);
    $previousAvg = (float)($scoreData['previous_avg'] ?? 0);
    if ($previousAvg > 0) {
        $stats['score_change'] = round($currentAvg - $previousAvg, 1);
    }
    
    // Get recent activity (last 10 submissions and quiz creations)
    $stmt = $conn->prepare("
        SELECT 
            'Examination completed' as activity,
            s.name as student_name,
            q.title as quiz_title,
            qs.submitted_at as activity_time,
            'examination' as type,
            'completed' as status
        FROM quiz_submissions qs
        INNER JOIN quizzes q ON q.id = qs.quiz_id
        INNER JOIN students s ON s.id = qs.student_id
        WHERE q.created_by = ? AND qs.status IN ('submitted', 'auto_submitted')
        ORDER BY qs.submitted_at DESC
        LIMIT 10
    ");
    $stmt->execute([$teacherId]);
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get chart data - Performance over last 6 months
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(qs.submitted_at, '%Y-%m') as month,
            AVG(qs.percentage) as avg_score
        FROM quiz_submissions qs
        INNER JOIN quizzes q ON q.id = qs.quiz_id
        WHERE q.created_by = ? 
            AND qs.status IN ('submitted', 'auto_submitted')
            AND qs.submitted_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(qs.submitted_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute([$teacherId]);
    $chartData['performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get success rate data
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN qs.percentage >= 50 THEN 1 ELSE 0 END) as passed
        FROM quiz_submissions qs
        INNER JOIN quizzes q ON q.id = qs.quiz_id
        WHERE q.created_by = ? AND qs.status IN ('submitted', 'auto_submitted')
    ");
    $stmt->execute([$teacherId]);
    $successData = $stmt->fetch(PDO::FETCH_ASSOC);
    $chartData['success_rate'] = [
        'total' => (int)($successData['total'] ?? 0),
        'passed' => (int)($successData['passed'] ?? 0),
        'failed' => (int)($successData['total'] ?? 0) - (int)($successData['passed'] ?? 0)
    ];
    
    // Notifications will be loaded via JavaScript API
    $notifications = [];
    $notificationCount = 0;
    
} catch (Exception $e) {
    error_log('Teacher dashboard data fetch error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Teacher Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="../assets/images/logo-removebg-preview.png">
    
    <!-- Apply theme immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <style>
        .teacher-branding-logo {
            height: 70px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .notification-wrapper { position: relative; }
        .notification-btn { position: relative; }
        .notification-badge { position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff); }
        .notification-dropdown { position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 40px); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden; animation: slideDown 0.3s ease; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .notification-dropdown-header { padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-secondary); }
        .notification-dropdown-body { max-height: 400px; overflow-y: auto; }
        .notification-dropdown-body::-webkit-scrollbar { width: 6px; }
        .notification-dropdown-body::-webkit-scrollbar-track { background: var(--bg-secondary); }
        .notification-dropdown-body::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 3px; }
        .notification-item { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease; }
        .notification-item:hover { background: var(--bg-secondary); }
        .notification-item.unread { background: var(--primary-light, rgba(13, 110, 253, 0.05)); }
        .notification-item.unread:hover { background: var(--primary-light, rgba(13, 110, 253, 0.1)); }
        .notification-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .notification-dropdown-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); text-align: center; background: var(--bg-secondary); }
        .view-all-link, .view-all-btn { color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: opacity 0.2s ease; }
        .view-all-link:hover, .view-all-btn:hover { opacity: 0.8; }
        @media (max-width: 768px) {
            .notification-dropdown { width: calc(100vw - 20px); right: -10px; }
        }
    </style>
</head>
<body class="loading">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="teacherSidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo" id="teacherLogoLink">
                    <img src="../assets/images/logo-removebg-preview.png" alt="Teacher Logo" class="teacher-branding-logo" id="teacherLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="teacherSubtitle">Teacher</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="analytics/performance.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="classes/class_list.php" class="nav-link">
                            <i class="bi bi-journal-bookmark"></i>
                            <span>Departments & Sections</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="quizzes/quiz_list.php" class="nav-link">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Examinations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="students/student_list.php" class="nav-link">
                            <i class="bi bi-mortarboard"></i>
                            <span>Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="results/quiz_results.php" class="nav-link">
                            <i class="bi bi-clipboard-data"></i>
                            <span>Department Results</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="notifications/send_notification.php" class="nav-link">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user" id="sidebarUserDropdown">
                    <div class="sidebar-user-header" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; border-radius: 8px; transition: all 0.2s ease;">
                        <div class="sidebar-user-avatar">T</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;">
                            <p class="sidebar-user-name">Teacher</p>
                            <p class="sidebar-user-role">Educator</p>
                        </div>
                        <i class="bi bi-chevron-down" id="userDropdownIcon" style="transition: transform 0.2s ease; color: var(--text-muted);"></i>
                    </div>
                    <div class="sidebar-user-menu" id="sidebarUserMenu">
                        <a href="profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <main class="admin-main protected-content">
            <!-- Floating Hamburger for Mobile -->
            <button class="floating-hamburger" id="floatingHamburger">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Topbar -->
            <div class="admin-topbar">
                <div class="topbar-left">
                    <h1 class="topbar-title">Dashboard</h1>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-actions" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.75rem !important; flex-wrap: nowrap !important;">
                        <!-- Notification Bell -->
                        <div class="notification-wrapper" style="position: relative;">
                            <button class="topbar-btn notification-btn" id="notificationBtn" title="Notifications" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                                <i class="bi bi-bell" style="font-size: 1.3rem !important;"></i>
                                <span class="notification-badge" id="notificationBadge" style="position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: none; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff);">0</span>
                            </button>
                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 40px); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden;">
                                <div class="notification-dropdown-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-secondary);">
                                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">Notifications</h3>
                                    <a href="notifications/view_all.php" class="view-all-link" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
                                </div>
                                <div class="notification-dropdown-body" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <div class="notification-item" style="padding: 1rem 1.25rem; text-align:center;">
                                        <p style="margin:0; color: var(--text-secondary);">Loading notifications...</p>
                                    </div>
                                </div>
                                <div class="notification-dropdown-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); text-align: center; background: var(--bg-secondary);">
                                    <a href="notifications/view_all.php" class="view-all-btn" style="color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem;">Show All Messages</a>
                                </div>
                            </div>
                        </div>
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="admin-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Students</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($stats['total_students']); ?></div>
                        <div class="stat-card-change <?php echo $stats['students_this_month'] > 0 ? 'positive' : ''; ?>">
                            <?php if ($stats['students_this_month'] > 0): ?>
                            <i class="bi bi-arrow-up"></i>
                            <span><?php echo $stats['students_this_month']; ?> new this month</span>
                            <?php else: ?>
                            <span>No new students</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Active Examinations</h3>
                            <div class="stat-card-icon green">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($stats['active_examinations']); ?></div>
                        <div class="stat-card-change <?php echo $stats['active_now'] > 0 ? 'positive' : ''; ?>">
                            <?php if ($stats['active_now'] > 0): ?>
                            <i class="bi bi-arrow-up"></i>
                            <span><?php echo $stats['active_now']; ?> active now</span>
                            <?php else: ?>
                            <span>No active exams</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Completed Examinations</h3>
                            <div class="stat-card-icon purple">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($stats['completed_examinations']); ?></div>
                        <div class="stat-card-change <?php echo $stats['completed_this_week'] > 0 ? 'positive' : ''; ?>">
                            <?php if ($stats['completed_this_week'] > 0): ?>
                            <i class="bi bi-arrow-up"></i>
                            <span><?php echo $stats['completed_this_week']; ?> this week</span>
                            <?php else: ?>
                            <span>No completions this week</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Average Score</h3>
                            <div class="stat-card-icon orange">
                                <i class="bi bi-star"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($stats['average_score'], 1); ?>%</div>
                        <div class="stat-card-change <?php echo $stats['score_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php if ($stats['score_change'] != 0): ?>
                            <i class="bi bi-arrow-<?php echo $stats['score_change'] >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo abs($stats['score_change']); ?>% <?php echo $stats['score_change'] >= 0 ? 'increase' : 'decrease'; ?></span>
                            <?php else: ?>
                            <span>No change</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Student Performance</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="studentPerformanceChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Examination Success Rate</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="quizSuccessRateChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Recent Activity</h2>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>Student</th>
                                        <th>Type</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentActivity)): ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                                                No recent activity
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentActivity as $activity): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                                <td><?php echo htmlspecialchars($activity['student_name'] ?? 'N/A'); ?></td>
                                                <td><span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($activity['type'] ?? 'Examination')); ?></span></td>
                                                <td><?php echo !empty($activity['activity_time']) ? date('M d, Y H:i', strtotime($activity['activity_time'])) : 'N/A'; ?></td>
                                                <td><span class="badge badge-success"><?php echo htmlspecialchars(ucfirst($activity['status'] ?? 'Completed')); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-functions.js"></script>
    
    <script>
        // Theme Management
        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            }
        }

        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        if (themeToggle) {
            let isToggling = false;
            
            themeToggle.addEventListener('mousedown', function(e) {
                e.preventDefault();
            });
            
            themeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (isToggling) return;
                isToggling = true;
                
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeIcon(newTheme);
                
                setTimeout(() => {
                    isToggling = false;
                }, 300);
            });
        }

        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const floatingHamburger = document.getElementById('floatingHamburger');
        const teacherSidebar = document.getElementById('teacherSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            teacherSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'flex';
            }
        }

        function openSidebar() {
            teacherSidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'none';
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebar();
            });
        }

        if (floatingHamburger) {
            floatingHamburger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openSidebar();
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                closeSidebar();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && teacherSidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // User Dropdown Toggle
        const sidebarUserDropdown = document.getElementById('sidebarUserDropdown');
        const sidebarUserMenu = document.getElementById('sidebarUserMenu');

        if (sidebarUserDropdown && sidebarUserMenu) {
            const userHeader = sidebarUserDropdown.querySelector('.sidebar-user-header');
            
            if (userHeader) {
                userHeader.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebarUserDropdown.classList.toggle('active');
                });
            }

            document.addEventListener('click', function(e) {
                if (!sidebarUserDropdown.contains(e.target)) {
                    sidebarUserDropdown.classList.remove('active');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebarUserDropdown.classList.contains('active')) {
                    sidebarUserDropdown.classList.remove('active');
                }
            });
        }

        // Notification functionality is handled by notifications.js

        // Remove loading class after page load
        window.addEventListener('load', function() {
            document.body.classList.remove('loading');
        });

        // Charts
        const chartColors = {
            primary: getComputedStyle(document.documentElement).getPropertyValue('--primary-color') || '#0d6efd',
            success: getComputedStyle(document.documentElement).getPropertyValue('--success-color') || '#198754',
            warning: getComputedStyle(document.documentElement).getPropertyValue('--warning-color') || '#ffc107',
            danger: getComputedStyle(document.documentElement).getPropertyValue('--danger-color') || '#dc3545',
            info: getComputedStyle(document.documentElement).getPropertyValue('--info-color') || '#0dcaf0'
        };

        // Student Performance Chart
        const studentPerfCtx = document.getElementById('studentPerformanceChart');
        if (studentPerfCtx) {
            const performanceData = <?php echo json_encode($chartData['performance']); ?>;
            const perfLabels = performanceData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short' });
            });
            const perfScores = performanceData.map(item => parseFloat(item.avg_score || 0));
            
            // If no data, show empty chart
            if (perfLabels.length === 0) {
                perfLabels.push('No Data');
                perfScores.push(0);
            }
            
            new Chart(studentPerfCtx, {
                type: 'line',
                data: {
                    labels: perfLabels,
                    datasets: [{
                        label: 'Average Score',
                        data: perfScores,
                        borderColor: chartColors.primary,
                        backgroundColor: chartColors.primary + '20',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 70,
                            max: 100
                        }
                    }
                }
            });
        }

        // Examination Success Rate Chart
        const quizSuccessCtx = document.getElementById('quizSuccessRateChart');
        if (quizSuccessCtx) {
            const successData = <?php echo json_encode($chartData['success_rate']); ?>;
            const total = successData.total || 0;
            const passed = successData.passed || 0;
            const failed = successData.failed || 0;
            
            new Chart(quizSuccessCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Passed', 'Failed'],
                    datasets: [{
                        data: total > 0 ? [passed, failed] : [0, 0],
                        backgroundColor: [chartColors.success, chartColors.danger],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
    <script src="assets/js/notifications.js"></script>
</body>
</html>

