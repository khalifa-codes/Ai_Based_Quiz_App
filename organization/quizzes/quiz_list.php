<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';

$organizationId = $_SESSION['org_id'] ?? 0;
$quizzes = [];
$totalQuizzes = 0;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Fetch all quizzes for this organization
    $stmt = $conn->prepare("
        SELECT 
            q.id,
            q.title,
            q.subject,
            q.duration,
            q.total_questions,
            q.status,
            q.created_at,
            t.name as teacher_name,
            COUNT(DISTINCT qs.id) as submission_count,
            COUNT(DISTINCT s.id) as student_count
        FROM quizzes q
        LEFT JOIN teachers t ON q.created_by = t.id
        LEFT JOIN quiz_submissions qs ON q.id = qs.quiz_id
        LEFT JOIN students s ON s.organization_id = ?
        WHERE q.organization_id = ?
        GROUP BY q.id, q.title, q.subject, q.duration, q.total_questions, q.status, q.created_at, t.name
        ORDER BY q.created_at DESC
    ");
    $stmt->execute([$organizationId, $organizationId]);
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
    error_log("Organization Quiz List Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examinations - Organization Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
    
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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    
    <style>
        /* Organization Branding Support */
        .org-branding-logo {
            height: 70px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            flex-shrink: 0;
        }
        /* Standard width for examinations section */
        .admin-content {
            max-width: 1600px;
            margin: 0 auto;
        }
        .content-card {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="orgSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo" id="orgLogoLink">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Organization Logo" class="org-branding-logo" id="orgLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="orgSubtitle">Organization</span>
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
                        <a href="../dashboard.php" class="nav-link">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../analytics.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="../teachers/teacher_list.php" class="nav-link">
                            <i class="bi bi-people"></i>
                            <span>Teachers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="quiz_list.php" class="nav-link active">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Examinations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../students/student_list.php" class="nav-link">
                            <i class="bi bi-mortarboard"></i>
                            <span>Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../notifications/send_notification.php" class="nav-link">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user" id="sidebarUserDropdown">
                    <div class="sidebar-user-header" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; border-radius: 8px; transition: all 0.2s ease;">
                        <div class="sidebar-user-avatar">O</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;">
                            <p class="sidebar-user-name">Org Admin</p>
                            <p class="sidebar-user-role">Organization Admin</p>
                        </div>
                        <i class="bi bi-chevron-down" id="userDropdownIcon" style="transition: transform 0.2s ease; color: var(--text-muted);"></i>
                    </div>
                    <div class="sidebar-user-menu" id="sidebarUserMenu">
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="../settings.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
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
        <main class="admin-main">
            <!-- Floating Hamburger for Mobile -->
            <button class="floating-hamburger" id="floatingHamburger">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Topbar -->
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
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="admin-content">
                <!-- Filters and Actions -->
                <div class="content-card mb-4">
                    <div class="content-card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="admin-form-label">Search</label>
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="examinationSearch" placeholder="Search by title or code...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="admin-form-label">Status</label>
                                <select class="admin-form-control" id="statusFilter">
                                    <option value="all">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="draft">Draft</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="admin-form-label">Sort By</label>
                                <select class="admin-form-control" id="sortFilter">
                                    <option value="title">Title</option>
                                    <option value="date">Date Created</option>
                                    <option value="students">Total Students</option>
                                    <option value="recent">Recently Added</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="admin-form-label">Actions</label>
                                <div class="d-flex gap-2 align-items-end">
                                    <button class="btn btn-primary" id="addExaminationBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-plus-lg"></i> Create
                                    </button>
                                    <button class="btn btn-outline-secondary" id="exportExaminationsBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                    <button class="btn btn-outline-danger" id="clearFiltersBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-x-circle"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Examinations Table -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">All Examinations</h2>
                        <span class="badge badge-primary" id="examinationCount" style="font-size: 1.5rem; padding: 0.6rem 1.2rem; font-weight: 700;"><?php echo $totalQuizzes; ?> Examination<?php echo $totalQuizzes !== 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Examination Title</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Status</th>
                                        <th>Submissions</th>
                                        <th>Date Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="examinationTableBody">
                                    <?php if (empty($quizzes)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <p class="text-muted mb-0">No examinations found.</p>
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
                                                            <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);"><?php echo $quiz['total_questions']; ?> questions</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($quiz['subject'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($quiz['teacher_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $quiz['status'] === 'published' ? 'badge-success' : ($quiz['status'] === 'draft' ? 'badge-secondary' : 'badge-warning'); ?>">
                                                        <?php echo ucfirst($quiz['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $quiz['submission_count']; ?> submission<?php echo $quiz['submission_count'] !== 1 ? 's' : ''; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($quiz['created_at'])); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="action-btn view" title="View" data-id="<?php echo $quiz['id']; ?>">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
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
        // Remove all hardcoded rows - they are now dynamically generated
        const tbody = document.getElementById('examinationTableBody');
        if (tbody) {
            // Remove any remaining hardcoded rows that might exist
            const rows = tbody.querySelectorAll('tr');
            rows.forEach(row => {
                const firstCell = row.querySelector('td:first-child');
                if (firstCell && firstCell.textContent.trim().match(/^[0-9]+$/)) {
                    // Check if it's a hardcoded row (has old structure)
                    const codeCell = row.querySelector('td:nth-child(3)');
                    if (codeCell && codeCell.textContent.match(/^[A-Z]+-[A-Z]+-[0-9]+$/)) {
                        row.remove();
                    }
                }
            });
        }
        
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
        
        // Update examination count function
        function updateExaminationCount() {
            const visibleRows = Array.from(document.querySelectorAll('#examinationTableBody tr')).filter(row => row.style.display !== 'none');
            const countEl = document.getElementById('examinationCount');
            if (countEl) {
                countEl.textContent = `${visibleRows.length} Examination${visibleRows.length !== 1 ? 's' : ''}`;
            }
        }
        
        // Table Search
        const examinationSearch = document.getElementById('examinationSearch');
        const examinationTableBody = document.getElementById('examinationTableBody');

        if (examinationSearch) {
            examinationSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = examinationTableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
                
                updateExaminationCount();
            });
        }

        // Status Filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const filterValue = this.value.toLowerCase();
                const rows = examinationTableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    if (filterValue === 'all') {
                        row.style.display = '';
                    } else {
                        const badge = row.querySelector('.badge');
                        const badgeText = badge ? badge.textContent.toLowerCase() : '';
                        row.style.display = badgeText.includes(filterValue) ? '' : 'none';
                    }
                });
                
                updateExaminationCount();
            });
        }

        // Action Buttons
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const examinationId = this.getAttribute('data-id');
                window.location.href = `view_examination.php?id=${examinationId}`;
            });
        });
    </script>
