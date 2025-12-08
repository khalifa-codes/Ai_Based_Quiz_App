<?php
require_once '../auth_check.php';
require_once __DIR__ . '/../includes/student_data_helper.php';

$studentId = (int)($_SESSION['user_id'] ?? 0);
$resultSubmissions = [];
$answerStats = [];
$upcomingQuizzes = [];
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
    $resultSubmissions = fetchStudentRecentSubmissions($conn, $studentId, null);
    $submissionIds = array_column($resultSubmissions, 'submission_id');
    $answerStats = fetchSubmissionAnswerStats($db, $submissionIds);
    $upcomingQuizzes = fetchStudentUpcomingQuizzes($db, $studentId, 5);
    $notifications = buildStudentNotifications($resultSubmissions, $upcomingQuizzes);
    $notificationCount = count($notifications);
} catch (Throwable $e) {
    error_log('Student results fetch error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Student Panel</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="../../assets/images/logo-removebg-preview.png">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
            padding: 0 1.5rem;
        }
        .result-card {
            border: 2px solid;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        .result-card.excellent {
            border-color: var(--success-color);
            background: rgba(25, 135, 84, 0.05);
        }
        .result-card.passed {
            border-color: var(--primary-color);
            background: rgba(13, 110, 253, 0.05);
        }
        .result-card.failed {
            border-color: var(--danger-color);
            background: rgba(220, 53, 69, 0.05);
        }
        .result-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .result-score {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .result-score.excellent {
            color: var(--success-color);
        }
        .result-score.passed {
            color: var(--primary-color);
        }
        .result-score.failed {
            color: var(--danger-color);
        }
        .result-status {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .result-status.excellent {
            color: var(--success-color);
        }
        .result-status.passed {
            color: var(--primary-color);
        }
        .result-status.failed {
            color: var(--danger-color);
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
                    <li class="nav-item"><a href="../performance/statistics.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Performance</span></a></li>
                </ul>
                <div class="nav-section-title">Examinations</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../quizzes/available_quizzes.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Available Examinations</span></a></li>
                    <li class="nav-item"><a href="results.php" class="nav-link active"><i class="bi bi-clipboard-data"></i><span>Results</span></a></li>
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
                        <h1 class="topbar-title">Results</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Results</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-actions" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.75rem !important; flex-wrap: nowrap !important;">
                        <div class="notification-wrapper" style="position: relative;">
                            <button class="topbar-btn notification-btn" id="notificationBtn" title="Notifications" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 44px !important; height: 44px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                                <i class="bi bi-bell" style="font-size: 1.3rem !important;"></i>
                                <?php if ($notificationCount > 0): ?>
                                <span class="notification-badge" id="notificationBadge" style="position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff);">
                                    <?php echo $notificationCount; ?>
                                </span>
                                <?php endif; ?>
                            </button>
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
            <div class="admin-content">
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Examination Results</h2>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" id="resultSearch" placeholder="Search examinations..." style="width: 250px;">
                            <select class="form-select" id="resultFilter" style="width: auto; min-width: 150px;">
                                <option value="all">All Results</option>
                                <option value="excellent">Excellent</option>
                                <option value="passed">Passed</option>
                                <option value="average">Average</option>
                                <option value="failed">Failed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <?php if (empty($resultSubmissions)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>No examination results available yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($resultSubmissions as $submission):
                                $scorePercent = $submission['score_percent'];
                                $statusCategory = categorizeScore($scorePercent);
                                $cardClass = $statusCategory;
                                $scoreClass = $statusCategory;
                                if ($statusCategory === 'pending') {
                                    $cardClass = 'passed';
                                }
                                $submittedAt = $submission['submitted_at'] ?? $submission['started_at'];
                                if (!empty($submittedAt)) {
                                    $submittedTimestamp = strtotime($submittedAt);
                                    $submittedText = $submittedTimestamp !== false ? date('M d, Y', $submittedTimestamp) : '—';
                                } else {
                                    $submittedText = '—';
                                }
                                $timeTaken = $submission['time_taken'] ?? null;
                                $timeTakenText = $timeTaken ? ceil($timeTaken / 60) . ' minutes' : '—';
                                $stat = $answerStats[$submission['submission_id']] ?? ['correct' => 0, 'answered_total' => 0];
                                $totalQuestions = $submission['total_questions'] ?? $stat['answered_total'] ?? 0;
                                $scoreText = $scorePercent !== null ? number_format($scorePercent, 2) . '%' : 'Pending';
                                ?>
                                <div class="result-card <?php echo htmlspecialchars($cardClass); ?>" data-status="<?php echo htmlspecialchars($statusCategory); ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($submission['title']); ?></h3>
                                            <p style="margin: 0 0 1rem 0; color: var(--text-secondary);"><?php echo htmlspecialchars($submission['subject'] ?? 'General'); ?></p>
                                            <div class="result-score <?php echo htmlspecialchars($scoreClass); ?>"><?php echo $scoreText; ?></div>
                                            <div class="result-status <?php echo htmlspecialchars($scoreClass); ?>">
                                                <?php echo ucfirst($statusCategory); ?>
                                            </div>
                                            <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary); font-size: 0.9rem;">
                                                <i class="bi bi-calendar"></i> Submitted: <?php echo $submittedText; ?><br>
                                                <i class="bi bi-clock"></i> Time Taken: <?php echo $timeTakenText; ?><br>
                                                <i class="bi bi-check-circle"></i> Correct Answers: <?php echo number_format($stat['correct'] ?? 0); ?>/<?php echo number_format($totalQuestions ?: ($stat['answered_total'] ?? 0)); ?>
                                            </p>
                                        </div>
                                        <a href="result_detail.php?id=<?php echo $submission['submission_id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        // Search and Filter Functionality
        const resultSearch = document.getElementById('resultSearch');
        const resultFilter = document.getElementById('resultFilter');
        const resultCards = document.querySelectorAll('.result-card');

        function filterResults() {
            const searchTerm = resultSearch.value.toLowerCase();
            const statusValue = resultFilter.value;

            resultCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const status = card.getAttribute('data-status');
                
                const matchesSearch = title.includes(searchTerm);
                const matchesStatus = statusValue === 'all' || status === statusValue;

                if (matchesSearch && matchesStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        if (resultSearch) {
            resultSearch.addEventListener('input', filterResults);
        }
        if (resultFilter) {
            resultFilter.addEventListener('change', filterResults);
        }
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>

