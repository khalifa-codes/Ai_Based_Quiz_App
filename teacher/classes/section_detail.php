<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';

$teacherId = (int)($_SESSION['user_id'] ?? 0);
$deptId = isset($_GET['dept_id']) ? $_GET['dept_id'] : 'general';
$deptName = isset($_GET['dept_name']) ? urldecode($_GET['dept_name']) : 'General Department';

$students = [];
$quizzes = [];
$assignedQuizzes = [];

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    
    // Fetch students for this department
    if ($deptId !== 'general' && is_numeric($deptId)) {
        $stmt = $conn->prepare("
            SELECT s.*, COUNT(DISTINCT qs.id) as quiz_count
            FROM students s
            LEFT JOIN quiz_submissions qs ON qs.student_id = s.id
            WHERE s.organization_id = ?
            GROUP BY s.id
            ORDER BY s.name ASC
        ");
        $stmt->execute([$deptId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // For general department, get students from teacher's quizzes
        $stmt = $conn->prepare("
            SELECT DISTINCT s.*, COUNT(DISTINCT qs.id) as quiz_count
            FROM students s
            INNER JOIN quiz_submissions qs ON qs.student_id = s.id
            INNER JOIN quizzes q ON q.id = qs.quiz_id
            WHERE q.created_by = ?
            GROUP BY s.id
            ORDER BY s.name ASC
        ");
        $stmt->execute([$teacherId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fetch all quizzes created by teacher
    $stmt = $conn->prepare("
        SELECT q.*, COUNT(DISTINCT qs.id) as submission_count
        FROM quizzes q
        LEFT JOIN quiz_submissions qs ON qs.quiz_id = q.id
        WHERE q.created_by = ?
        GROUP BY q.id
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([$teacherId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch notifications
    $notifStmt = $conn->prepare("SELECT * FROM notifications WHERE teacher_id = ? ORDER BY created_at DESC LIMIT 5");
    $notifStmt->execute([$teacherId]);
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
    $notificationCount = count($notifications);
} catch (Exception $e) {
    error_log('Section detail error: ' . $e->getMessage());
    $students = [];
    $quizzes = [];
    $notifications = [];
    $notificationCount = 0;
    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($deptName); ?> - Teacher Panel</title>
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
    </style>
</head>
<body class="loading">
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
                    <li class="nav-item"><a href="class_list.php" class="nav-link active"><i class="bi bi-journal-bookmark"></i><span>Departments & Sections</span></a></li>
                    <li class="nav-item"><a href="../quizzes/quiz_list.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Examinations</span></a></li>
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
            <!-- Topbar -->
            <div class="admin-topbar">
                <div class="topbar-left">
                    <h1 class="topbar-title"><?php echo htmlspecialchars($deptName); ?></h1>
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
                                    <a href="../notifications/view_all.php" class="view-all-link" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
                                </div>
                                <div class="notification-dropdown-body" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <div class="notification-item" style="padding: 1rem 1.25rem; text-align:center;">
                                        <p style="margin:0; color: var(--text-secondary);">Loading notifications...</p>
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
                <!-- Students Section -->
                <div class="content-card mb-4">
                    <div class="content-card-header d-flex justify-content-between align-items-center">
                        <h2 class="content-card-title">Students</h2>
                        <button class="btn btn-primary" id="addStudentBtn">
                            <i class="bi bi-plus-lg"></i> Add Student
                        </button>
                    </div>
                    <div class="content-card-body">
                        <?php if (empty($students)): ?>
                            <div class="text-center" style="padding: 2rem;">
                                <i class="bi bi-mortarboard" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary);">No students in this department yet.</p>
                                <button class="btn btn-primary" id="addStudentBtnEmpty">
                                    <i class="bi bi-plus-lg"></i> Add Student
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Student ID</th>
                                            <th>Quizzes Taken</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($student['student_id'] ?? 'N/A'); ?></td>
                                                <td><?php echo number_format((int)($student['quiz_count'] ?? 0)); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger remove-student-btn" data-student-id="<?php echo $student['id']; ?>" data-student-name="<?php echo htmlspecialchars($student['name']); ?>">
                                                        <i class="bi bi-trash"></i> Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quizzes Section -->
                <div class="content-card">
                    <div class="content-card-header d-flex justify-content-between align-items-center">
                        <h2 class="content-card-title">Assign Examinations</h2>
                        <a href="../quizzes/quiz_list.php" class="btn btn-outline-primary">
                            <i class="bi bi-plus-lg"></i> Create New Quiz
                        </a>
                    </div>
                    <div class="content-card-body">
                        <?php if (empty($quizzes)): ?>
                            <div class="text-center" style="padding: 2rem;">
                                <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                <p style="color: var(--text-secondary);">No quizzes available. Create a quiz first.</p>
                                <a href="../quizzes/quiz_list.php" class="btn btn-primary">
                                    <i class="bi bi-plus-lg"></i> Create Quiz
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Quiz Title</th>
                                            <th>Subject</th>
                                            <th>Duration</th>
                                            <th>Submissions</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quizzes as $quiz): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($quiz['title'] ?? 'Untitled'); ?></td>
                                                <td><?php echo htmlspecialchars($quiz['subject'] ?? 'N/A'); ?></td>
                                                <td><?php echo round(($quiz['duration'] ?? 0) / 60); ?> min</td>
                                                <td><?php echo number_format((int)($quiz['submission_count'] ?? 0)); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $quiz['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($quiz['status'] ?? 'draft'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="../quizzes/quiz_list.php?assign=<?php echo $quiz['id']; ?>&dept_id=<?php echo urlencode($deptId); ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-check-circle"></i> Assign
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
        
        // Add Student Button
        const addStudentBtn = document.getElementById('addStudentBtn') || document.getElementById('addStudentBtnEmpty');
        if (addStudentBtn) {
            addStudentBtn.addEventListener('click', function() {
                window.location.href = '../students/student_list.php?add_to_dept=<?php echo urlencode($deptId); ?>';
            });
        }
        
        // Remove Student Button - Permanent Functionality
        const removeBtns = document.querySelectorAll('.remove-student-btn');
        removeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const studentName = this.getAttribute('data-student-name');
                const studentId = parseInt(this.getAttribute('data-student-id'));
                
                if (confirm('Are you sure you want to remove "' + studentName + '" from this department?')) {
                    // Disable button during removal
                    this.disabled = true;
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Removing...';
                    
                    fetch('../../api/teacher/remove_student_from_dept.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            student_id: studentId,
                            dept_id: '<?php echo $deptId; ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const row = this.closest('tr');
                            if (row) {
                                row.style.transition = 'opacity 0.3s ease';
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                }, 300);
                            }
                        } else {
                            this.disabled = false;
                            this.innerHTML = originalText;
                            alert(data.message || 'Error removing student');
                        }
                    })
                    .catch(error => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                        console.error('Error:', error);
                        alert('Error removing student. Please try again.');
                    });
                }
            });
        });
        
        // Assign Quiz Button - Permanent Functionality
        document.querySelectorAll('.btn-primary[href*="assign"]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                const match = href.match(/assign=(\d+).*dept_id=([^&]+)/);
                if (match) {
                    const quizId = parseInt(match[1]);
                    const deptId = decodeURIComponent(match[2]);
                    
                    const originalText = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Assigning...';
                    
                    fetch('../../api/teacher/assign_quiz.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            quiz_id: quizId,
                            dept_id: deptId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                        
                        if (data.success) {
                            alert('Quiz assigned successfully!');
                            // Update button to show assigned state
                            this.classList.remove('btn-primary');
                            this.classList.add('btn-success');
                            this.innerHTML = '<i class="bi bi-check-circle"></i> Assigned';
                            this.onclick = null; // Remove click handler
                        } else {
                            alert(data.message || 'Error assigning quiz');
                        }
                    })
                    .catch(error => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                        console.error('Error:', error);
                        alert('Error assigning quiz. Please try again.');
                    });
                }
            });
        });
        
        // Remove loading class after page load
        window.addEventListener('load', function() {
            document.body.classList.remove('loading');
        });
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>

