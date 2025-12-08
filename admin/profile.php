<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin Panel</title>
    
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
                        <a href="dashboard.php" class="nav-link">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link active">
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
                    <h1 class="topbar-title">Profile</h1>
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
                    <!-- Profile Card -->
                    <div class="col-lg-4 mb-4">
                        <div class="profile-card">
                            <div class="profile-avatar-large">A</div>
                            <h3 class="profile-name">Admin User</h3>
                            <p class="profile-email">admin@saasplatform.com</p>
                            <span class="badge badge-primary">Administrator</span>
                            
                            <div class="mt-4">
                                <button class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-camera"></i> Change Avatar
                                </button>
                                <button class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-key"></i> Change Password
                                </button>
                            </div>
                        </div>

                        <!-- Stats Card -->
                        <div class="content-card mt-4">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Account Stats</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-secondary">Last Login</span>
                                    <strong>2 hours ago</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-secondary">Account Created</span>
                                    <strong>Jan 15, 2024</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-secondary">Total Sessions</span>
                                    <strong>1,234</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">Account Status</span>
                                    <span class="badge badge-success">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="col-lg-8">
                        <div class="content-card mb-4">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Personal Information</h2>
                                <button class="btn btn-primary" id="editProfileBtn">
                                    <i class="bi bi-pencil"></i> Edit Profile
                                </button>
                            </div>
                            <div class="content-card-body">
                                <form id="profileForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">First Name <span class="required-asterisk">*</span></label>
                                                <input type="text" class="admin-form-control" value="Admin" disabled>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Last Name <span class="required-asterisk">*</span></label>
                                                <input type="text" class="admin-form-control" value="User" disabled>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Email Address <span class="required-asterisk">*</span></label>
                                        <input type="email" class="admin-form-control" value="admin@saasplatform.com" disabled>
                                    </div>

                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Phone Number</label>
                                        <input type="tel" class="admin-form-control" value="+1 (555) 123-4567" disabled>
                                    </div>

                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Bio</label>
                                        <textarea class="admin-form-control" rows="4" disabled>System administrator with full access to all platform features and settings.</textarea>
                                    </div>

                                    <div class="d-none" id="saveButtonsContainer">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg"></i> Save Changes
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary ms-2" id="cancelEditBtn">
                                            <i class="bi bi-x-lg"></i> Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="content-card">
                            <div class="content-card-header">
                                <h2 class="content-card-title">Security Settings</h2>
                            </div>
                            <div class="content-card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                                    <div>
                                        <h6 class="mb-1">Two-Factor Authentication</h6>
                                        <p class="text-secondary mb-0" style="font-size: 0.85rem;">Add an extra layer of security to your account</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="twoFactorSwitch">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom: 1px solid var(--border-color);">
                                    <div>
                                        <h6 class="mb-1">Login Notifications</h6>
                                        <p class="text-secondary mb-0" style="font-size: 0.85rem;">Get notified when someone logs into your account</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="loginNotificationsSwitch" checked>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Password</h6>
                                        <p class="text-secondary mb-0" style="font-size: 0.85rem;">Last changed 30 days ago</p>
                                    </div>
                                    <button class="btn btn-outline-primary">Change Password</button>
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

        // Edit Profile Functionality
        const editProfileBtn = document.getElementById('editProfileBtn');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const profileForm = document.getElementById('profileForm');
        const saveButtonsContainer = document.getElementById('saveButtonsContainer');
        const formInputs = profileForm.querySelectorAll('input, textarea');

        editProfileBtn.addEventListener('click', function() {
            formInputs.forEach(input => input.disabled = false);
            saveButtonsContainer.classList.remove('d-none');
            editProfileBtn.classList.add('d-none');
        });

        cancelEditBtn.addEventListener('click', function() {
            formInputs.forEach(input => input.disabled = true);
            saveButtonsContainer.classList.add('d-none');
            editProfileBtn.classList.remove('d-none');
            profileForm.reset();
        });

        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validate email
            if (data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            try {
                // TODO: Replace with actual API endpoint when backend is ready
                // const response = await fetch('../api/profile/update.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify(data)
                // });
                
                // const result = await response.json();
                
                // For now, simulate API call
                const result = { success: true };
                
                if (result.success) {
                    formInputs.forEach(input => input.disabled = true);
                    saveButtonsContainer.classList.add('d-none');
                    editProfileBtn.classList.remove('d-none');
                    alert('Profile updated successfully!');
                } else {
                    alert('Failed to update profile. Please try again.');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                alert('An error occurred while updating the profile.');
            }
        });
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
</body>
</html>

