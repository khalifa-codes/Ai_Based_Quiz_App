<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Plan - Admin Panel</title>
    
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
                        <h1 class="topbar-title">Create Plan</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="plan_list.php">Plans</a></li>
                                <li class="breadcrumb-item active">Create</li>
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
                    <div class="col-lg-8 mx-auto">
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Plan Details</h2>
                            </div>
                            <div class="content-card-body">
                                <form id="createPlanForm" method="POST">
                                    <!-- Basic Information -->
                                    <h5 class="mb-3" style="color: var(--text-primary); font-weight: 600;">Basic Information</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Plan Name <span class="required-asterisk">*</span></label>
                                                <input type="text" name="planName" class="admin-form-control" placeholder="e.g., Basic, Premium, Enterprise" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Price (USD) <span class="required-asterisk">*</span></label>
                                                <input type="number" name="price" class="admin-form-control" placeholder="49" step="0.01" required>
                                                <small class="text-muted">Enter 0 for free plans or -1 for custom pricing</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Billing Cycle <span class="required-asterisk">*</span></label>
                                                <select name="billingCycle" class="admin-form-control" required>
                                                    <option value="monthly">Monthly</option>
                                                    <option value="yearly">Yearly</option>
                                                    <option value="lifetime">Lifetime</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Status <span class="required-asterisk">*</span></label>
                                                <select name="status" class="admin-form-control" required>
                                                    <option value="active">Active</option>
                                                    <option value="inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Description</label>
                                        <textarea name="description" class="admin-form-control" rows="3" placeholder="Brief description of the plan..."></textarea>
                                    </div>

                                    <!-- Limits & Features -->
                                    <h5 class="mb-3 mt-4" style="color: var(--text-primary); font-weight: 600;">Limits & Quotas</h5>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Max Teachers <span class="required-asterisk">*</span></label>
                                                <input type="number" name="maxTeachers" class="admin-form-control" placeholder="50" required>
                                                <small class="text-muted">Use -1 for unlimited</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Max Students <span class="required-asterisk">*</span></label>
                                                <input type="number" name="maxStudents" class="admin-form-control" placeholder="500" required>
                                                <small class="text-muted">Use -1 for unlimited</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Max Quizzes/Month <span class="required-asterisk">*</span></label>
                                                <input type="number" name="maxQuizzes" class="admin-form-control" placeholder="100" required>
                                                <small class="text-muted">Use -1 for unlimited</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Storage Limit (GB)</label>
                                                <input type="number" name="storageLimit" class="admin-form-control" placeholder="10">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">API Calls/Month</label>
                                                <input type="number" name="apiCalls" class="admin-form-control" placeholder="10000">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Features -->
                                    <h5 class="mb-3 mt-4" style="color: var(--text-primary); font-weight: 600;">Features</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="basicAnalytics" name="basicAnalytics">
                                                <label class="form-check-label" for="basicAnalytics">
                                                    Basic Analytics
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="advancedAnalytics" name="advancedAnalytics">
                                                <label class="form-check-label" for="advancedAnalytics">
                                                    Advanced Analytics
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="customBranding" name="customBranding">
                                                <label class="form-check-label" for="customBranding">
                                                    Custom Branding
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="apiAccess" name="apiAccess">
                                                <label class="form-check-label" for="apiAccess">
                                                    API Access
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="prioritySupport" name="prioritySupport">
                                                <label class="form-check-label" for="prioritySupport">
                                                    Priority Support
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="whiteLabel" name="whiteLabel">
                                                <label class="form-check-label" for="whiteLabel">
                                                    White-Label Solution
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="sso" name="sso">
                                                <label class="form-check-label" for="sso">
                                                    Single Sign-On (SSO)
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="dataExport" name="dataExport">
                                                <label class="form-check-label" for="dataExport">
                                                    Data Export
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Settings -->
                                    <h5 class="mb-3 mt-4" style="color: var(--text-primary); font-weight: 600;">Additional Settings</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Trial Period (Days)</label>
                                                <input type="number" name="trialDays" class="admin-form-control" placeholder="14" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Display Order</label>
                                                <input type="number" name="displayOrder" class="admin-form-control" placeholder="1" value="1">
                                                <small class="text-muted">Lower numbers appear first</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                        <label class="form-check-label" for="featured">
                                            Mark as Featured/Recommended Plan
                                        </label>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg"></i> Create Plan
                                        </button>
                                        <a href="plan_list.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-lg"></i> Cancel
                                        </a>
                                    </div>
                                </form>
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

        // Form Submission
        const createPlanForm = document.getElementById('createPlanForm');
        
        createPlanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validate plan name
            if (!data.planName || data.planName.trim().length < 3) {
                alert('Plan name must be at least 3 characters');
                return;
            }
            
            // Validate price
            if (data.price === '' || parseFloat(data.price) < -1) {
                alert('Please enter a valid price (0 for free, -1 for custom)');
                return;
            }
            
            // Backend integration will be added here
            console.log('Creating plan:', data);
            
            // Show success message and redirect
            alert('Plan created successfully!');
            window.location.href = 'plan_list.php';
        });
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

