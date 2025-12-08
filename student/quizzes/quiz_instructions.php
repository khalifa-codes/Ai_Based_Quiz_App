<?php 
require_once '../auth_check.php';
require_once '../../includes/security_helpers.php';

// Generate CSRF token for the page
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    <title>Examination Instructions - Student Panel</title>
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
        .student-branding-logo { 
            height: 70px !important; 
            width: auto !important; 
            max-width: 160px !important; 
            object-fit: contain !important; 
            flex-shrink: 0 !important; 
            display: block !important; 
            visibility: visible !important; 
            opacity: 1 !important; 
        }
        .instructions-card {
            max-width: 900px;
            margin: 2rem auto;
        }
        .content-card {
            min-height: calc(100vh - 200px);
            padding: 2.5rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .instructions-header {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        .instructions-header h2 {
            color: var(--text-primary);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .instructions-header p {
            color: var(--text-secondary);
            margin: 0;
        }
        .instructions-section {
            margin-bottom: 2rem;
        }
        .instructions-section h3 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .instructions-section h3 i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        .instructions-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .instructions-list li {
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: var(--bg-secondary);
            border-left: 4px solid var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: start;
            gap: 1rem;
        }
        .instructions-list li i {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-top: 0.2rem;
            flex-shrink: 0;
        }
        .instructions-list li .instruction-text {
            flex: 1;
            color: var(--text-primary);
            line-height: 1.6;
        }
        .warning-box {
            background: rgba(255, 193, 7, 0.1);
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        .warning-box h4 {
            color: #856404;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .warning-box h4 i {
            color: #ffc107;
        }
        .warning-box ul {
            margin: 0;
            padding-left: 1.5rem;
            color: var(--text-primary);
        }
        .warning-box ul li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }
        .start-quiz-btn-container {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
        }
        .start-quiz-btn {
            padding: 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="studentSidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo" id="studentLogoLink">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Student Logo" class="student-branding-logo" id="studentLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle" id="studentSubtitle">Student</span>
                    </span>
                </a>
                <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-x-lg"></i></button>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="../performance/statistics.php" class="nav-link"><i class="bi bi-graph-up"></i><span>Performance</span></a></li>
                </ul>
                <div class="nav-section-title">Examinations</div>
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="available_quizzes.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Available Examinations</span></a></li>
                    <li class="nav-item"><a href="../results/results.php" class="nav-link"><i class="bi bi-clipboard-data"></i><span>Results</span></a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="sidebar-user" id="sidebarUserDropdown">
                    <div class="sidebar-user-header" style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; padding: 0.75rem; border-radius: 8px; transition: all 0.2s ease;">
                        <div class="sidebar-user-avatar">S</div>
                        <div class="sidebar-user-info" style="flex: 1; min-width: 0;">
                            <p class="sidebar-user-name">Student</p>
                            <p class="sidebar-user-role">Learner</p>
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
                        <h1 class="topbar-title">Examination Instructions</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="available_quizzes.php">Available Examinations</a></li>
                                <li class="breadcrumb-item active">Instructions</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="topbar-right">
                    <!-- Theme Toggle -->
                    <button class="topbar-btn theme-toggle" id="themeToggle" title="Toggle theme" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 44px !important; height: 44px !important; position: relative !important; flex-shrink: 0 !important; margin: 0 !important;">
                        <i class="bi bi-moon-fill" id="themeIcon" style="font-size: 1.2rem !important;"></i>
                    </button>
                </div>
            </div>
            <div class="admin-content">
                <div class="instructions-card">
                    <div class="content-card">
                        <div class="instructions-header">
                            <h2>Examination Instructions</h2>
                            <p>Please read all instructions carefully before starting the examination</p>
                        </div>

                        <!-- General Instructions -->
                        <div class="instructions-section">
                            <h3><i class="bi bi-info-circle-fill"></i> General Instructions</h3>
                            <ul class="instructions-list">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div class="instruction-text">
                                        <strong>Time Limit:</strong> The examination has a fixed time limit. Once you start, the timer will begin counting down. You cannot pause or extend the time.
                                    </div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div class="instruction-text">
                                        <strong>Question Navigation:</strong> You can submit or postpone each question individually. Once submitted, you cannot change your answer. Postponed questions will appear after all other questions are completed.
                                    </div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div class="instruction-text">
                                        <strong>Answer Selection:</strong> Select your answer by clicking on the option. Make sure to review your answer before submitting the question.
                                    </div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div class="instruction-text">
                                        <strong>Auto-Submission:</strong> If time runs out, your examination will be automatically submitted with the answers you have provided so far.
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Security & Precautions -->
                        <div class="instructions-section">
                            <h3><i class="bi bi-shield-check-fill"></i> Security & Precautions</h3>
                            <div class="warning-box">
                                <h4><i class="bi bi-exclamation-triangle-fill"></i> Important Security Measures</h4>
                                <ul>
                                    <li><strong>Tab Switching:</strong> Switching to another browser tab or application will automatically submit your examination. Keep the examination window active at all times.</li>
                                    <li><strong>Window Minimization:</strong> Minimizing the browser window will trigger automatic submission. Ensure the window remains visible during the examination.</li>
                                    <li><strong>Back Navigation:</strong> Attempting to navigate back or use the browser's back button will automatically submit your examination. You cannot return to previous pages once the quiz starts.</li>
                                    <li><strong>Page Refresh:</strong> Refreshing the page (F5 or Ctrl+R) will automatically submit your examination. Do not refresh the page during the examination.</li>
                                    <li><strong>Keyboard Shortcuts:</strong> All keyboard shortcuts (F12, Ctrl+Shift+I, Ctrl+U, Ctrl+S, Ctrl+P, Print Screen, etc.) are disabled during the examination.</li>
                                    <li><strong>Developer Tools:</strong> Opening developer tools or inspect element will automatically submit your examination.</li>
                                    <li><strong>Copy/Paste:</strong> Copying and pasting is disabled during the examination to maintain integrity.</li>
                                    <li><strong>Right-Click:</strong> Right-click context menu is disabled during the examination.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Technical Requirements -->
                        <div class="instructions-section">
                            <h3><i class="bi bi-gear-fill"></i> Technical Requirements</h3>
                            <ul class="instructions-list">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div class="instruction-text">
                                        <strong>Browser:</strong> Use a modern, updated browser (Chrome, Firefox, Edge, or Safari recommended).
                                    </div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div class="instruction-text">
                                        <strong>Internet Connection:</strong> Ensure you have a stable internet connection throughout the examination.
                                    </div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div class="instruction-text">
                                        <strong>Fullscreen Mode:</strong> The examination will automatically enter fullscreen mode. Do not exit fullscreen during the examination.
                                    </div>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <div class="instruction-text">
                                        <strong>Device:</strong> Use a desktop or laptop computer. Mobile devices are not recommended for taking examinations.
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Start Quiz Button -->
                        <div class="start-quiz-btn-container">
                            <?php
                            $quizId = isset($_GET['id']) ? intval($_GET['id']) : 1;
                            ?>
                            <button type="button" id="startQuizBtn" class="btn btn-primary btn-lg start-quiz-btn" data-quiz-id="<?php echo $quizId; ?>">
                                <span id="startQuizBtnText"><i class="bi bi-play-circle-fill"></i> Start Quiz</span>
                            </button>
                            <div id="startQuizError" class="alert alert-danger mt-3 d-none" role="alert"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/activity-tracker.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startQuizBtn = document.getElementById('startQuizBtn');
            const startQuizBtnText = document.getElementById('startQuizBtnText');
            const startQuizError = document.getElementById('startQuizError');
            
            if (startQuizBtn) {
                startQuizBtn.addEventListener('click', function() {
                    const quizId = startQuizBtn.getAttribute('data-quiz-id');
                    
                    if (!quizId || quizId === '0' || quizId === '') {
                        startQuizError.textContent = 'Invalid quiz ID. Please select a quiz from the available examinations page.';
                        startQuizError.classList.remove('d-none');
                        // Redirect after 2 seconds
                        setTimeout(() => {
                            window.location.href = 'available_quizzes.php';
                        }, 2000);
                        return;
                    }
                    
                    // Disable button and show loading state
                    startQuizBtn.disabled = true;
                    startQuizBtnText.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Starting...';
                    startQuizError.classList.add('d-none');
                    
                    // Get CSRF token if available
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    
                    // Call start quiz API
                    fetch('../../api/student/quiz/start.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            quiz_id: parseInt(quizId) || 1,
                            csrf_token: csrfToken
                        })
                    })
                    .then(response => {
                        // Always try to parse response, even for error statuses
                        // Check if response is ok or has error status (including 403, 404)
                        if (!response.ok && response.status !== 400 && response.status !== 401 && response.status !== 403 && response.status !== 404 && response.status !== 429) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        
                        // Try to parse JSON response
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                // If not JSON, return error
                                throw new Error('Invalid response format: ' + text.substring(0, 100));
                            }
                        });
                    })
                    .then(data => {
                        // Always reset button state first
                        startQuizBtn.disabled = false;
                        startQuizBtnText.innerHTML = '<i class="bi bi-play-circle-fill"></i> Start Quiz';
                        
                        if (data && data.success) {
                            // Quiz started successfully - redirect to quiz window
                            window.location.href = 'quiz_window.php?id=' + quizId;
                        } else {
                            // Show error message
                            let errorMsg = 'Failed to start quiz. Please try again.';
                            if (data) {
                                if (data.message) {
                                    errorMsg = data.message;
                                } else if (data.errors && data.errors.length > 0) {
                                    errorMsg = data.errors[0];
                                }
                            }
                            startQuizError.textContent = errorMsg;
                            startQuizError.classList.remove('d-none');
                        }
                    })
                    .catch(error => {
                        console.error('Error starting quiz:', error);
                        // Reset button state
                        startQuizBtn.disabled = false;
                        startQuizBtnText.innerHTML = '<i class="bi bi-play-circle-fill"></i> Start Quiz';
                        const errorMsg = error.message || 'Network error. Please check your connection and try again.';
                        startQuizError.textContent = errorMsg;
                        startQuizError.classList.remove('d-none');
                    });
                });
            }
        });
    </script>
</body>
</html>

