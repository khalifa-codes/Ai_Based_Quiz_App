<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';

session_start();

// Fetch audit logs from database
$logs = [];
$totalLogs = 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$actionFilter = $_GET['action'] ?? 'all';
$userFilter = $_GET['user'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Build query
    $where = [];
    $params = [];
    
    if ($actionFilter !== 'all') {
        $where[] = "action = ?";
        $params[] = $actionFilter;
    }
    
    if ($userFilter) {
        $where[] = "(user_id LIKE ? OR user_type LIKE ?)";
        $params[] = "%$userFilter%";
        $params[] = "%$userFilter%";
    }
    
    if ($dateFrom) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $dateFrom;
    }
    
    if ($dateTo) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $dateTo;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM audit_logs $whereClause");
    $countStmt->execute($params);
    $totalLogs = (int)$countStmt->fetchColumn();
    
    // Get logs
    $stmt = $conn->prepare("
        SELECT id, user_id, user_type, action, resource_type, resource_id, 
               ip_address, user_agent, details, created_at
        FROM audit_logs 
        $whereClause
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $params[] = $perPage;
    $params[] = $offset;
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Audit Logs Error: " . $e->getMessage());
}

$totalPages = ceil($totalLogs / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Admin Panel</title>
    
    <!-- Favicon -->
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
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Quizaura Logo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle">Admin</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-x-lg"></i></button>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="../profile.php" class="nav-link"><i class="bi bi-person"></i><span>Profile</span></a></li>
                </ul>
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../organizations/organization_list.php" class="nav-link"><i class="bi bi-building"></i><span>Organizations</span></a></li>
                    <li class="nav-item"><a href="../plans/plan_list.php" class="nav-link"><i class="bi bi-box-seam"></i><span>Plans</span></a></li>
                </ul>
                <div class="nav-section-title">Security</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="security_dashboard.php" class="nav-link"><i class="bi bi-shield-check"></i><span>Security Dashboard</span></a></li>
                    <li class="nav-item"><a href="ip_management.php" class="nav-link"><i class="bi bi-router"></i><span>IP Management</span></a></li>
                    <li class="nav-item"><a href="security_settings.php" class="nav-link"><i class="bi bi-gear"></i><span>Security Settings</span></a></li>
                    <li class="nav-item"><a href="audit_logs.php" class="nav-link active"><i class="bi bi-file-text"></i><span>Audit Logs</span></a></li>
                    <li class="nav-item"><a href="data_retention.php" class="nav-link"><i class="bi bi-database"></i><span>Data Retention</span></a></li>
                </ul>
                <div class="nav-section-title">Analytics</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../reports/system_report.php" class="nav-link"><i class="bi bi-graph-up"></i><span>System Reports</span></a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user" id="sidebarUserDropdown">
                    <div class="sidebar-user-header" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; border-radius: 8px; transition: all 0.2s ease;">
                        <div class="sidebar-user-avatar">A</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;"><p class="sidebar-user-name">Admin User</p><p class="sidebar-user-role">Administrator</p></div>
                        <i class="bi bi-chevron-down" id="userDropdownIcon" style="transition: transform 0.2s ease; color: var(--text-muted);"></i>
                    </div>
                    <div class="sidebar-user-menu" id="sidebarUserMenu">
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-person"></i><span>Profile</span></a>
                        <a href="#" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-gear"></i><span>Settings</span></a>
                        <a href="../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
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
                        <h1 class="topbar-title">Audit Logs</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="security_dashboard.php">Security</a></li>
                                <li class="breadcrumb-item active">Audit Logs</li>
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
                        <h2 class="content-card-title">System Audit Logs</h2>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" id="exportLogsBtn"><i class="bi bi-download"></i> Export</button>
                            <button class="btn btn-sm btn-outline-danger" id="clearLogsBtn"><i class="bi bi-trash"></i> Clear Logs</button>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="admin-form-label">Action Type</label>
                                <select class="admin-form-control" id="actionTypeFilter">
                                    <option value="all">All Actions</option>
                                    <option value="login">Login</option>
                                    <option value="logout">Logout</option>
                                    <option value="create">Create</option>
                                    <option value="update">Update</option>
                                    <option value="delete">Delete</option>
                                    <option value="security">Security</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="admin-form-label">User</label>
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="userFilter" placeholder="Search user...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="admin-form-label">Date From</label>
                                <input type="date" class="admin-form-control" id="dateFromFilter">
                            </div>
                            <div class="col-md-3">
                                <label class="admin-form-label">Date To</label>
                                <input type="date" class="admin-form-control" id="dateToFilter">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Resource</th>
                                        <th>IP Address</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody id="auditLogsTableBody">
                                    <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No audit logs found</td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($logs as $log): 
                                        $actionClass = 'primary';
                                        if (stripos($log['action'], 'login') !== false) $actionClass = 'primary';
                                        elseif (stripos($log['action'], 'create') !== false) $actionClass = 'success';
                                        elseif (stripos($log['action'], 'update') !== false) $actionClass = 'warning';
                                        elseif (stripos($log['action'], 'delete') !== false || stripos($log['action'], 'fail') !== false) $actionClass = 'danger';
                                        elseif (stripos($log['action'], 'security') !== false) $actionClass = 'info';
                                        
                                        $userDisplay = $log['user_type'] . ' #' . ($log['user_id'] ?? 'N/A');
                                        $details = $log['details'] ? json_decode($log['details'], true) : null;
                                    ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($userDisplay); ?></td>
                                        <td><span class="badge badge-<?php echo $actionClass; ?>"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                        <td><?php echo htmlspecialchars($log['resource_type'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></td>
                                        <td><span class="badge badge-success">Success</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary viewDetailsBtn" 
                                                    data-id="<?php echo $log['id']; ?>"
                                                    data-details='<?php echo htmlspecialchars(json_encode($details)); ?>'
                                                    data-user-agent="<?php echo htmlspecialchars($log['user_agent'] ?? ''); ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>Showing <strong><?php echo $offset + 1; ?>-<?php echo min($offset + $perPage, $totalLogs); ?></strong> of <strong><?php echo $totalLogs; ?></strong> logs</div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&action=<?php echo $actionFilter; ?>&user=<?php echo urlencode($userFilter); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">Previous</a>
                                    </li>
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&action=<?php echo $actionFilter; ?>&user=<?php echo urlencode($userFilter); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&action=<?php echo $actionFilter; ?>&user=<?php echo urlencode($userFilter); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
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
        const sidebarToggle = document.getElementById('sidebarToggle');
        const floatingHamburger = document.getElementById('floatingHamburger');
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        function closeSidebar() { adminSidebar.classList.remove('active'); sidebarOverlay.classList.remove('active'); if (floatingHamburger) floatingHamburger.style.display = 'flex'; }
        function openSidebar() { adminSidebar.classList.add('active'); sidebarOverlay.classList.add('active'); if (floatingHamburger) floatingHamburger.style.display = 'none'; }
        if (sidebarToggle) sidebarToggle.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); closeSidebar(); });
        if (floatingHamburger) floatingHamburger.addEventListener('click', function(e) { e.preventDefault(); openSidebar(); });
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', function() { closeSidebar(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && adminSidebar.classList.contains('active')) closeSidebar(); });
        const sidebarUserDropdown = document.getElementById('sidebarUserDropdown');
        const sidebarUserMenu = document.getElementById('sidebarUserMenu');
        if (sidebarUserDropdown && sidebarUserMenu) {
            const userHeader = sidebarUserDropdown.querySelector('.sidebar-user-header');
            if (userHeader) userHeader.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); sidebarUserDropdown.classList.toggle('active'); });
            document.addEventListener('click', function(e) { if (!sidebarUserDropdown.contains(e.target)) sidebarUserDropdown.classList.remove('active'); });
        }
        document.getElementById('exportLogsBtn').addEventListener('click', function() {
            const btn = this;
            const icon = btn.querySelector('i');
            icon.classList.add('spin');
            btn.disabled = true;
            setTimeout(() => {
                icon.classList.remove('spin');
                btn.disabled = false;
                alert('Audit logs exported successfully!');
            }, 1500);
        });
        document.getElementById('clearLogsBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all audit logs? This action cannot be undone.')) {
                alert('Audit logs cleared successfully!');
            }
        });
        document.querySelectorAll('.viewDetailsBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                alert(`Viewing details for audit log #${id}`);
            });
        });

        // Filter Functionality
        const actionTypeFilter = document.getElementById('actionTypeFilter');
        const userFilter = document.getElementById('userFilter');
        const dateFromFilter = document.getElementById('dateFromFilter');
        const dateToFilter = document.getElementById('dateToFilter');

        function applyFilters() {
            const actionType = actionTypeFilter.value;
            const user = userFilter.value.toLowerCase();
            const dateFrom = dateFromFilter.value;
            const dateTo = dateToFilter.value;

            // TODO: Replace with actual API call when backend is ready
            // fetch(`../api/security/filterAuditLogs.php?actionType=${actionType}&user=${user}&dateFrom=${dateFrom}&dateTo=${dateTo}`)
            //     .then(response => response.json())
            //     .then(data => {
            //         // Update table with filtered data
            //     });

            // For now, show message
            console.log('Applying filters:', { actionType, user, dateFrom, dateTo });
            alert('Filters applied! (Backend integration pending)');
        }

        if (actionTypeFilter) {
            actionTypeFilter.addEventListener('change', applyFilters);
        }
        if (userFilter) {
            userFilter.addEventListener('input', applyFilters);
        }
        if (dateFromFilter) {
            dateFromFilter.addEventListener('change', applyFilters);
        }
        if (dateToFilter) {
            dateToFilter.addEventListener('change', applyFilters);
        }

        const style = document.createElement('style');
        style.textContent = `@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .spin { animation: spin 1s linear infinite; }`;
        document.head.appendChild(style);
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