</body>
</html>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem; background: rgba(13, 110, 253, 0.1); color: var(--primary-color);">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">Mathematics Final Exam</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#EXM001</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>MATH-FIN-2024</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><strong>245</strong></td>
                                        <td>2024-01-15</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="1">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="1">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="1">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>2</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem; background: rgba(13, 110, 253, 0.1); color: var(--primary-color);">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">Physics Midterm</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#EXM002</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>PHYS-MID-2024</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><strong>198</strong></td>
                                        <td>2024-01-18</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="2">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="2">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="2">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>3</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem; background: rgba(13, 110, 253, 0.1); color: var(--primary-color);">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">Chemistry Examination</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#EXM003</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>CHEM-EXAM-2024</td>
                                        <td><span class="badge badge-warning">Draft</span></td>
                                        <td><strong>0</strong></td>
                                        <td>2024-01-20</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="3">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="3">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="3">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>4</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem; background: rgba(13, 110, 253, 0.1); color: var(--primary-color);">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">Biology Assessment</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#EXM004</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>BIO-ASS-2024</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><strong>312</strong></td>
                                        <td>2024-01-22</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="4">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="4">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="4">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>5</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem; background: rgba(13, 110, 253, 0.1); color: var(--primary-color);">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">English Literature Test</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#EXM005</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>ENG-LIT-2024</td>
                                        <td><span class="badge badge-info">Completed</span></td>
                                        <td><strong>189</strong></td>
                                        <td>2024-01-10</td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="5">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="5">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="5">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
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
        const orgSidebar = document.getElementById('orgSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            orgSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'flex';
            }
        }

        function openSidebar() {
            orgSidebar.classList.add('active');
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
            if (e.key === 'Escape' && orgSidebar.classList.contains('active')) {
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

        // Load Organization Branding
        function loadOrganizationBranding() {
            const orgBranding = JSON.parse(localStorage.getItem('orgBranding') || '{}');
            
            if (orgBranding.logo) {
                const logoImg = document.getElementById('orgLogo');
                if (logoImg) {
                    logoImg.src = orgBranding.logo;
                }
            }
            
            if (orgBranding.name) {
                const sidebarSubtitle = document.getElementById('orgSubtitle');
                if (sidebarSubtitle) {
                    sidebarSubtitle.textContent = orgBranding.name;
                }
            }
            
            if (orgBranding.primaryColor) {
                document.documentElement.style.setProperty('--primary-color', orgBranding.primaryColor);
            }
            
            if (orgBranding.secondaryColor) {
                document.documentElement.style.setProperty('--primary-hover', orgBranding.secondaryColor);
            }
        }

        loadOrganizationBranding();

        // Table Search
        const examinationSearch = document.getElementById('examinationSearch');
        const examinationTableBody = document.getElementById('examinationTableBody');

        if (examinationSearch) {
            examinationSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = examinationTableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
                
                updateExaminationCount();
            });
        }

        // Status Filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const filterValue = this.value.toLowerCase();
                const rows = examinationTableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    if (filterValue === 'all') {
                        row.style.display = '';
                    } else {
                        const badges = row.querySelectorAll('.badge');
                        let found = false;
                        badges.forEach(badge => {
                            if (badge.textContent.toLowerCase() === filterValue) {
                                found = true;
                            }
                        });
                        row.style.display = found ? '' : 'none';
                    }
                });
                
                updateExaminationCount();
            });
        }

        // Sort Filter
        const sortFilter = document.getElementById('sortFilter');
        if (sortFilter) {
            sortFilter.addEventListener('change', function() {
                const sortBy = this.value;
                // TODO: Implement sorting logic
                console.log('Sorting by:', sortBy);
            });
        }

        // Update Examination Count
        function updateExaminationCount() {
            const visibleRows = Array.from(examinationTableBody.getElementsByTagName('tr')).filter(row => row.style.display !== 'none');
            const examinationCount = document.getElementById('examinationCount');
            if (examinationCount) {
                examinationCount.textContent = `${visibleRows.length} Examinations`;
            }
        }

        // Add Examination Button
        const addExaminationBtn = document.getElementById('addExaminationBtn');
        if (addExaminationBtn) {
            addExaminationBtn.addEventListener('click', function() {
                window.location.href = 'create_examination.php';
            });
        }

        // Export Examinations Button
        const exportExaminationsBtn = document.getElementById('exportExaminationsBtn');
        if (exportExaminationsBtn) {
            exportExaminationsBtn.addEventListener('click', function() {
                // TODO: Replace with actual export functionality
                console.log('Exporting examinations...');
                alert('Export feature will be available after backend integration.');
            });
        }

        // Clear Filters Button
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                if (examinationSearch) examinationSearch.value = '';
                if (statusFilter) statusFilter.value = 'all';
                if (sortFilter) sortFilter.value = 'title';
                
                // Show all rows
                const rows = examinationTableBody.getElementsByTagName('tr');
                Array.from(rows).forEach(row => {
                    row.style.display = '';
                });
                
                updateExaminationCount();
            });
        }

        // Action Buttons Functionality
        // View Button
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const examinationId = this.getAttribute('data-id');
                window.location.href = `view_examination.php?id=${examinationId}`;
            });
        });

        // Edit Button
        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const examinationId = this.getAttribute('data-id');
                window.location.href = `edit_examination.php?id=${examinationId}`;
            });
        });

        // Delete Button
        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const examinationId = this.getAttribute('data-id');
                const row = this.closest('tr');
                const examinationTitle = row.querySelector('h6')?.textContent || 'this examination';
                
                if (confirm(`Are you sure you want to delete ${examinationTitle}? This will also affect all associated students and results. This action cannot be undone.`)) {
                    deleteExamination(examinationId);
                }
            });
        });

        // Delete Examination Function (Backend Ready)
        async function deleteExamination(examinationId) {
            try {
                // TODO: Replace with actual API endpoint when backend is ready
                // const response = await fetch(`../../api/organization/examinations/delete.php`, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ id: examinationId })
                // });
                
                // For now, simulate API call
                const response = { ok: true };
                
                if (response.ok) {
                    alert('Examination deleted successfully!');
                    location.reload(); // Reload page to reflect changes
                } else {
                    alert('Failed to delete examination. Please try again.');
                }
            } catch (error) {
                console.error('Error deleting examination:', error);
                alert('An error occurred while deleting the examination.');
            }
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>
