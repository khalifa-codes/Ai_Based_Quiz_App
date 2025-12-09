<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/branding_loader.php';

$teacherId = $_SESSION['user_id'] ?? 0;

// Fetch real quiz results
$results = [];
$totalResults = 0;

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    
    // Get all quiz submissions for quizzes created by this teacher
    $stmt = $conn->prepare("
        SELECT qs.id, qs.quiz_id, qs.student_id, qs.total_score, qs.percentage, 
               qs.status, qs.submitted_at, qs.ai_provider, qs.ai_model,
               q.title as quiz_title, q.subject,
               s.name as student_name, s.student_id as student_id_code,
               o.name as org_name
        FROM quiz_submissions qs
        JOIN quizzes q ON q.id = qs.quiz_id
        JOIN students s ON s.id = qs.student_id
        LEFT JOIN organizations o ON o.id = s.organization_id
        WHERE q.created_by = ? AND qs.status IN ('submitted', 'auto_submitted')
        ORDER BY qs.submitted_at DESC
        LIMIT 100
    ");
    $stmt->execute([$teacherId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalResults = count($results);
    
    // Get unique quizzes for filter
    $stmt = $conn->prepare("
        SELECT DISTINCT q.id, q.title 
        FROM quizzes q
        WHERE q.created_by = ?
        ORDER BY q.title
    ");
    $stmt->execute([$teacherId]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Teacher Results Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Results - Teacher Panel</title>
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
                    <li class="nav-item"><a href="../quizzes/quiz_list.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Examinations</span></a></li>
                    <li class="nav-item"><a href="../students/student_list.php" class="nav-link"><i class="bi bi-mortarboard"></i><span>Students</span></a></li>
                    <li class="nav-item"><a href="quiz_results.php" class="nav-link active"><i class="bi bi-clipboard-data"></i><span>Department Results</span></a></li>
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
                        <h1 class="topbar-title">Department Results</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Department Results</li>
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
                            <div class="col-md-3">
                                <label class="admin-form-label">Search</label>
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="resultSearch" placeholder="Search by department, section or examination...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="admin-form-label">Examination</label>
                                <select class="admin-form-control" id="quizFilter">
                                    <option value="all">All Examinations</option>
                                    <?php foreach ($quizzes as $quiz): ?>
                                    <option value="<?php echo $quiz['id']; ?>"><?php echo htmlspecialchars($quiz['title'] ?? 'Untitled'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="admin-form-label">Department & Section</label>
                                <select class="admin-form-control" id="classFilter">
                                    <option value="all">All Departments</option>
                                    <option value="CS-A">CS Dept - Section A</option>
                                    <option value="CS-B">CS Dept - Section B</option>
                                    <option value="CS-C">CS Dept - Section C</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="admin-form-label">Actions</label>
                                <div class="d-flex gap-2 align-items-end">
                                    <button class="btn btn-outline-primary" id="exportPdfBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-file-pdf"></i> Export PDF
                                    </button>
                                    <button class="btn btn-outline-secondary" id="exportExcelBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-file-excel"></i> Export Excel
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
                        <h2 class="content-card-title">All Student Results</h2>
                        <span class="badge badge-primary" id="resultCount" style="font-size: 1.5rem; padding: 0.6rem 1.2rem; font-weight: 700;"><?php echo $totalResults; ?> Results</span>
                    </div>
                    <div class="content-card-body" style="overflow-x: hidden; width: 100%; max-width: 100%; box-sizing: border-box;">
                        <div class="table-responsive" style="overflow-x: hidden !important; width: 100%; max-width: 100%;">
                            <table class="admin-table" style="width: 100%; max-width: 100%; table-layout: fixed;">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Student</th>
                                        <th>Examination</th>
                                        <th>Subject</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>AI Evaluated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="resultTableBody">
                                    <?php if (empty($results)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No results found</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($results as $index => $result): 
                                        $initials = strtoupper(substr($result['student_name'], 0, 2));
                                        $scorePercent = $result['percentage'] ?? 0;
                                        $statusClass = $scorePercent >= 60 ? 'success' : ($scorePercent >= 40 ? 'warning' : 'danger');
                                        $statusText = $scorePercent >= 60 ? 'Passed' : ($scorePercent >= 40 ? 'Average' : 'Failed');
                                        $hasAI = !empty($result['ai_provider']);
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $index + 1; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;"><?php echo $initials; ?></div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);"><?php echo htmlspecialchars($result['student_name'] ?? ''); ?></h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);"><?php echo htmlspecialchars($result['student_id_code'] ?? 'N/A'); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($result['quiz_title'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($result['subject'] ?? 'N/A'); ?></td>
                                        <td><strong><?php echo number_format($result['total_score'], 2); ?></strong></td>
                                        <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo number_format($scorePercent, 1); ?>%</span></td>
                                        <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                        <td><?php echo !empty($result['submitted_at']) ? date('M d, Y', strtotime($result['submitted_at'])) : 'N/A'; ?></td>
                                        <td>
                                            <?php if ($hasAI): ?>
                                            <span class="badge badge-info" title="AI Provider: <?php echo strtoupper($result['ai_provider'] ?? 'N/A'); ?>, Model: <?php echo htmlspecialchars($result['ai_model'] ?? 'N/A'); ?>">
                                                <i class="bi bi-robot"></i> Yes
                                            </span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="result_detail.php?id=<?php echo $result['id']; ?>&quiz_id=<?php echo $result['quiz_id']; ?>" class="action-btn view" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="export_pdf.php?id=<?php echo $result['id']; ?>" class="action-btn" title="Export PDF" style="background: var(--danger-color); color: white;">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
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
    <script src="../assets/js/common.js"></script>
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
        const teacherSidebar = document.getElementById('teacherSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            teacherSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'flex';
            }
        }

        function openSidebar() {
            teacherSidebar.classList.add('active');
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
            if (e.key === 'Escape' && teacherSidebar.classList.contains('active')) {
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

        // Search functionality
        const resultSearch = document.getElementById('resultSearch');
        const resultTableBody = document.getElementById('resultTableBody');
        const quizFilter = document.getElementById('quizFilter');
        
        function filterResults() {
            const searchTerm = resultSearch ? resultSearch.value.toLowerCase() : '';
            const quizFilterValue = quizFilter ? quizFilter.value : 'all';
            const rows = resultTableBody.getElementsByTagName('tr');
            let visibleCount = 0;
            
            Array.from(rows).forEach(row => {
                if (row.querySelector('td[colspan]')) {
                    // Skip "no results" row
                    return;
                }
                
                const text = row.textContent.toLowerCase();
                const quizId = row.querySelector('td:nth-child(3)')?.textContent || '';
                const matchesSearch = !searchTerm || text.includes(searchTerm);
                const matchesQuiz = quizFilterValue === 'all' || quizId.includes(quizFilterValue);
                
                if (matchesSearch && matchesQuiz) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update result count
            const resultCount = document.getElementById('resultCount');
            if (resultCount) {
                resultCount.textContent = visibleCount + ' Results';
            }
        }
        
        if (resultSearch) {
            resultSearch.addEventListener('input', filterResults);
        }
        if (quizFilter) {
            quizFilter.addEventListener('change', filterResults);
        }
        // Export PDF
        const exportPdfBtn = document.getElementById('exportPdfBtn');
        if (exportPdfBtn) {
            exportPdfBtn.addEventListener('click', function() {
                window.location.href = 'export_pdf.php?all=1';
            });
        }
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>


