<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notifications - Teacher Panel</title>
    
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
        .teacher-branding-logo { height: 70px; width: auto; max-width: 160px; object-fit: contain; flex-shrink: 0; }
        .notification-page-header { margin-bottom: 2rem; }
        .notification-filters { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .filter-btn { padding: 0.5rem 1.25rem; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary); border-radius: 8px; cursor: pointer; transition: all 0.2s ease; }
        .filter-btn:hover { background: var(--primary-light); border-color: var(--primary-color); }
        .filter-btn.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .notification-card { padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 1.5rem; transition: all 0.2s ease; background: var(--bg-primary); }
        .notification-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-2px); }
        .notification-card.unread { border-left: 4px solid var(--primary-color); background: var(--primary-light, rgba(13, 110, 253, 0.05)); }
        .notification-header { display: flex; align-items: start; gap: 1rem; margin-bottom: 0.75rem; }
        .notification-icon-large { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.25rem; }
        .notification-content { flex: 1; min-width: 0; }
        .notification-title { margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary); }
        .notification-message { margin: 0 0 0.75rem 0; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6; }
        .notification-meta { display: flex; align-items: center; gap: 1rem; font-size: 0.85rem; color: var(--text-muted); }
        .notification-actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .mark-read-btn, .delete-btn { padding: 0.4rem 0.8rem; border: none; border-radius: 6px; font-size: 0.85rem; cursor: pointer; transition: all 0.2s ease; }
        .mark-read-btn { background: var(--primary-color); color: white; }
        .mark-read-btn:hover { opacity: 0.9; }
        .delete-btn { background: var(--danger-color, #dc3545); color: white; }
        .delete-btn:hover { opacity: 0.9; }
        .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.5; }
    </style>
</head>
<body class="loading">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="teacherSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo" id="teacherLogoLink">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Teacher Logo" class="teacher-branding-logo" id="teacherLogo">
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
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="../dashboard.php" class="nav-link">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../analytics/performance.php" class="nav-link">
                            <i class="bi bi-graph-up"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item">
                        <a href="../classes/class_list.php" class="nav-link">
                            <i class="bi bi-journal-bookmark"></i>
                            <span>Departments & Sections</span>
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
                        <a href="../results/quiz_results.php" class="nav-link">
                            <i class="bi bi-clipboard-data"></i>
                            <span>Department Results</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="send_notification.php" class="nav-link">
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
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
                            <i class="bi bi-person"></i>
                            <span>Profile</span>
                        </a>
                        <a href="../../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;">
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
                        <h1 class="topbar-title">All Notifications</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="send_notification.php">Notifications</a></li>
                                <li class="breadcrumb-item active">All Messages</li>
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
                <div class="content-card" style="padding: 2rem;">
                    <!-- Filters -->
                    <div class="notification-filters" style="margin-bottom: 2rem;">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="unread">Unread</button>
                        <button class="filter-btn" data-filter="read">Read</button>
                        <button class="filter-btn" data-filter="announcement">Announcements</button>
                        <button class="filter-btn" data-filter="results">Results</button>
                    </div>

                    <!-- Notifications List -->
                    <div id="notificationsContainer" style="margin-top: 1.5rem;">
                        <!-- Sample Notifications -->
                        <div class="notification-card unread" data-type="announcement">
                            <div class="notification-header">
                                <div class="notification-icon-large" style="background: var(--primary-color); color: white;">
                                    <i class="bi bi-megaphone"></i>
                                </div>
                                <div class="notification-content">
                                    <h3 class="notification-title">New Examination Schedule</h3>
                                    <p class="notification-message">The Data Structures Midterm examination has been scheduled for next week. Please review the schedule and prepare your students accordingly. The examination will cover chapters 1-5 and will be conducted in the main hall.</p>
                                    <div class="notification-meta">
                                        <span><i class="bi bi-clock"></i> 2 hours ago</span>
                                        <span><i class="bi bi-tag"></i> Announcement</span>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-actions">
                                <button class="mark-read-btn" onclick="markAsRead(this)"><i class="bi bi-check-circle"></i> Mark as Read</button>
                                <button class="delete-btn" onclick="deleteNotification(this)"><i class="bi bi-trash"></i> Delete</button>
                            </div>
                        </div>

                        <div class="notification-card unread" data-type="results">
                            <div class="notification-header">
                                <div class="notification-icon-large" style="background: var(--success-color, #198754); color: white;">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="notification-content">
                                    <h3 class="notification-title">Results Published</h3>
                                    <p class="notification-message">Database Systems Assignment results are now available for review. You can access the results from the Department Results section. Overall performance: 85% average score.</p>
                                    <div class="notification-meta">
                                        <span><i class="bi bi-clock"></i> 5 hours ago</span>
                                        <span><i class="bi bi-tag"></i> Results</span>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-actions">
                                <button class="mark-read-btn" onclick="markAsRead(this)"><i class="bi bi-check-circle"></i> Mark as Read</button>
                                <button class="delete-btn" onclick="deleteNotification(this)"><i class="bi bi-trash"></i> Delete</button>
                            </div>
                        </div>

                        <div class="notification-card" data-type="announcement">
                            <div class="notification-header">
                                <div class="notification-icon-large" style="background: var(--info-color, #0dcaf0); color: white;">
                                    <i class="bi bi-info-circle"></i>
                                </div>
                                <div class="notification-content">
                                    <h3 class="notification-title">System Update</h3>
                                    <p class="notification-message">New features have been added to the examination system. You can now use AI-powered evaluation for subjective questions and send results directly via email to students.</p>
                                    <div class="notification-meta">
                                        <span><i class="bi bi-clock"></i> 1 day ago</span>
                                        <span><i class="bi bi-tag"></i> Announcement</span>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-actions">
                                <button class="mark-read-btn" onclick="markAsRead(this)" style="display: none;"><i class="bi bi-check-circle"></i> Mark as Read</button>
                                <button class="delete-btn" onclick="deleteNotification(this)"><i class="bi bi-trash"></i> Delete</button>
                            </div>
                        </div>

                        <div class="notification-card" data-type="announcement">
                            <div class="notification-header">
                                <div class="notification-icon-large" style="background: var(--warning-color, #ffc107); color: white;">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div class="notification-content">
                                    <h3 class="notification-title">Examination Reminder</h3>
                                    <p class="notification-message">Reminder: Computer Networks Quiz is scheduled for tomorrow at 10:00 AM. Please ensure all students are aware of the examination schedule.</p>
                                    <div class="notification-meta">
                                        <span><i class="bi bi-clock"></i> 2 days ago</span>
                                        <span><i class="bi bi-tag"></i> Announcement</span>
                                    </div>
                                </div>
                            </div>
                            <div class="notification-actions">
                                <button class="mark-read-btn" onclick="markAsRead(this)" style="display: none;"><i class="bi bi-check-circle"></i> Mark as Read</button>
                                <button class="delete-btn" onclick="deleteNotification(this)"><i class="bi bi-trash"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
    <script>
        // Theme Management
        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        if (themeToggle) {
            let isToggling = false;
            themeToggle.addEventListener('mousedown', e => e.preventDefault());
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
                setTimeout(() => { isToggling = false; }, 300);
            });
        }

        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const floatingHamburger = document.getElementById('floatingHamburger');
        const teacherSidebar = document.getElementById('teacherSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        function closeSidebar() {
            teacherSidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            if (floatingHamburger) floatingHamburger.style.display = 'flex';
        }
        
        function openSidebar() {
            teacherSidebar.classList.add('active');
            sidebarOverlay.classList.add('active');
            if (floatingHamburger) floatingHamburger.style.display = 'none';
        }
        
        if (sidebarToggle) sidebarToggle.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); closeSidebar(); });
        if (floatingHamburger) floatingHamburger.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); openSidebar(); });
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && teacherSidebar.classList.contains('active')) closeSidebar(); });

        // User Dropdown
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
            document.addEventListener('click', e => { if (!sidebarUserDropdown.contains(e.target)) sidebarUserDropdown.classList.remove('active'); });
        }

        // Filter Functionality
        const filterBtns = document.querySelectorAll('.filter-btn');
        const notificationCards = document.querySelectorAll('.notification-card');
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                notificationCards.forEach(card => {
                    if (filter === 'all') {
                        card.style.display = 'block';
                    } else if (filter === 'unread') {
                        card.style.display = card.classList.contains('unread') ? 'block' : 'none';
                    } else if (filter === 'read') {
                        card.style.display = card.classList.contains('unread') ? 'none' : 'block';
                    } else {
                        card.style.display = card.getAttribute('data-type') === filter ? 'block' : 'none';
                    }
                });
            });
        });

        // Mark as Read
        function markAsRead(btn) {
            const card = btn.closest('.notification-card');
            card.classList.remove('unread');
            btn.style.display = 'none';
            // TODO: Update in backend
        }

        // Delete Notification
        function deleteNotification(btn) {
            if (confirm('Are you sure you want to delete this notification?')) {
                const card = btn.closest('.notification-card');
                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    // Check if no notifications left
                    if (document.querySelectorAll('.notification-card').length === 0) {
                        document.getElementById('notificationsContainer').innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-bell-slash"></i>
                                <h3>No Notifications</h3>
                                <p>You're all caught up! No notifications to display.</p>
                            </div>
                        `;
                    }
                }, 300);
                // TODO: Delete from backend
            }
        }
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

