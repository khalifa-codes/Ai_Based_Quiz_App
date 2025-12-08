<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security & Compliance - Admin Panel</title>
    
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                        <a href="security_dashboard.php" class="nav-link active">
                            <i class="bi bi-shield-check"></i>
                            <span>Security Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="ip_management.php" class="nav-link">
                            <i class="bi bi-router"></i>
                            <span>IP Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="security_settings.php" class="nav-link">
                            <i class="bi bi-gear"></i>
                            <span>Security Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="audit_logs.php" class="nav-link">
                            <i class="bi bi-file-text"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="data_retention.php" class="nav-link">
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
                        <h1 class="topbar-title">Security & Compliance</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Security Dashboard</li>
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
                <!-- Security Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Security Score</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-shield-check"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">92%</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>3% improvement</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Failed Login Attempts</h3>
                            <div class="stat-card-icon red">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">23</div>
                        <div class="stat-card-change negative">
                            <i class="bi bi-arrow-down"></i>
                            <span>12% decrease</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Blocked IPs</h3>
                            <div class="stat-card-icon orange">
                                <i class="bi bi-ban"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">156</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>8 new blocks</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Active Sessions</h3>
                            <div class="stat-card-icon green">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">1,234</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>5% increase</span>
                        </div>
                    </div>
                </div>

                <!-- Security Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Security Events (Last 7 Days)</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="securityEventsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Threat Distribution</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="threatDistributionChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Security Alerts -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Recent Security Alerts</h2>
                        <button class="btn btn-sm btn-outline-primary" id="refreshAlertsBtn">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Source IP</th>
                                        <th>Description</th>
                                        <th>Severity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="securityAlertsTableBody">
                                    <tr>
                                        <td>2 hours ago</td>
                                        <td><span class="badge badge-warning">Failed Login</span></td>
                                        <td>192.168.1.100</td>
                                        <td>Multiple failed login attempts detected</td>
                                        <td><span class="badge badge-danger">High</span></td>
                                        <td><span class="badge badge-warning">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger blockIpBtn" data-ip="192.168.1.100">
                                                <i class="bi bi-ban"></i> Block IP
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>5 hours ago</td>
                                        <td><span class="badge badge-info">Suspicious Activity</span></td>
                                        <td>10.0.0.50</td>
                                        <td>Unusual access pattern detected</td>
                                        <td><span class="badge badge-warning">Medium</span></td>
                                        <td><span class="badge badge-success">Resolved</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary viewDetailsBtn" data-id="2">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>1 day ago</td>
                                        <td><span class="badge badge-danger">Data Breach Attempt</span></td>
                                        <td>172.16.0.200</td>
                                        <td>Unauthorized access attempt to database</td>
                                        <td><span class="badge badge-danger">Critical</span></td>
                                        <td><span class="badge badge-success">Blocked</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger blockIpBtn" data-ip="172.16.0.200">
                                                <i class="bi bi-ban"></i> Block IP
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2 days ago</td>
                                        <td><span class="badge badge-warning">Rate Limit Exceeded</span></td>
                                        <td>203.0.113.45</td>
                                        <td>API rate limit exceeded from single IP</td>
                                        <td><span class="badge badge-warning">Medium</span></td>
                                        <td><span class="badge badge-success">Resolved</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary viewDetailsBtn" data-id="4">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3 days ago</td>
                                        <td><span class="badge badge-info">Password Reset</span></td>
                                        <td>198.51.100.30</td>
                                        <td>Multiple password reset requests</td>
                                        <td><span class="badge badge-info">Low</span></td>
                                        <td><span class="badge badge-success">Resolved</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary viewDetailsBtn" data-id="5">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Security Status Overview -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Security Status</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="security-status-list">
                                    <div class="security-status-item">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span><i class="bi bi-shield-check text-success"></i> Two-Factor Authentication</span>
                                            <span class="badge badge-success">Enabled</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="security-status-item">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span><i class="bi bi-lock text-success"></i> SSL/TLS Encryption</span>
                                            <span class="badge badge-success">Active</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="security-status-item">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span><i class="bi bi-firewall text-warning"></i> Firewall Protection</span>
                                            <span class="badge badge-warning">Needs Review</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-warning" style="width: 75%"></div>
                                        </div>
                                    </div>
                                    <div class="security-status-item">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span><i class="bi bi-database text-success"></i> Data Backup</span>
                                            <span class="badge badge-success">Active</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: 100%"></div>
                                        </div>
                                    </div>
                                    <div class="security-status-item">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span><i class="bi bi-file-earmark-lock text-info"></i> GDPR Compliance</span>
                                            <span class="badge badge-info">In Progress</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-info" style="width: 85%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Quick Actions</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="d-grid gap-2">
                                    <a href="ip_management.php" class="btn btn-primary">
                                        <i class="bi bi-router"></i> Manage IP Whitelist/Blacklist
                                    </a>
                                    <a href="security_settings.php" class="btn btn-outline-primary">
                                        <i class="bi bi-gear"></i> Security Settings
                                    </a>
                                    <a href="audit_logs.php" class="btn btn-outline-primary">
                                        <i class="bi bi-file-text"></i> View Audit Logs
                                    </a>
                                    <a href="data_retention.php" class="btn btn-outline-primary">
                                        <i class="bi bi-database"></i> Data Retention Policy
                                    </a>
                                    <button class="btn btn-outline-danger" id="exportSecurityReportBtn">
                                        <i class="bi bi-download"></i> Export Security Report
                                    </button>
                                    <button class="btn btn-outline-primary" id="runSecurityScanBtn">
                                        <i class="bi bi-shield-check"></i> Run Security Scan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
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
                
                // Update charts theme
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
                openSidebar();
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                closeSidebar();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && adminSidebar.classList.contains('active')) {
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

        // Security Events Chart
        const securityEventsCtx = document.getElementById('securityEventsChart');
        let securityEventsChart = null;
        
        if (securityEventsCtx) {
            securityEventsChart = new Chart(securityEventsCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'Failed Logins',
                            data: [12, 19, 15, 25, 22, 18, 23],
                            borderColor: chartColors.danger,
                            backgroundColor: chartColors.danger + '20',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Blocked IPs',
                            data: [5, 8, 6, 12, 10, 7, 9],
                            borderColor: chartColors.warning,
                            backgroundColor: chartColors.warning + '20',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Security Alerts',
                            data: [3, 5, 4, 8, 6, 4, 7],
                            borderColor: chartColors.info,
                            backgroundColor: chartColors.info + '20',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: chartColors.textPrimary,
                                usePointStyle: true,
                                padding: 15
                            }
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

        // Threat Distribution Chart
        const threatDistributionCtx = document.getElementById('threatDistributionChart');
        let threatDistributionChart = null;
        
        if (threatDistributionCtx) {
            threatDistributionChart = new Chart(threatDistributionCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Failed Logins', 'Suspicious Activity', 'Rate Limit', 'Data Breach Attempts', 'Other'],
                    datasets: [{
                        data: [45, 25, 15, 10, 5],
                        backgroundColor: [
                            chartColors.danger,
                            chartColors.warning,
                            chartColors.info,
                            chartColors.primary,
                            chartColors.textSecondary
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
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Update Charts Theme
        function updateChartsTheme(theme) {
            const newColors = {
                primary: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim(),
                success: getComputedStyle(document.documentElement).getPropertyValue('--success-color').trim(),
                warning: getComputedStyle(document.documentElement).getPropertyValue('--warning-color').trim(),
                danger: getComputedStyle(document.documentElement).getPropertyValue('--danger-color').trim(),
                info: getComputedStyle(document.documentElement).getPropertyValue('--info-color').trim(),
                textPrimary: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim(),
                textSecondary: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim(),
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim()
            };

            // Update Security Events Chart
            if (securityEventsChart) {
                securityEventsChart.options.plugins.legend.labels.color = newColors.textPrimary;
                securityEventsChart.options.scales.y.ticks.color = newColors.textSecondary;
                securityEventsChart.options.scales.y.grid.color = newColors.borderColor;
                securityEventsChart.options.scales.x.ticks.color = newColors.textSecondary;
                securityEventsChart.update();
            }

            // Update Threat Distribution Chart
            if (threatDistributionChart) {
                threatDistributionChart.options.plugins.legend.labels.color = newColors.textPrimary;
                threatDistributionChart.update();
            }
        }

        // Refresh Alerts Button
        document.getElementById('refreshAlertsBtn').addEventListener('click', function() {
            const btn = this;
            const icon = btn.querySelector('i');
            icon.classList.add('spin');
            btn.disabled = true;
            
            setTimeout(() => {
                icon.classList.remove('spin');
                btn.disabled = false;
                alert('Security alerts refreshed successfully!');
            }, 1000);
        });

        // Block IP Button
        document.querySelectorAll('.blockIpBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const ip = this.getAttribute('data-ip');
                if (confirm(`Are you sure you want to block IP address ${ip}?`)) {
                    this.innerHTML = '<i class="bi bi-check"></i> Blocked';
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-success');
                    this.disabled = true;
                    alert(`IP address ${ip} has been blocked successfully!`);
                }
            });
        });

        // View Details Button
        document.querySelectorAll('.viewDetailsBtn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                alert(`Viewing details for security alert #${id}`);
            });
        });

        // Export Security Report
        document.getElementById('exportSecurityReportBtn').addEventListener('click', function() {
            const btn = this;
            const icon = btn.querySelector('i');
            icon.classList.add('spin');
            btn.disabled = true;
            
            setTimeout(() => {
                icon.classList.remove('spin');
                btn.disabled = false;
                alert('Security report exported successfully!');
            }, 2000);
        });

        // Run Security Scan
        document.getElementById('runSecurityScanBtn').addEventListener('click', function() {
            const btn = this;
            const icon = btn.querySelector('i');
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Scanning...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Security scan completed! No critical issues found.');
            }, 3000);
        });

        // Add spin animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .spin {
                animation: spin 1s linear infinite;
            }
        `;
        document.head.appendChild(style);
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

