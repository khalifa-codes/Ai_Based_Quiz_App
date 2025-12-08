<?php
/**
 * Admin Dashboard
 * Protected page - requires authentication
 */
require_once 'auth_check.php';
require_once __DIR__ . '/../config/database.php';

// Fetch real-time statistics
$stats = [
    'total_organizations' => 0,
    'total_students' => 0,
    'total_teachers' => 0,
    'active_quizzes' => 0,
    'total_quizzes' => 0,
    'recent_organizations' => []
];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Total organizations
    $stmt = $conn->query("SELECT COUNT(*) FROM organizations WHERE status = 'active'");
    $stats['total_organizations'] = (int)$stmt->fetchColumn();
    
    // Total students
    $stmt = $conn->query("SELECT COUNT(*) FROM students");
    $stats['total_students'] = (int)$stmt->fetchColumn();
    
    // Total teachers
    $stmt = $conn->query("SELECT COUNT(*) FROM teachers");
    $stats['total_teachers'] = (int)$stmt->fetchColumn();
    
    // Active quizzes
    $stmt = $conn->query("SELECT COUNT(*) FROM quizzes WHERE status = 'published'");
    $stats['active_quizzes'] = (int)$stmt->fetchColumn();
    
    // Total quizzes
    $stmt = $conn->query("SELECT COUNT(*) FROM quizzes");
    $stats['total_quizzes'] = (int)$stmt->fetchColumn();
    
    // Recent organizations (last 5)
    $stmt = $conn->query("
        SELECT id, name, email, status, created_at 
        FROM organizations 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stats['recent_organizations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate growth (simplified - compare with last month)
    $lastMonth = date('Y-m-d', strtotime('-1 month'));
    $stmt = $conn->prepare("SELECT COUNT(*) FROM organizations WHERE created_at >= ?");
    $stmt->execute([$lastMonth]);
    $newOrgsThisMonth = (int)$stmt->fetchColumn();
    $stats['org_growth_percent'] = $stats['total_organizations'] > 0 
        ? round(($newOrgsThisMonth / $stats['total_organizations']) * 100, 1) 
        : 0;
    
} catch (Exception $e) {
    error_log("Admin Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard - Admin Panel</title>
    
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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">
                    <img src="../assets/images/logo-removebg-preview.png" alt="Quizaura Logo">
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
                        <a href="dashboard.php" class="nav-link active">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="organizations/organization_list.php" class="nav-link">
                            <i class="bi bi-building"></i>
                            <span>Organizations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="plans/plan_list.php" class="nav-link">
                            <i class="bi bi-box-seam"></i>
                            <span>Plans</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Security</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="security/security_dashboard.php" class="nav-link">
                            <i class="bi bi-shield-check"></i>
                            <span>Security Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="security/ip_management.php" class="nav-link">
                            <i class="bi bi-router"></i>
                            <span>IP Management</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="security/security_settings.php" class="nav-link">
                            <i class="bi bi-gear"></i>
                            <span>Security Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="security/audit_logs.php" class="nav-link">
                            <i class="bi bi-file-text"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="security/data_retention.php" class="nav-link">
                            <i class="bi bi-database"></i>
                            <span>Data Retention</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Analytics</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="reports/system_report.php" class="nav-link">
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
                        <a href="profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="#" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-gear"></i>
                            <span>Settings</span>
                        </a>
                        <a href="logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
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
                    <h1 class="topbar-title">Dashboard</h1>
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
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Organizations</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-building"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($stats['total_organizations']); ?></div>
                        <div class="stat-card-change <?php echo $stats['org_growth_percent'] >= 0 ? 'positive' : 'negative'; ?>">
                            <i class="bi bi-arrow-<?php echo $stats['org_growth_percent'] >= 0 ? 'up' : 'down'; ?>"></i>
                            <span><?php echo abs($stats['org_growth_percent']); ?>% from last month</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Students</h3>
                            <div class="stat-card-icon green">
                                <i class="bi bi-mortarboard"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($stats['total_students']); ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>Active students</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Active Quizzes</h3>
                            <div class="stat-card-icon orange">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($stats['active_quizzes']); ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span><?php echo number_format($stats['total_quizzes']); ?> total quizzes</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Teachers</h3>
                            <div class="stat-card-icon purple">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div class="stat-card-value"><?php echo number_format($stats['total_teachers']); ?></div>
                        <div class="stat-card-change positive">
                            <i class="bi bi-arrow-up"></i>
                            <span>Active teachers</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">User Growth</h2>
                                <select class="filter-select">
                                    <option>Last 7 days</option>
                                    <option>Last 30 days</option>
                                    <option>Last 90 days</option>
                                </select>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="userGrowthChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Quiz Activity</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="quizActivityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Revenue Overview</h2>
                                <select class="filter-select">
                                    <option>This Year</option>
                                    <option>Last Year</option>
                                </select>
                            </div>
                            <div class="content-card-body">
                                <div class="chart-container">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Recent Activity of Organizations</h2>
                        <a href="organizations/organization_list.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Organization</th>
                                        <th>Action</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($stats['recent_organizations'])): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No organizations found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($stats['recent_organizations'] as $org): 
                                            $initials = strtoupper(substr($org['name'], 0, 2));
                                            $timeAgo = '';
                                            if ($org['created_at']) {
                                                $created = strtotime($org['created_at']);
                                                $diff = time() - $created;
                                                if ($diff < 3600) {
                                                    $timeAgo = round($diff / 60) . ' minutes ago';
                                                } elseif ($diff < 86400) {
                                                    $timeAgo = round($diff / 3600) . ' hours ago';
                                                } else {
                                                    $timeAgo = round($diff / 86400) . ' days ago';
                                                }
                                            }
                                            $statusClass = $org['status'] === 'active' ? 'badge-success' : 
                                                         ($org['status'] === 'suspended' ? 'badge-danger' : 'badge-secondary');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                                    <div class="user-details">
                                                        <h6><?php echo htmlspecialchars($org['name']); ?></h6>
                                                        <p><?php echo htmlspecialchars($org['email']); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>Organization Registered</td>
                                            <td><?php echo $timeAgo; ?></td>
                                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($org['status']); ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
            if (adminSidebar) {
                adminSidebar.classList.remove('active');
            }
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('active');
            }
            if (floatingHamburger) {
                floatingHamburger.style.display = 'flex';
            }
        }

        function openSidebar() {
            if (adminSidebar) {
                adminSidebar.classList.add('active');
            }
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('active');
            }
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

        // Fetch real-time chart data
        function loadChartData(period = '7days') {
            fetch(`../api/admin/dashboard_stats.php?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        updateCharts(data.data);
                    }
                })
                .catch(error => {
                    console.error('Error loading chart data:', error);
                });
        }
        
        // Update charts with real data
        function updateCharts(chartData) {
            if (userGrowthChart && chartData.userGrowth) {
                userGrowthChart.data.labels = chartData.userGrowth.labels;
                userGrowthChart.data.datasets[0].data = chartData.userGrowth.organizations;
                userGrowthChart.data.datasets[1].data = chartData.userGrowth.students;
                userGrowthChart.update();
            }
            
            if (quizActivityChart && chartData.quizActivity) {
                quizActivityChart.data.labels = chartData.quizActivity.labels;
                quizActivityChart.data.datasets[0].data = chartData.quizActivity.values;
                quizActivityChart.update();
            }
            
            if (revenueChart && chartData.revenue) {
                revenueChart.data.labels = chartData.revenue.labels;
                revenueChart.data.datasets[0].data = chartData.revenue.values;
                revenueChart.update();
            }
        }
        
        // User Growth Chart (Line Chart)
        const userGrowthCtx = document.getElementById('userGrowthChart');
        let userGrowthChart = null;
        
        if (userGrowthCtx) {
            userGrowthChart = new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: ['Loading...'],
                    datasets: [
                        {
                            label: 'Organizations',
                            data: [0],
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '20',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Students',
                            data: [0],
                            borderColor: chartColors.success,
                            backgroundColor: chartColors.success + '20',
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

        // Quiz Activity Chart (Doughnut Chart)
        const quizActivityCtx = document.getElementById('quizActivityChart');
        let quizActivityChart = null;
        
        if (quizActivityCtx) {
            quizActivityChart = new Chart(quizActivityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Published', 'Draft', 'Archived'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: [
                            chartColors.success,
                            chartColors.warning,
                            chartColors.danger
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

        // Revenue Chart (Bar Chart) - Shows Quiz Activity
        const revenueCtx = document.getElementById('revenueChart');
        let revenueChart = null;
        
        if (revenueCtx) {
            revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Quiz Submissions',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: chartColors.primary,
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
                                    return value;
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

        // Update charts theme when theme changes
        function updateChartsTheme(theme) {
            const newColors = {
                primary: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim(),
                success: getComputedStyle(document.documentElement).getPropertyValue('--success-color').trim(),
                warning: getComputedStyle(document.documentElement).getPropertyValue('--warning-color').trim(),
                danger: getComputedStyle(document.documentElement).getPropertyValue('--danger-color').trim(),
                textPrimary: getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim(),
                textSecondary: getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim(),
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim()
            };

            // Update all charts
            if (userGrowthChart) {
                userGrowthChart.options.plugins.legend.labels.color = newColors.textPrimary;
                userGrowthChart.options.scales.y.ticks.color = newColors.textSecondary;
                userGrowthChart.options.scales.y.grid.color = newColors.borderColor;
                userGrowthChart.options.scales.x.ticks.color = newColors.textSecondary;
                userGrowthChart.update();
            }

            if (quizActivityChart) {
                quizActivityChart.options.plugins.legend.labels.color = newColors.textPrimary;
                quizActivityChart.update();
            }

            if (revenueChart) {
                revenueChart.options.scales.y.ticks.color = newColors.textSecondary;
                revenueChart.options.scales.y.grid.color = newColors.borderColor;
                revenueChart.options.scales.x.ticks.color = newColors.textSecondary;
                revenueChart.update();
            }
        }

        // Load initial chart data
        loadChartData('7days');
        
        // Chart Dropdown Functionality
        // User Growth Chart Dropdown
        const userGrowthSelect = document.querySelector('.content-card:has(#userGrowthChart) .filter-select');
        if (userGrowthSelect) {
            userGrowthSelect.addEventListener('change', function() {
                const period = this.value.toLowerCase().replace(' ', '');
                let periodParam = '7days';
                if (period.includes('30')) periodParam = '30days';
                else if (period.includes('90')) periodParam = '90days';
                loadChartData(periodParam);
            });
        }

        // Revenue Chart Dropdown
        const revenueSelect = document.querySelector('.content-card:has(#revenueChart) .filter-select');
        if (revenueSelect) {
            revenueSelect.addEventListener('change', function() {
                // Revenue chart shows yearly data, so reload with current period
                loadChartData('7days');
            });
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
</body>
</html>

