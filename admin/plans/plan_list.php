<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plans - Admin Panel</title>
    
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
                        <a href="../organizations/organization_list.php" class="nav-link">
                            <i class="bi bi-building"></i>
                            <span>Organizations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="plan_list.php" class="nav-link active">
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
                        <h1 class="topbar-title">Subscription Plans</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Plans</li>
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
                            <h3 class="stat-card-title">Active Plans</h3>
                            <div class="stat-card-icon blue">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">4</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Total Subscribers</h3>
                            <div class="stat-card-icon green">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">145</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <h3 class="stat-card-title">Monthly Revenue</h3>
                            <div class="stat-card-icon purple">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                        <div class="stat-card-value">$8.4K</div>
                    </div>
                </div>

                <!-- Plans Display -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Available Plans</h2>
                        <a href="create_plan.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Create Plan
                        </a>
                    </div>
                    <div class="content-card-body">
                        <div class="row">
                            <!-- Free Plan -->
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="plan-card">
                                    <div class="plan-name">Free</div>
                                    <div class="plan-price">$0<span>/month</span></div>
                                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Perfect for getting started</p>
                                    
                                    <ul class="plan-features">
                                        <li><i class="bi bi-check-circle-fill"></i> Up to 5 teachers</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Up to 50 students</li>
                                        <li><i class="bi bi-check-circle-fill"></i> 10 quizzes/month</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Basic analytics</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Email support</li>
                                    </ul>
                                    
                                    <div class="quick-actions">
                                        <button class="btn btn-primary w-100 mb-2">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger w-100">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-secondary" style="font-size: 0.85rem;">Subscribers:</span>
                                            <strong>48</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-secondary" style="font-size: 0.85rem;">Status:</span>
                                            <span class="badge badge-success">Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Plan -->
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="plan-card">
                                    <div class="plan-name">Basic</div>
                                    <div class="plan-price">$49<span>/month</span></div>
                                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">For small organizations</p>
                                    
                                    <ul class="plan-features">
                                        <li><i class="bi bi-check-circle-fill"></i> Up to 20 teachers</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Up to 200 students</li>
                                        <li><i class="bi bi-check-circle-fill"></i> 50 quizzes/month</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Advanced analytics</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Priority email support</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Custom branding</li>
                                    </ul>
                                    
                                    <div class="quick-actions">
                                        <button class="btn btn-primary w-100 mb-2">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger w-100">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-secondary" style="font-size: 0.85rem;">Subscribers:</span>
                                            <strong>35</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-secondary" style="font-size: 0.85rem;">Status:</span>
                                            <span class="badge badge-success">Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Premium Plan (Featured) -->
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="plan-card featured">
                                    <div class="plan-name">Premium</div>
                                    <div class="plan-price">$99<span>/month</span></div>
                                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Most popular choice</p>
                                    
                                    <ul class="plan-features">
                                        <li><i class="bi bi-check-circle-fill"></i> Up to 100 teachers</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Up to 1000 students</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Unlimited quizzes</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Premium analytics</li>
                                        <li><i class="bi bi-check-circle-fill"></i> 24/7 priority support</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Custom branding</li>
                                        <li><i class="bi bi-check-circle-fill"></i> API access</li>
                                    </ul>
                                    
                                    <div class="quick-actions">
                                        <button class="btn btn-primary w-100 mb-2">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger w-100">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-secondary" style="font-size: 0.85rem;">Subscribers:</span>
                                            <strong>52</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-secondary" style="font-size: 0.85rem;">Status:</span>
                                            <span class="badge badge-success">Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Enterprise Plan -->
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="plan-card">
                                    <div class="plan-name">Enterprise</div>
                                    <div class="plan-price">Custom</div>
                                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">For large organizations</p>
                                    
                                    <ul class="plan-features">
                                        <li><i class="bi bi-check-circle-fill"></i> Unlimited teachers</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Unlimited students</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Unlimited quizzes</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Custom analytics</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Dedicated support</li>
                                        <li><i class="bi bi-check-circle-fill"></i> White-label solution</li>
                                        <li><i class="bi bi-check-circle-fill"></i> Custom integrations</li>
                                        <li><i class="bi bi-check-circle-fill"></i> SLA guarantee</li>
                                    </ul>
                                    
                                    <div class="quick-actions">
                                        <button class="btn btn-primary w-100 mb-2">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger w-100">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-secondary" style="font-size: 0.85rem;">Subscribers:</span>
                                            <strong>10</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-secondary" style="font-size: 0.85rem;">Status:</span>
                                            <span class="badge badge-success">Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plans Table -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Plan Details</h2>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Plan Name</th>
                                        <th>Price</th>
                                        <th>Teachers Limit</th>
                                        <th>Students Limit</th>
                                        <th>Quizzes/Month</th>
                                        <th>Subscribers</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Free</strong></td>
                                        <td>$0</td>
                                        <td>5</td>
                                        <td>50</td>
                                        <td>10</td>
                                        <td>48</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
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
                                        <td><strong>Basic</strong></td>
                                        <td>$49</td>
                                        <td>20</td>
                                        <td>200</td>
                                        <td>50</td>
                                        <td>35</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
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
                                        <td><strong>Premium</strong></td>
                                        <td>$99</td>
                                        <td>100</td>
                                        <td>1000</td>
                                        <td>Unlimited</td>
                                        <td>52</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
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
                                        <td><strong>Enterprise</strong></td>
                                        <td>Custom</td>
                                        <td>Unlimited</td>
                                        <td>Unlimited</td>
                                        <td>Unlimited</td>
                                        <td>10</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <div class="action-buttons">
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

        // Plan Card Edit Buttons
        document.querySelectorAll('.plan-card .btn-primary').forEach(btn => {
            if (btn.textContent.includes('Edit')) {
                btn.addEventListener('click', function() {
                    const planCard = this.closest('.plan-card');
                    const planName = planCard.querySelector('.plan-name')?.textContent || 'Free';
                    const planId = planName.toLowerCase().replace(/\s+/g, '_');
                    window.location.href = `edit_plan.php?id=${planId}`;
                });
            }
        });

        // Plan Card Delete Buttons
        document.querySelectorAll('.plan-card .btn-outline-danger').forEach(btn => {
            btn.addEventListener('click', function() {
                const planCard = this.closest('.plan-card');
                const planName = planCard.querySelector('.plan-name')?.textContent || 'this plan';
                const planId = planName.toLowerCase().replace(/\s+/g, '_');
                
                if (confirm(`Are you sure you want to delete the ${planName} plan? Existing subscribers will need to be migrated to another plan. This action cannot be undone.`)) {
                    deletePlan(planId);
                }
            });
        });

        // Table Action Buttons
        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const planName = row.querySelector('td strong')?.textContent || 'free';
                const planId = planName.toLowerCase().replace(/\s+/g, '_');
                window.location.href = `edit_plan.php?id=${planId}`;
            });
        });

        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const planName = row.querySelector('td strong')?.textContent || 'this plan';
                const planId = planName.toLowerCase().replace(/\s+/g, '_');
                
                if (confirm(`Are you sure you want to delete the ${planName} plan? Existing subscribers will need to be migrated to another plan. This action cannot be undone.`)) {
                    deletePlan(planId);
                }
            });
        });

        // Delete Plan Function (Backend Ready)
        async function deletePlan(planId) {
            try {
                // TODO: Replace with actual API endpoint when backend is ready
                // const response = await fetch(`../api/plans/delete.php`, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ id: planId })
                // });
                
                // For now, simulate API call
                const response = { ok: true };
                
                if (response.ok) {
                    alert('Plan deleted successfully!');
                    location.reload(); // Reload page to reflect changes
                } else {
                    alert('Failed to delete plan. Please try again.');
                }
            } catch (error) {
                console.error('Error deleting plan:', error);
                alert('An error occurred while deleting the plan.');
            }
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

