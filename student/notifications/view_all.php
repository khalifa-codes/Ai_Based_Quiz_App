<?php
require_once '../auth_check.php';
require_once __DIR__ . '/../includes/student_data_helper.php';

$studentId = (int)($_SESSION['user_id'] ?? 0);
$recentSubmissions = [];
$upcomingQuizzes = [];
$notifications = [];

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    $recentSubmissions = fetchStudentRecentSubmissions($conn, $studentId, 20);
    $upcomingQuizzes = fetchStudentUpcomingQuizzes($conn, $studentId, 20);
    $notifications = buildStudentNotifications($recentSubmissions, $upcomingQuizzes);
} catch (Throwable $e) {
    error_log('Student notifications fetch error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notifications - Student Panel</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link rel="stylesheet" href="../../../assets/css/admin.css">
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
        .notification-page-header { margin-bottom: 2rem; }
        .notification-filters { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .filter-btn { padding: 0.5rem 1.25rem; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary); border-radius: 8px; cursor: pointer; transition: all 0.2s ease; }
        .filter-btn:hover { background: var(--primary-light); border-color: var(--primary-color); }
        .filter-btn.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .notification-card { padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 1.5rem; transition: all 0.2s ease; background: var(--bg-primary); }
        .notification-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-2px); }
        .notification-card.unread { border-left: 4px solid var(--primary-color); background: var(--primary-light, rgba(13, 110, 253, 0.05)); }
        .notification-header { display: flex; align-items: start; gap: 1rem; margin-bottom: 0.75rem; }
        .notification-icon-large { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.25rem; }
        .notification-content { flex: 1; min-width: 0; }
        .notification-title { margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary); }
        .notification-message { margin: 0 0 0.75rem 0; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6; }
        .notification-meta { display: flex; align-items: center; gap: 1rem; font-size: 0.85rem; color: var(--text-muted); }
        .notification-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .mark-read-btn, .delete-btn { padding: 0.4rem 0.8rem; border: none; border-radius: 6px; font-size: 0.85rem; cursor: pointer; transition: all 0.2s ease; }
        .mark-read-btn { background: var(--primary-color); color: white; }
        .mark-read-btn:hover { opacity: 0.9; }
        .delete-btn { background: var(--danger-color, #dc3545); color: white; }
        .delete-btn:hover { opacity: 0.9; }
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.5; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="studentSidebar">
            <div class="sidebar-header">
                <a href="../../dashboard.php" class="sidebar-logo" id="studentLogoLink">
                    <img src="../../../assets/images/logo-removebg-preview.png" alt="Student Logo" class="student-branding-logo" id="studentLogo">
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
                    <li class="nav-item"><a href="../../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="../../performance/statistics.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Performance</span></a></li>
                </ul>
                <div class="nav-section-title">Examinations</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../../quizzes/available_quizzes.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Available Examinations</span></a></li>
                    <li class="nav-item"><a href="../../results/results.php" class="nav-link"><i class="bi bi-clipboard-data"></i><span>Results</span></a></li>
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
                        <a href="../../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-person"></i><span>Profile</span></a>
                        <a href="../../../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
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
                        <h1 class="topbar-title">All Notifications</h1>
                        <nav aria-label="breadcrumb" style="margin-top: 0.5rem;">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="view_all.php">Notifications</a></li>
                                <li class="breadcrumb-item active">All Messages</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-actions" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.75rem !important; flex-wrap: nowrap !important;">
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="admin-content">
                <div class="content-card" style="padding: 2rem;">
                    <!-- Filters -->
                    <div class="notification-filters" style="margin-bottom: 2rem;">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="unread">Unread</button>
                        <button class="filter-btn" data-filter="read">Read</button>
                        <button class="filter-btn" data-filter="exam">Examinations</button>
                        <button class="filter-btn" data-filter="result">Results</button>
                    </div>

                    <!-- Notifications List -->
                    <div id="notificationsContainer" style="margin-top: 1.5rem;">
                        <?php if (empty($notifications)): ?>
                            <div class="empty-state">
                                <i class="bi bi-bell-slash"></i>
                                <h3>No Notifications</h3>
                                <p>You don't have any notifications yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $note): ?>
                                <div class="notification-card unread" data-type="<?php echo htmlspecialchars($note['type']); ?>">
                                    <div class="notification-header">
                                        <div class="notification-icon-large" style="background: <?php echo $note['type'] === 'result' ? 'var(--success-color, #198754)' : 'var(--primary-color)'; ?>; color: white;">
                                            <i class="bi <?php echo $note['type'] === 'result' ? 'bi-check-circle' : 'bi-megaphone'; ?>"></i>
                                        </div>
                                        <div class="notification-content">
                                            <h3 class="notification-title"><?php echo htmlspecialchars($note['title']); ?></h3>
                                            <p class="notification-message"><?php echo htmlspecialchars($note['description']); ?></p>
                                            <div class="notification-meta">
                                                <span><i class="bi bi-clock"></i> <?php echo formatRelativeTime($note['timestamp'] ?? null); ?></span>
                                                <span><i class="bi bi-tag"></i> <?php echo $note['type'] === 'result' ? 'Results' : 'Examination'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-actions">
                                        <button class="mark-read-btn" onclick="markAsRead(this)"><i class="bi bi-check-circle"></i> Mark as Read</button>
                                        <button class="delete-btn" onclick="deleteNotification(this)"><i class="bi bi-trash"></i> Delete</button>
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
    <script src="../../../assets/js/admin-functions.js"></script>
    <script src="../../assets/js/common.js"></script>
    <script>
        // Filter functionality
        document.querySelectorAll('[data-filter]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                const cards = document.querySelectorAll('.notification-card');
                
                cards.forEach(card => {
                    if (filter === 'all') {
                        card.style.display = 'block';
                    } else if (filter === 'unread') {
                        card.style.display = card.classList.contains('unread') ? 'block' : 'none';
                    } else if (filter === 'read') {
                        card.style.display = card.classList.contains('unread') ? 'none' : 'block';
                    } else {
                        const cardType = card.getAttribute('data-type');
                        if (filter === 'exam') {
                            card.style.display = cardType === 'exam' ? 'block' : 'none';
                        } else if (filter === 'result') {
                            card.style.display = cardType === 'result' ? 'block' : 'none';
                        } else {
                            card.style.display = cardType === filter ? 'block' : 'none';
                        }
                    }
                });
            });
        });

        function markAsRead(btn) {
            const card = btn.closest('.notification-card');
            card.classList.remove('unread');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Read';
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
        }

        function deleteNotification(btn) {
            if (confirm('Are you sure you want to delete this notification?')) {
                btn.closest('.notification-card').remove();
            }
        }
    </script>
    <script src="../../assets/js/activity-tracker.js"></script>
</body>
</html>

