<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers - Organization Panel</title>
    
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
    
    <style>
        /* Organization Branding Support */
        .org-branding-logo {
            height: 70px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="orgSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo" id="orgLogoLink">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Organization Logo" class="org-branding-logo" id="orgLogo">
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
                        <a href="../dashboard.php" class="nav-link">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../analytics.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="teacher_list.php" class="nav-link active">
                            <i class="bi bi-people"></i>
                            <span>Teachers</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../quizzes/quiz_list.php" class="nav-link">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Examinations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../students/student_list.php" class="nav-link">
                            <i class="bi bi-mortarboard"></i>
                            <span>Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../notifications/send_notification.php" class="nav-link">
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
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="../settings.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
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
                        <h1 class="topbar-title">Teachers</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Teachers</li>
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
                <!-- Filters and Actions -->
                <div class="content-card mb-4">
                    <div class="content-card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="admin-form-label">Search</label>
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" id="teacherSearch" placeholder="Search by name or email...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="admin-form-label">Status</label>
                                <select class="admin-form-control" id="statusFilter">
                                    <option value="all">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="admin-form-label">Sort By</label>
                                <select class="admin-form-control" id="sortFilter">
                                    <option value="name">Name</option>
                                    <option value="students">Total Students</option>
                                    <option value="examinations">Total Examinations</option>
                                    <option value="recent">Recently Added</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="admin-form-label">Actions</label>
                                <div class="d-flex gap-2 align-items-end">
                                    <button class="btn btn-primary" id="addTeacherBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-plus-lg"></i> Add Teacher
                                    </button>
                                    <button class="btn btn-outline-secondary" id="exportTeachersBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                    <button class="btn btn-outline-danger" id="clearFiltersBtn" style="white-space: nowrap; flex: 1; min-width: 0; height: 42px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-x-circle"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teachers Table -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">All Teachers</h2>
                        <span class="badge badge-primary" id="teacherCount" style="font-size: 1.5rem; padding: 0.6rem 1.2rem; font-weight: 700;">24 Teachers</span>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Teacher Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Total Students</th>
                                        <th>Total Examinations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="teacherTableBody">
                                    <tr>
                                        <td><strong>1</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">JD</div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">John Doe</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#TCH001</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>john.doe@example.com</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><strong>320</strong></td>
                                        <td><strong>45</strong></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="1">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="1">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="1">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>2</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">SS</div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">Sarah Smith</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#TCH002</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>sarah.smith@example.com</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><strong>285</strong></td>
                                        <td><strong>38</strong></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="2">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="2">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="2">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>3</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">MJ</div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">Mike Johnson</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#TCH003</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>mike.johnson@example.com</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><strong>298</strong></td>
                                        <td><strong>42</strong></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="3">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="3">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="3">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>4</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">EW</div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">Emma Wilson</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#TCH004</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>emma.wilson@example.com</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td><strong>265</strong></td>
                                        <td><strong>35</strong></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="4">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="4">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="4">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>5</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="sidebar-user-avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">DB</div>
                                                <div>
                                                    <h6 style="margin: 0; color: var(--text-primary);">David Brown</h6>
                                                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-secondary);">#TCH005</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>david.brown@example.com</td>
                                        <td><span class="badge badge-warning">Inactive</span></td>
                                        <td><strong>240</strong></td>
                                        <td><strong>28</strong></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn view" title="View" data-id="5">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="action-btn edit" title="Edit" data-id="5">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="action-btn delete" title="Delete" data-id="5">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
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

        // Table Search
        const teacherSearch = document.getElementById('teacherSearch');
        const teacherTableBody = document.getElementById('teacherTableBody');

        if (teacherSearch) {
            teacherSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = teacherTableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
                
                updateTeacherCount();
            });
        }

        // Status Filter
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                const filterValue = this.value.toLowerCase();
                const rows = teacherTableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    if (filterValue === 'all') {
                        row.style.display = '';
                    } else {
                        const badges = row.querySelectorAll('.badge');
                        let found = false;
                        badges.forEach(badge => {
                            if (badge.textContent.toLowerCase() === filterValue) {
                                found = true;
                            }
                        });
                        row.style.display = found ? '' : 'none';
                    }
                });
                
                updateTeacherCount();
            });
        }

        // Sort Filter
        const sortFilter = document.getElementById('sortFilter');
        if (sortFilter) {
            sortFilter.addEventListener('change', function() {
                const sortBy = this.value;
                // TODO: Implement sorting logic
                console.log('Sorting by:', sortBy);
            });
        }

        // Update Teacher Count
        function updateTeacherCount() {
            const visibleRows = Array.from(teacherTableBody.getElementsByTagName('tr')).filter(row => row.style.display !== 'none');
            const teacherCount = document.getElementById('teacherCount');
            if (teacherCount) {
                teacherCount.textContent = `${visibleRows.length} Teachers`;
            }
        }

        // Add Teacher Button
        const addTeacherBtn = document.getElementById('addTeacherBtn');
        if (addTeacherBtn) {
            addTeacherBtn.addEventListener('click', function() {
                window.location.href = 'create_teacher.php';
            });
        }

        // Export Teachers Button
        const exportTeachersBtn = document.getElementById('exportTeachersBtn');
        if (exportTeachersBtn) {
            exportTeachersBtn.addEventListener('click', function() {
                // TODO: Replace with actual export functionality
                console.log('Exporting teachers...');
                alert('Export feature will be available after backend integration.');
            });
        }

        // Clear Filters Button
        const clearFiltersBtn = document.getElementById('clearFiltersBtn');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                if (teacherSearch) teacherSearch.value = '';
                if (statusFilter) statusFilter.value = 'all';
                if (sortFilter) sortFilter.value = 'name';
                
                // Show all rows
                const rows = teacherTableBody.getElementsByTagName('tr');
                Array.from(rows).forEach(row => {
                    row.style.display = '';
                });
                
                updateTeacherCount();
            });
        }

        // Action Buttons Functionality
        // View Button
        document.querySelectorAll('.action-btn.view').forEach(btn => {
            btn.addEventListener('click', function() {
                const teacherId = this.getAttribute('data-id');
                window.location.href = `view_teacher.php?id=${teacherId}`;
            });
        });

        // Edit Button
        document.querySelectorAll('.action-btn.edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const teacherId = this.getAttribute('data-id');
                window.location.href = `edit_teacher.php?id=${teacherId}`;
            });
        });

        // Delete Button
        document.querySelectorAll('.action-btn.delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const teacherId = this.getAttribute('data-id');
                const row = this.closest('tr');
                const teacherName = row.querySelector('h6')?.textContent || 'this teacher';
                
                if (confirm(`Are you sure you want to delete ${teacherName}? This will also affect all associated examinations and students. This action cannot be undone.`)) {
                    deleteTeacher(teacherId);
                }
            });
        });

        // Delete Teacher Function (Backend Ready)
        async function deleteTeacher(teacherId) {
            try {
                // TODO: Replace with actual API endpoint when backend is ready
                // const response = await fetch(`../../api/organization/teachers/delete.php`, {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ id: teacherId })
                // });
                
                // For now, simulate API call
                const response = { ok: true };
                
                if (response.ok) {
                    alert('Teacher deleted successfully!');
                    location.reload(); // Reload page to reflect changes
                } else {
                    alert('Failed to delete teacher. Please try again.');
                }
            } catch (error) {
                console.error('Error deleting teacher:', error);
                alert('An error occurred while deleting the teacher.');
            }
        }
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

