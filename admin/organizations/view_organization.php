<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Organization - Admin Panel</title>
    
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
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Quizaura Logo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle">Admin</span>
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
                        <a href="../profile.php" class="nav-link">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="organization_list.php" class="nav-link active">
                            <i class="bi bi-building"></i>
                            <span>Organizations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../plans/plan_list.php" class="nav-link">
                            <i class="bi bi-box-seam"></i>
                            <span>Plans</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Security</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="../security/security_dashboard.php" class="nav-link">
                            <i class="bi bi-shield-check"></i>
                            <span>Security Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../security/ip_management.php" class="nav-link">
                            <i class="bi bi-router"></i>
                            <span>IP Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../security/security_settings.php" class="nav-link">
                            <i class="bi bi-gear"></i>
                            <span>Security Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../security/audit_logs.php" class="nav-link">
                            <i class="bi bi-file-text"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../security/data_retention.php" class="nav-link">
                            <i class="bi bi-database"></i>
                            <span>Data Retention</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Analytics</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="../reports/system_report.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>System Reports</span>
                        </a>
                    </li>
                </ul>
                
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user" id="sidebarUserDropdown">
                    <div class="sidebar-user-header" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; border-radius: 8px; transition: all 0.2s ease;">
                        <div class="sidebar-user-avatar">A</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;">
                            <p class="sidebar-user-name">Admin User</p>
                            <p class="sidebar-user-role">Administrator</p>
                        </div>
                        <i class="bi bi-chevron-down" id="userDropdownIcon" style="transition: transform 0.2s ease; color: var(--text-muted);"></i>
                    </div>
                    <div class="sidebar-user-menu" id="sidebarUserMenu">
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="#" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
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
                        <h1 class="topbar-title">Organization Details</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="organization_list.php">Organizations</a></li>
                                <li class="breadcrumb-item active">View</li>
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
                <div class="row">
                    <div class="col-lg-10 mx-auto">
                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 mb-4">
                            <a href="edit_organization.php?id=<?php echo htmlspecialchars($_GET['id'] ?? 'ORG001'); ?>" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Organization
                            </a>
                            <a href="organization_list.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                        </div>

                        <!-- Organization Info Card -->
                        <div class="content-card mb-4">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Basic Information</h2>
                            </div>
                            <div class="content-card-body">
                                <?php
                                // TODO: Replace with actual database query when backend is ready
                                // $orgId = $_GET['id'] ?? '';
                                // $org = getOrganizationById($orgId);
                                
                                // Dummy data for now
                                $org = [
                                    'id' => $_GET['id'] ?? 'ORG001',
                                    'name' => 'ABC Learning Center',
                                    'email' => 'contact@abclearning.com',
                                    'phone' => '+1 (555) 123-4567',
                                    'website' => 'https://www.abclearning.com',
                                    'status' => 'active',
                                    'plan' => 'premium',
                                    'created_at' => '2024-01-15',
                                    'description' => 'A leading educational institution providing quality learning experiences.'
                                ];
                                ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Organization Name</label>
                                        <p style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin: 0.5rem 0 0 0;">
                                            <?php echo htmlspecialchars($org['name']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Organization ID</label>
                                        <p style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin: 0.5rem 0 0 0;">
                                            #<?php echo htmlspecialchars($org['id']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Contact Email</label>
                                        <p style="font-size: 1rem; color: var(--text-primary); margin: 0.5rem 0 0 0;">
                                            <a href="mailto:<?php echo htmlspecialchars($org['email']); ?>" style="color: var(--primary-color); text-decoration: none;">
                                                <?php echo htmlspecialchars($org['email']); ?>
                                            </a>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Phone Number</label>
                                        <p style="font-size: 1rem; color: var(--text-primary); margin: 0.5rem 0 0 0;">
                                            <?php echo htmlspecialchars($org['phone']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Website</label>
                                        <p style="font-size: 1rem; color: var(--text-primary); margin: 0.5rem 0 0 0;">
                                            <a href="<?php echo htmlspecialchars($org['website']); ?>" target="_blank" style="color: var(--primary-color); text-decoration: none;">
                                                <?php echo htmlspecialchars($org['website']); ?>
                                            </a>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Status</label>
                                        <p style="margin: 0.5rem 0 0 0;">
                                            <span class="badge badge-<?php echo $org['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($org['status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Subscription Plan</label>
                                        <p style="margin: 0.5rem 0 0 0;">
                                            <span class="badge badge-primary">
                                                <?php echo ucfirst($org['plan']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Created Date</label>
                                        <p style="font-size: 1rem; color: var(--text-primary); margin: 0.5rem 0 0 0;">
                                            <?php echo htmlspecialchars($org['created_at']); ?>
                                        </p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="admin-form-label" style="color: var(--text-secondary); font-size: 0.9rem;">Description</label>
                                        <p style="font-size: 1rem; color: var(--text-primary); margin: 0.5rem 0 0 0; line-height: 1.6;">
                                            <?php echo htmlspecialchars($org['description']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Card -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Organization Statistics</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="stat-card">
                                            <div class="stat-card-header">
                                                <h3 class="stat-card-title">Teachers</h3>
                                            </div>
                                            <div class="stat-card-value">45</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="stat-card">
                                            <div class="stat-card-header">
                                                <h3 class="stat-card-title">Students</h3>
                                            </div>
                                            <div class="stat-card-value">680</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="stat-card">
                                            <div class="stat-card-header">
                                                <h3 class="stat-card-title">Quizzes</h3>
                                            </div>
                                            <div class="stat-card-value">234</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="stat-card">
                                            <div class="stat-card-header">
                                                <h3 class="stat-card-title">Active Sessions</h3>
                                            </div>
                                            <div class="stat-card-value">12</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function closeSidebar() {
            adminSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) {
                floatingHamburger.style.display = 'flex';
            }
        }

        function openSidebar() {
            adminSidebar.classList.add('active');
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
            sidebarOverlay.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebar();
            });
        }

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
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

