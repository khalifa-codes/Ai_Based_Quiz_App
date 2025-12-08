<?php
require_once '../auth_check.php';
require_once __DIR__ . '/../includes/student_data_helper.php';

$studentId = (int)($_SESSION['user_id'] ?? 0);
$recentSubmissions = [];
$upcomingQuizzes = [];
$notifications = [];
$notificationCount = 0;
$scoreTrendDataset = ['labels' => [], 'values' => []];
$performanceDistributionData = ['labels' => ['Excellent (90-100)', 'Good (70-89)', 'Average (50-69)', 'Below 50'], 'values' => [0, 0, 0, 0]];
$subjectPerformanceData = ['labels' => [], 'values' => []];
$improvementData = ['labels' => [], 'values' => []];
$distributionBuckets = [
    'excellent' => 0,
    'passed' => 0,
    'average' => 0,
    'failed' => 0,
];

try {
    $db = Database::getInstance()->getConnection();
    $recentSubmissions = fetchStudentRecentSubmissions($db, $studentId, 20);
    $upcomingQuizzes = fetchStudentUpcomingQuizzes($db, $studentId, 5);
    $notifications = buildStudentNotifications($recentSubmissions, $upcomingQuizzes);
    $notificationCount = count($notifications);

    $chronological = array_reverse($recentSubmissions);
    $subjectTotals = [];
    $subjectCounts = [];
    $previousScore = null;

    foreach ($chronological as $submission) {
        $labelSource = $submission['submitted_at'] ?? $submission['started_at'];
        $label = $labelSource ? date('M d', strtotime($labelSource)) : $submission['title'];
        $score = $submission['score_percent'];

        if ($score !== null) {
            $scoreTrendDataset['labels'][] = $label;
            $scoreTrendDataset['values'][] = $score;

            if ($previousScore !== null) {
                $improvementData['labels'][] = $label;
                $improvementData['values'][] = round($score - $previousScore, 2);
            }
            $previousScore = $score;
        }

        $bucket = categorizeScore($score);
        if (isset($distributionBuckets[$bucket])) {
            $distributionBuckets[$bucket]++;
        }

        $subjectKey = $submission['subject'] ?? 'General';
        if (!isset($subjectTotals[$subjectKey])) {
            $subjectTotals[$subjectKey] = 0;
            $subjectCounts[$subjectKey] = 0;
        }
        if ($score !== null) {
            $subjectTotals[$subjectKey] += $score;
            $subjectCounts[$subjectKey]++;
        }
    }

    foreach ($subjectTotals as $subject => $total) {
        $count = $subjectCounts[$subject] ?? 0;
        if ($count > 0) {
            $subjectPerformanceData['labels'][] = $subject;
            $subjectPerformanceData['values'][] = round($total / $count, 2);
        }
    }

    if (empty($subjectPerformanceData['labels'])) {
        $subjectPerformanceData = ['labels' => ['No data'], 'values' => [0]];
    }
    if (empty($scoreTrendDataset['labels'])) {
        $scoreTrendDataset = ['labels' => ['No data'], 'values' => [0]];
    }
    if (empty($improvementData['labels'])) {
        $improvementData = ['labels' => ['Latest'], 'values' => [0]];
    }

    $performanceDistributionData['values'] = [
        $distributionBuckets['excellent'],
        $distributionBuckets['passed'],
        $distributionBuckets['average'],
        $distributionBuckets['failed'],
    ];
} catch (Throwable $e) {
    error_log('Student performance data error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Statistics - Student Panel</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
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
        
        #studentLogo {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            height: 70px !important;
            width: auto !important;
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
        .admin-content {
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
            padding: 1.5rem !important;
            padding-left: 2rem !important;
            margin-top: 1.5rem;
        }
        
        .content-card-body {
            padding: 1.5rem;
        }
        
        .content-card-body canvas {
            max-height: 400px !important;
            width: 100% !important;
        }
        
        .content-card-body canvas {
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="studentSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo" id="studentLogoLink">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Student Logo" class="student-branding-logo" id="studentLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="studentSubtitle">Student</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-x-lg"></i></button>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="statistics.php" class="nav-link active"><i class="bi bi-graph-up"></i><span>Performance</span></a></li>
                </ul>
                <div class="nav-section-title">Examinations</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../quizzes/available_quizzes.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Available Examinations</span></a></li>
                    <li class="nav-item"><a href="../results/results.php" class="nav-link"><i class="bi bi-clipboard-data"></i><span>Results</span></a></li>
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
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-person"></i><span>Profile</span></a>
                        <a href="../../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
                    </div>
                </div>
            </div>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <main class="admin-main">
            <button class="floating-hamburger" id="floatingHamburger"><i class="bi bi-list"></i></button>
            <div class="admin-topbar">
                <div class="topbar-left">
                    <div>
                        <h1 class="topbar-title">Performance Statistics</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Performance</li>
                            </ol>
                        </nav>
                    </div>
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
                                    <a href="../notifications/view_all.php" class="view-all-link" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
                                </div>
                                <div class="notification-dropdown-body" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <?php if (empty($notifications)): ?>
                                        <div class="notification-item" style="padding: 1rem 1.25rem; text-align:center;">
                                            <p style="margin:0; color: var(--text-secondary);">No notifications yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($notifications as $note): ?>
                                            <div class="notification-item" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer;">
                                                <a href="<?php echo htmlspecialchars($note['url']); ?>" style="text-decoration:none; color:inherit; display:flex; gap:0.75rem;">
                                                    <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                        <i class="bi <?php echo $note['type'] === 'result' ? 'bi-check-circle' : 'bi-megaphone'; ?>"></i>
                                                    </div>
                                                    <div style="flex: 1; min-width: 0;">
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
                                    <a href="../notifications/view_all.php" class="view-all-btn" style="color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem;">Show All Messages</a>
                                </div>
                            </div>
                        </div>
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 44px !important; height: 44px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="admin-content" style="margin-top: 1.5rem;">
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Score Trend</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="scoreTrendChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Performance Distribution</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="performanceDistributionChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Subject Performance</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="subjectPerformanceChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Improvement Rate</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="improvementRateChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        const scoreTrendData = <?php echo json_encode($scoreTrendDataset, JSON_UNESCAPED_UNICODE); ?>;
        const performanceDistData = <?php echo json_encode($performanceDistributionData, JSON_UNESCAPED_UNICODE); ?>;
        const subjectPerfData = <?php echo json_encode($subjectPerformanceData, JSON_UNESCAPED_UNICODE); ?>;
        const improvementData = <?php echo json_encode($improvementData, JSON_UNESCAPED_UNICODE); ?>;

        // Score Trend Chart
        const scoreTrendCtx = document.getElementById('scoreTrendChart');
        if (scoreTrendCtx) {
            new Chart(scoreTrendCtx, {
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

        // Performance Distribution Chart
        const performanceDistCtx = document.getElementById('performanceDistributionChart');
        if (performanceDistCtx) {
            new Chart(performanceDistCtx, {
                type: 'doughnut',
                data: {
                    labels: performanceDistData.labels,
                    datasets: [{
                        data: performanceDistData.values,
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

        // Subject Performance Chart
        const subjectPerfCtx = document.getElementById('subjectPerformanceChart');
        if (subjectPerfCtx) {
            new Chart(subjectPerfCtx, {
                type: 'bar',
                data: {
                    labels: subjectPerfData.labels,
                    datasets: [{
                        label: 'Average Score',
                        data: subjectPerfData.values,
                        backgroundColor: subjectPerfData.values.map(() => '#0d6efd'),
                        borderWidth: 0,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
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
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Improvement Rate Chart
        const improvementRateCtx = document.getElementById('improvementRateChart');
        if (improvementRateCtx) {
            new Chart(improvementRateCtx, {
                type: 'line',
                data: {
                    labels: improvementData.labels,
                    datasets: [{
                        label: 'Improvement %',
                        data: improvementData.values,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 1.5,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
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
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>

