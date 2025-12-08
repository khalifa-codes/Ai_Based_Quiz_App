<?php 
require_once '../auth_check.php';
require_once '../../config/database.php';

// Get student ID
$studentId = intval($_SESSION['user_id']);

// Fetch quizzes from database
$availableQuizzes = [];
$completedQuizzes = [];
$notifications = [];
$notificationCount = 0;

try {
    $db = Database::getInstance();
    if (!$db) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $db->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    
    // Fetch all published quizzes
    $stmt = $conn->prepare("
        SELECT q.id, q.title, q.subject, q.duration, q.total_questions, q.created_at,
               COUNT(DISTINCT qu.id) as actual_questions
        FROM quizzes q
        LEFT JOIN questions qu ON q.id = qu.quiz_id
        WHERE q.status = 'published'
        GROUP BY q.id, q.title, q.subject, q.duration, q.total_questions, q.created_at
        ORDER BY q.created_at DESC
    ");
    $stmt->execute();
    $allQuizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Optimize: Fetch all submissions in one query instead of N+1 queries
    $quizIds = array_column($allQuizzes, 'id');
    $submissionsMap = [];
    
    if (!empty($quizIds)) {
        $placeholders = implode(',', array_fill(0, count($quizIds), '?'));
        $submissionStmt = $conn->prepare("
            SELECT qs.id, qs.quiz_id, qs.status, qs.submitted_at, qs.started_at
            FROM quiz_submissions qs
            INNER JOIN (
                SELECT quiz_id, MAX(started_at) as max_started_at
                FROM quiz_submissions
                WHERE quiz_id IN ($placeholders) AND student_id = ?
                GROUP BY quiz_id
            ) latest ON qs.quiz_id = latest.quiz_id 
                AND qs.started_at = latest.max_started_at
                AND qs.student_id = ?
        ");
        $params = array_merge($quizIds, [$studentId, $studentId]);
        $submissionStmt->execute($params);
        
        while ($submission = $submissionStmt->fetch(PDO::FETCH_ASSOC)) {
            $submissionsMap[$submission['quiz_id']] = $submission;
        }
    }
    
    // Map submissions to quizzes
    foreach ($allQuizzes as $quiz) {
        $submission = $submissionsMap[$quiz['id']] ?? null;
        
        // Add submission info to quiz
        $quiz['submission'] = $submission;
        $quiz['is_completed'] = ($submission && ($submission['status'] === 'submitted' || $submission['status'] === 'auto_submitted'));
        $quiz['is_in_progress'] = ($submission && $submission['status'] === 'in_progress');
        
        if ($quiz['is_completed']) {
            $completedQuizzes[] = $quiz;
        } else {
            $availableQuizzes[] = $quiz;
        }
    }
    
    // Fetch notifications for badge count
    require_once __DIR__ . '/../includes/student_data_helper.php';
    $recentSubmissions = fetchStudentRecentSubmissions($conn, $studentId, 10);
    $upcomingQuizzes = fetchStudentUpcomingQuizzes($conn, $studentId, 5);
    $notifications = buildStudentNotifications($recentSubmissions, $upcomingQuizzes);
    $notificationCount = count($notifications);
    
} catch (Exception $e) {
    error_log("Error fetching quizzes: " . $e->getMessage());
    $availableQuizzes = [];
    $completedQuizzes = [];
    $notifications = [];
    $notificationCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Examinations - Student Panel</title>
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
            padding: 1.5rem !important;
            padding-left: 2rem !important;
            margin-top: 1.5rem;
        }
        
        .content-card-body {
            padding: 1.5rem;
        }
        
        #quizSearch {
            max-width: 400px;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        
        #statusFilter {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        .quiz-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            background: var(--bg-primary);
        }
        .quiz-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }
        .quiz-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .quiz-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        .quiz-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .quiz-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .quiz-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .quiz-info-item i {
            color: var(--primary-color);
        }
        .quiz-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
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
                    <li class="nav-item"><a href="available_quizzes.php" class="nav-link active"><i class="bi bi-file-earmark-text"></i><span>Available Examinations</span></a></li>
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
                        <h1 class="topbar-title">Available Examinations</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Available Examinations</li>
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
                                    <?php echo $notificationCount > 99 ? '99+' : $notificationCount; ?>
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
                    <div class="content-card-body">
                        <!-- Filter Section -->
                        <div class="d-flex gap-2 mb-4 flex-wrap">
                            <input type="text" class="form-control" id="quizSearch" placeholder="Search examinations..." style="flex: 1; min-width: 200px;">
                            <select class="form-select" id="statusFilter" style="width: auto; min-width: 150px;">
                                <option value="all">All Status</option>
                                <option value="available">Available</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <!-- Available Quizzes Section -->
                        <h4 class="mb-3" style="color: var(--text-primary); font-weight: 600;">
                            <i class="bi bi-file-earmark-text me-2"></i>Available Examinations
                        </h4>
                        <div id="availableQuizzesContainer" class="mb-5">
                            <?php if (empty($availableQuizzes)): ?>
                                <div class="alert alert-info" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>No available examinations at the moment.
                                </div>
                            <?php else: ?>
                                <?php foreach ($availableQuizzes as $quiz): ?>
                                    <div class="quiz-card" data-status="<?php echo $quiz['is_in_progress'] ? 'pending' : 'available'; ?>" data-quiz-id="<?php echo $quiz['id']; ?>">
                                        <div class="quiz-card-header">
                                            <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                            <span class="quiz-status badge <?php echo $quiz['is_in_progress'] ? 'bg-warning' : 'bg-success'; ?>">
                                                <?php echo $quiz['is_in_progress'] ? 'In Progress' : 'Available'; ?>
                                            </span>
                                        </div>
                                        <div class="quiz-info">
                                            <div class="quiz-info-item">
                                                <i class="bi bi-book"></i>
                                                <span><?php echo htmlspecialchars($quiz['subject']); ?></span>
                                            </div>
                                            <div class="quiz-info-item">
                                                <i class="bi bi-clock"></i>
                                                <span>Duration: <?php 
                                                    // Duration is stored in seconds, convert to minutes for display
                                                    $durationMinutes = round($quiz['duration'] / 60, 1);
                                                    // If less than 1 minute, show in seconds
                                                    if ($durationMinutes < 1) {
                                                        echo $quiz['duration'] . ' seconds';
                                                    } else {
                                                        // Remove .0 if it's a whole number
                                                        echo ($durationMinutes == (int)$durationMinutes) ? (int)$durationMinutes : $durationMinutes;
                                                        echo ' minutes';
                                                    }
                                                ?></span>
                                            </div>
                                            <div class="quiz-info-item">
                                                <i class="bi bi-calendar"></i>
                                                <span>Created: <?php echo !empty($quiz['created_at']) ? date('M d, Y', strtotime($quiz['created_at'])) : '—'; ?></span>
                                            </div>
                                            <div class="quiz-info-item">
                                                <i class="bi bi-question-circle"></i>
                                                <span><?php echo $quiz['actual_questions']; ?> Questions</span>
                                            </div>
                                        </div>
                                        <div class="quiz-actions">
                                            <?php if ($quiz['is_in_progress']): ?>
                                                <a href="quiz_window.php?id=<?php echo $quiz['id']; ?>" class="btn btn-warning">
                                                    <i class="bi bi-arrow-clockwise"></i> Resume Examination
                                                </a>
                                            <?php else: ?>
                                                <a href="quiz_instructions.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">
                                                    <i class="bi bi-play-circle"></i> Start Examination
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-secondary" onclick="viewDetails(<?php echo $quiz['id']; ?>)">
                                                <i class="bi bi-info-circle"></i> Details
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Completed Quizzes Section -->
                        <h4 class="mb-3" style="color: var(--text-primary); font-weight: 600; margin-top: 2rem !important;">
                            <i class="bi bi-check-circle me-2"></i>Completed Examinations
                        </h4>
                        <div id="completedQuizzesContainer">
                            <?php if (empty($completedQuizzes)): ?>
                                <div class="alert alert-secondary" role="alert">
                                    <i class="bi bi-inbox me-2"></i>No completed examinations yet.
                                </div>
                            <?php else: ?>
                                <?php foreach ($completedQuizzes as $quiz): ?>
                                    <div class="quiz-card" data-status="completed" data-quiz-id="<?php echo $quiz['id']; ?>">
                                        <div class="quiz-card-header">
                                            <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                                            <span class="quiz-status badge bg-secondary">Completed</span>
                                        </div>
                                        <div class="quiz-info">
                                            <div class="quiz-info-item">
                                                <i class="bi bi-book"></i>
                                                <span><?php echo htmlspecialchars($quiz['subject']); ?></span>
                                            </div>
                                            <div class="quiz-info-item">
                                                <i class="bi bi-clock"></i>
                                                <span>Duration: <?php echo $quiz['duration']; ?> minutes</span>
                                            </div>
                                            <div class="quiz-info-item">
                                                <i class="bi bi-calendar"></i>
                                                <span>Completed: <?php 
                                                    if ($quiz['submission'] && !empty($quiz['submission']['submitted_at'])) {
                                                        $submittedDate = strtotime($quiz['submission']['submitted_at']);
                                                        echo $submittedDate !== false ? date('M d, Y', $submittedDate) : '—';
                                                    } elseif ($quiz['submission'] && !empty($quiz['submission']['started_at'])) {
                                                        $startedDate = strtotime($quiz['submission']['started_at']);
                                                        echo $startedDate !== false ? date('M d, Y', $startedDate) : '—';
                                                    } else {
                                                        echo '—';
                                                    }
                                                ?></span>
                                            </div>
                                            <div class="quiz-info-item">
                                                <i class="bi bi-question-circle"></i>
                                                <span><?php echo $quiz['actual_questions']; ?> Questions</span>
                                            </div>
                                        </div>
                                        <div class="quiz-actions">
                                            <?php 
                                            // Get submission_id from submission data for result detail link
                                            $submissionIdForResult = null;
                                            if ($quiz['submission'] && isset($quiz['submission']['id'])) {
                                                $submissionIdForResult = $quiz['submission']['id'];
                                            }
                                            ?>
                                            <?php if ($submissionIdForResult): ?>
                                                <a href="../results/result_detail.php?id=<?php echo $submissionIdForResult; ?>" class="btn btn-outline-primary">
                                                    <i class="bi bi-clipboard-data"></i> View Results
                                                </a>
                                            <?php else: ?>
                                                <span class="btn btn-outline-secondary disabled" title="Results not available yet">
                                                    <i class="bi bi-clipboard-data"></i> Results Pending
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
        // Search and Filter Functionality
        const quizSearch = document.getElementById('quizSearch');
        const statusFilter = document.getElementById('statusFilter');

        function filterQuizzes() {
            const searchTerm = quizSearch.value.toLowerCase();
            const statusValue = statusFilter.value;

            // Get all quiz cards from both containers
            const allCards = document.querySelectorAll('.quiz-card');
            
            allCards.forEach(card => {
                const title = card.querySelector('.quiz-title').textContent.toLowerCase();
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

        // Initialize filter on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (quizSearch) {
                quizSearch.addEventListener('input', filterQuizzes);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', filterQuizzes);
            }
        });

        function viewDetails(quizId) {
            // Redirect to quiz instructions page
            window.location.href = 'quiz_instructions.php?id=' + quizId;
        }
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>

