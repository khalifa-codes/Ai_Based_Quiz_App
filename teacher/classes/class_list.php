<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';

$teacherId = (int)($_SESSION['user_id'] ?? 0);
$notifications = [];
$notificationCount = 0;
$departments = [];

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    
    // Fetch all organizations (departments) that teacher has access to
    $stmt = $conn->prepare("
        SELECT 
            o.id as organization_id,
            o.name as department_name,
            o.created_at,
            COUNT(DISTINCT s.id) as student_count,
            COUNT(DISTINCT q.id) as quiz_count,
            GROUP_CONCAT(DISTINCT q.subject SEPARATOR ', ') as subjects
        FROM organizations o
        LEFT JOIN students s ON s.organization_id = o.id
        LEFT JOIN quizzes q ON q.created_by = ? AND (q.organization_id = o.id OR q.organization_id IS NULL)
        WHERE o.status = 'active'
        GROUP BY o.id, o.name, o.created_at
        ORDER BY o.name ASC
    ");
    $stmt->execute([$teacherId]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no departments found, check for general departments from quizzes
    if (empty($departments)) {
        $stmt = $conn->prepare("
            SELECT DISTINCT
                COALESCE(q.subject, 'General') as department_name,
                NULL as organization_id,
                NULL as created_at,
                COUNT(DISTINCT s.id) as student_count,
                COUNT(DISTINCT q.id) as quiz_count,
                GROUP_CONCAT(DISTINCT q.subject SEPARATOR ', ') as subjects
            FROM quizzes q
            LEFT JOIN quiz_submissions qs ON qs.quiz_id = q.id
            LEFT JOIN students s ON s.id = qs.student_id
            WHERE q.created_by = ?
            GROUP BY q.subject
            ORDER BY department_name ASC
        ");
        $stmt->execute([$teacherId]);
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fetch notifications for teacher
    $notifStmt = $conn->prepare("SELECT * FROM notifications WHERE teacher_id = ? ORDER BY created_at DESC LIMIT 5");
    $notifStmt->execute([$teacherId]);
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);
    $notificationCount = count($notifications);
} catch (Exception $e) {
    error_log('Teacher class list data fetch error: ' . $e->getMessage());
    $notifications = [];
    $notificationCount = 0;
    $departments = [];
    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments & Sections - Teacher Panel</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
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
                    <div>
                        <h1 class="topbar-title">Departments & Sections</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Departments & Sections</li>
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
                <div class="content-card mb-4">
                    <div class="content-card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="admin-form-label">Search</label>
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="classSearch" placeholder="Search by department or section...">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="admin-form-label">Actions</label>
                                <div class="d-flex gap-2 align-items-end">
                                    <button class="btn btn-primary" id="addClassBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-plus-lg"></i> Add Department
                                    </button>
                                    <button class="btn btn-outline-secondary" id="exportClassesBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">All Departments & Sections</h2>
                        <span class="badge badge-primary" id="deptCount" style="font-size: 1.5rem; padding: 0.6rem 1.2rem; font-weight: 700;"><?php echo count($departments); ?> Department<?php echo count($departments) !== 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Department Name</th>
                                        <th>Subjects</th>
                                        <th>Students</th>
                                        <th>Examinations</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="deptTableBody">
                                    <?php if (empty($departments)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-journal-bookmark" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                                                <p class="text-muted mb-0">No departments found. <button class="btn btn-link p-0" id="addDeptFromEmpty" style="text-decoration: none;">Create your first department</button></p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($departments as $index => $dept): ?>
                                            <tr>
                                                <td><strong><?php echo $index + 1; ?></strong></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem; background: rgba(13, 110, 253, 0.1); color: var(--primary-color);">
                                                            <i class="bi bi-journal-bookmark"></i>
                                                        </div>
                                                        <div>
                                                            <h6 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($dept['department_name']); ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($dept['subjects'] ?? 'No subjects'); ?></td>
                                                <td><strong><?php echo number_format((int)($dept['student_count'] ?? 0)); ?></strong></td>
                                                <td><strong><?php echo number_format((int)($dept['quiz_count'] ?? 0)); ?></strong></td>
                                                <td><?php echo !empty($dept['created_at']) ? date('M d, Y', strtotime($dept['created_at'])) : 'N/A'; ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="section_detail.php?dept_id=<?php echo htmlspecialchars($dept['organization_id'] ?? 'general'); ?>&dept_name=<?php echo urlencode($dept['department_name']); ?>" class="action-btn view" title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <button class="action-btn delete-dept-btn" data-dept-id="<?php echo htmlspecialchars($dept['organization_id'] ?? 'general'); ?>" data-dept-name="<?php echo htmlspecialchars($dept['department_name']); ?>" title="Remove Department" style="background: var(--danger-color); color: white;">
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
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: var(--bg-primary); border: 1px solid var(--border-color);">
                <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                    <h5 class="modal-title" id="addDepartmentModalLabel" style="color: var(--text-primary);">Add New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--text-primary);"></button>
                </div>
                <form id="addDepartmentForm">
                    <div class="modal-body" style="padding: 1.5rem;">
                        <div class="mb-3">
                            <label for="deptName" class="form-label" style="color: var(--text-primary); font-weight: 500;">Department Name <span style="color: var(--danger-color);">*</span></label>
                            <input type="text" class="form-control" id="deptName" name="dept_name" required placeholder="e.g., Computer Science" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <small class="form-text" style="color: var(--text-secondary);">Enter a unique department name</small>
                        </div>
                        <div class="mb-3">
                            <label for="deptCode" class="form-label" style="color: var(--text-primary); font-weight: 500;">Department Code</label>
                            <input type="text" class="form-control" id="deptCode" name="dept_code" placeholder="e.g., CS" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary);">
                            <small class="form-text" style="color: var(--text-secondary);">Optional: Short code for the department</small>
                        </div>
                        <div class="mb-3">
                            <label for="deptDescription" class="form-label" style="color: var(--text-primary); font-weight: 500;">Description</label>
                            <textarea class="form-control" id="deptDescription" name="dept_description" rows="3" placeholder="Enter department description..." style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary);"></textarea>
                        </div>
                        <input type="hidden" name="teacher_id" value="<?php echo $teacherId; ?>">
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Create Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
        // Department Search
        const classSearch = document.getElementById('classSearch');
        if (classSearch) {
            classSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('#deptTableBody tr');
                rows.forEach(row => {
                    if (row.querySelector('td[colspan]')) return;
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
        
        // Add Department Modal
        const addClassBtn = document.getElementById('addClassBtn');
        const addDeptFromEmpty = document.getElementById('addDeptFromEmpty');
        const addDeptModal = document.getElementById('addDepartmentModal');
        const addDeptForm = document.getElementById('addDepartmentForm');
        
        function openAddModal() {
            if (addDeptModal) {
                const modal = new bootstrap.Modal(addDeptModal);
                modal.show();
            }
        }
        
        if (addClassBtn) {
            addClassBtn.addEventListener('click', openAddModal);
        }
        if (addDeptFromEmpty) {
            addDeptFromEmpty.addEventListener('click', openAddModal);
        }
        
        if (addDeptForm) {
            addDeptForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
                
                fetch('api/add_department.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error creating department');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        }
        
        // Delete Department Button
        const deleteBtns = document.querySelectorAll('.delete-dept-btn');
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const deptName = this.getAttribute('data-dept-name');
                const deptId = this.getAttribute('data-dept-id');
                if (confirm('Are you sure you want to remove "' + deptName + '"? This action cannot be undone.')) {
                    fetch('api/delete_department.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ dept_id: deptId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error deleting department');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    });
                }
            });
        });
        
        // Export Departments - Permanent Functionality
        const exportClassesBtn = document.getElementById('exportClassesBtn');
        if (exportClassesBtn) {
            exportClassesBtn.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
                
                fetch('api/export_departments.php', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    this.disabled = false;
                    this.innerHTML = originalText;
                    
                    if (data.success) {
                        const csvContent = atob(data.data);
                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        const url = URL.createObjectURL(blob);
                        link.setAttribute('href', url);
                        link.setAttribute('download', data.filename);
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert(data.message || 'Error exporting departments');
                    }
                })
                .catch(error => {
                    this.disabled = false;
                    this.innerHTML = originalText;
                    console.error('Error:', error);
                    alert('Error exporting departments. Please try again.');
                });
            });
        }
        
        // Remove loading class after page load
        window.addEventListener('load', function() {
            document.body.classList.remove('loading');
        });
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>

