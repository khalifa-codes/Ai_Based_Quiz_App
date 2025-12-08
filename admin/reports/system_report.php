<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - Admin Panel</title>
    
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
<body class="loading">
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
                        <a href="../organizations/organization_list.php" class="nav-link">
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
                        <a href="system_report.php" class="nav-link active">
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
                        <h1 class="topbar-title">System Reports</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Reports</li>
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
                <!-- Date Range Selector -->
                <div class="content-card mb-4">
                    <div class="content-card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="admin-form-label">From Date</label>
                                <input type="date" class="admin-form-control" id="fromDate" value="2024-01-01">
                            </div>
                            <div class="col-md-3">
                                <label class="admin-form-label">To Date</label>
                                <input type="date" class="admin-form-control" id="toDate" value="2024-12-31">
                            </div>
                            <div class="col-md-3">
                                <label class="admin-form-label">Report Type</label>
                                <select class="admin-form-control" id="reportType">
                                    <option>Overview</option>
                                    <option>User Activity</option>
                                    <option>Revenue</option>
                                    <option>System Performance</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100">
                                    <i class="bi bi-funnel"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="report-section">
                    <h3 class="report-section-title">Key Performance Indicators</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-card-title">Total Revenue</h3>
                                <div class="stat-card-icon purple">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                            </div>
                            <div class="stat-card-value">$124,582</div>
                            <div class="stat-card-change positive">
                                <i class="bi bi-arrow-up"></i>
                                <span>18.2% from last period</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-card-title">Total Users</h3>
                                <div class="stat-card-icon blue">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                            <div class="stat-card-value">6,912</div>
                            <div class="stat-card-change positive">
                                <i class="bi bi-arrow-up"></i>
                                <span>12.5% from last period</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-card-title">Active Sessions</h3>
                                <div class="stat-card-icon green">
                                    <i class="bi bi-activity"></i>
                                </div>
                            </div>
                            <div class="stat-card-value">1,458</div>
                            <div class="stat-card-change positive">
                                <i class="bi bi-arrow-up"></i>
                                <span>24.8% from last period</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-card-header">
                                <h3 class="stat-card-title">Avg. Response Time</h3>
                                <div class="stat-card-icon orange">
                                    <i class="bi bi-speedometer"></i>
                                </div>
                            </div>
                            <div class="stat-card-value">142ms</div>
                            <div class="stat-card-change negative">
                                <i class="bi bi-arrow-down"></i>
                                <span>8.2% improvement</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="report-section">
                    <h3 class="report-section-title">User Analytics</h3>
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Monthly Active Users</h2>
                                    <select class="filter-select">
                                        <option>Last 12 Months</option>
                                        <option>Last 6 Months</option>
                                        <option>Last 3 Months</option>
                                    </select>
                                </div>
                                <div class="content-card-body">
                                    <div class="chart-container">
                                        <canvas id="monthlyUsersChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">User Types</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="chart-container">
                                        <canvas id="userTypesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="report-section">
                    <h3 class="report-section-title">Revenue Analytics</h3>
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Revenue Breakdown</h2>
                                    <select class="filter-select">
                                        <option>This Year</option>
                                        <option>Last Year</option>
                                    </select>
                                </div>
                                <div class="content-card-body">
                                    <div class="chart-container">
                                        <canvas id="revenueBreakdownChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Revenue by Plan</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="chart-container">
                                        <canvas id="revenuePlanChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="report-section">
                    <h3 class="report-section-title">System Health</h3>
                    <div class="report-grid">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Server Status</h2>
                                <span class="badge badge-success">Operational</span>
                            </div>
                            <div class="content-card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span>CPU Usage</span>
                                    <strong>42%</strong>
                                </div>
                                <div class="progress mb-4" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 42%"></div>
                                </div>

                                <div class="d-flex justify-content-between mb-3">
                                    <span>Memory Usage</span>
                                    <strong>68%</strong>
                                </div>
                                <div class="progress mb-4" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: 68%"></div>
                                </div>

                                <div class="d-flex justify-content-between mb-3">
                                    <span>Disk Usage</span>
                                    <strong>54%</strong>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: 54%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Database Stats</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                                    <span>Total Records</span>
                                    <strong>2,458,642</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                                    <span>Database Size</span>
                                    <strong>12.4 GB</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                                    <span>Avg. Query Time</span>
                                    <strong>24ms</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Active Connections</span>
                                    <strong>156</strong>
                                </div>
                            </div>
                        </div>

                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">API Performance</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                                    <span>Total Requests</span>
                                    <strong>1.2M</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                                    <span>Success Rate</span>
                                    <strong>99.8%</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                                    <span>Avg. Response</span>
                                    <strong>142ms</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Errors (24h)</span>
                                    <strong class="text-danger">24</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Export Report</h2>
                    </div>
                    <div class="content-card-body">
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Download this report in various formats for offline analysis or sharing with stakeholders.</p>
                        <div class="quick-actions">
                            <button class="btn btn-outline-primary">
                                <i class="bi bi-file-pdf"></i> Export as PDF
                            </button>
                            <button class="btn btn-outline-success">
                                <i class="bi bi-file-excel"></i> Export as Excel
                            </button>
                            <button class="btn btn-outline-info">
                                <i class="bi bi-file-text"></i> Export as CSV
                            </button>
                            <button class="btn btn-outline-secondary">
                                <i class="bi bi-printer"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
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
                
                updateChartsTheme(newTheme);
                
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

        // Chart.js Setup
        const chartColors = {
            primary: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#0d6efd',
            success: getComputedStyle(document.documentElement).getPropertyValue('--success-color').trim() || '#198754',
            warning: getComputedStyle(document.documentElement).getPropertyValue('--warning-color').trim() || '#ffc107',
            danger: getComputedStyle(document.documentElement).getPropertyValue('--danger-color').trim() || '#dc3545',
            info: getComputedStyle(document.documentElement).getPropertyValue('--info-color').trim() || '#0dcaf0',
            textPrimary: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim() || '#212529',
            textSecondary: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim() || '#6c757d',
            borderColor: getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim() || '#dee2e6'
        };

        // Monthly Active Users Chart
        const monthlyUsersCtx = document.getElementById('monthlyUsersChart');
        let monthlyUsersChart = null;
        
        if (monthlyUsersCtx) {
            monthlyUsersChart = new Chart(monthlyUsersCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Active Users',
                        data: [3200, 3500, 3800, 4100, 4500, 4800, 5200, 5600, 6000, 6300, 6600, 6912],
                        borderColor: chartColors.primary,
                        backgroundColor: chartColors.primary + '20',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: chartColors.textSecondary
                            },
                            grid: {
                                color: chartColors.borderColor
                            }
                        },
                        x: {
                            ticks: {
                                color: chartColors.textSecondary
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // User Types Chart
        const userTypesCtx = document.getElementById('userTypesChart');
        let userTypesChart = null;
        
        if (userTypesCtx) {
            userTypesChart = new Chart(userTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Organizations', 'Students', 'Admins'],
                    datasets: [{
                        data: [156, 5678, 145],
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.success,
                            chartColors.warning
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: chartColors.textPrimary,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Revenue Breakdown Chart
        const revenueBreakdownCtx = document.getElementById('revenueBreakdownChart');
        let revenueBreakdownChart = null;
        
        if (revenueBreakdownCtx) {
            revenueBreakdownChart = new Chart(revenueBreakdownCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue ($)',
                        data: [5200, 6800, 7500, 8200, 9100, 9800, 10500, 11200, 11800, 12500, 11900, 13200],
                        backgroundColor: chartColors.success,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: chartColors.textSecondary,
                                callback: function(value) {
                                    return '$' + value;
                                }
                            },
                            grid: {
                                color: chartColors.borderColor
                            }
                        },
                        x: {
                            ticks: {
                                color: chartColors.textSecondary
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Revenue by Plan Chart
        const revenuePlanCtx = document.getElementById('revenuePlanChart');
        let revenuePlanChart = null;
        
        if (revenuePlanCtx) {
            revenuePlanChart = new Chart(revenuePlanCtx, {
                type: 'pie',
                data: {
                    labels: ['Free', 'Basic', 'Premium', 'Enterprise'],
                    datasets: [{
                        data: [0, 25800, 75800, 45000],
                        backgroundColor: [
                            chartColors.textSecondary,
                            chartColors.info,
                            chartColors.primary,
                            chartColors.warning
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: chartColors.textPrimary,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Update charts theme
        function updateChartsTheme(theme) {
            const newColors = {
                primary: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim(),
                success: getComputedStyle(document.documentElement).getPropertyValue('--success-color').trim(),
                warning: getComputedStyle(document.documentElement).getPropertyValue('--warning-color').trim(),
                textPrimary: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim(),
                textSecondary: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim(),
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim()
            };

            if (monthlyUsersChart) {
                monthlyUsersChart.options.scales.y.ticks.color = newColors.textSecondary;
                monthlyUsersChart.options.scales.y.grid.color = newColors.borderColor;
                monthlyUsersChart.options.scales.x.ticks.color = newColors.textSecondary;
                monthlyUsersChart.update();
            }

            if (userTypesChart) {
                userTypesChart.options.plugins.legend.labels.color = newColors.textPrimary;
                userTypesChart.update();
            }

            if (revenueBreakdownChart) {
                revenueBreakdownChart.options.scales.y.ticks.color = newColors.textSecondary;
                revenueBreakdownChart.options.scales.y.grid.color = newColors.borderColor;
                revenueBreakdownChart.options.scales.x.ticks.color = newColors.textSecondary;
                revenueBreakdownChart.update();
            }

            if (revenuePlanChart) {
                revenuePlanChart.options.plugins.legend.labels.color = newColors.textPrimary;
                revenuePlanChart.update();
            }
        }

        // Remove loading class to prevent shrinking animation
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.body.classList.remove('loading');
            }, 50);
        });
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

