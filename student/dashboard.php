<?php
require_once 'auth_check.php';
require_once __DIR__ . '/../includes/branding_loader.php';
require_once __DIR__ . '/includes/student_data_helper.php';

$studentId = (int)($_SESSION['user_id'] ?? 0);

$stats = [
    'total_quizzes' => 0,
    'completed_quizzes' => 0,
    'pending_quizzes' => 0,
    'in_progress' => 0,
    'average_score' => 0,
];
$recentSubmissions = [];
$upcomingQuizzes = [];
$notifications = [];
$scoreTrendLabels = [];
$scoreTrendValues = [];
$distributionBuckets = [
    'excellent' => 0,
    'passed' => 0,
    'average' => 0,
    'failed' => 0,
];
$subjectTotals = [];
$subjectCounts = [];
$subjectPerformance = [];
$statusBadges = [
    'submitted' => ['label' => 'Completed', 'class' => 'bg-success'],
    'auto_submitted' => ['label' => 'Auto Submitted', 'class' => 'bg-danger'],
    'in_progress' => ['label' => 'In Progress', 'class' => 'bg-warning'],
    'pending' => ['label' => 'Pending', 'class' => 'bg-secondary'],
];
$performanceDistributionData = [
    'labels' => ['Excellent (90-100)', 'Good (70-89)', 'Average (50-69)', 'Below 50'],
    'values' => [0, 0, 0, 0],
];
$subjectPerformanceData = ['labels' => [], 'values' => []];
$scoreTrendDataset = ['labels' => [], 'values' => []];

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    $stats = fetchStudentStats($conn, $studentId);
    $recentSubmissions = fetchStudentRecentSubmissions($conn, $studentId, 10);
    $upcomingQuizzes = fetchStudentUpcomingQuizzes($conn, $studentId, 5);
    $notifications = buildStudentNotifications($recentSubmissions, $upcomingQuizzes);

    // Chart data prep
    $chronological = array_reverse($recentSubmissions);
    foreach ($chronological as $submission) {
        $labelSource = $submission['submitted_at'] ?? $submission['started_at'];
        if ($labelSource) {
            $scoreTrendLabels[] = date('M d', strtotime($labelSource));
        } else {
            $scoreTrendLabels[] = $submission['title'];
        }
        $scoreTrendValues[] = $submission['score_percent'] ?? null;

        $bucket = categorizeScore($submission['score_percent']);
        if (isset($distributionBuckets[$bucket])) {
            $distributionBuckets[$bucket]++;
        }

        $subjectKey = $submission['subject'] ?? 'General';
        if (!isset($subjectTotals[$subjectKey])) {
            $subjectTotals[$subjectKey] = 0;
            $subjectCounts[$subjectKey] = 0;
        }
        if ($submission['score_percent'] !== null) {
            $subjectTotals[$subjectKey] += $submission['score_percent'];
            $subjectCounts[$subjectKey]++;
        }
    }

    foreach ($subjectTotals as $subject => $totalScore) {
        $count = $subjectCounts[$subject] ?? 0;
        if ($count > 0) {
            $subjectPerformance[$subject] = round($totalScore / $count, 2);
        }
    }

    foreach ($scoreTrendLabels as $idx => $label) {
        $value = $scoreTrendValues[$idx] ?? null;
        if ($value !== null) {
            $scoreTrendDataset['labels'][] = $label;
            $scoreTrendDataset['values'][] = $value;
        }
    }
    if (empty($scoreTrendDataset['labels'])) {
        $scoreTrendDataset = [
            'labels' => ['No data'],
            'values' => [0],
        ];
    }

    $performanceDistributionData['values'] = [
        $distributionBuckets['excellent'],
        $distributionBuckets['passed'],
        $distributionBuckets['average'],
        $distributionBuckets['failed'],
    ];

    foreach ($subjectPerformance as $subject => $average) {
        $subjectPerformanceData['labels'][] = $subject;
        $subjectPerformanceData['values'][] = $average;
    }
    if (empty($subjectPerformanceData['labels'])) {
        $subjectPerformanceData = [
            'labels' => ['No data'],
            'values' => [0],
        ];
    }
} catch (Throwable $e) {
    error_log('Student dashboard data error: ' . $e->getMessage());
}

