<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Organization Panel</title>
    <link rel="icon" type="image/png" href="../assets/images/logo-removebg-preview.png">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .org-branding-logo { height: 70px; width: auto; max-width: 160px; object-fit: contain; flex-shrink: 0; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="orgSidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo" id="orgLogoLink">
                    <img src="../assets/images/logo-removebg-preview.png" alt="Organization Logo" class="org-branding-logo" id="orgLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="orgSubtitle">Organization</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-x-lg"></i></button>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="analytics.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Analytics</span></a></li>
                </ul>
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="teachers/teacher_list.php" class="nav-link"><i class="bi bi-people"></i><span>Teachers</span></a></li>
                    <li class="nav-item"><a href="quizzes/quiz_list.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Examinations</span></a></li>
                    <li class="nav-item"><a href="students/student_list.php" class="nav-link"><i class="bi bi-mortarboard"></i><span>Students</span></a></li>
                    <li class="nav-item"><a href="notifications/send_notification.php" class="nav-link"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
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
                        <a href="profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-person"></i><span>Profile</span></a>
                        <a href="settings.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-gear"></i><span>Settings</span></a>
                        <a href="../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
                    </div>
                </div>
            </div>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <main class="admin-main">
            <button class="floating-hamburger" id="floatingHamburger"><i class="bi bi-list"></i></button>
            <div class="admin-topbar">
                <div class="topbar-left">
                    <div>
                        <h1 class="topbar-title">Settings</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Settings</li>
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
            <div class="admin-content">
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Organization Settings</h2>
                    </div>
                    <div class="content-card-body">
                        <form id="settingsForm">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Email Notifications</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                    <label class="form-check-label" for="emailNotifications">Receive email notifications</label>
                                </div>
                            </div>
                            <div class="admin-form-group">
                                <label class="admin-form-label">Auto-approve Teachers</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="autoApproveTeachers">
                                    <label class="form-check-label" for="autoApproveTeachers">Automatically approve new teacher registrations</label>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                                <button type="button" class="btn btn-outline-secondary" id="resetSettingsBtn">Reset to Default</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
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

        function loadOrganizationBranding() {
            const orgBranding = JSON.parse(localStorage.getItem('orgBranding') || '{}');
            if (orgBranding.logo) {
                const logoImg = document.getElementById('orgLogo');
                if (logoImg) logoImg.src = orgBranding.logo;
            }
            if (orgBranding.name) {
                const sidebarSubtitle = document.getElementById('orgSubtitle');
                if (sidebarSubtitle) sidebarSubtitle.textContent = orgBranding.name;
            }
            if (orgBranding.primaryColor) {
                document.documentElement.style.setProperty('--primary-color', orgBranding.primaryColor);
            }
            if (orgBranding.secondaryColor) {
                document.documentElement.style.setProperty('--primary-hover', orgBranding.secondaryColor);
            }
        }
        loadOrganizationBranding();
        
        const settingsForm = document.getElementById('settingsForm');
        if (settingsForm) {
            settingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                // TODO: Replace with actual API call
                alert('Settings saved successfully!');
            });
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="assets/js/activity-tracker.js"></script>
</body>
</html>

