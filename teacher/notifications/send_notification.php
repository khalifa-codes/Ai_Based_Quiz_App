<?php require_once '../auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification - Teacher Panel</title>
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
        .teacher-branding-logo { height: 70px; width: auto; max-width: 160px; object-fit: contain; flex-shrink: 0; }
        .notification-wrapper { position: relative; }
        .notification-btn { position: relative; }
        .notification-badge { position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff); }
        .notification-dropdown { position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 40px); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden; animation: slideDown 0.3s ease; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .notification-dropdown-header { padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-secondary); }
        .notification-dropdown-body { max-height: 400px; overflow-y: auto; }
        .notification-dropdown-body::-webkit-scrollbar { width: 6px; }
        .notification-dropdown-body::-webkit-scrollbar-track { background: var(--bg-secondary); }
        .notification-dropdown-body::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 3px; }
        .notification-item { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease; }
        .notification-item:hover { background: var(--bg-secondary); }
        .notification-item.unread { background: var(--primary-light, rgba(13, 110, 253, 0.05)); }
        .notification-item.unread:hover { background: var(--primary-light, rgba(13, 110, 253, 0.1)); }
        .notification-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .notification-dropdown-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); text-align: center; background: var(--bg-secondary); }
        .view-all-link, .view-all-btn { color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: opacity 0.2s ease; }
        .view-all-link:hover, .view-all-btn:hover { opacity: 0.8; }
        @media (max-width: 768px) {
            .notification-dropdown { width: calc(100vw - 20px); right: -10px; }
        }
        .notification-type-card {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--bg-primary);
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
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="teacherSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo" id="teacherLogoLink">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Teacher Logo" class="teacher-branding-logo" id="teacherLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="teacherSubtitle">Teacher</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-x-lg"></i></button>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="../analytics/performance.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Analytics</span></a></li>
                </ul>
                <div class="nav-section-title">Management</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../classes/class_list.php" class="nav-link"><i class="bi bi-journal-bookmark"></i><span>Departments & Sections</span></a></li>
                    <li class="nav-item"><a href="../quizzes/quiz_list.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Examinations</span></a></li>
                    <li class="nav-item"><a href="../students/student_list.php" class="nav-link"><i class="bi bi-mortarboard"></i><span>Students</span></a></li>
                    <li class="nav-item"><a href="../results/quiz_results.php" class="nav-link"><i class="bi bi-clipboard-data"></i><span>Department Results</span></a></li>
                    <li class="nav-item"><a href="send_notification.php" class="nav-link active"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
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
                        <a href="../profile.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-person"></i><span>Profile</span></a>
                        <a href="../../logout.php" class="user-menu-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; color: var(--text-secondary); text-decoration: none; border-radius: 8px; transition: all 0.2s ease;"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
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
                        <!-- Notification Bell -->
                        <div class="notification-wrapper" style="position: relative;">
                            <button class="topbar-btn notification-btn" id="notificationBtn" title="Notifications" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                                <i class="bi bi-bell" style="font-size: 1.3rem !important;"></i>
                                <span class="notification-badge" id="notificationBadge" style="position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff);">3</span>
                            </button>
                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 40px); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden;">
                                <div class="notification-dropdown-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-secondary);">
                                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">Notifications</h3>
                                    <a href="view_all.php" class="view-all-link" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
                                </div>
                                <div class="notification-dropdown-body" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <div class="notification-item unread" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease; background: var(--primary-light, rgba(13, 110, 253, 0.05));">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-megaphone"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">New Examination Schedule</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">Data Structures Midterm examination has been scheduled for next week.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">2 hours ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-item unread" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease;">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--success-color, #198754); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-check-circle"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">Results Published</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">Database Systems Assignment results are now available for review.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">5 hours ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="notification-item" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s ease;">
                                        <div style="display: flex; align-items: start; gap: 0.75rem;">
                                            <div class="notification-icon" style="width: 40px; height: 40px; border-radius: 50%; background: var(--info-color, #0dcaf0); color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="bi bi-info-circle"></i>
                                            </div>
                                            <div style="flex: 1; min-width: 0;">
                                                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">System Update</h4>
                                                <p style="margin: 0 0 0.25rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">New features have been added to the examination system.</p>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">1 day ago</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="notification-dropdown-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); text-align: center; background: var(--bg-secondary);">
                                    <a href="view_all.php" class="view-all-btn" style="color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem;">Show All Messages</a>
                                </div>
                            </div>
                        </div>
                        <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 40px !important; height: 40px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="admin-content">
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Select Notification Type</h2>
                    </div>
                    <div class="content-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="notification-type-card active" data-type="announcement" id="announcementCard">
                                    <div class="notification-type-icon">
                                        <i class="bi bi-megaphone-fill"></i>
                                    </div>
                                    <h3 class="notification-type-title">Announcement</h3>
                                    <p class="notification-type-desc">Send announcements related to examinations, schedules, or important updates to your students.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="notification-type-card" data-type="results" id="resultsCard">
                                    <div class="notification-type-icon">
                                        <i class="bi bi-clipboard-data"></i>
                                    </div>
                                    <h3 class="notification-type-title">Department Results</h3>
                                    <p class="notification-type-desc">Notify students about their department results and performance feedback.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <form id="notificationForm">
                    <div class="content-card">
                        <div class="content-card-header">
                            <h2 class="content-card-title">Notification Details</h2>
                        </div>
                        <div class="content-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Subject <span class="text-danger">*</span></label>
                                        <input type="text" class="admin-form-control" id="notificationSubject" placeholder="Enter notification subject" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Related Examination</label>
                                        <select class="admin-form-control" id="relatedQuiz">
                                            <option value="">Select Examination (Optional)</option>
                                            <option value="1">Data Structures Midterm</option>
                                            <option value="2">Database Systems Assignment</option>
                                            <option value="3">Computer Networks Quiz</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Message <span class="text-danger">*</span></label>
                                        <textarea class="admin-form-control" id="notificationMessage" rows="6" placeholder="Enter your message here..." required></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Recipients <span class="text-danger">*</span></label>
                                        <select class="admin-form-control" id="recipients" required>
                                            <option value="">Select Recipients</option>
                                            <option value="all">All Students</option>
                                            <option value="cs-a">CS Dept - Section A</option>
                                            <option value="cs-b">CS Dept - Section B</option>
                                            <option value="cs-c">CS Dept - Section C</option>
                                        </select>
                                    </div>
                                </div>
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
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary" id="clearFormBtn">Clear</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Send Notification
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
    <script src="../assets/js/common.js"></script>
    <script>
        // Notification Type Selection
        let selectedType = 'announcement';
        const announcementCard = document.getElementById('announcementCard');
        const resultsCard = document.getElementById('resultsCard');
        
        if (announcementCard) {
            announcementCard.addEventListener('click', function() {
                announcementCard.classList.add('active');
                resultsCard.classList.remove('active');
                selectedType = 'announcement';
            });
        }
        
        if (resultsCard) {
            resultsCard.addEventListener('click', function() {
                resultsCard.classList.add('active');
                announcementCard.classList.remove('active');
                selectedType = 'results';
                // Auto-fill related examination if results type
                document.getElementById('relatedQuiz').value = '';
            });
        }
        
        // Form Submission
        document.getElementById('notificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // TODO: Implement API call to send notification
            alert('Notification sent successfully! (Backend integration pending)');
            this.reset();
        });
        
        // Clear Form
        const clearFormBtn = document.getElementById('clearFormBtn');
        if (clearFormBtn) {
            clearFormBtn.addEventListener('click', function() {
                document.getElementById('notificationForm').reset();
                announcementCard.classList.add('active');
                resultsCard.classList.remove('active');
                selectedType = 'announcement';
            });
        }
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>

