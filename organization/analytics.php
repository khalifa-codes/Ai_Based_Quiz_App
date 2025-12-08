<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Organization Panel</title>
    
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
<body>
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
                        <a href="dashboard.php" class="nav-link">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="analytics.php" class="nav-link active">
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
                            <p class="sidebar-user-name">Org Admin</p>
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
                    <div>
                        <h1 class="topbar-title">Analytics</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Analytics</li>
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
                                    <option>Examination Activity</option>
                                    <option>Teacher Performance</option>
                                    <option>Student Growth</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary w-100" id="generateReportBtn">
                                    <i class="bi bi-funnel"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Examination Activity -->
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Monthly Examination Activity</h2>
                        <select class="filter-select" id="monthlyActivityPeriod">
                            <option>Last 12 Months</option>
                            <option>Last 6 Months</option>
                            <option>Last 3 Months</option>
                        </select>
                    </div>
                    <div class="content-card-body">
                        <div class="chart-container">
                            <canvas id="monthlyActivityChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Teacher Performance Comparison</h2>
                                <select class="filter-select" id="teacherPerformancePeriod">
                                    <option>This Month</option>
                                    <option>Last Month</option>
                                    <option>Last 3 Months</option>
                                    <option>This Year</option>
                                </select>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="teacherPerformanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Top Teachers</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="topTeachersChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Growth -->
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Student Growth Over Time</h2>
                        <select class="filter-select" id="studentGrowthPeriod">
                            <option>Last 12 Months</option>
                            <option>Last 6 Months</option>
                            <option>Last 3 Months</option>
                            <option>All Time</option>
                        </select>
                    </div>
                    <div class="content-card-body">
                        <div class="chart-container">
                            <canvas id="studentGrowthChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Additional Metrics -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Examination Completion Rate</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="completionRateChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Average Scores</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="averageScoresChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="content-card mt-4">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Export Analytics</h2>
                    </div>
                    <div class="content-card-body">
                        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Download analytics data in various formats for offline analysis or sharing.</p>
                        <div class="quick-actions">
                            <button class="btn btn-outline-primary" id="exportPdfBtn">
                                <i class="bi bi-file-pdf"></i> Export as PDF
                            </button>
                            <button class="btn btn-outline-success" id="exportExcelBtn">
                                <i class="bi bi-file-excel"></i> Export as Excel
                            </button>
                            <button class="btn btn-outline-info" id="exportCsvBtn">
                                <i class="bi bi-file-text"></i> Export as CSV
                            </button>
                            <button class="btn btn-outline-secondary" id="printReportBtn">
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
    
    <script>
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

        document.addEventListener('keydown', function(e) {
            if (e.target.closest('.protected-content')) {
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
        const monthlyActivityCtx = document.getElementById('monthlyActivityChart');
        let monthlyActivityChart = null;
        
        if (monthlyActivityCtx) {
            monthlyActivityChart = new Chart(monthlyActivityCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Examinations Created',
                        data: [8, 12, 15, 18, 22, 25, 28, 30, 32, 35, 38, 42],
                        backgroundColor: chartColors.primary,
                        borderRadius: 8
                    }, {
                        label: 'Examinations Completed',
                        data: [45, 52, 58, 65, 72, 78, 85, 92, 98, 105, 112, 120],
                        backgroundColor: chartColors.success,
                        borderRadius: 8
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

        // Teacher Performance Comparison Chart
        const teacherPerformanceCtx = document.getElementById('teacherPerformanceChart');
        let teacherPerformanceChart = null;
        
        if (teacherPerformanceCtx) {
            teacherPerformanceChart = new Chart(teacherPerformanceCtx, {
                type: 'bar',
                data: {
                    labels: ['John Doe', 'Sarah Smith', 'Mike Johnson', 'Emma Wilson', 'David Brown', 'Lisa Anderson'],
                    datasets: [{
                        label: 'Examinations Created',
                        data: [45, 38, 42, 35, 28, 32],
                        backgroundColor: chartColors.primary,
                        borderRadius: 8
                    }, {
                        label: 'Students Enrolled',
                        data: [320, 285, 298, 265, 240, 255],
                        backgroundColor: chartColors.info,
                        borderRadius: 8
                    }, {
                        label: 'Avg. Score',
                        data: [85, 88, 82, 90, 87, 84],
                        backgroundColor: chartColors.success,
                        borderRadius: 8
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

        // Top Teachers Chart
        const topTeachersCtx = document.getElementById('topTeachersChart');
        let topTeachersChart = null;
        
        if (topTeachersCtx) {
            topTeachersChart = new Chart(topTeachersCtx, {
                type: 'doughnut',
                data: {
                    labels: ['John Doe', 'Sarah Smith', 'Mike Johnson', 'Emma Wilson', 'Others'],
                    datasets: [{
                        data: [25, 20, 18, 15, 22],
                        backgroundColor: [
                            chartColors.primary,
                            chartColors.success,
                            chartColors.info,
                            chartColors.warning,
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
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Student Growth Chart
        const studentGrowthCtx = document.getElementById('studentGrowthChart');
        let studentGrowthChart = null;
        
        if (studentGrowthCtx) {
            studentGrowthChart = new Chart(studentGrowthCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Total Students',
                        data: [850, 920, 980, 1050, 1120, 1180, 1200, 1220, 1230, 1235, 1240, 1245],
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
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
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

        // Examination Completion Rate Chart
        const completionRateCtx = document.getElementById('completionRateChart');
        let completionRateChart = null;
        
        if (completionRateCtx) {
            completionRateChart = new Chart(completionRateCtx, {
                type: 'pie',
                data: {
                    labels: ['Completed', 'In Progress', 'Not Started'],
                    datasets: [{
                        data: [78, 15, 7],
                        backgroundColor: [
                            chartColors.success,
                            chartColors.warning,
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
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Average Scores Chart
        const averageScoresCtx = document.getElementById('averageScoresChart');
        let averageScoresChart = null;
        
        if (averageScoresCtx) {
            averageScoresChart = new Chart(averageScoresCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Average Score (%)',
                        data: [72, 75, 78, 80, 82, 84, 85, 86, 87, 88, 89, 90],
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
                            beginAtZero: false,
                            min: 60,
                            max: 100,
                            ticks: {
                                color: chartColors.textSecondary,
                                callback: function(value) {
                                    return value + '%';
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

        // Update charts theme
        function updateChartsTheme(theme) {
            const newColors = {
                primary: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim(),
                success: getComputedStyle(document.documentElement).getPropertyValue('--success-color').trim(),
                warning: getComputedStyle(document.documentElement).getPropertyValue('--warning-color').trim(),
                info: getComputedStyle(document.documentElement).getPropertyValue('--info-color').trim(),
                textPrimary: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim(),
                textSecondary: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim(),
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim()
            };

            if (monthlyActivityChart) {
                monthlyActivityChart.options.plugins.legend.labels.color = newColors.textPrimary;
                monthlyActivityChart.options.scales.y.ticks.color = newColors.textSecondary;
                monthlyActivityChart.options.scales.y.grid.color = newColors.borderColor;
                monthlyActivityChart.options.scales.x.ticks.color = newColors.textSecondary;
                monthlyActivityChart.update();
            }

            if (teacherPerformanceChart) {
                teacherPerformanceChart.options.plugins.legend.labels.color = newColors.textPrimary;
                teacherPerformanceChart.options.scales.y.ticks.color = newColors.textSecondary;
                teacherPerformanceChart.options.scales.y.grid.color = newColors.borderColor;
                teacherPerformanceChart.options.scales.x.ticks.color = newColors.textSecondary;
                teacherPerformanceChart.update();
            }

            if (topTeachersChart) {
                topTeachersChart.options.plugins.legend.labels.color = newColors.textPrimary;
                topTeachersChart.update();
            }

            if (studentGrowthChart) {
                studentGrowthChart.options.scales.y.ticks.color = newColors.textSecondary;
                studentGrowthChart.options.scales.y.grid.color = newColors.borderColor;
                studentGrowthChart.options.scales.x.ticks.color = newColors.textSecondary;
                studentGrowthChart.update();
            }

            if (completionRateChart) {
                completionRateChart.options.plugins.legend.labels.color = newColors.textPrimary;
                completionRateChart.update();
            }

            if (averageScoresChart) {
                averageScoresChart.options.scales.y.ticks.color = newColors.textSecondary;
                averageScoresChart.options.scales.y.grid.color = newColors.borderColor;
                averageScoresChart.options.scales.x.ticks.color = newColors.textSecondary;
                averageScoresChart.update();
            }
        }

        // Chart Period Selectors
        const monthlyActivityPeriod = document.getElementById('monthlyActivityPeriod');
        if (monthlyActivityPeriod) {
            monthlyActivityPeriod.addEventListener('change', function() {
                const period = this.value;
                // TODO: Replace with actual API call
                console.log('Updating Monthly Activity chart for period:', period);
            });
        }

        const teacherPerformancePeriod = document.getElementById('teacherPerformancePeriod');
        if (teacherPerformancePeriod) {
            teacherPerformancePeriod.addEventListener('change', function() {
                const period = this.value;
                // TODO: Replace with actual API call
                console.log('Updating Teacher Performance chart for period:', period);
            });
        }

        const studentGrowthPeriod = document.getElementById('studentGrowthPeriod');
        if (studentGrowthPeriod) {
            studentGrowthPeriod.addEventListener('change', function() {
                const period = this.value;
                // TODO: Replace with actual API call
                console.log('Updating Student Growth chart for period:', period);
            });
        }

        // Generate Report Button
        const generateReportBtn = document.getElementById('generateReportBtn');
        if (generateReportBtn) {
            generateReportBtn.addEventListener('click', function() {
                const fromDate = document.getElementById('fromDate').value;
                const toDate = document.getElementById('toDate').value;
                const reportType = document.getElementById('reportType').value;
                
                // TODO: Replace with actual API call
                console.log('Generating report:', { fromDate, toDate, reportType });
                alert('Report generation feature will be available after backend integration.');
            });
        }

        // Export Buttons
        const exportPdfBtn = document.getElementById('exportPdfBtn');
        const exportExcelBtn = document.getElementById('exportExcelBtn');
        const exportCsvBtn = document.getElementById('exportCsvBtn');
        const printReportBtn = document.getElementById('printReportBtn');

        if (exportPdfBtn) {
            exportPdfBtn.addEventListener('click', function() {
                // TODO: Replace with actual export functionality
                console.log('Exporting as PDF...');
                alert('PDF export feature will be available after backend integration.');
            });
        }

        if (exportExcelBtn) {
            exportExcelBtn.addEventListener('click', function() {
                // TODO: Replace with actual export functionality
                console.log('Exporting as Excel...');
                alert('Excel export feature will be available after backend integration.');
            });
        }

        if (exportCsvBtn) {
            exportCsvBtn.addEventListener('click', function() {
                // TODO: Replace with actual export functionality
                console.log('Exporting as CSV...');
                alert('CSV export feature will be available after backend integration.');
            });
        }

        if (printReportBtn) {
            printReportBtn.addEventListener('click', function() {
                window.print();
            });
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
</body>
</html>

