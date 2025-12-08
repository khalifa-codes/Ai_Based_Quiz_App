<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';

$teacherId = $_SESSION['user_id'] ?? 0;
$quizzes = [];
$totalQuizzes = 0;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            q.id,
            q.title,
            q.subject,
            q.duration,
            q.total_questions,
            q.total_marks,
            q.status,
            q.ai_provider,
            q.created_at,
            COUNT(DISTINCT qs.id) as submission_count
        FROM quizzes q
        LEFT JOIN quiz_submissions qs ON q.id = qs.quiz_id
        WHERE q.created_by = ?
        GROUP BY q.id
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([$teacherId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalQuizzes = count($quizzes);
    
    // Determine quiz type for each quiz
    foreach ($quizzes as &$quiz) {
        $typeStmt = $conn->prepare("
            SELECT DISTINCT question_type 
            FROM questions 
            WHERE quiz_id = ?
        ");
        $typeStmt->execute([$quiz['id']]);
        $questionTypes = $typeStmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('subjective', $questionTypes)) {
            $quiz['type'] = 'subjective';
        } else {
            $quiz['type'] = 'objective';
        }
        
        $quiz['duration_minutes'] = round($quiz['duration'] / 60);
    }
} catch (Exception $e) {
    error_log("Quiz List Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examinations - Teacher Panel</title>
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
        .teacher-branding-logo { height: 70px; width: auto; max-width: 160px; object-fit: contain; flex-shrink: 0; }
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
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 26px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }
        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="teacherSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo" id="teacherLogoLink">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Teacher Logo" class="teacher-branding-logo" id="teacherLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="teacherSubtitle">Teacher</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-x-lg"></i></button>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="../analytics/performance.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Analytics</span></a></li>
                </ul>
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../classes/class_list.php" class="nav-link"><i class="bi bi-journal-bookmark"></i><span>Departments & Sections</span></a></li>
                    <li class="nav-item"><a href="quiz_list.php" class="nav-link active"><i class="bi bi-file-earmark-text"></i><span>Examinations</span></a></li>
                    <li class="nav-item"><a href="../students/student_list.php" class="nav-link"><i class="bi bi-mortarboard"></i><span>Students</span></a></li>
                    <li class="nav-item"><a href="../results/quiz_results.php" class="nav-link"><i class="bi bi-clipboard-data"></i><span>Department Results</span></a></li>
                    <li class="nav-item"><a href="../notifications/send_notification.php" class="nav-link"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
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
                        <h1 class="topbar-title">Examinations</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Examinations</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-actions" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.75rem !important; flex-wrap: nowrap !important;">
                        <!-- Notification Bell -->
                        <div class="notification-wrapper" style="position: relative;">
                            <button class="topbar-btn notification-btn" id="notificationBtn" title="Notifications" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                                <i class="bi bi-bell" style="font-size: 1.3rem !important;"></i>
                                <span class="notification-badge" id="notificationBadge" style="position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff);">3</span>
                            </button>
                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 40px); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden;">
                                <div class="notification-dropdown-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-secondary);">
                                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">Notifications</h3>
                                    <a href="../notifications/view_all.php" class="view-all-link" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
                                </div>
                                <div class="notification-dropdown-body" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <div class="notification-item unread" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease; background: var(--primary-light, rgba(13, 110, 253, 0.05));">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-megaphone"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">New Examination Schedule</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">Data Structures Midterm examination has been scheduled for next week.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">2 hours ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-item unread" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease;">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--success-color, #198754); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-check-circle"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">Results Published</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">Database Systems Assignment results are now available for review.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">5 hours ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-item" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease;">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--info-color, #0dcaf0); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-info-circle"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">System Update</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">New features have been added to the examination system.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">1 day ago</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification-dropdown-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); text-align: center; background: var(--bg-secondary);">
                                    <a href="../notifications/view_all.php" class="view-all-btn" style="color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem;">Show All Messages</a>
                                </div>
                            </div>
                        </div>
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="admin-content">
                <div class="content-card mb-4">
                    <div class="content-card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="admin-form-label">Search</label>
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="quizSearch" placeholder="Search by title or code...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="admin-form-label">Type</label>
                                <select class="admin-form-control" id="typeFilter">
                                    <option value="all">All Types</option>
                                    <option value="objective">Objective</option>
                                    <option value="subjective">Subjective</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="admin-form-label">Status</label>
                                <select class="admin-form-control" id="statusFilter">
                                    <option value="all">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="admin-form-label">Actions</label>
                                <div class="d-flex gap-2 align-items-end">
                                    <a href="quiz_type_select.php" class="btn btn-primary" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-plus-lg"></i> Create
                                    </a>
                                    <button class="btn btn-outline-secondary" id="exportQuizzesBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                    <button class="btn btn-outline-danger" id="clearFiltersBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">All Examinations</h2>
                        <span class="badge badge-primary" id="quizCount" style="font-size: 1.5rem; padding: 0.6rem 1.2rem; font-weight: 700;"><?php echo $totalQuizzes; ?> Examination<?php echo $totalQuizzes !== 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Examination Title</th>
                                        <th>Subject</th>
                                        <th>Type</th>
                                        <th>Questions</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="quizTableBody">
                                    <?php if (empty($quizzes)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <p class="text-muted mb-0">No examinations found. <a href="quiz_type_select.php">Create your first examination</a></p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($quizzes as $index => $quiz): ?>
                                            <tr>
                                                <td><strong><?php echo $index + 1; ?></strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem; background: rgba(13, 110, 253, 0.1); color: var(--primary-color);">
                                                            <i class="bi bi-file-earmark-text"></i>
                                                        </div>
                                                        <div>
                                                            <h6 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($quiz['title']); ?></h6>
                                                            <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($quiz['subject'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $quiz['type'] === 'subjective' ? 'badge-warning' : 'badge-info'; ?>">
                                                        <?php echo ucfirst($quiz['type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $quiz['total_questions']; ?> questions</td>
                                                <td><?php echo $quiz['duration_minutes']; ?> min</td>
                                                <td>
                                                    <label class="toggle-switch">
                                                        <input type="checkbox" class="status-toggle" data-id="<?php echo $quiz['id']; ?>" <?php echo $quiz['status'] === 'published' ? 'checked' : ''; ?>>
                                                        <span class="toggle-slider"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="action-btn view" title="View" data-id="<?php echo $quiz['id']; ?>">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="action-btn edit" title="Edit" data-id="<?php echo $quiz['id']; ?>">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="action-btn delete" title="Delete" data-id="<?php echo $quiz['id']; ?>">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
    <script>
        // Theme Management
        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        if (themeToggle) {
            let isToggling = false;
            themeToggle.addEventListener('mousedown', e => e.preventDefault());
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
                setTimeout(() => { isToggling = false; }, 300);
            });
        }
        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const floatingHamburger = document.getElementById('floatingHamburger');
        const teacherSidebar = document.getElementById('teacherSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        function closeSidebar() {
            teacherSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) floatingHamburger.style.display = 'flex';
        }
        function openSidebar() {
            teacherSidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            if (floatingHamburger) floatingHamburger.style.display = 'none';
        }
        if (sidebarToggle) sidebarToggle.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); closeSidebar(); });
        if (floatingHamburger) floatingHamburger.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); openSidebar(); });
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && teacherSidebar.classList.contains('active')) closeSidebar(); });
        // User Dropdown
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
            document.addEventListener('click', e => { if (!sidebarUserDropdown.contains(e.target)) sidebarUserDropdown.classList.remove('active'); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape' && sidebarUserDropdown.classList.contains('active')) sidebarUserDropdown.classList.remove('active'); });
        }
        // Table Search
        const quizSearch = document.getElementById('quizSearch');
        const quizTableBody = document.getElementById('quizTableBody');
        if (quizSearch) {
            quizSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = quizTableBody.getElementsByTagName('tr');
                Array.from(rows).forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
                updateQuizCount();
            });
        }
        // Status Filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const filterValue = this.value.toLowerCase();
                const rows = quizTableBody.getElementsByTagName('tr');
                Array.from(rows).forEach(row => {
                    if (filterValue === 'all') {
                        row.style.display = '';
                    } else {
                        const statusToggle = row.querySelector('.status-toggle');
                        const isActive = statusToggle && statusToggle.checked;
                        if (filterValue === 'active' && isActive) row.style.display = '';
                        else if (filterValue === 'inactive' && !isActive) row.style.display = '';
                        else if (filterValue !== 'active' && filterValue !== 'inactive') row.style.display = '';
                        else row.style.display = 'none';
                    }
                });
                updateQuizCount();
            });
        }
        // Type Filter
        const typeFilter = document.getElementById('typeFilter');
        if (typeFilter) {
            typeFilter.addEventListener('change', function() {
                const filterValue = this.value.toLowerCase();
                const rows = quizTableBody.getElementsByTagName('tr');
                Array.from(rows).forEach(row => {
                    if (filterValue === 'all') {
                        row.style.display = '';
                    } else {
                        const badges = row.querySelectorAll('.badge');
                        let found = false;
                        badges.forEach(badge => {
                            if (badge.textContent.toLowerCase() === filterValue) found = true;
                        });
                        row.style.display = found ? '' : 'none';
                    }
                });
                updateQuizCount();
            });
        }
        // Update Quiz Count
        function updateQuizCount() {
            const visibleRows = Array.from(quizTableBody.getElementsByTagName('tr')).filter(row => row.style.display !== 'none');
            const quizCount = document.getElementById('quizCount');
            if (quizCount) quizCount.textContent = `${visibleRows.length} Examinations`;
        }
        // Toggle Switches
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const quizId = this.getAttribute('data-id');
                const status = this.checked ? 'active' : 'inactive';
                // TODO: Implement API call to update status
                console.log(`Quiz ${quizId} status changed to ${status}`);
            });
        });
        document.querySelectorAll('.timer-toggle').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const quizId = this.getAttribute('data-id');
                const timer = this.checked ? 'on' : 'off';
                // TODO: Implement API call to update timer
                console.log(`Quiz ${quizId} timer changed to ${timer}`);
            });
        });
        // Action Buttons
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const quizId = this.getAttribute('data-id');
                window.location.href = `preview_quiz.php?id=${quizId}`;
            });
        });
        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const quizId = this.getAttribute('data-id');
                window.location.href = `upload_quiz.php?id=${quizId}&edit=1`;
            });
        });
        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const quizId = this.getAttribute('data-id');
                const row = this.closest('tr');
                const quizTitle = row.querySelector('h6')?.textContent || 'this examination';
                if (confirm(`Are you sure you want to delete ${quizTitle}? This action cannot be undone.`)) {
                    // TODO: Implement delete functionality
                    alert('Examination deleted successfully!');
                    row.remove();
                    updateQuizCount();
                }
            });
        });
        // Clear Filters
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                if (quizSearch) quizSearch.value = '';
                if (statusFilter) statusFilter.value = 'all';
                if (typeFilter) typeFilter.value = 'all';
                const rows = quizTableBody.getElementsByTagName('tr');
                Array.from(rows).forEach(row => row.style.display = '');
                updateQuizCount();
            });
        }
        // Export
        const exportQuizzesBtn = document.getElementById('exportQuizzesBtn');
        if (exportQuizzesBtn) {
            exportQuizzesBtn.addEventListener('click', function() {
                alert('Export feature will be available after backend integration.');
            });
        }
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>

