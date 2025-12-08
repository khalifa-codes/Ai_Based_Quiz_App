<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Organization - Admin Panel</title>
    
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
                        <a href="organization_list.php" class="nav-link active">
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
                        <h1 class="topbar-title">Edit Organization</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="organization_list.php">Organizations</a></li>
                                <li class="breadcrumb-item active">Edit</li>
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
                                <h2 class="content-card-title">Organization Details</h2>
                            </div>
                            <div class="content-card-body">
                                <?php
                                // TODO: Replace with actual database query when backend is ready
                                // $orgId = $_GET['id'] ?? '';
                                // $org = getOrganizationById($orgId);
                                
                                // Dummy data for now
                                $orgId = $_GET['id'] ?? 'ORG001';
                                $org = [
                                    'id' => $orgId,
                                    'name' => 'ABC Learning Center',
                                    'email' => 'contact@abclearning.com',
                                    'phone' => '+1 (555) 123-4567',
                                    'website' => 'https://www.abclearning.com',
                                    'address' => '123 Main Street',
                                    'city' => 'New York',
                                    'state' => 'NY',
                                    'zip' => '10001',
                                    'status' => 'active',
                                    'plan' => 'premium',
                                    'maxTeachers' => '50',
                                    'maxStudents' => '500',
                                    'maxQuizzes' => '100',
                                    'description' => 'A leading educational institution providing quality learning experiences.'
                                ];
                                ?>
                                
                                <form id="editOrganizationForm" method="POST">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($org['id']); ?>">
                                    
                                    <!-- Basic Information -->
                                    <h5 class="mb-3" style="color: var(--text-primary); font-weight: 600;">Basic Information</h5>
                                    
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Organization Name <span class="required-asterisk">*</span></label>
                                        <input type="text" name="organizationName" class="admin-form-control" placeholder="Enter organization name" value="<?php echo htmlspecialchars($org['name']); ?>" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Contact Email <span class="required-asterisk">*</span></label>
                                                <input type="email" name="email" class="admin-form-control" placeholder="contact@organization.com" value="<?php echo htmlspecialchars($org['email']); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Phone Number</label>
                                                <input type="tel" name="phone" class="admin-form-control" placeholder="+1 (555) 123-4567" value="<?php echo htmlspecialchars($org['phone']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Website</label>
                                        <input type="url" name="website" class="admin-form-control" placeholder="https://www.organization.com" value="<?php echo htmlspecialchars($org['website']); ?>">
                                    </div>

                                    <!-- Address -->
                                    <h5 class="mb-3 mt-4" style="color: var(--text-primary); font-weight: 600;">Address</h5>

                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Street Address</label>
                                        <input type="text" name="address" class="admin-form-control" placeholder="123 Main Street" value="<?php echo htmlspecialchars($org['address']); ?>">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">City</label>
                                                <input type="text" name="city" class="admin-form-control" placeholder="City" value="<?php echo htmlspecialchars($org['city']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">State</label>
                                                <input type="text" name="state" class="admin-form-control" placeholder="State" value="<?php echo htmlspecialchars($org['state']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Zip Code</label>
                                                <input type="text" name="zip" class="admin-form-control" placeholder="12345" value="<?php echo htmlspecialchars($org['zip']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Subscription -->
                                    <h5 class="mb-3 mt-4" style="color: var(--text-primary); font-weight: 600;">Subscription Plan</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Plan <span class="required-asterisk">*</span></label>
                                                <select name="plan" class="admin-form-control" required>
                                                    <option value="">Select a plan</option>
                                                    <option value="free" <?php echo $org['plan'] === 'free' ? 'selected' : ''; ?>>Free Plan</option>
                                                    <option value="basic" <?php echo $org['plan'] === 'basic' ? 'selected' : ''; ?>>Basic Plan - $49/month</option>
                                                    <option value="premium" <?php echo $org['plan'] === 'premium' ? 'selected' : ''; ?>>Premium Plan - $99/month</option>
                                                    <option value="enterprise" <?php echo $org['plan'] === 'enterprise' ? 'selected' : ''; ?>>Enterprise Plan - Custom</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Status <span class="required-asterisk">*</span></label>
                                                <select name="status" class="admin-form-control" required>
                                                    <option value="">Select status</option>
                                                    <option value="active" <?php echo $org['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $org['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    <option value="trial" <?php echo $org['status'] === 'trial' ? 'selected' : ''; ?>>Trial</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Limits -->
                                    <h5 class="mb-3 mt-4" style="color: var(--text-primary); font-weight: 600;">Account Limits</h5>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Max Teachers</label>
                                                <input type="number" name="maxTeachers" class="admin-form-control" placeholder="50" value="<?php echo htmlspecialchars($org['maxTeachers']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Max Students</label>
                                                <input type="number" name="maxStudents" class="admin-form-control" placeholder="500" value="<?php echo htmlspecialchars($org['maxStudents']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Max Quizzes/Month</label>
                                                <input type="number" name="maxQuizzes" class="admin-form-control" placeholder="100" value="<?php echo htmlspecialchars($org['maxQuizzes']); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Description</label>
                                        <textarea name="description" class="admin-form-control" rows="4" placeholder="Organization description and details..."><?php echo htmlspecialchars($org['description']); ?></textarea>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg"></i> Update Organization
                                        </button>
                                        <a href="view_organization.php?id=<?php echo htmlspecialchars($org['id']); ?>" class="btn btn-outline-secondary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="organization_list.php" class="btn btn-outline-secondary">
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

        // Form Submission
        const editOrganizationForm = document.getElementById('editOrganizationForm');
        
        editOrganizationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Validate email
            if (!data.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            // Validate organization name
            if (!data.organizationName || data.organizationName.trim().length < 3) {
                alert('Organization name must be at least 3 characters');
                return;
            }
            
            try {
                // TODO: Replace with actual API endpoint when backend is ready
                // const response = await fetch('../api/organizations/update.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify(data)
                // });
                
                // const result = await response.json();
                
                // For now, simulate API call
                const result = { success: true };
                
                if (result.success) {
                    alert('Organization updated successfully!');
                    window.location.href = 'view_organization.php?id=' + data.id;
                } else {
                    alert('Failed to update organization. Please try again.');
                }
            } catch (error) {
                console.error('Error updating organization:', error);
                alert('An error occurred while updating the organization.');
            }
        });
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

