<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification - Organization Panel</title>
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
    <style>
        .org-branding-logo { height: 70px; width: auto; max-width: 160px; object-fit: contain; flex-shrink: 0; }
        
        .notification-type-card {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--bg-primary);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            min-height: 200px;
        }
        
        .notification-type-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .notification-type-card.active {
            border-color: var(--primary-color);
            background: var(--primary-light);
            box-shadow: var(--shadow-md);
        }
        
        .notification-type-card.active::before {
            content: '';
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            width: 24px;
            height: 24px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .notification-type-card.active::after {
            content: '✓';
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            width: 24px;
            height: 24px;
            color: white;
            font-size: 0.875rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }
        
        .notification-type-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .notification-type-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .notification-type-desc {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin: 0;
            flex: 1;
        }
        
        .recipient-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary-light);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0.25rem;
        }
        
        .recipient-tag .remove-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            padding: 0;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .recipient-tag .remove-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .message-preview {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            min-height: 200px;
            margin-top: 1rem;
        }
        
        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .priority-high {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .priority-medium {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .priority-low {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="orgSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo" id="orgLogoLink">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Organization Logo" class="org-branding-logo" id="orgLogo">
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
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="../analytics.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Analytics</span></a></li>
                </ul>
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../teachers/teacher_list.php" class="nav-link"><i class="bi bi-people"></i><span>Teachers</span></a></li>
                    <li class="nav-item"><a href="../quizzes/quiz_list.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Examinations</span></a></li>
                    <li class="nav-item"><a href="../students/student_list.php" class="nav-link"><i class="bi bi-mortarboard"></i><span>Students</span></a></li>
                    <li class="nav-item"><a href="send_notification.php" class="nav-link active"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
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
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-person"></i><span>Profile</span></a>
                        <a href="../settings.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-gear"></i><span>Settings</span></a>
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
                        <h1 class="topbar-title">Send Notification</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Send Notification</li>
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
                <!-- Notification Type Selection -->
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Select Notification Type</h2>
                    </div>
                    <div class="content-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="notification-type-card" data-type="notification">
                                    <div class="notification-type-icon">
                                        <i class="bi bi-bell-fill"></i>
                                    </div>
                                    <h3 class="notification-type-title">In-App Notification</h3>
                                    <p class="notification-type-desc">Send real-time notifications that appear in the app dashboard</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="notification-type-card active" data-type="announcement">
                                    <div class="notification-type-icon">
                                        <i class="bi bi-megaphone-fill"></i>
                                    </div>
                                    <h3 class="notification-type-title">Announcement</h3>
                                    <p class="notification-type-desc">Post important announcements visible to all users</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="notification-type-card" data-type="email">
                                    <div class="notification-type-icon">
                                        <i class="bi bi-envelope-fill"></i>
                                    </div>
                                    <h3 class="notification-type-title">Email</h3>
                                    <p class="notification-type-desc">Send email notifications directly to recipients' inbox</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Form -->
                <form id="notificationForm">
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Basic Information -->
                            <div class="content-card mb-4">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Notification Details</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Subject / Title <span class="required-asterisk">*</span></label>
                                        <input type="text" class="admin-form-control" id="notificationSubject" placeholder="Enter notification subject" required>
                                    </div>
                                    
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Message <span class="required-asterisk">*</span></label>
                                        <textarea class="admin-form-control" id="notificationMessage" rows="8" placeholder="Enter your message here..." required></textarea>
                                        <small class="form-text text-muted">You can use HTML formatting for rich text messages</small>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Priority</label>
                                                <select class="admin-form-control" id="notificationPriority">
                                                    <option value="low">Low</option>
                                                    <option value="medium" selected>Medium</option>
                                                    <option value="high">High</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="admin-form-group">
                                                <label class="admin-form-label">Category</label>
                                                <select class="admin-form-control" id="notificationCategory">
                                                    <option value="general">General</option>
                                                    <option value="examination">Examination Related</option>
                                                    <option value="system">System Update</option>
                                                    <option value="event">Event</option>
                                                    <option value="maintenance">Maintenance</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recipients -->
                            <div class="content-card mb-4">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Recipients</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Select Recipients <span class="required-asterisk">*</span></label>
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="recipientAll" value="all">
                                                    <label class="form-check-label" for="recipientAll">
                                                        <strong>All Users</strong>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="recipientTeachers" value="teachers">
                                                    <label class="form-check-label" for="recipientTeachers">
                                                        <strong>All Teachers</strong>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="recipientStudents" value="students">
                                                    <label class="form-check-label" for="recipientStudents">
                                                        <strong>All Students</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="admin-form-group">
                                            <label class="admin-form-label">Or Select Specific Recipients</label>
                                            <select class="admin-form-control" id="specificRecipients" multiple style="min-height: 150px;">
                                                <optgroup label="Teachers">
                                                    <option value="teacher_1">John Doe</option>
                                                    <option value="teacher_2">Sarah Smith</option>
                                                    <option value="teacher_3">Mike Johnson</option>
                                                    <option value="teacher_4">Emma Wilson</option>
                                                </optgroup>
                                                <optgroup label="Students">
                                                    <option value="student_1">Alice Brown</option>
                                                    <option value="student_2">Bob Davis</option>
                                                    <option value="student_3">Charlie Miller</option>
                                                </optgroup>
                                            </select>
                                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple recipients</small>
                                        </div>
                                        
                                        <div id="selectedRecipients" class="mt-3">
                                            <label class="admin-form-label">Selected Recipients:</label>
                                            <div id="recipientTags" class="d-flex flex-wrap gap-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Scheduling (for Email) -->
                            <div class="content-card mb-4" id="schedulingSection" style="display: none;">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Scheduling</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="admin-form-group">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="scheduleNotification">
                                            <label class="form-check-label" for="scheduleNotification">
                                                Schedule for later
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div id="scheduleOptions" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Date</label>
                                                    <input type="date" class="admin-form-control" id="scheduleDate">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="admin-form-group">
                                                    <label class="admin-form-label">Time</label>
                                                    <input type="time" class="admin-form-control" id="scheduleTime">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview & Actions -->
                        <div class="col-lg-4">
                            <div class="content-card mb-4">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Preview</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="message-preview" id="messagePreview">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 style="margin: 0; color: var(--text-primary);" id="previewSubject">Notification Subject</h5>
                                                <span class="priority-badge priority-medium" id="previewPriority">Medium Priority</span>
                                            </div>
                                            <span class="badge badge-info" id="previewType">Announcement</span>
                                        </div>
                                        <p style="color: var(--text-secondary); margin: 0; white-space: pre-wrap;" id="previewMessage">Your message will appear here...</p>
                                    </div>
                                </div>
                            </div>

                            <div class="content-card">
                                <div class="content-card-header">
                                    <h2 class="content-card-title">Actions</h2>
                                </div>
                                <div class="content-card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary" id="sendNotificationBtn">
                                            <i class="bi bi-send"></i> Send Notification
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn">
                                            <i class="bi bi-file-earmark"></i> Save as Draft
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" id="clearFormBtn">
                                            <i class="bi bi-x-circle"></i> Clear Form
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> 
                                            <span id="recipientCount">0 recipients</span> will receive this notification
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
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

        // Notification Type Selection
        const notificationTypeCards = document.querySelectorAll('.notification-type-card');
        let selectedType = 'announcement';

        notificationTypeCards.forEach(card => {
            card.addEventListener('click', function() {
                notificationTypeCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                selectedType = this.getAttribute('data-type');
                
                // Update preview
                const previewType = document.getElementById('previewType');
                if (previewType) {
                    previewType.textContent = selectedType.charAt(0).toUpperCase() + selectedType.slice(1);
                }
                
                // Show/hide scheduling for email
                const schedulingSection = document.getElementById('schedulingSection');
                if (schedulingSection) {
                    schedulingSection.style.display = selectedType === 'email' ? 'block' : 'none';
                }
            });
        });

        // Schedule Toggle
        const scheduleNotification = document.getElementById('scheduleNotification');
        const scheduleOptions = document.getElementById('scheduleOptions');
        
        if (scheduleNotification && scheduleOptions) {
            scheduleNotification.addEventListener('change', function() {
                scheduleOptions.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Recipient Selection
        const recipientCheckboxes = document.querySelectorAll('input[type="checkbox"][id^="recipient"]');
        const specificRecipients = document.getElementById('specificRecipients');
        const recipientTags = document.getElementById('recipientTags');
        let selectedRecipients = [];

        function updateRecipientTags() {
            if (!recipientTags) return;
            
            recipientTags.innerHTML = '';
            selectedRecipients.forEach(recipient => {
                const tag = document.createElement('span');
                tag.className = 'recipient-tag';
                tag.innerHTML = `
                    ${recipient.name}
                    <button type="button" class="remove-btn" data-id="${recipient.id}">
                        <i class="bi bi-x"></i>
                    </button>
                `;
                recipientTags.appendChild(tag);
            });
            
            updateRecipientCount();
        }

        function updateRecipientCount() {
            const recipientCount = document.getElementById('recipientCount');
            if (recipientCount) {
                recipientCount.textContent = `${selectedRecipients.length} recipient${selectedRecipients.length !== 1 ? 's' : ''}`;
            }
        }

        recipientCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.id === 'recipientAll' && this.checked) {
                    // Uncheck others when "All" is selected
                    document.getElementById('recipientTeachers').checked = false;
                    document.getElementById('recipientStudents').checked = false;
                    selectedRecipients = [{ id: 'all', name: 'All Users' }];
                } else if (this.checked) {
                    // Remove "All" if specific group is selected
                    document.getElementById('recipientAll').checked = false;
                    const existingAll = selectedRecipients.find(r => r.id === 'all');
                    if (existingAll) {
                        selectedRecipients = [];
                    }
                    
                    const recipientName = this.nextElementSibling.textContent.trim();
                    if (!selectedRecipients.find(r => r.id === this.value)) {
                        selectedRecipients.push({ id: this.value, name: recipientName });
                    }
                } else {
                    selectedRecipients = selectedRecipients.filter(r => r.id !== this.value);
                }
                updateRecipientTags();
            });
        });

        if (specificRecipients) {
            specificRecipients.addEventListener('change', function() {
                const options = Array.from(this.selectedOptions);
                options.forEach(option => {
                    if (!selectedRecipients.find(r => r.id === option.value)) {
                        selectedRecipients.push({ id: option.value, name: option.text });
                    }
                });
                updateRecipientTags();
            });
        }

        // Remove recipient tag
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-btn')) {
                const btn = e.target.closest('.remove-btn');
                const recipientId = btn.getAttribute('data-id');
                selectedRecipients = selectedRecipients.filter(r => r.id !== recipientId);
                
                // Uncheck corresponding checkbox
                const checkbox = document.querySelector(`input[value="${recipientId}"]`);
                if (checkbox) checkbox.checked = false;
                
                // Uncheck specific recipient option
                if (specificRecipients) {
                    const option = specificRecipients.querySelector(`option[value="${recipientId}"]`);
                    if (option) option.selected = false;
                }
                
                updateRecipientTags();
            }
        });

        // Live Preview
        const notificationSubject = document.getElementById('notificationSubject');
        const notificationMessage = document.getElementById('notificationMessage');
        const notificationPriority = document.getElementById('notificationPriority');
        const previewSubject = document.getElementById('previewSubject');
        const previewMessage = document.getElementById('previewMessage');
        const previewPriority = document.getElementById('previewPriority');

        if (notificationSubject && previewSubject) {
            notificationSubject.addEventListener('input', function() {
                previewSubject.textContent = this.value || 'Notification Subject';
            });
        }

        if (notificationMessage && previewMessage) {
            notificationMessage.addEventListener('input', function() {
                previewMessage.textContent = this.value || 'Your message will appear here...';
            });
        }

        if (notificationPriority && previewPriority) {
            notificationPriority.addEventListener('change', function() {
                const priority = this.value;
                previewPriority.className = `priority-badge priority-${priority}`;
                previewPriority.textContent = `${priority.charAt(0).toUpperCase() + priority.slice(1)} Priority`;
            });
        }

        // Form Submission
        const notificationForm = document.getElementById('notificationForm');
        const sendNotificationBtn = document.getElementById('sendNotificationBtn');

        if (notificationForm) {
            notificationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (selectedRecipients.length === 0) {
                    alert('Please select at least one recipient.');
                    return;
                }
                
                const formData = {
                    type: selectedType,
                    subject: notificationSubject ? notificationSubject.value : '',
                    message: notificationMessage ? notificationMessage.value : '',
                    priority: notificationPriority ? notificationPriority.value : 'medium',
                    category: document.getElementById('notificationCategory') ? document.getElementById('notificationCategory').value : 'general',
                    recipients: selectedRecipients.map(r => r.id),
                    scheduled: scheduleNotification && scheduleNotification.checked,
                    scheduleDate: document.getElementById('scheduleDate') ? document.getElementById('scheduleDate').value : '',
                    scheduleTime: document.getElementById('scheduleTime') ? document.getElementById('scheduleTime').value : ''
                };
                
                // Disable button and show loading
                if (sendNotificationBtn) {
                    sendNotificationBtn.disabled = true;
                    sendNotificationBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
                }
                
                // TODO: Replace with actual API call
                setTimeout(() => {
                    console.log('Notification data:', formData);
                    alert(`Notification sent successfully to ${selectedRecipients.length} recipient(s)!`);
                    
                    // Reset form
                    notificationForm.reset();
                    selectedRecipients = [];
                    updateRecipientTags();
                    notificationTypeCards[1].click(); // Reset to announcement
                    
                    // Re-enable button
                    if (sendNotificationBtn) {
                        sendNotificationBtn.disabled = false;
                        sendNotificationBtn.innerHTML = '<i class="bi bi-send"></i> Send Notification';
                    }
                }, 1500);
            });
        }

        // Save Draft
        const saveDraftBtn = document.getElementById('saveDraftBtn');
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', function() {
                const draftData = {
                    type: selectedType,
                    subject: notificationSubject ? notificationSubject.value : '',
                    message: notificationMessage ? notificationMessage.value : '',
                    priority: notificationPriority ? notificationPriority.value : 'medium',
                    category: document.getElementById('notificationCategory') ? document.getElementById('notificationCategory').value : 'general',
                    recipients: selectedRecipients.map(r => r.id)
                };
                
                localStorage.setItem('notificationDraft', JSON.stringify(draftData));
                alert('Draft saved successfully!');
            });
        }

        // Clear Form
        const clearFormBtn = document.getElementById('clearFormBtn');
        if (clearFormBtn) {
            clearFormBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear the form? All unsaved data will be lost.')) {
                    notificationForm.reset();
                    selectedRecipients = [];
                    updateRecipientTags();
                    notificationTypeCards[1].click(); // Reset to announcement
                }
            });
        }

        // Load draft on page load
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('notificationDraft');
            if (draft) {
                const draftData = JSON.parse(draft);
                if (confirm('You have a saved draft. Would you like to load it?')) {
                    if (notificationSubject) notificationSubject.value = draftData.subject || '';
                    if (notificationMessage) notificationMessage.value = draftData.message || '';
                    if (notificationPriority) notificationPriority.value = draftData.priority || 'medium';
                    const categorySelect = document.getElementById('notificationCategory');
                    if (categorySelect) categorySelect.value = draftData.category || 'general';
                    
                    // Trigger preview updates
                    if (notificationSubject) notificationSubject.dispatchEvent(new Event('input'));
                    if (notificationMessage) notificationMessage.dispatchEvent(new Event('input'));
                    if (notificationPriority) notificationPriority.dispatchEvent(new Event('change'));
                }
            }
        });
    </script>
    <!-- Activity Tracker - Session Inactivity Timeout (15 minutes) -->
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

