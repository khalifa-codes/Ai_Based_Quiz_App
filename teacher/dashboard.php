<?php 
require_once 'auth_check.php';
require_once __DIR__ . '/../includes/branding_loader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Teacher Panel</title>
    
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
        .teacher-branding-logo {
            height: 70px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            flex-shrink: 0;
        }
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
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="teacherSidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo" id="teacherLogoLink">
                    <img src="../assets/images/logo-removebg-preview.png" alt="Teacher Logo" class="teacher-branding-logo" id="teacherLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="teacherSubtitle">Teacher</span>
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
                        <a href="analytics/performance.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="classes/class_list.php" class="nav-link">
                            <i class="bi bi-journal-bookmark"></i>
                            <span>Departments & Sections</span>
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
                        <a href="results/quiz_results.php" class="nav-link">
                            <i class="bi bi-clipboard-data"></i>
                            <span>Department Results</span>
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
                        <div class="sidebar-user-avatar">T</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;">
                            <p class="sidebar-user-name">Teacher</p>
                            <p class="sidebar-user-role">Educator</p>
                        </div>
                        <i class="bi bi-chevron-down" id="userDropdownIcon" style="transition: transform 0.2s ease; color: var(--text-muted);"></i>
                    </div>
                    <div class="sidebar-user-menu" id="sidebarUserMenu">
                        <a href="profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
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
                        <!-- Notification Bell -->
                        <div class="notification-wrapper" style="position: relative;">
                            <button class="topbar-btn notification-btn" id="notificationBtn" title="Notifications" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                                <i class="bi bi-bell" style="font-size: 1.3rem !important;"></i>
                                <span class="notification-badge" id="notificationBadge" style="position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff);">3</span>
                            </button>
                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 40px); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden;">
                                <div class="notification-dropdown-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-secondary);">
                                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">Notifications</h3>
                                    <a href="notifications/view_all.php" class="view-all-link" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
                                </div>
                                <div class="notification-dropdown-body" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Notifications will be loaded here -->
                                    <div class="notification-item unread" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease; background: var(--primary-light, rgba(13, 110, 253, 0.05));">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-megaphone"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">New Examination Schedule</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">Data Structures Midterm examination has been scheduled for next week.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">2 hours ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-item unread" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease;">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--success-color, #198754); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-check-circle"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">Results Published</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">Database Systems Assignment results are now available for review.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">5 hours ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-item" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease;">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--info-color, #0dcaf0); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-info-circle"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">System Update</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">New features have been added to the examination system.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">1 day ago</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification-dropdown-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); text-align: center; background: var(--bg-secondary);">
                                    <a href="notifications/view_all.php" class="view-all-btn" style="color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem;">Show All Messages</a>
                                </div>
                            </div>
                        </div>
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
                            <h3 class="stat-card-title">Total Students</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-mortarboard"></i>
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
                            <div class="stat-card-icon green">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">24</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>3 active now</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Completed Examinations</h3>
                            <div class="stat-card-icon purple">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">98</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>8 this week</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Average Score</h3>
                            <div class="stat-card-icon orange">
                                <i class="bi bi-star"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">85%</div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>2% increase</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Student Performance</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="studentPerformanceChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Examination Success Rate</h2>
                            </div>
                            <div class="content-card-body">
                                <canvas id="quizSuccessRateChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Recent Activity</h2>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>Student</th>
                                        <th>Type</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Examination completed</td>
                                        <td>John Doe</td>
                                        <td><span class="badge badge-info">Examination</span></td>
                                        <td>2 hours ago</td>
                                        <td><span class="badge badge-success">Completed</span></td>
                                    </tr>
                                    <tr>
                                        <td>New examination created</td>
                                        <td>You</td>
                                        <td><span class="badge badge-info">Examination</span></td>
                                        <td>5 hours ago</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                    </tr>
                                    <tr>
                                        <td>Student enrolled</td>
                                        <td>Alice Smith</td>
                                        <td><span class="badge badge-primary">Student</span></td>
                                        <td>1 day ago</td>
                                        <td><span class="badge badge-success">Active</span></td>
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
    <script src="../assets/js/admin-functions.js"></script>
    
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

        // Notification functionality is handled by notifications.js

        // Remove loading class after page load
        window.addEventListener('load', function() {
            document.body.classList.remove('loading');
        });

        // Charts
        const chartColors = {
            primary: getComputedStyle(document.documentElement).getPropertyValue('--primary-color') || '#0d6efd',
            success: getComputedStyle(document.documentElement).getPropertyValue('--success-color') || '#198754',
            warning: getComputedStyle(document.documentElement).getPropertyValue('--warning-color') || '#ffc107',
            danger: getComputedStyle(document.documentElement).getPropertyValue('--danger-color') || '#dc3545',
            info: getComputedStyle(document.documentElement).getPropertyValue('--info-color') || '#0dcaf0'
        };

        // Student Performance Chart
        const studentPerfCtx = document.getElementById('studentPerformanceChart');
        if (studentPerfCtx) {
            new Chart(studentPerfCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Average Score',
                        data: [78, 82, 80, 85, 88, 85],
                        borderColor: chartColors.primary,
                        backgroundColor: chartColors.primary + '20',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 70,
                            max: 100
                        }
                    }
                }
            });
        }

        // Examination Success Rate Chart
        const quizSuccessCtx = document.getElementById('quizSuccessRateChart');
        if (quizSuccessCtx) {
            new Chart(quizSuccessCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Passed', 'Failed'],
                    datasets: [{
                        data: [85, 15],
                        backgroundColor: [chartColors.success, chartColors.danger],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
    <script src="assets/js/notifications.js"></script>
</body>
</html>

