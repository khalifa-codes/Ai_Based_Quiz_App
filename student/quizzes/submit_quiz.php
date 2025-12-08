<?php 
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/branding_loader.php';

$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$studentId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Validate inputs before processing
if ($quizId <= 0 || $studentId <= 0) {
    header('Location: available_quizzes.php');
    exit;
}

$submission = null;
$quiz = null;
$aiEvaluations = [];
$totalScore = 0;
$totalMarks = 0;
$percentage = 0;
$aiMarks = 0;
$aiTotalMarks = 0;
$aiPercentage = 0;

try {
    $dbInstance = Database::getInstance();
    if (!$dbInstance) {
        throw new Exception('Database instance could not be created');
    }
    $conn = $dbInstance->getConnection();
    if (!$conn) {
        throw new Exception('Database connection could not be established');
    }
        
        // Get latest submission for this quiz and student
        $stmt = $conn->prepare("
            SELECT * FROM quiz_submissions 
            WHERE quiz_id = ? AND student_id = ? 
            ORDER BY submitted_at DESC LIMIT 1
        ");
        $stmt->execute([$quizId, $studentId]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($submission) {
            // Get quiz details
            $stmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
            $stmt->execute([$quizId]);
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get AI evaluations with question details
            $stmt = $conn->prepare("
                SELECT 
                    ae.*,
                    q.question_text,
                    q.max_marks as question_max_marks,
                    sa.answer_value
                FROM ai_evaluations ae
                INNER JOIN questions q ON ae.question_id = q.id
                INNER JOIN student_answers sa ON ae.answer_id = sa.id
                WHERE ae.submission_id = ?
                ORDER BY q.question_order ASC
            ");
            $stmt->execute([$submission['id']]);
            $aiEvaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate totals
            $totalScore = floatval($submission['total_score'] ?? 0);
            $totalMarks = floatval($quiz['total_marks'] ?? 0);
            $percentage = floatval($submission['percentage'] ?? 0);
            $aiMarks = floatval($submission['total_ai_marks'] ?? 0);
            $aiTotalMarks = floatval($submission['total_max_marks'] ?? 0);
            $aiPercentage = floatval($submission['ai_percentage'] ?? 0);
        }
} catch (Exception $e) {
    error_log("Error fetching submission data: " . $e->getMessage());
    // Redirect on error
    header('Location: available_quizzes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Examination - Student Panel</title>
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
        .ai-loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }
        .ai-loader-content {
            background: var(--bg-primary);
            padding: 2.5rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
        }
        .ai-loader-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .ai-loader-text {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .ai-loader-subtext {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .content-hidden {
            opacity: 0;
            pointer-events: none;
        }
        .submit-card {
            max-width: 700px;
            margin: 2rem auto;
            text-align: center;
        }
        .content-card {
            min-height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .submit-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            margin: 0 auto 2.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .submit-icon.excellent {
            background: #28a745; /* Green for excellent */
        }
        .submit-icon.passed {
            background: #007bff; /* Blue for passed */
        }
        .submit-icon.failed {
            background: #dc3545; /* Red for failed */
        }
        .score-display {
            margin: 2rem 0;
            padding: 2rem;
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 2px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .score-percentage {
            font-size: 3rem;
            font-weight: bold;
            margin: 0.5rem 0;
            color: var(--text-primary);
        }
        .score-details {
            display: flex;
            justify-content: space-around;
            margin-top: 1.5rem;
            gap: 1.5rem;
        }
        .score-item {
            flex: 1;
            padding: 1.5rem;
            background: var(--bg-primary);
            border-radius: 10px;
            border: 2px solid var(--border-color);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .score-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .score-item.correct {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        .score-item.total {
            border-color: #007bff;
            background: rgba(0, 123, 255, 0.1);
        }
        .score-item-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .score-item-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-primary);
        }
        .score-item.correct .score-item-value {
            color: #28a745;
        }
        .score-item.total .score-item-value {
            color: #007bff;
        }
        .ai-evaluation-details {
            margin-top: 2rem;
            padding: 1.5rem;
            background: var(--bg-secondary);
            border-radius: 12px;
            border: 2px solid var(--border-color);
        }
        .question-evaluation {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--bg-primary);
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }
        .question-evaluation:last-child {
            margin-bottom: 0;
        }
        .question-evaluation h5 {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .question-evaluation .answer-text {
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: 8px;
            margin-bottom: 1rem;
            color: var(--text-secondary);
            font-style: italic;
        }
        .criteria-scores {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .criterion-item {
            padding: 0.75rem;
            background: var(--bg-secondary);
            border-radius: 6px;
            text-align: center;
        }
        .criterion-item .criterion-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            text-transform: capitalize;
        }
        .criterion-item .criterion-score {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .feedback-section {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(13, 110, 253, 0.05);
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }
        .feedback-section h6 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .feedback-section p {
            color: var(--text-secondary);
            margin: 0;
            line-height: 1.6;
        }
        .evaluation-score {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .evaluation-score .score-badge {
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border-radius: 6px;
            font-weight: bold;
            font-size: 1.1rem;
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
                        <h1 class="topbar-title">Examination Submitted</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="available_quizzes.php">Available Examinations</a></li>
                                <li class="breadcrumb-item active">Submit</li>
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
                <!-- AI Evaluation Loader -->
                <div id="aiLoaderOverlay" class="ai-loader-overlay" style="display: none;">
                    <div class="ai-loader-content">
                        <div class="ai-loader-spinner"></div>
                        <div class="ai-loader-text">
                            <i class="bi bi-robot"></i> AI Evaluation in Progress
                        </div>
                        <div class="ai-loader-subtext">
                            Please wait while we evaluate your subjective answers...
                        </div>
                    </div>
                </div>
                
                <div class="submit-card" id="resultContent">
                    <div class="content-card">
                        <div class="submit-icon" id="submitIcon">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <h2 style="margin-bottom: 1rem; color: var(--text-primary);">Examination Submitted</h2>
                        <?php
                        $autoSubmit = isset($_GET['auto_submit']) && $_GET['auto_submit'] == '1';
                        $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
                        
                        if ($autoSubmit && $reason === 'tab_switch') {
                            echo '<div class="alert alert-warning" style="margin-bottom: 1.5rem; text-align: left;">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    <strong>Examination Auto-Submitted:</strong> Your examination was automatically submitted because you switched tabs or changed windows. This is a security measure to maintain examination integrity.
                                  </div>';
                        } elseif ($autoSubmit && $reason === 'minimize') {
                            echo '<div class="alert alert-warning" style="margin-bottom: 1.5rem; text-align: left;">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    <strong>Examination Auto-Submitted:</strong> Your examination was automatically submitted because you minimized the browser window. This is a security measure to maintain examination integrity.
                                  </div>';
                        } elseif ($autoSubmit && $reason === 'back_button') {
                            echo '<div class="alert alert-warning" style="margin-bottom: 1.5rem; text-align: left;">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    <strong>Examination Auto-Submitted:</strong> Your examination was automatically submitted because you attempted to navigate back. This is a security measure to maintain examination integrity.
                                  </div>';
                        } elseif ($autoSubmit && $reason === 'refresh') {
                            echo '<div class="alert alert-warning" style="margin-bottom: 1.5rem; text-align: left;">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    <strong>Examination Auto-Submitted:</strong> Your examination was automatically submitted because you refreshed the page. This is a security measure to maintain examination integrity.
                                  </div>';
                        } elseif ($autoSubmit && $reason === 'devtools') {
                            echo '<div class="alert alert-warning" style="margin-bottom: 1.5rem; text-align: left;">
                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                    <strong>Examination Auto-Submitted:</strong> Your examination was automatically submitted because developer tools were detected. This is a security measure to maintain examination integrity.
                                  </div>';
                        } elseif ($autoSubmit) {
                            echo '<div class="alert alert-info" style="margin-bottom: 1.5rem; text-align: left;">
                                    <i class="bi bi-info-circle-fill"></i> 
                                    <strong>Examination Auto-Submitted:</strong> Your examination was automatically submitted.
                                  </div>';
                        }
                        ?>
                        <div class="score-display" id="scoreDisplay">
                            <div class="score-percentage" id="scorePercentage"><?php echo number_format($percentage, 1); ?>%</div>
                            <?php if ($submission && $aiTotalMarks > 0): ?>
                            <div id="aiEvaluationInfo" style="margin: 1rem 0; padding: 1rem; background: rgba(13, 110, 253, 0.1); border-radius: 8px;">
                                <div style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                    <i class="bi bi-robot"></i> <strong>AI Evaluated:</strong> <span id="aiMarks"><?php echo number_format($aiMarks, 1); ?></span> / <span id="aiTotalMarks"><?php echo number_format($aiTotalMarks, 1); ?></span> (<?php echo number_format($aiPercentage, 1); ?>%)
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                    Provider: <span id="aiProvider"><?php echo htmlspecialchars(ucfirst($submission['ai_provider'] ?? 'N/A')); ?></span> | Model: <span id="aiModel"><?php echo htmlspecialchars($submission['ai_model'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="score-details">
                                <div class="score-item total">
                                    <div class="score-item-label">Total Score</div>
                                    <div class="score-item-value" id="totalScore"><?php echo number_format($totalScore, 1); ?></div>
                                </div>
                                <div class="score-item total">
                                    <div class="score-item-label">Total Marks</div>
                                    <div class="score-item-value" id="totalMarks"><?php echo number_format($totalMarks, 1); ?></div>
                                </div>
                                <?php if ($aiTotalMarks > 0): ?>
                                <div class="score-item correct">
                                    <div class="score-item-label">AI Score</div>
                                    <div class="score-item-value" id="aiScore"><?php echo number_format($aiMarks, 1); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($aiEvaluations)): ?>
                        <div class="ai-evaluation-details">
                            <h3 style="margin-bottom: 1.5rem; color: var(--text-primary);">
                                <i class="bi bi-robot"></i> AI Evaluation Details
                            </h3>
                            <?php foreach ($aiEvaluations as $index => $eval): 
                                $criteriaScores = json_decode($eval['criteria_scores'] ?? '{}', true);
                            ?>
                            <div class="question-evaluation">
                                <h5>Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($eval['question_text']); ?></h5>
                                
                                <div class="answer-text">
                                    <strong>Your Answer:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($eval['answer_value'])); ?>
                                </div>
                                
                                <div class="evaluation-score">
                                    <div class="score-badge">
                                        Score: <?php echo number_format($eval['total_score'], 1); ?> / <?php echo number_format($eval['total_marks'], 1); ?>
                                    </div>
                                    <div style="color: var(--text-secondary);">
                                        (<?php echo number_format(($eval['total_score'] / $eval['total_marks']) * 100, 1); ?>%)
                                    </div>
                                </div>
                                
                                <?php if (!empty($criteriaScores)): ?>
                                <div class="criteria-scores">
                                    <?php foreach ($criteriaScores as $criterion => $score): ?>
                                    <div class="criterion-item">
                                        <div class="criterion-label"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $criterion))); ?></div>
                                        <div class="criterion-score"><?php echo htmlspecialchars($score); ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($eval['feedback'])): ?>
                                <div class="feedback-section">
                                    <h6><i class="bi bi-chat-left-text"></i> AI Feedback:</h6>
                                    <p><?php echo nl2br(htmlspecialchars($eval['feedback'])); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <p style="color: var(--text-secondary); margin-bottom: 2rem; margin-top: 1rem;">
                            <?php if (!empty($aiEvaluations)): ?>
                                Your examination has been submitted and evaluated by AI. Review the detailed feedback above.
                            <?php else: ?>
                                Your examination has been submitted. Results will be available once the teacher reviews your answers.
                            <?php endif; ?>
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="../dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
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
        (function() {
            // Check if this is a subjective quiz and AI evaluation might be in progress
            const quizId = <?php echo $quizId; ?>;
            const submissionId = <?php echo isset($submission['id']) ? $submission['id'] : 'null'; ?>;
            const hasSubjectiveQuestions = <?php echo !empty($aiEvaluations) ? 'true' : 'false'; ?>;
            const aiEvaluationsCount = <?php echo count($aiEvaluations); ?>;
            
            // Show loader if we have a submission but no AI evaluations yet (might be processing)
            const loaderOverlay = document.getElementById('aiLoaderOverlay');
            const resultContent = document.getElementById('resultContent');
            
            // Check if AI evaluation is needed but not yet complete
            function checkAIEvaluationStatus() {
                if (!submissionId || hasSubjectiveQuestions) {
                    // If we already have evaluations, hide loader immediately
                    if (aiEvaluationsCount > 0) {
                        if (loaderOverlay) loaderOverlay.style.display = 'none';
                        if (resultContent) resultContent.classList.remove('content-hidden');
                        return;
                    }
                    
                    // Check if submission exists but AI evaluation might be pending
                    fetch(`../../api/student/quiz/check-evaluation.php?submission_id=${submissionId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.evaluation_complete) {
                                // Evaluation complete, reload page to show results
                                if (loaderOverlay) loaderOverlay.style.display = 'none';
                                if (resultContent) resultContent.classList.remove('content-hidden');
                                // Reload to get fresh data
                                setTimeout(() => {
                                    window.location.reload();
                                }, 500);
                            } else if (data.success && !data.evaluation_complete) {
                                // Still processing, keep loader visible and check again
                                if (loaderOverlay) loaderOverlay.style.display = 'flex';
                                if (resultContent) resultContent.classList.add('content-hidden');
                                setTimeout(checkAIEvaluationStatus, 2000); // Check again in 2 seconds
                            } else {
                                // Error or no evaluation needed, hide loader
                                if (loaderOverlay) loaderOverlay.style.display = 'none';
                                if (resultContent) resultContent.classList.remove('content-hidden');
                            }
                        })
                        .catch(error => {
                            // Error checking, hide loader and show content
                            if (loaderOverlay) loaderOverlay.style.display = 'none';
                            if (resultContent) resultContent.classList.remove('content-hidden');
                        });
                } else {
                    // No subjective questions, hide loader immediately
                    if (loaderOverlay) loaderOverlay.style.display = 'none';
                    if (resultContent) resultContent.classList.remove('content-hidden');
                }
            }
            
            // Initial check - if we have evaluations or no submission, show content immediately
            if (aiEvaluationsCount > 0 || !submissionId || !hasSubjectiveQuestions) {
                if (loaderOverlay) loaderOverlay.style.display = 'none';
                if (resultContent) resultContent.classList.remove('content-hidden');
            } else if (submissionId && hasSubjectiveQuestions) {
                // Show loader and check evaluation status
                if (loaderOverlay) loaderOverlay.style.display = 'flex';
                if (resultContent) resultContent.classList.add('content-hidden');
                checkAIEvaluationStatus();
            }
            
            // Set icon color based on score
            const submitIcon = document.getElementById('submitIcon');
            if (submitIcon) {
                submitIcon.classList.remove('excellent', 'passed', 'failed');
                
                const percentage = <?php echo $percentage; ?>;
                
                if (percentage >= 80) {
                    submitIcon.classList.add('excellent'); // Green for excellent
                } else if (percentage >= 50) {
                    submitIcon.classList.add('passed'); // Blue for passed
                } else {
                    submitIcon.classList.add('failed'); // Red for failed
                }
            }
        })();
    </script>
</body>
</html>

