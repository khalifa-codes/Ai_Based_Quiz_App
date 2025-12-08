<?php 
require_once 'auth_check.php';
require_once __DIR__ . '/../includes/branding_loader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Organization Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="../assets/images/logo-removebg-preview.png">
    
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <style>
        /* Organization Branding Support */
        .org-branding-logo {
            height: 70px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            flex-shrink: 0;
        }
        
        /* Right-click and copy protection */
        .protected-content {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        
        .protected-content img {
            pointer-events: none;
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
        }
    </style>
</head>
<body class="loading">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="orgSidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo" id="orgLogoLink">
                    <img src="../assets/images/logo-removebg-preview.png" alt="Organization Logo" class="org-branding-logo" id="orgLogo">
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
                        <a href="dashboard.php" class="nav-link active">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="analytics.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="teachers/teacher_list.php" class="nav-link">
                            <i class="bi bi-people"></i>
                            <span>Teachers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="quizzes/quiz_list.php" class="nav-link">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Examinations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="students/student_list.php" class="nav-link">
                            <i class="bi bi-mortarboard"></i>
                            <span>Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="notifications/send_notification.php" class="nav-link">
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
                            <p class="sidebar-user-name">Hamza</p>
                            <p class="sidebar-user-role">Organization Admin</p>
                        </div>
                        <i class="bi bi-chevron-down" id="userDropdownIcon" style="transition: transform 0.2s ease; color: var(--text-muted);"></i>
                    </div>
                    <div class="sidebar-user-menu" id="sidebarUserMenu">
                        <a href="profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="settings.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
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
        <main class="admin-main protected-content">
            <!-- Floating Hamburger for Mobile -->
            <button class="floating-hamburger" id="floatingHamburger">
                <i class="bi bi-list"></i>
            </button>
            
            <!-- Topbar -->
            <div class="admin-topbar">
                <div class="topbar-left">
                    <h1 class="topbar-title">Dashboard</h1>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-actions" style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.75rem !important; flex-wrap: nowrap !important;">
                        <a href="notifications/send_notification.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="bi bi-bell"></i>
                            <span>Send Notification</span>
                        </a>
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="admin-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Teachers</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">24</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>3 new this month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Students</h3>
                            <div class="stat-card-icon green">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">1,245</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>45 new this month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Examinations</h3>
                            <div class="stat-card-icon orange">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">156</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>12 new this month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Active Examinations</h3>
                            <div class="stat-card-icon purple">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">98</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>8 active now</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Inactive Examinations</h3>
                            <div class="stat-card-icon" style="background: rgba(108, 117, 125, 0.1); color: var(--text-secondary);">
                                <i class="bi bi-x-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">58</div>
                        <div class="stat-card-change" style="color: var(--text-secondary);">
                            <i class="bi bi-dash"></i>
                            <span>No change</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Monthly Examination Activity</h2>
                                <select class="filter-select">
                                    <option>Last 12 Months</option>
                                    <option>Last 6 Months</option>
                                    <option>Last 3 Months</option>
                                </select>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="monthlyQuizChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Examination Status</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="quizStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Recent Activity</h2>
                        <a href="activity_log.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>New examination created</td>
                                        <td>John Doe</td>
                                        <td><span class="badge badge-info">Examination</span></td>
                                        <td>2 hours ago</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                    </tr>
                                    <tr>
                                        <td>Student enrolled</td>
                                        <td>Sarah Smith</td>
                                        <td><span class="badge badge-primary">Student</span></td>
                                        <td>5 hours ago</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                    </tr>
                                    <tr>
                                        <td>Teacher added</td>
                                        <td>Mike Johnson</td>
                                        <td><span class="badge badge-warning">Teacher</span></td>
                                        <td>1 day ago</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                    </tr>
                                    <tr>
                                        <td>Examination completed</td>
                                        <td>Emma Wilson</td>
                                        <td><span class="badge badge-info">Examination</span></td>
                                        <td>2 days ago</td>
                                        <td><span class="badge badge-success">Completed</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Prevent animations on page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.body.classList.remove('loading');
            }, 50);
        });
        
        // Right-click and copy protection
        document.addEventListener('contextmenu', function(e) {
            if (e.target.closest('.protected-content')) {
                e.preventDefault();
                return false;
            }
        });

        document.addEventListener('selectstart', function(e) {
            if (e.target.closest('.protected-content')) {
                e.preventDefault();
                return false;
            }
        });

        document.addEventListener('copy', function(e) {
            if (e.target.closest('.protected-content')) {
                e.preventDefault();
                return false;
            }
        });

        // Disable keyboard shortcuts for copy/cut
        document.addEventListener('keydown', function(e) {
            if (e.target.closest('.protected-content')) {
                // Disable Ctrl+C, Ctrl+X, Ctrl+A
                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'x' || e.key === 'a')) {
                    e.preventDefault();
                    return false;
                }
            }
        });

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
            // Get branding from localStorage (set by branding page)
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

        // Load branding on page load
        loadOrganizationBranding();

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

        // Monthly Examination Activity Chart
        const monthlyQuizCtx = document.getElementById('monthlyQuizChart');
        let monthlyQuizChart = null;
        
        if (monthlyQuizCtx) {
            monthlyQuizChart = new Chart(monthlyQuizCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Examinations Created',
                        data: [8, 12, 15, 18, 22, 25, 28, 30, 32, 35, 38, 42],
                        borderColor: chartColors.primary,
                        backgroundColor: chartColors.primary + '20',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Examinations Completed',
                        data: [45, 52, 58, 65, 72, 78, 85, 92, 98, 105, 112, 120],
                        borderColor: chartColors.success,
                        backgroundColor: chartColors.success + '20',
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
                            display: true,
                            position: 'top',
                            labels: {
                                color: chartColors.textPrimary,
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

        // Examination Status Chart
        const quizStatusCtx = document.getElementById('quizStatusChart');
        let quizStatusChart = null;
        
        if (quizStatusCtx) {
            quizStatusChart = new Chart(quizStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Inactive', 'Draft'],
                    datasets: [{
                        data: [98, 58, 12],
                        backgroundColor: [
                            chartColors.success,
                            chartColors.textSecondary,
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

            if (monthlyQuizChart) {
                monthlyQuizChart.options.plugins.legend.labels.color = newColors.textPrimary;
                monthlyQuizChart.options.scales.y.ticks.color = newColors.textSecondary;
                monthlyQuizChart.options.scales.y.grid.color = newColors.borderColor;
                monthlyQuizChart.options.scales.x.ticks.color = newColors.textSecondary;
                monthlyQuizChart.update();
            }

            if (quizStatusChart) {
                quizStatusChart.options.plugins.legend.labels.color = newColors.textPrimary;
                quizStatusChart.update();
            }
        }

        // Chart Dropdown Functionality
        const monthlyQuizSelect = document.querySelector('.content-card:has(#monthlyQuizChart) .filter-select');
        if (monthlyQuizSelect) {
            monthlyQuizSelect.addEventListener('change', function() {
                const period = this.value;
                // TODO: Replace with actual API call when backend is ready
                console.log('Updating Monthly Examination chart for period:', period);
            });
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
</body>
</html>

