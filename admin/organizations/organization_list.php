<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizations - Admin Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="../../assets/images/logo-removebg-preview.png">
    
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
                        <h1 class="topbar-title">Organizations</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Organizations</li>
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
                <!-- Stats Summary -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Organizations</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-building"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">145</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Active Subscriptions</h3>
                            <div class="stat-card-icon green">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">132</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Revenue</h3>
                            <div class="stat-card-icon purple">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">$24.5K</div>
                    </div>
                </div>

                <!-- Organizations Table -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">All Organizations</h2>
                        <a href="create_organization.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Organization
                        </a>
                    </div>
                    <div class="content-card-body">
                        <!-- Search and Filter -->
                        <div class="search-filter-bar">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" id="tableSearch" placeholder="Search organizations...">
                            </div>
                            <select class="filter-select" id="planFilter">
                                <option value="all">All Plans</option>
                                <option value="free">Free</option>
                                <option value="basic">Basic</option>
                                <option value="premium">Premium</option>
                            </select>
                            <select class="filter-select" id="statusFilter">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Organization</th>
                                        <th>Contact Email</th>
                                        <th>Plan</th>
                                        <th>Teachers</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="organizationTableBody">
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">AL</div>
                                                <div class="user-details">
                                                    <h6>ABC Learning Center</h6>
                                                    <p>Org ID: #ORG001</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>contact@abclearning.com</td>
                                        <td><span class="badge badge-primary">Premium</span></td>
                                        <td>45</td>
                                        <td>680</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">XE</div>
                                                <div class="user-details">
                                                    <h6>XYZ Education Hub</h6>
                                                    <p>Org ID: #ORG002</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>info@xyzedu.com</td>
                                        <td><span class="badge badge-warning">Basic</span></td>
                                        <td>28</td>
                                        <td>420</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">TA</div>
                                                <div class="user-details">
                                                    <h6>Tech Academy</h6>
                                                    <p>Org ID: #ORG003</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>admin@techacademy.com</td>
                                        <td><span class="badge badge-primary">Premium</span></td>
                                        <td>62</td>
                                        <td>890</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">LS</div>
                                                <div class="user-details">
                                                    <h6>Language School Pro</h6>
                                                    <p>Org ID: #ORG004</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>contact@langschool.com</td>
                                        <td><span class="badge badge-secondary">Free</span></td>
                                        <td>12</td>
                                        <td>156</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">MC</div>
                                                <div class="user-details">
                                                    <h6>Math Champions Institute</h6>
                                                    <p>Org ID: #ORG005</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>support@mathchamps.com</td>
                                        <td><span class="badge badge-warning">Basic</span></td>
                                        <td>19</td>
                                        <td>245</td>
                                        <td><span class="badge badge-danger">Inactive</span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete">
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

        // Close sidebar
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeSidebar();
            });
        }

        // Open sidebar
        if (floatingHamburger) {
            floatingHamburger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openSidebar();
            });
        }

        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                closeSidebar();
            });
        }

        // Close sidebar on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && adminSidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // User Dropdown Toggle
        const sidebarUserDropdown = document.getElementById('sidebarUserDropdown');
        const sidebarUserMenu = document.getElementById('sidebarUserMenu');
        const userDropdownIcon = document.getElementById('userDropdownIcon');

        if (sidebarUserDropdown && sidebarUserMenu) {
            const userHeader = sidebarUserDropdown.querySelector('.sidebar-user-header');
            
            if (userHeader) {
                userHeader.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebarUserDropdown.classList.toggle('active');
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!sidebarUserDropdown.contains(e.target)) {
                    sidebarUserDropdown.classList.remove('active');
                }
            });

            // Close dropdown on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebarUserDropdown.classList.contains('active')) {
                    sidebarUserDropdown.classList.remove('active');
                }
            });
        }

        // Table Search
        const tableSearch = document.getElementById('tableSearch');
        const organizationTableBody = document.getElementById('organizationTableBody');

        if (tableSearch) {
            tableSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = organizationTableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        // Plan Filter
        const planFilter = document.getElementById('planFilter');
        if (planFilter) {
            planFilter.addEventListener('change', function() {
                const filterValue = this.value.toLowerCase();
                const rows = organizationTableBody.getElementsByTagName('tr');

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
            });
        }

        // Status Filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const filterValue = this.value.toLowerCase();
                const rows = organizationTableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    if (filterValue === 'all') {
                        row.style.display = '';
                    } else {
                        const statusBadge = row.querySelectorAll('.badge')[1]; // Second badge is status
                        const status = statusBadge ? statusBadge.textContent.toLowerCase() : '';
                        row.style.display = status === filterValue ? '' : 'none';
                    }
                });
            });
        }

        // Action Buttons Functionality
        // View Button
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const orgId = row.querySelector('p')?.textContent.match(/#ORG\d+/)?.[0] || 'ORG001';
                window.location.href = `view_organization.php?id=${orgId.replace('#', '')}`;
            });
        });

        // Edit Button
        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const orgId = row.querySelector('p')?.textContent.match(/#ORG\d+/)?.[0] || 'ORG001';
                window.location.href = `edit_organization.php?id=${orgId.replace('#', '')}`;
            });
        });

        // Delete Button
        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const orgName = row.querySelector('h6')?.textContent || 'this organization';
                const orgId = row.querySelector('p')?.textContent.match(/#ORG\d+/)?.[0] || 'ORG001';
                
                if (confirm(`Are you sure you want to delete ${orgName}? This will also affect all associated teachers and students. This action cannot be undone.`)) {
                    deleteOrganization(orgId.replace('#', ''));
                }
            });
        });

        // Delete Organization Function (Backend Ready)
        async function deleteOrganization(orgId) {
            try {
                // TODO: Replace with actual API endpoint when backend is ready
                // const response = await fetch(`../api/organizations/delete.php`, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ id: orgId })
                // });
                
                // For now, simulate API call
                const response = { ok: true };
                
                if (response.ok) {
                    alert('Organization deleted successfully!');
                    location.reload(); // Reload page to reflect changes
                } else {
                    alert('Failed to delete organization. Please try again.');
                }
            } catch (error) {
                console.error('Error deleting organization:', error);
                alert('An error occurred while deleting the organization.');
            }
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

