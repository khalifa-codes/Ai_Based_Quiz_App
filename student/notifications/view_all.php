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
        .notification-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
            background: var(--bg-primary);
        }
        .notification-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .notification-card.unread {
            background: var(--primary-light, rgba(13, 110, 253, 0.05));
            border-color: var(--primary-color);
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
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Notifications</h2>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-primary active" data-filter="all">All</button>
                            <button class="btn btn-sm btn-outline-primary" data-filter="unread">Unread</button>
                            <button class="btn btn-sm btn-outline-primary" data-filter="read">Read</button>
                            <button class="btn btn-sm btn-outline-primary" data-filter="announcements">Announcements</button>
                            <button class="btn btn-sm btn-outline-primary" data-filter="results">Results</button>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <?php if (empty($notifications)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle me-2"></i>No notifications yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $note): ?>
                                <div class="notification-card" data-type="<?php echo htmlspecialchars($note['type']); ?>">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="notification-icon" style="width: 50px; height: 50px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="bi <?php echo $note['type'] === 'result' ? 'bi-check-circle' : 'bi-megaphone'; ?>" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">
                                                <?php echo htmlspecialchars($note['title']); ?>
                                            </h4>
                                            <p style="margin: 0 0 0.5rem 0; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.5;">
                                                <?php echo htmlspecialchars($note['description']); ?>
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span style="font-size: 0.85rem; color: var(--text-muted);">
                                                    <i class="bi bi-clock"></i> <?php echo formatRelativeTime($note['timestamp'] ?? null); ?>
                                                </span>
                                                <div class="d-flex gap-2">
                                                    <a href="<?php echo htmlspecialchars($note['url']); ?>" class="btn btn-sm btn-outline-primary">Open</a>
                                                </div>
                                            </div>
                                        </div>
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
                        card.style.display = card.getAttribute('data-type') === filter ? 'block' : 'none';
                    }
                });
            });
        });

        function markAsRead(btn) {
            const card = btn.closest('.notification-card');
            card.classList.remove('unread');
            btn.textContent = 'Read';
            btn.disabled = true;
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

