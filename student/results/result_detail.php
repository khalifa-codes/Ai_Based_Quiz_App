<?php
require_once '../auth_check.php';
require_once __DIR__ . '/../includes/student_data_helper.php';

$studentId = (int)($_SESSION['user_id'] ?? 0);
$submissionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$detail = null;
$detailError = null;

if ($submissionId > 0) {
    try {
        $dbInstance = Database::getInstance();
        if (!$dbInstance) {
            throw new Exception('Database instance could not be created');
        }
        $conn = $dbInstance->getConnection();
        if (!$conn) {
            throw new Exception('Database connection could not be established');
        }
        $detail = fetchSubmissionDetail($conn, $submissionId, $studentId);
    } catch (Throwable $e) {
        error_log('Result detail error: ' . $e->getMessage());
        $detailError = 'Unable to load result details.';
    }
} else {
    $detailError = 'Invalid result identifier.';
}

$submission = $detail['submission'] ?? null;
$questions = $detail['questions'] ?? [];
$summary = $detail['summary'] ?? ['total_questions' => 0, 'answered' => 0, 'correct' => 0, 'incorrect' => 0];
$scorePercent = null;
$aiOverallMarks = null;
$aiMaxMarks = null;
$aiProvider = null;
$aiModel = null;

if ($submission) {
    $scorePercent = calculateSubmissionPercentage(
        $submission['total_score'] ?? null,
        $submission['total_marks'] ?? null,
        $submission['percentage'] ?? null
    );
    $aiOverallMarks = $submission['total_ai_marks'] ?? null;
    $aiMaxMarks = $submission['total_max_marks'] ?? null;
    $aiProvider = $submission['ai_provider'] ?? null;
    $aiModel = $submission['ai_model'] ?? null;
}