$notificationCount = count($notifications);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Panel</title>
    
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
        .student-branding-logo {
            height: 70px !important;
            width: auto !important;
            max-width: 160px !important;
            object-fit: contain !important;
            flex-shrink: 0 !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
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
        .stat-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin: 0;
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
            margin-top: 1rem;
        }
        
        .chart-container canvas {
            max-height: 400px !important;
            width: 100% !important;
            height: 100% !important;
        }
        
        .content-card-body canvas {
            max-height: 400px !important;
            width: 100% !important;
        }
        
        .admin-table .btn {
            min-width: 120px;
            white-space: nowrap;
        }
        
        /* Ensure topbar matches other modules */
        .admin-topbar {
            padding: 1rem 1.5rem !important;
            min-height: auto !important;
        }
        
        .topbar-title {
            font-size: 2rem !important;
            font-weight: 700 !important;
            margin: 0 !important;
        }
        
        /* Override loading state styles to prevent flash */
        body.loading .admin-main {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        body.loading .admin-content {
            max-width: 1600px !important;
            width: 100% !important;
            margin: 0 auto !important;
            padding: 1.5rem !important;
            padding-left: 2rem !important;
            box-sizing: border-box !important;
        }
        
        body.loading .content-card {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        
        body.loading .row {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .admin-main {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .admin-content {
            max-width: 1600px !important;
            width: 100% !important;
            margin: 0 auto !important;
            padding: 1.5rem !important;
            padding-left: 2rem !important;
            box-sizing: border-box !important;
        }
        
        .content-card {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        
        .row {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        @media (min-width: 1200px) {
            body.loading .admin-content,
            .admin-content {
                max-width: 1600px !important;
                width: 100% !important;
            }
        }
        
        @media (min-width: 1400px) {
            body.loading .admin-content,
            .admin-content {
                max-width: 1600px !important;
                width: 100% !important;
            }
        }
        
        @media (min-width: 1600px) {
            body.loading .admin-content,
            .admin-content {
                max-width: 1600px !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="loading">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="studentSidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo" id="studentLogoLink">
                    <img src="../assets/images/logo-removebg-preview.png" alt="Student Logo" class="student-branding-logo" id="studentLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="studentSubtitle">Student</span>
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
                        <a href="performance/statistics.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Performance</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Examinations</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="quizzes/available_quizzes.php" class="nav-link">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Available Examinations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="results/results.php" class="nav-link">
                            <i class="bi bi-clipboard-data"></i>
                            <span>Results</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user" id="sidebarUserDropdown">
                    <div class="sidebar-user-header" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; border-radius: 8px; transition: all 0.2s ease;">
                        <div class="sidebar-user-avatar">S</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;">
                            <p class="sidebar-user-name">Student</p>
                            <p class="sidebar-user-role">Learner</p>
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
                            <button class="topbar-btn notification-btn" id="notificationBtn" title="Notifications" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 44px !important; height: 44px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                                <i class="bi bi-bell" style="font-size: 1.3rem !important;"></i>
                                <?php if ($notificationCount > 0): ?>
                                <span class="notification-badge" id="notificationBadge" style="position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff);">
                                    <?php echo $notificationCount; ?>
                                </span>
                                <?php endif; ?>
                            </button>
                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 40px); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden;">
                                <div class="notification-dropdown-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-secondary);">
                                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">Notifications</h3>
                                    <a href="notifications/view_all.php" class="view-all-link" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
                                </div>
                                <div class="notification-dropdown-body" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <?php if (empty($notifications)): ?>
                                        <div class="notification-item" style="padding: 1rem 1.25rem; text-align:center;">
                                            <p style="margin:0; color: var(--text-secondary);">No notifications yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($notifications as $note): ?>
                                            <div class="notification-item" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease;">
                                                <a href="<?php echo htmlspecialchars($note['url']); ?>" style="text-decoration:none; color:inherit; display:flex; gap:0.75rem;">
                                                    <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                        <i class="bi <?php echo $note['type'] === 'result' ? 'bi-check-circle' : 'bi-megaphone'; ?>"></i>
                                                    </div>
                                                    <div style="flex:1; min-width:0;">
                                                        <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">
                                                            <?php echo htmlspecialchars($note['title']); ?>
                                                        </h4>
                                                        <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">
                                                            <?php echo htmlspecialchars($note['description']); ?>
                                                        </p>
                                                        <span style="font-size: 0.75rem; color: var(--text-muted);">
                                                            <?php echo formatRelativeTime($note['timestamp'] ?? null); ?>
                                                        </span>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="notification-dropdown-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); text-align: center; background: var(--bg-secondary);">
                                    <a href="notifications/view_all.php" class="view-all-btn" style="color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem;">Show All Messages</a>
                                </div>
                            </div>
                        </div>
                        <!-- Theme Toggle -->
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 44px !important; height: 44px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon" style="font-size: 1.2rem !important;"></i>
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
                            <h3 class="stat-card-title">Total Examinations</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="totalExams"><?php echo number_format($stats['total_quizzes']); ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span><?php echo $stats['total_quizzes'] > 0 ? 'All time' : 'No exams yet'; ?></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Completed</h3>
                            <div class="stat-card-icon green">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="completedExams"><?php echo number_format($stats['completed_quizzes']); ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span><?php echo $stats['completed_quizzes'] > 0 ? 'Completed' : 'Awaiting submissions'; ?></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Pending</h3>
                            <div class="stat-card-icon orange">
                                <i class="bi bi-clock-history"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="pendingExams"><?php echo number_format($stats['pending_quizzes']); ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span><?php echo $stats['pending_quizzes'] > 0 ? 'Upcoming' : 'All caught up'; ?></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Average Score</h3>
                            <div class="stat-card-icon purple">
                                <i class="bi bi-trophy"></i>
                            </div>
                        </div>
                        <div class="stat-card-value" id="averageScore">
                            <?php echo $stats['average_score'] ? $stats['average_score'] . '%' : '—'; ?>
                        </div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span><?php echo $stats['average_score'] ? 'Recent average' : 'No scores yet'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Performance Trend</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="performanceChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Score Distribution</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="scoreDistributionChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Examinations -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Recent Examinations</h2>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Examination</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentSubmissions)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No examinations attempted yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentSubmissions as $submission): 
                                            $statusKey = $submission['status'] ?? 'pending';
                                            $badge = $statusBadges[$statusKey] ?? $statusBadges['pending'];
                                            $scorePercent = $submission['score_percent'];
                                            $scoreClass = 'var(--text-secondary)';
                                            $scoreCategory = categorizeScore($scorePercent);
                                            if ($scoreCategory === 'excellent') {
                                                $scoreClass = 'var(--success-color)';
                                            } elseif ($scoreCategory === 'passed') {
                                                $scoreClass = 'var(--primary-color)';
                                            } elseif ($scoreCategory === 'failed') {
                                                $scoreClass = 'var(--danger-color)';
                                            }
                                            $scoreText = $scorePercent !== null ? number_format($scorePercent, 2) . '%' : '—';
                                            $dateSource = $submission['submitted_at'] ?? $submission['started_at'];
                                            if (!empty($dateSource)) {
                                                $dateTimestamp = strtotime($dateSource);
                                                $dateText = $dateTimestamp !== false ? date('M d, Y', $dateTimestamp) : '—';
                                            } else {
                                                $dateText = '—';
                                            }
                                            $actionLabel = 'Start Exam';
                                            $actionClass = 'btn btn-sm btn-primary';
                                            $actionUrl = 'quizzes/quiz_instructions.php?id=' . $submission['quiz_id'];
                                            if (in_array($statusKey, ['submitted', 'auto_submitted'], true)) {
                                                $actionLabel = 'View Result';
                                                $actionClass = 'btn btn-sm btn-outline-primary';
                                                $actionUrl = 'results/result_detail.php?id=' . $submission['submission_id'];
                                            } elseif ($statusKey === 'in_progress') {
                                                $actionLabel = 'Resume Exam';
                                                $actionClass = 'btn btn-sm btn-warning';
                                                $actionUrl = 'quizzes/quiz_window.php?id=' . $submission['quiz_id'];
                                            }
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($submission['title']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($submission['subject'] ?? '—'); ?></td>
                                            <td><span class="badge <?php echo $badge['class']; ?>"><?php echo $badge['label']; ?></span></td>
                                            <td><strong style="color: <?php echo $scoreClass; ?>;"><?php echo $scoreText; ?></strong></td>
                                            <td><?php echo $dateText; ?></td>
                                            <td><a href="<?php echo htmlspecialchars($actionUrl); ?>" class="<?php echo $actionClass; ?>" style="min-width: 120px;"><?php echo $actionLabel; ?></a></td>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-functions.js"></script>
    <script src="assets/js/common.js"></script>
    <script>
        const scoreTrendData = <?php echo json_encode($scoreTrendDataset, JSON_UNESCAPED_UNICODE); ?>;
        const perfDistributionData = <?php echo json_encode($performanceDistributionData, JSON_UNESCAPED_UNICODE); ?>;

        const performanceCtx = document.getElementById('performanceChart');
        if (performanceCtx) {
            new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: scoreTrendData.labels,
                    datasets: [{
                        label: 'Average Score',
                        data: scoreTrendData.values,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0d6efd',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 2,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            min: 0,
                            max: 100,
                            ticks: {
                                color: '#6c757d'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6c757d'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        }

        const scoreDistCtx = document.getElementById('scoreDistributionChart');
        if (scoreDistCtx) {
            new Chart(scoreDistCtx, {
                type: 'doughnut',
                data: {
                    labels: perfDistributionData.labels,
                    datasets: [{
                        data: perfDistributionData.values,
                        backgroundColor: [
                            '#198754',
                            '#0d6efd',
                            '#ffc107',
                            '#dc3545'
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: '#212529',
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script>
        // Remove loading class immediately after DOM is ready to prevent flash
        // But keep styles applied during loading state
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                // Small delay to ensure styles are applied
                setTimeout(function() {
                    document.body.classList.remove('loading');
                }, 50);
            });
        } else {
            // DOM already loaded
            setTimeout(function() {
                document.body.classList.remove('loading');
            }, 50);
        }
    </script>
</body>
</html>

