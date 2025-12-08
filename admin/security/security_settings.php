<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings - Admin Panel</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
    
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
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
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="../profile.php" class="nav-link"><i class="bi bi-person"></i><span>Profile</span></a></li>
                </ul>
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../organizations/organization_list.php" class="nav-link"><i class="bi bi-building"></i><span>Organizations</span></a></li>
                    <li class="nav-item"><a href="../plans/plan_list.php" class="nav-link"><i class="bi bi-box-seam"></i><span>Plans</span></a></li>
                </ul>
                <div class="nav-section-title">Security</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="security_dashboard.php" class="nav-link"><i class="bi bi-shield-check"></i><span>Security Dashboard</span></a></li>
                    <li class="nav-item"><a href="ip_management.php" class="nav-link"><i class="bi bi-router"></i><span>IP Management</span></a></li>
                    <li class="nav-item"><a href="security_settings.php" class="nav-link active"><i class="bi bi-gear"></i><span>Security Settings</span></a></li>
                    <li class="nav-item"><a href="audit_logs.php" class="nav-link"><i class="bi bi-file-text"></i><span>Audit Logs</span></a></li>
                    <li class="nav-item"><a href="data_retention.php" class="nav-link"><i class="bi bi-database"></i><span>Data Retention</span></a></li>
                </ul>
                <div class="nav-section-title">Analytics</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../reports/system_report.php" class="nav-link"><i class="bi bi-graph-up"></i><span>System Reports</span></a></li>
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
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-person"></i><span>Profile</span></a>
                        <a href="#" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-gear"></i><span>Settings</span></a>
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
                        <h1 class="topbar-title">Security Settings</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="security_dashboard.php">Security</a></li>
                                <li class="breadcrumb-item active">Security Settings</li>
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
                <form id="securitySettingsForm">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Password Policy</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="mb-3">
                                        <label class="admin-form-label">Minimum Password Length</label>
                                        <input type="number" name="minPasswordLength" class="admin-form-control" value="8" min="6" max="32">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requireUppercase" id="requireUppercase" checked>
                                            <label class="form-check-label" for="requireUppercase">Require Uppercase Letters</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requireLowercase" id="requireLowercase" checked>
                                            <label class="form-check-label" for="requireLowercase">Require Lowercase Letters</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requireNumbers" id="requireNumbers" checked>
                                            <label class="form-check-label" for="requireNumbers">Require Numbers</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="requireSpecialChars" id="requireSpecialChars" checked>
                                            <label class="form-check-label" for="requireSpecialChars">Require Special Characters</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Password Expiry (days)</label>
                                        <input type="number" name="passwordExpiry" class="admin-form-control" value="90" min="0">
                                        <small class="form-text text-muted">0 = Never expires</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Max Failed Login Attempts</label>
                                        <input type="number" name="maxFailedAttempts" class="admin-form-control" value="5" min="1" max="10">
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Account Lockout Duration (minutes)</label>
                                        <input type="number" name="lockoutDuration" class="admin-form-control" value="30" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Two-Factor Authentication</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="enable2FA" id="enable2FA" checked>
                                            <label class="form-check-label" for="enable2FA">Enable 2FA for All Users</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">2FA Method</label>
                                        <select name="twoFAMethod" class="admin-form-control">
                                            <option value="totp" selected>TOTP (Time-based One-Time Password)</option>
                                            <option value="sms">SMS</option>
                                            <option value="email">Email</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">2FA Backup Codes</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="enableBackupCodes" id="enableBackupCodes" checked>
                                            <label class="form-check-label" for="enableBackupCodes">Generate Backup Codes</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="content-card mt-4">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Session Management</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="mb-3">
                                        <label class="admin-form-label">Session Timeout (minutes)</label>
                                        <input type="number" name="sessionTimeout" class="admin-form-control" value="30" min="5">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="forceLogoutOnPasswordChange" id="forceLogoutOnPasswordChange" checked>
                                            <label class="form-check-label" for="forceLogoutOnPasswordChange">Force Logout on Password Change</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Max Concurrent Sessions</label>
                                        <input type="number" name="maxSessions" class="admin-form-control" value="3" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-4 mt-2">
                        <div class="col-12">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Rate Limiting</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="admin-form-label">API Rate Limit (requests/minute)</label>
                                                <input type="number" name="apiRateLimit" class="admin-form-control" value="100" min="1">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="admin-form-label">Login Rate Limit (attempts/hour)</label>
                                                <input type="number" name="loginRateLimit" class="admin-form-control" value="5" min="1">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="admin-form-label">Password Reset Limit (requests/hour)</label>
                                                <input type="number" name="passwordResetLimit" class="admin-form-control" value="3" min="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-4 mt-2">
                        <div class="col-12">
                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Security Headers</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enableCSP" id="enableCSP" checked>
                                                    <label class="form-check-label" for="enableCSP">Content Security Policy (CSP)</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enableHSTS" id="enableHSTS" checked>
                                                    <label class="form-check-label" for="enableHSTS">HTTP Strict Transport Security (HSTS)</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enableXFrameOptions" id="enableXFrameOptions" checked>
                                                    <label class="form-check-label" for="enableXFrameOptions">X-Frame-Options</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enableXSSProtection" id="enableXSSProtection" checked>
                                                    <label class="form-check-label" for="enableXSSProtection">X-XSS-Protection</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enableReferrerPolicy" id="enableReferrerPolicy" checked>
                                                    <label class="form-check-label" for="enableReferrerPolicy">Referrer-Policy</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enablePermissionsPolicy" id="enablePermissionsPolicy" checked>
                                                    <label class="form-check-label" for="enablePermissionsPolicy">Permissions-Policy</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="resetBtn">Reset to Defaults</button>
                        <button type="submit" class="btn btn-primary">Save Security Settings</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
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
        const sidebarToggle = document.getElementById('sidebarToggle');
        const floatingHamburger = document.getElementById('floatingHamburger');
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        function closeSidebar() {
            adminSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) floatingHamburger.style.display = 'flex';
        }
        function openSidebar() {
            adminSidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            if (floatingHamburger) floatingHamburger.style.display = 'none';
        }
        if (sidebarToggle) sidebarToggle.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); closeSidebar(); });
        if (floatingHamburger) floatingHamburger.addEventListener('click', function(e) { e.preventDefault(); openSidebar(); });
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', function() { closeSidebar(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && adminSidebar.classList.contains('active')) closeSidebar(); });
        const sidebarUserDropdown = document.getElementById('sidebarUserDropdown');
        const sidebarUserMenu = document.getElementById('sidebarUserMenu');
        if (sidebarUserDropdown && sidebarUserMenu) {
            const userHeader = sidebarUserDropdown.querySelector('.sidebar-user-header');
            if (userHeader) userHeader.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); sidebarUserDropdown.classList.toggle('active'); });
            document.addEventListener('click', function(e) { if (!sidebarUserDropdown.contains(e.target)) sidebarUserDropdown.classList.remove('active'); });
        }
        document.getElementById('securitySettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
            btn.disabled = true;
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Security settings saved successfully!');
            }, 1500);
        });
        document.getElementById('resetBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to reset all security settings to defaults?')) {
                document.getElementById('securitySettingsForm').reset();
                alert('Settings reset to defaults!');
            }
        });
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