$statusClass = $scorePercent !== null ? categorizeScore($scorePercent) : 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Details - Student Panel</title>
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
        .result-detail-card {
            max-width: 1000px;
            margin: 0 auto;
        }
        .content-card {
            padding: 2.5rem;
        }
        .content-card-body {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .score-summary {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }
        .score-percentage {
            font-size: 4rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        .score-percentage.excellent {
            color: #28a745;
        }
        .score-percentage.passed {
            color: #007bff;
        }
        .score-percentage.failed {
            color: #dc3545;
        }
        .score-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .score-item {
            padding: 1.5rem;
            background: var(--bg-primary);
            border-radius: 10px;
            border: 2px solid var(--border-color);
            text-align: center;
        }
        .score-item-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .score-item-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-primary);
        }
        .ai-evaluation-section {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
        }
        .ai-evaluation-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        .ai-evaluation-header h3 {
            margin: 0;
            color: var(--text-primary);
            font-weight: 600;
        }
        .ai-evaluation-header i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        .ai-marks-display {
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .ai-marks-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .ai-total-marks {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .ai-total-marks-label {
            font-size: 1rem;
            color: var(--text-secondary);
        }
        .ai-total-marks-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .criteria-section {
            margin-top: 2rem;
        }
        .criteria-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .criteria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        .criteria-item {
            padding: 1rem;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .criteria-item i {
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        .criteria-item-name {
            flex: 1;
            color: var(--text-primary);
            font-weight: 500;
        }
        .criteria-item-score {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .question-details {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
        }
        .question-item {
            padding: 1.5rem;
            background: var(--bg-primary);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .question-item:last-child {
            margin-bottom: 0;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .question-number {
            font-weight: 600;
            color: var(--text-primary);
        }
        .question-marks {
            padding: 0.25rem 0.75rem;
            background: var(--primary-color);
            color: white;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .question-text {
            color: var(--text-primary);
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .student-answer {
            background: var(--bg-secondary);
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1rem;
        }
        .student-answer-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .student-answer-text {
            color: var(--text-primary);
            line-height: 1.6;
        }
        .ai-feedback {
            background: rgba(13, 110, 253, 0.1);
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .ai-feedback-label {
            font-size: 0.85rem;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .ai-feedback-text {
            color: var(--text-primary);
            line-height: 1.6;
        }
        .ai-marks-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .ai-mark-item {
            padding: 0.75rem;
            background: var(--bg-secondary);
            border-radius: 6px;
            text-align: center;
        }
        .ai-mark-item-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        .ai-mark-item-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--primary-color);
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
                    <li class="nav-item"><a href="../quizzes/available_quizzes.php" class="nav-link"><i class="bi bi-file-earmark-text"></i><span>Available Examinations</span></a></li>
                    <li class="nav-item"><a href="results.php" class="nav-link active"><i class="bi bi-clipboard-data"></i><span>Results</span></a></li>
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
                        <h1 class="topbar-title">Result Details</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="results.php">Results</a></li>
                                <li class="breadcrumb-item active">Details</li>
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
                <div class="result-detail-card">
                    <?php if ($detailError): ?>
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($detailError); ?>
                        </div>
                    <?php elseif (!$submission): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle me-2"></i>Result not found or unavailable.
                        </div>
                    <?php else: ?>
                        <div class="content-card">
                            <div class="content-card-body">
                                <div class="score-summary">
                                    <h2 style="margin: 0 0 0.5rem 0; color: var(--text-primary);"><?php echo htmlspecialchars($submission['title']); ?></h2>
                                    <p style="margin: 0 0 1rem 0; color: var(--text-secondary);"><?php echo htmlspecialchars($submission['subject'] ?? 'General'); ?></p>
                                    <div class="score-percentage <?php echo htmlspecialchars($statusClass); ?>">
                                        <?php echo $scorePercent !== null ? number_format($scorePercent, 2) . '%' : 'Pending'; ?>
                                    </div>
                                    <div class="score-breakdown">
                                        <div class="score-item">
                                            <div class="score-item-label">Total Marks</div>
                                            <div class="score-item-value"><?php echo number_format($submission['total_marks'] ?? 0); ?></div>
                                        </div>
                                        <div class="score-item">
                                            <div class="score-item-label">Obtained Score</div>
                                            <div class="score-item-value" style="color: var(--primary-color);">
                                                <?php echo number_format($submission['total_score'] ?? 0, 2); ?>
                                            </div>
                                        </div>
                                        <div class="score-item">
                                            <div class="score-item-label">Status</div>
                                            <div class="score-item-value" style="color: <?php echo $statusClass === 'excellent' ? '#28a745' : ($statusClass === 'passed' ? '#0d6efd' : ($statusClass === 'failed' ? '#dc3545' : '#6c757d')); ?>;">
                                                <?php echo ucfirst($statusClass); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="score-breakdown" style="margin-top: 1rem;">
                                    <div class="score-item">
                                        <div class="score-item-label">Total Questions</div>
                                        <div class="score-item-value"><?php echo number_format($summary['total_questions']); ?></div>
                                    </div>
                                    <div class="score-item">
                                        <div class="score-item-label">Correct Answers</div>
                                        <div class="score-item-value" style="color: var(--success-color);"><?php echo number_format($summary['correct']); ?></div>
                                    </div>
                                    <div class="score-item">
                                        <div class="score-item-label">Incorrect Answers</div>
                                        <div class="score-item-value" style="color: var(--danger-color);"><?php echo number_format($summary['incorrect']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($aiOverallMarks !== null || array_filter($questions, fn($q) => !empty($q['ai']))): ?>
                        <div class="ai-evaluation-section">
                            <div class="ai-evaluation-header">
                                <i class="bi bi-robot"></i>
                                <div>
                                    <h3>AI Evaluation Summary</h3>
                                    <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">
                                        <?php if ($aiProvider): ?>
                                            Provider: <?php echo htmlspecialchars(strtoupper($aiProvider)); ?>
                                        <?php endif; ?>
                                        <?php if ($aiModel): ?>
                                            (<?php echo htmlspecialchars($aiModel); ?>)
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($aiOverallMarks !== null && $aiMaxMarks !== null): ?>
                            <div class="ai-marks-display">
                                <div class="ai-marks-title">Overall AI Score</div>
                                <div class="ai-total-marks">
                                    <span class="ai-total-marks-label">AI Awarded Marks</span>
                                    <span class="ai-total-marks-value"><?php echo number_format($aiOverallMarks, 2); ?> / <?php echo number_format($aiMaxMarks, 2); ?></span>
                                </div>
                                <?php $aiPercent = $aiMaxMarks > 0 ? min(100, max(0, ($aiOverallMarks / $aiMaxMarks) * 100)) : 0; ?>
                                <div class="progress mt-3" style="height: 12px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $aiPercent; ?>%;" aria-valuenow="<?php echo $aiPercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="question-details" style="margin-top: 2rem;">
                            <h3 style="margin-bottom: 1.5rem; color: var(--text-primary);">Question-wise Breakdown</h3>
                            <?php foreach ($questions as $question):
                                $status = $question['status'];
                                $studentAnswerText = $question['student_answer_text'] ?? 'Not answered';
                                $correctAnswerText = $question['correct_answer_text'] ?? '—';
                                $aiData = $question['ai'] ?? null;
                            ?>
                                <div class="question-item">
                                    <div class="question-header">
                                        <div>
                                            <div class="question-number">Question <?php echo $question['order']; ?></div>
                                            <div class="question-text"><?php echo htmlspecialchars($question['text']); ?></div>
                                        </div>
                                        <div class="question-marks"><?php echo $question['marks']; ?> Marks</div>
                                    </div>
                                    
                                    <div class="student-answer">
                                        <div class="student-answer-label">Your Answer</div>
                                        <div class="student-answer-text"><?php echo $studentAnswerText !== '' ? htmlspecialchars($studentAnswerText) : '<span style="color: var(--text-muted);">Not answered</span>'; ?></div>
                                    </div>

                                    <?php if (!empty($question['options'])): ?>
                                        <div class="mt-3">
                                            <p style="margin-bottom: 0.5rem; font-weight: 600;">Options</p>
                                            <ul class="list-unstyled">
                                                <?php foreach ($question['options'] as $option):
                                                    $isChosen = $option['option_value'] === $question['student_answer_value'];
                                                    $isCorrect = (int)$option['is_correct'] === 1;
                                                ?>
                                                    <li style="padding: 0.35rem 0.5rem; border-radius: 6px; margin-bottom: 0.25rem; background: <?php echo $isCorrect ? 'rgba(25,135,84,0.1)' : 'var(--bg-primary)'; ?>;">
                                                        <?php if ($isChosen): ?>
                                                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-circle me-2"></i>
                                                        <?php endif; ?>
                                                        <?php echo htmlspecialchars($option['option_text']); ?>
                                                        <?php if ($isCorrect): ?>
                                                            <span class="badge bg-success ms-2">Correct</span>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <p style="margin: 0.5rem 0 0 0; color: var(--text-secondary); font-size: 0.9rem;">
                                        <i class="bi bi-check-circle"></i> Correct Answer: <strong><?php echo htmlspecialchars($correctAnswerText); ?></strong>
                                    </p>

                                    <?php if ($aiData): ?>
                                        <div class="ai-feedback">
                                            <div class="ai-feedback-label">
                                                <i class="bi bi-robot"></i>
                                                AI Feedback (<?php echo number_format($aiData['total_score'] ?? 0, 2); ?> / <?php echo number_format($aiData['total_marks'] ?? 0, 2); ?>)
                                            </div>
                                            <div class="ai-feedback-text"><?php echo htmlspecialchars($aiData['feedback'] ?? 'No feedback provided.'); ?></div>
                                            <?php if (!empty($aiData['criteria_scores']) && is_array($aiData['criteria_scores'])): ?>
                                                <div class="ai-marks-breakdown">
                                                    <?php foreach ($aiData['criteria_scores'] as $criterion => $score): ?>
                                                        <div class="ai-mark-item">
                                                            <div class="ai-mark-item-label"><?php echo ucfirst($criterion); ?></div>
                                                            <div class="ai-mark-item-value"><?php echo number_format($score, 2); ?></div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="results.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Results
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin-functions.js"></script>
    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/activity-tracker.js"></script>
</body>
</html>

