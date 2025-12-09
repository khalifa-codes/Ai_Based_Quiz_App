<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';

$teacherId = $_SESSION['user_id'] ?? 0;
$notifications = [];
$notificationCount = 0;

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
    // Fetch notifications for teacher (empty for now, will be implemented later)
    $notifications = [];
    $notificationCount = count($notifications);
} catch (Exception $e) {
    error_log('Teacher upload quiz notifications fetch error: ' . $e->getMessage());
    $notifications = [];
    $notificationCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Examination - Teacher Panel</title>
    <link rel="icon" type="image/png" href="../../assets/images/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="../../assets/images/logo-removebg-preview.png">
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
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            background: var(--bg-secondary);
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        .upload-area.dragover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        .upload-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .file-info {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .ai-options-section {
            display: none;
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .criteria-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .criteria-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: var(--bg-primary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .criteria-checkbox:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        .criteria-checkbox input[type="checkbox"]:checked + label {
            color: var(--primary-color);
            font-weight: 600;
        }
        .question-item {
            transition: all 0.3s ease;
        }
        .question-item:hover {
            box-shadow: var(--shadow-sm);
        }
        #manualEntryBtn.active,
        #uploadDocxBtn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
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
                    <li class="nav-item"><a href="quiz_list.php" class="nav-link active"><i class="bi bi-file-earmark-text"></i><span>Examinations</span></a></li>
                    <li class="nav-item"><a href="../students/student_list.php" class="nav-link"><i class="bi bi-mortarboard"></i><span>Students</span></a></li>
                    <li class="nav-item"><a href="../results/quiz_results.php" class="nav-link"><i class="bi bi-clipboard-data"></i><span>Department Results</span></a></li>
                    <li class="nav-item"><a href="../notifications/send_notification.php" class="nav-link"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
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
                        <h1 class="topbar-title">Upload Examination</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="quiz_list.php">Examinations</a></li>
                                <li class="breadcrumb-item active">Upload</li>
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
                                <span class="notification-badge" id="notificationBadge" style="position: absolute; top: 4px; right: 4px; background: var(--danger-color, #dc3545); color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: none; align-items: center; justify-content: center; font-weight: 600; border: 2px solid var(--bg-primary, #fff);">0</span>
                            </button>
                            <!-- Notification Dropdown -->
                            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 380px; max-width: calc(100vw - 40px); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); z-index: 1000; overflow: hidden;">
                                <div class="notification-dropdown-header" style="padding: 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: var(--bg-secondary);">
                                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text-primary);">Notifications</h3>
                                    <a href="../notifications/view_all.php" class="view-all-link" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
                                </div>
                                <div class="notification-dropdown-body" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <div class="notification-item" style="padding: 1rem 1.25rem; text-align:center;">
                                        <p style="margin:0; color: var(--text-secondary);">Loading notifications...</p>
                                    </div>
                                </div>
                                <div class="notification-dropdown-footer" style="padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); text-align: center; background: var(--bg-secondary);">
                                    <a href="../notifications/view_all.php" class="view-all-btn" style="color: var(--primary-color); text-decoration: none; font-weight: 500; font-size: 0.9rem;">Show All Messages</a>
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
                <!-- Success Message Container -->
                <div id="successMessage" class="alert alert-success alert-dismissible fade" role="alert" style="display: none; position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong id="successMessageText">Examination uploaded successfully!</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                
                <!-- Error Message Container -->
                <div id="errorMessage" class="alert alert-danger alert-dismissible fade" role="alert" style="display: none; position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong id="errorMessageText">Error occurred!</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                
                <form id="uploadQuizForm" method="POST" action="#" novalidate>
                    <div class="content-card">
                        <div class="content-card-header">
                            <h2 class="content-card-title">Examination Details</h2>
                        </div>
                        <div class="content-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Examination Title <span class="text-danger">*</span></label>
                                        <input type="text" class="admin-form-control" id="quizTitle" placeholder="Enter examination title" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Examination Code <span class="text-danger">*</span></label>
                                        <input type="text" class="admin-form-control" id="quizCode" placeholder="e.g., MATH-EXAM-2024" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Class <span class="text-danger">*</span></label>
                                        <select class="admin-form-control" id="quizClass" required>
                                            <option value="">Select Class</option>
                                            <option value="1">CS Dept - Section A</option>
                                            <option value="2">CS Dept - Section B</option>
                                            <option value="3">CS Dept - Section C</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                        <input type="number" class="admin-form-control" id="quizDuration" placeholder="60" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Total Marks <span class="text-danger">*</span></label>
                                        <input type="number" class="admin-form-control" id="quizMarks" placeholder="100" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Examination Type</label>
                                        <input type="text" class="admin-form-control" id="quizTypeDisplay" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Question Input Method Selection -->
                    <div class="content-card">
                        <div class="content-card-header">
                            <h2 class="content-card-title">Add Questions</h2>
                        </div>
                        <div class="content-card-body">
                            <div class="d-flex gap-2 mb-4">
                                <button type="button" class="btn btn-outline-primary active" id="manualEntryBtn" style="flex: 1;">
                                    <i class="bi bi-pencil-square"></i> Add Questions Manually
                                </button>
                                <button type="button" class="btn btn-outline-primary" id="uploadDocxBtn" style="flex: 1;">
                                    <i class="bi bi-cloud-upload"></i> Upload DOCX File
                                </button>
                            </div>

                            <!-- Manual Question Entry Section -->
                            <div id="manualEntrySection">
                                <div id="questionsContainer">
                                    <!-- Questions will be added here dynamically -->
                                </div>
                                <button type="button" class="btn btn-primary mt-3" id="addQuestionBtn">
                                    <i class="bi bi-plus-circle"></i> Add Question
                                </button>
                            </div>

                            <!-- DOCX Upload Section -->
                            <div id="uploadDocxSection" style="display: none;">
                                <div class="upload-area" id="uploadArea">
                                    <div class="upload-icon">
                                        <i class="bi bi-cloud-upload"></i>
                                    </div>
                                    <h4>Drag & Drop DOCX File</h4>
                                    <p class="text-muted">or click to browse</p>
                                    <input type="file" id="docxFile" accept=".docx" style="display: none;">
                                    <button type="button" class="btn btn-primary mt-3" onclick="document.getElementById('docxFile').click()">
                                        <i class="bi bi-upload"></i> Browse Files
                                    </button>
                                    <p class="text-muted mt-2" style="font-size: 0.85rem;">Supported format: .docx (Microsoft Word Document)</p>
                                </div>
                                <div class="file-info" id="fileInfo" style="display: none;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-word text-primary" style="font-size: 2rem;"></i>
                                            <div>
                                                <strong id="fileName"></strong>
                                                <p class="text-muted mb-0" style="font-size: 0.85rem;" id="fileSize"></p>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeFileBtn">
                                            <i class="bi bi-x"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AI Options for Subjective Questions -->
                    <div class="ai-options-section" id="aiOptionsSection">
                        <h4 class="mb-3"><i class="bi bi-robot"></i> AI Evaluation Settings</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">AI Provider <span class="text-danger">*</span></label>
                                    <select class="admin-form-control" id="aiModel" required>
                                        <option value="">Select AI Provider</option>
                                        <option value="gemini">Gemini (Recommended - Free tier available)</option>
                                        <option value="groq">Groq (⚡⚡ Ultra Fast - Free tier available)</option>
                                        <option value="perplexity">Perplexity Pro (🔍 Web Search - Pro tier)</option>
                                    </select>
                                    <small class="text-muted">Choose the AI provider for evaluating subjective answers</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">Evaluation Criteria</label>
                                    <div class="criteria-options">
                                        <div class="criteria-checkbox">
                                            <input type="checkbox" id="criteria1" name="criteria" value="accuracy">
                                            <label for="criteria1">Accuracy</label>
                                        </div>
                                        <div class="criteria-checkbox">
                                            <input type="checkbox" id="criteria2" name="criteria" value="completeness">
                                            <label for="criteria2">Completeness</label>
                                        </div>
                                        <div class="criteria-checkbox">
                                            <input type="checkbox" id="criteria3" name="criteria" value="clarity">
                                            <label for="criteria3">Clarity</label>
                                        </div>
                                        <div class="criteria-checkbox">
                                            <input type="checkbox" id="criteria4" name="criteria" value="logic">
                                            <label for="criteria4">Logic & Reasoning</label>
                                        </div>
                                        <div class="criteria-checkbox">
                                            <input type="checkbox" id="criteria5" name="criteria" value="examples">
                                            <label for="criteria5">Examples & Evidence</label>
                                        </div>
                                        <div class="criteria-checkbox">
                                            <input type="checkbox" id="criteria6" name="criteria" value="structure">
                                            <label for="criteria6">Structure & Format</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="admin-form-group">
                                    <label class="admin-form-label">Additional Instructions for AI</label>
                                    <textarea class="admin-form-control" id="aiInstructions" rows="3" placeholder="Provide specific instructions for AI evaluation (optional)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Examination Settings -->
                    <div class="content-card">
                        <div class="content-card-header">
                            <h2 class="content-card-title">Examination Settings</h2>
                        </div>
                        <div class="content-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">Start Date & Time</label>
                                        <input type="datetime-local" class="admin-form-control" id="startDateTime">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="admin-form-group">
                                        <label class="admin-form-label">End Date & Time</label>
                                        <input type="datetime-local" class="admin-form-control" id="endDateTime">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center justify-content-between p-3" style="background: var(--bg-secondary); border-radius: 8px;">
                                        <div>
                                            <label class="admin-form-label mb-0">Enable Timer</label>
                                            <p class="text-muted mb-0" style="font-size: 0.85rem;">Automatically submit when time expires</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="timerToggle" checked style="width: 3rem; height: 1.5rem;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="d-flex align-items-center justify-content-between p-3" style="background: var(--bg-secondary); border-radius: 8px;">
                                        <div>
                                            <label class="admin-form-label mb-0">Examination Status</label>
                                            <p class="text-muted mb-0" style="font-size: 0.85rem;">Make examination active or inactive</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="statusToggle" checked style="width: 3rem; height: 1.5rem;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <div class="d-flex align-items-center justify-content-between p-3" style="background: var(--bg-secondary); border-radius: 8px;">
                                        <div>
                                            <label class="admin-form-label mb-0">Send Results via Email</label>
                                            <p class="text-muted mb-0" style="font-size: 0.85rem;">Send examination results to students via email after submission</p>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="emailResultsToggle" checked style="width: 3rem; height: 1.5rem;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="quiz_list.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" id="submitQuizBtn" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Upload & Create Examination
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
    <script>
        // Get quiz type from URL
        const urlParams = new URLSearchParams(window.location.search);
        const quizType = urlParams.get('type') || 'objective';
        document.getElementById('quizTypeDisplay').value = quizType.charAt(0).toUpperCase() + quizType.slice(1);

        // Show AI options for subjective questions
        if (quizType === 'subjective') {
            document.getElementById('aiOptionsSection').style.display = 'block';
            document.getElementById('aiModel').required = true;
        }

        // Toggle between manual entry and DOCX upload
        const manualEntryBtn = document.getElementById('manualEntryBtn');
        const uploadDocxBtn = document.getElementById('uploadDocxBtn');
        const manualEntrySection = document.getElementById('manualEntrySection');
        const uploadDocxSection = document.getElementById('uploadDocxSection');

        manualEntryBtn.addEventListener('click', function() {
            this.classList.add('active');
            uploadDocxBtn.classList.remove('active');
            manualEntrySection.style.display = 'block';
            uploadDocxSection.style.display = 'none';
        });

        uploadDocxBtn.addEventListener('click', function() {
            this.classList.add('active');
            manualEntryBtn.classList.remove('active');
            manualEntrySection.style.display = 'none';
            uploadDocxSection.style.display = 'block';
        });

        // Question management
        let questionCount = 0;
        const questionsContainer = document.getElementById('questionsContainer');
        const addQuestionBtn = document.getElementById('addQuestionBtn');

        function addQuestion() {
            questionCount++;
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question-item mb-4 p-3';
            questionDiv.style.border = '1px solid var(--border-color)';
            questionDiv.style.borderRadius = '8px';
            questionDiv.style.background = 'var(--bg-secondary)';
            questionDiv.id = `question-${questionCount}`;

            if (quizType === 'objective') {
                questionDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Question ${questionCount}</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-question" data-question-id="${questionCount}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="admin-form-group mb-3">
                        <label class="admin-form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea class="admin-form-control" name="questions[${questionCount}][question]" rows="3" placeholder="Enter your question here..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Option A <span class="text-danger">*</span></label>
                                <input type="text" class="admin-form-control" name="questions[${questionCount}][option_a]" placeholder="Enter option A">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Option B <span class="text-danger">*</span></label>
                                <input type="text" class="admin-form-control" name="questions[${questionCount}][option_b]" placeholder="Enter option B">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Option C <span class="text-danger">*</span></label>
                                <input type="text" class="admin-form-control" name="questions[${questionCount}][option_c]" placeholder="Enter option C">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Option D <span class="text-danger">*</span></label>
                                <input type="text" class="admin-form-control" name="questions[${questionCount}][option_d]" placeholder="Enter option D">
                            </div>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Correct Answer <span class="text-danger">*</span></label>
                        <select class="admin-form-control" name="questions[${questionCount}][correct_answer]">
                            <option value="">Select correct answer</option>
                            <option value="a">Option A</option>
                            <option value="b">Option B</option>
                            <option value="c">Option C</option>
                            <option value="d">Option D</option>
                        </select>
                    </div>
                    <input type="hidden" name="questions[${questionCount}][type]" value="objective">
                `;
            } else {
                questionDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Question ${questionCount}</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-question" data-question-id="${questionCount}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="admin-form-group mb-3">
                        <label class="admin-form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea class="admin-form-control" name="questions[${questionCount}][question]" rows="3" placeholder="Enter your question here..."></textarea>
                    </div>
                    <div class="admin-form-group mb-3">
                        <label class="admin-form-label">Answer Instructions / Format</label>
                        <textarea class="admin-form-control" name="questions[${questionCount}][instructions]" rows="2" placeholder="Provide instructions for students (e.g., 'Write a detailed essay of 500 words...')"></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Points / Marks</label>
                        <input type="number" class="admin-form-control" name="questions[${questionCount}][points]" min="1" value="10" placeholder="Points for this question">
                    </div>
                    <input type="hidden" name="questions[${questionCount}][type]" value="subjective">
                `;
            }

            questionsContainer.appendChild(questionDiv);

            // Add remove functionality
            const removeBtn = questionDiv.querySelector('.remove-question');
            removeBtn.addEventListener('click', function() {
                const questionId = this.getAttribute('data-question-id');
                const questionElement = document.getElementById(`question-${questionId}`);
                if (questionElement) {
                    questionElement.remove();
                    updateQuestionNumbers();
                }
            });
        }

        function updateQuestionNumbers() {
            const questions = questionsContainer.querySelectorAll('.question-item');
            questions.forEach((question, index) => {
                const questionNum = index + 1;
                question.querySelector('h5').textContent = `Question ${questionNum}`;
                question.id = `question-${questionNum}`;
                const removeBtn = question.querySelector('.remove-question');
                if (removeBtn) {
                    removeBtn.setAttribute('data-question-id', questionNum);
                }
            });
        }

        addQuestionBtn.addEventListener('click', addQuestion);

        // Add first question by default
        addQuestion();

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
            document.addEventListener('keydown', e => { if (e.key === 'Escape' && sidebarUserDropdown.classList.contains('active')) sidebarUserDropdown.classList.remove('active'); });
        }
        // File Upload (only when upload section is visible)
        const uploadArea = document.getElementById('uploadArea');
        const docxFile = document.getElementById('docxFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const removeFileBtn = document.getElementById('removeFileBtn');

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        function handleFileSelect(file) {
            if (file && file.name.endsWith('.docx')) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.style.display = 'block';
                uploadArea.style.display = 'none';
                
                // Process the DOCX file automatically
                processDocxFile(file);
            } else {
                alert('Please select a valid DOCX file.');
            }
        }


        if (removeFileBtn) {
            removeFileBtn.addEventListener('click', function() {
                docxFile.value = '';
                fileInfo.style.display = 'none';
                uploadArea.style.display = 'block';
            });
        }

        if (uploadArea) {
            uploadArea.addEventListener('click', () => docxFile.click());
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const file = e.dataTransfer.files[0];
                if (file) {
                    docxFile.files = e.dataTransfer.files;
                    handleFileSelect(file);
                }
            });
        }

        if (docxFile) {
            docxFile.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) handleFileSelect(file);
            });
        }

        // DOCX File Processing
        let processedQuestions = [];
        let isProcessingDocx = false;

        async function processDocxFile(file) {
            if (!file || !file.name.endsWith('.docx')) {
                alert('Please select a valid DOCX file.');
                return;
            }

            isProcessingDocx = true;
            const uploadArea = document.getElementById('uploadArea');
            const originalContent = uploadArea.innerHTML;
            uploadArea.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Processing DOCX file...</p></div>';

            try {
                const formData = new FormData();
                formData.append('quiz_file', file);

                const response = await fetch('../../api/teacher/upload_quiz_docx.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success && result.data && result.data.quiz) {
                    const quizData = result.data.quiz;
                    
                    // Populate form fields from metadata
                    if (quizData.metadata) {
                        if (quizData.metadata.title && !document.getElementById('quizTitle').value) {
                            document.getElementById('quizTitle').value = quizData.metadata.title;
                        }
                        if (quizData.metadata.subject) {
                            // You can add subject field if needed
                        }
                        if (quizData.metadata.duration && !document.getElementById('quizDuration').value) {
                            document.getElementById('quizDuration').value = Math.floor(quizData.metadata.duration / 60);
                        }
                        if (quizData.metadata.total_marks && !document.getElementById('quizMarks').value) {
                            document.getElementById('quizMarks').value = quizData.metadata.total_marks;
                        }
                    }

                    // Clear existing questions
                    questionsContainer.innerHTML = '';
                    questionCount = 0;

                    // Add questions from JSON
                    if (quizData.questions && quizData.questions.length > 0) {
                        processedQuestions = quizData.questions;
                        
                        console.log('Processing questions from DOCX:', quizData.questions.length);
                        quizData.questions.forEach((q, index) => {
                            console.log(`Question ${index + 1}:`, {
                                question: q.question,
                                type: q.type,
                                options: q.options ? q.options.length : 0
                            });
                            questionCount++;
                            addQuestionFromJson(q, questionCount);
                        });
                        
                        console.log('Total questions added:', questionCount);
                        console.log('Questions container children:', questionsContainer.children.length);

                        // Show success message and switch to manual entry to show questions
                        uploadArea.innerHTML = `
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle-fill"></i>
                                <strong>Success!</strong> Processed ${quizData.questions.length} questions from DOCX file.
                            </div>
                        `;

                        // Switch to manual entry section to show loaded questions
                        setTimeout(() => {
                            uploadArea.style.display = 'none';
                            fileInfo.style.display = 'block';
                            
                            // Switch to manual entry view so teachers can see and edit questions
                            manualEntryBtn.click();
                        }, 2000);
                    } else {
                        throw new Error('No questions found in DOCX file');
                    }
                } else {
                    throw new Error(result.message || 'Failed to process DOCX file');
                }
            } catch (error) {
                uploadArea.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Error:</strong> ${error.message}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="location.reload()">
                        Try Again
                    </button>
                `;
                console.error('DOCX processing error:', error);
            } finally {
                isProcessingDocx = false;
            }
        }

        function addQuestionFromJson(questionData, questionNum) {
            console.log(`Adding question ${questionNum} from JSON:`, questionData);
            
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question-item mb-4 p-3';
            questionDiv.style.border = '1px solid var(--border-color)';
            questionDiv.style.borderRadius = '8px';
            questionDiv.style.background = 'var(--bg-secondary)';
            questionDiv.id = `question-${questionNum}`;

            // Normalize question type - handle both 'subjective' and variations
            let questionType = questionData.type || 'subjective';
            if (questionType === 'mcq' || questionType === 'multiple_choice' || questionType === 'objective') {
                questionType = 'mcq';
            } else {
                questionType = 'subjective';
            }
            
            const isObjective = questionType === 'mcq' || questionType === 'objective';
            
            console.log(`Question ${questionNum} type: ${questionType}, isObjective: ${isObjective}`);

            // Check if it's MCQ with options
            if (isObjective && questionData.options && Array.isArray(questionData.options) && questionData.options.length > 0) {
                // MCQ Question
                const options = questionData.options;
                const correctAnswer = questionData.correct_answer ? questionData.correct_answer.toLowerCase() : '';

                questionDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Question ${questionNum}</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-question" data-question-id="${questionNum}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="admin-form-group mb-3">
                        <label class="admin-form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea class="admin-form-control" name="questions[${questionNum}][question]" rows="3">${escapeHtml(questionData.question || questionData.question_text || '')}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Option A <span class="text-danger">*</span></label>
                                <input type="text" class="admin-form-control" name="questions[${questionNum}][option_a]" value="${escapeHtml(options[0]?.text || '')}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Option B <span class="text-danger">*</span></label>
                                <input type="text" class="admin-form-control" name="questions[${questionNum}][option_b]" value="${escapeHtml(options[1]?.text || '')}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Option C <span class="text-danger">*</span></label>
                                <input type="text" class="admin-form-control" name="questions[${questionNum}][option_c]" value="${escapeHtml(options[2]?.text || '')}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="admin-form-group">
                                <label class="admin-form-label">Option D <span class="text-danger">*</span></label>
                                <input type="text" class="admin-form-control" name="questions[${questionNum}][option_d]" value="${escapeHtml(options[3]?.text || '')}">
                            </div>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Correct Answer <span class="text-danger">*</span></label>
                        <select class="admin-form-control" name="questions[${questionNum}][correct_answer]">
                            <option value="">Select correct answer</option>
                            <option value="a" ${correctAnswer === 'a' ? 'selected' : ''}>Option A</option>
                            <option value="b" ${correctAnswer === 'b' ? 'selected' : ''}>Option B</option>
                            <option value="c" ${correctAnswer === 'c' ? 'selected' : ''}>Option C</option>
                            <option value="d" ${correctAnswer === 'd' ? 'selected' : ''}>Option D</option>
                        </select>
                    </div>
                    <input type="hidden" name="questions[${questionNum}][type]" value="objective">
                `;
            } else {
                // Subjective Question
                questionDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Question ${questionNum}</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-question" data-question-id="${questionNum}">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                    <div class="admin-form-group mb-3">
                        <label class="admin-form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea class="admin-form-control" name="questions[${questionNum}][question]" rows="3">${escapeHtml(questionData.question || questionData.question_text || '')}</textarea>
                    </div>
                    <div class="admin-form-group mb-3">
                        <label class="admin-form-label">Answer Instructions / Format</label>
                        <textarea class="admin-form-control" name="questions[${questionNum}][instructions]" rows="2" placeholder="Provide instructions for students (e.g., 'Write a detailed essay of 500 words...')"></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-label">Points / Marks</label>
                        <input type="number" class="admin-form-control" name="questions[${questionNum}][points]" min="1" value="${questionData.max_marks || 10}" placeholder="Points for this question">
                    </div>
                    <input type="hidden" name="questions[${questionNum}][type]" value="subjective">
                `;
            }

            questionsContainer.appendChild(questionDiv);

            // Add remove functionality
            const removeBtn = questionDiv.querySelector('.remove-question');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    const questionId = this.getAttribute('data-question-id');
                    const questionElement = document.getElementById(`question-${questionId}`);
                    if (questionElement) {
                        questionElement.remove();
                        updateQuestionNumbers();
                    }
                });
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Update file handling to process DOCX
        function handleFileSelect(file) {
            if (file && file.name.endsWith('.docx')) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.style.display = 'block';
                uploadArea.style.display = 'none';
                
                // Process the DOCX file
                processDocxFile(file);
            } else {
                alert('Please select a valid DOCX file.');
            }
        }

        // Form Submission - Wrap in DOMContentLoaded to ensure DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Initializing form submission handler');
            
            const uploadQuizForm = document.getElementById('uploadQuizForm');
            const submitQuizBtn = document.getElementById('submitQuizBtn');
            
            if (!uploadQuizForm) {
                console.error('Form not found!');
                return;
            }
            
            if (!submitQuizBtn) {
                console.error('Submit button not found!');
                return;
            }
            
            console.log('Form and button found, attaching handlers...');
            
            // Add submit event handler to form
            uploadQuizForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Form submission started...');
                
                // Check if DOCX is being processed
                if (isProcessingDocx) {
                    alert('Please wait for DOCX file processing to complete.');
                    return;
                }

                // Validate form
                const title = document.getElementById('quizTitle')?.value.trim();
                const duration = parseInt(document.getElementById('quizDuration')?.value) || 0;
                const totalMarks = parseInt(document.getElementById('quizMarks')?.value) || 0;
                
                console.log('Form data:', { title, duration, totalMarks });
                
                if (!title || !duration || !totalMarks) {
                    alert('Please fill in all required fields (Title, Duration, Total Marks).');
                    return;
                }
                
                // Collect questions from form
                const questions = [];
                const questionElements = questionsContainer.querySelectorAll('.question-item');
                
                console.log('Found question elements:', questionElements.length);
                console.log('Questions container:', questionsContainer);
                console.log('Questions container HTML:', questionsContainer.innerHTML.substring(0, 500));
                
                if (questionElements.length === 0) {
                    alert('Please add at least one question. If you uploaded a DOCX file, make sure it was processed successfully.');
                    return;
                }
                
                let validationError = null;
                
                for (let index = 0; index < questionElements.length; index++) {
                    const questionEl = questionElements[index];
                    const questionTextarea = questionEl.querySelector('textarea[name*="[question]"]');
                    const questionText = questionTextarea ? questionTextarea.value.trim() : '';
                    const questionTypeInput = questionEl.querySelector('input[name*="[type]"]');
                    const questionType = questionTypeInput ? questionTypeInput.value : quizType;
                    
                    console.log(`Question ${index + 1}:`, {
                        text: questionText,
                        type: questionType,
                        textarea: questionTextarea ? 'found' : 'not found',
                        typeInput: questionTypeInput ? 'found' : 'not found'
                    });
                    
                    if (!questionText) {
                        console.log(`Skipping empty question ${index + 1}`);
                        continue; // Skip empty questions
                    }
                    
                    const questionData = {
                        question: questionText,
                        type: questionType === 'objective' ? 'multiple_choice' : 'subjective',
                        marks: 1,
                        max_marks: 10
                    };
                    
                    if (questionType === 'objective' || questionType === 'multiple_choice') {
                        // Get options
                        questionData.option_a = questionEl.querySelector('input[name*="[option_a]"]')?.value.trim() || '';
                        questionData.option_b = questionEl.querySelector('input[name*="[option_b]"]')?.value.trim() || '';
                        questionData.option_c = questionEl.querySelector('input[name*="[option_c]"]')?.value.trim() || '';
                        questionData.option_d = questionEl.querySelector('input[name*="[option_d]"]')?.value.trim() || '';
                        questionData.correct_answer = questionEl.querySelector('select[name*="[correct_answer]"]')?.value || '';
                        
                        // Validate MCQ question
                        if (!questionData.option_a || !questionData.option_b || !questionData.option_c || !questionData.option_d) {
                            validationError = `Question ${index + 1}: Please fill in all options (A, B, C, D).`;
                            break;
                        }
                        if (!questionData.correct_answer) {
                            validationError = `Question ${index + 1}: Please select the correct answer.`;
                            break;
                        }
                    } else {
                        // Subjective question
                        const pointsInput = questionEl.querySelector('input[name*="[points]"]');
                        questionData.points = pointsInput ? (parseInt(pointsInput.value) || 10) : 10;
                        questionData.max_marks = questionData.points;
                        questionData.marks = questionData.points;
                    }
                    
                    questions.push(questionData);
                }
                
                if (validationError) {
                    alert(validationError);
                    return;
                }
                
                if (questions.length === 0) {
                    alert('Please add at least one valid question with all required fields filled. Make sure your questions have text in the question field.');
                    console.error('No valid questions found. Question elements:', questionElements.length);
                    return;
                }
                
                console.log('Collected questions:', questions.length);
                
                // Prepare submission data
                const submitData = {
                    title: title,
                    quiz_code: document.getElementById('quizCode')?.value.trim() || null,
                    subject: document.getElementById('quizClass')?.value || null,
                    description: title,
                    duration: duration,
                    total_marks: totalMarks,
                    quiz_type: quizType,
                    status: document.getElementById('statusToggle')?.checked ? 'published' : 'draft',
                    questions: questions
                };
                
                // Add AI settings for subjective quizzes
                if (quizType === 'subjective') {
                    const aiModel = document.getElementById('aiModel')?.value;
                    if (aiModel) {
                        submitData.ai_provider = aiModel;
                        submitData.ai_model = aiModel === 'gemini' ? 'gemini-2.5-pro' : null;
                    } else {
                        alert('Please select an AI provider for subjective questions.');
                        return;
                    }
                    
                    // Get selected criteria
                    const selectedCriteria = [];
                    document.querySelectorAll('input[name="criteria"]:checked').forEach(cb => {
                        selectedCriteria.push(cb.value);
                    });
                    if (selectedCriteria.length > 0) {
                        submitData.criteria = selectedCriteria;
                    }
                }
                
                console.log('Submitting data:', submitData);
                
                // Show loading state
                const submitBtn = document.getElementById('submitQuizBtn') || this.querySelector('button[type="submit"]');
                if (!submitBtn) {
                    console.error('Submit button not found!');
                    alert('Submit button not found!');
                    return;
                }
                
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Uploading...';
                
                try {
                    const apiUrl = '../../api/teacher/create_quiz.php';
                    console.log('Calling API:', apiUrl);
                    
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(submitData)
                    });
                    
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('API Error:', errorText);
                        throw new Error(`Server error: ${response.status} - ${errorText}`);
                    }
                    
                    const result = await response.json();
                    console.log('API Result:', result);
                    
                    if (result.success) {
                        // Show success message
                        const successMsg = document.getElementById('successMessage');
                        const successText = document.getElementById('successMessageText');
                        if (successMsg && successText) {
                            successText.textContent = 'Examination uploaded successfully! Redirecting...';
                            successMsg.style.display = 'block';
                            successMsg.classList.add('show');
                            
                            // Hide error message if visible
                            const errorMsg = document.getElementById('errorMessage');
                            if (errorMsg) {
                                errorMsg.style.display = 'none';
                                errorMsg.classList.remove('show');
                            }
                        }
                        
                        // Redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = 'quiz_list.php';
                        }, 2000);
                    } else {
                        // Show error message
                        const errorMsg = document.getElementById('errorMessage');
                        const errorText = document.getElementById('errorMessageText');
                        if (errorMsg && errorText) {
                            errorText.textContent = result.message || 'Failed to upload examination';
                            errorMsg.style.display = 'block';
                            errorMsg.classList.add('show');
                            
                            // Hide success message if visible
                            const successMsg = document.getElementById('successMessage');
                            if (successMsg) {
                                successMsg.style.display = 'none';
                                successMsg.classList.remove('show');
                            }
                        } else {
                            alert('Error: ' + (result.message || 'Failed to upload examination'));
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    
                    // Show error message
                    const errorMsg = document.getElementById('errorMessage');
                    const errorText = document.getElementById('errorMessageText');
                    if (errorMsg && errorText) {
                        errorText.textContent = 'Error uploading examination: ' + error.message;
                        errorMsg.style.display = 'block';
                        errorMsg.classList.add('show');
                        
                        // Hide success message if visible
                        const successMsg = document.getElementById('successMessage');
                        if (successMsg) {
                            successMsg.style.display = 'none';
                            successMsg.classList.remove('show');
                        }
                    } else {
                        alert('Error uploading examination: ' + error.message + '\n\nPlease check the browser console for details.');
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
            
            console.log('Form submission handler attached successfully');
        });
        
        // Also try on window load as fallback
        window.addEventListener('load', function() {
            const form = document.getElementById('uploadQuizForm');
            console.log('Window loaded - Form element:', form);
            if (form) {
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
            }
        });
    </script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>

