<?php
/**
 * Teacher Result Detail View
 * Shows detailed student results with AI evaluation criteria
 */
require_once '../auth_check.php';
require_once __DIR__ . '/../../config/database.php';

$teacherId = $_SESSION['user_id'] ?? 0;
$submissionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quizId = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

$submission = null;
$student = null;
$quiz = null;
$questions = [];
$statistics = [
    'total_questions' => 0,
    'answered' => 0,
    'correct' => 0,
    'incorrect' => 0,
    'subjective_count' => 0,
    'ai_evaluated' => 0
];

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get submission details
    if ($submissionId > 0) {
        $stmt = $conn->prepare("
            SELECT qs.*, q.title, q.subject, q.total_marks, q.total_questions,
                   s.name as student_name, s.email as student_email, s.student_id
            FROM quiz_submissions qs
            JOIN quizzes q ON q.id = qs.quiz_id
            JOIN students s ON s.id = qs.student_id
            WHERE qs.id = ? AND q.created_by = ?
        ");
        $stmt->execute([$submissionId, $teacherId]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($submission) {
            $student = [
                'name' => $submission['student_name'],
                'email' => $submission['student_email'],
                'student_id' => $submission['student_id']
            ];
            $quiz = [
                'id' => $submission['quiz_id'],
                'title' => $submission['title'],
                'subject' => $submission['subject'],
                'total_marks' => $submission['total_marks'],
                'total_questions' => $submission['total_questions']
            ];
            
            // Get questions and answers
            $stmt = $conn->prepare("
                SELECT q.id, q.question_text, q.question_type, q.marks, q.max_marks,
                       sa.answer_value, sa.is_correct, sa.ai_score,
                       ae.accuracy_score, ae.completeness_score, ae.clarity_score,
                       ae.logic_score, ae.examples_score, ae.structure_score,
                       ae.total_score as ai_total_score, ae.total_marks as ai_total_marks,
                       ae.feedback, ae.ai_provider, ae.ai_model,
                       ae.criteria_scores
                FROM questions q
                LEFT JOIN student_answers sa ON sa.question_id = q.id AND sa.submission_id = ?
                LEFT JOIN ai_evaluations ae ON ae.answer_id = sa.id
                WHERE q.quiz_id = ?
                ORDER BY q.question_order
            ");
            $stmt->execute([$submissionId, $submission['quiz_id']]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate statistics
            foreach ($questions as $q) {
                $statistics['total_questions']++;
                if ($q['answer_value']) {
                    $statistics['answered']++;
                }
                if ($q['is_correct'] === 1) {
                    $statistics['correct']++;
                } elseif ($q['is_correct'] === 0) {
                    $statistics['incorrect']++;
                }
                if ($q['question_type'] === 'subjective') {
                    $statistics['subjective_count']++;
                    if ($q['ai_score'] !== null) {
                        $statistics['ai_evaluated']++;
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Teacher Result Detail Error: " . $e->getMessage());
}

if (!$submission) {
    header('Location: quiz_results.php');
    exit;
}

$scorePercent = $submission['percentage'] ?? 0;
$statusClass = $scorePercent >= 60 ? 'success' : ($scorePercent >= 40 ? 'warning' : 'danger');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Details - Teacher Panel</title>
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
        .evaluation-criteria {
            background: var(--bg-secondary);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .criteria-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        .criteria-item:last-child {
            border-bottom: none;
        }
        .criteria-label {
            font-weight: 500;
            color: var(--text-primary);
        }
        .criteria-score {
            font-weight: 600;
            color: var(--primary-color);
        }
        .ai-feedback {
            background: var(--primary-light, rgba(13, 110, 253, 0.05));
            border-left: 3px solid var(--primary-color);
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 4px;
        }
        .stat-card {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/branding_loader.php'; ?>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="../dashboard.php" class="sidebar-logo">
                    <img src="../../assets/images/logo-removebg-preview.png" alt="Logo" id="orgLogo">
                    <span class="sidebar-logo-text">
                        <span class="logo-brand">QuizAura</span>
                        <span class="logo-subtitle">Teacher</span>
                    </span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul class="list-unstyled">
                    <li class="nav-item"><a href="../dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                    <li class="nav-item"><a href="quiz_results.php" class="nav-link active"><i class="bi bi-clipboard-data"></i><span>Results</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main">
            <div class="admin-topbar">
                <div class="topbar-left">
                    <h1 class="topbar-title">Result Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="quiz_results.php">Results</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </nav>
                </div>
                <div class="topbar-right">
                    <a href="quiz_results.php" class="btn btn-outline-secondary">Back to Results</a>
                </div>
            </div>
            <div class="admin-content">
                <!-- Student & Quiz Info -->
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Student Information</h2>
                    </div>
                    <div class="content-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Quiz:</strong> <?php echo htmlspecialchars($quiz['title']); ?></p>
                                <p><strong>Subject:</strong> <?php echo htmlspecialchars($quiz['subject']); ?></p>
                                <p><strong>Submitted:</strong> <?php echo $submission['submitted_at'] ? date('M d, Y H:i', strtotime($submission['submitted_at'])) : 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($scorePercent, 1); ?>%</div>
                            <div class="stat-label">Overall Score</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $statistics['correct']; ?>/<?php echo $statistics['total_questions']; ?></div>
                            <div class="stat-label">Correct Answers</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $statistics['ai_evaluated']; ?>/<?php echo $statistics['subjective_count']; ?></div>
                            <div class="stat-label">AI Evaluated</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $submission['time_taken'] ? ceil($submission['time_taken'] / 60) : 0; ?> min</div>
                            <div class="stat-label">Time Taken</div>
                        </div>
                    </div>
                </div>

                <!-- AI Evaluation Summary -->
                <?php if ($submission['ai_provider'] && $submission['total_ai_marks'] > 0): ?>
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <h2 class="content-card-title">AI Evaluation Summary</h2>
                    </div>
                    <div class="content-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>AI Provider:</strong> <?php echo strtoupper($submission['ai_provider']); ?></p>
                                <p><strong>AI Model:</strong> <?php echo htmlspecialchars($submission['ai_model']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>AI Score:</strong> <?php echo number_format($submission['total_ai_marks'], 2); ?>/<?php echo number_format($submission['total_max_marks'], 2); ?></p>
                                <p><strong>AI Percentage:</strong> <?php echo number_format($submission['ai_percentage'], 2); ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Questions & Answers -->
                <div class="content-card">
                    <div class="content-card-header">
                        <h2 class="content-card-title">Questions & Answers</h2>
                    </div>
                    <div class="content-card-body">
                        <?php foreach ($questions as $index => $q): 
                            $isCorrect = $q['is_correct'];
                            $answerClass = $isCorrect === 1 ? 'success' : ($isCorrect === 0 ? 'danger' : 'secondary');
                        ?>
                        <div class="mb-4 p-3" style="border: 1px solid var(--border-color); border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5>Question <?php echo $index + 1; ?> (<?php echo ucfirst(str_replace('_', ' ', $q['question_type'])); ?>)</h5>
                                <span class="badge badge-<?php echo $answerClass; ?>">
                                    <?php 
                                    if ($isCorrect === 1) echo 'Correct';
                                    elseif ($isCorrect === 0) echo 'Incorrect';
                                    else echo 'Not Answered';
                                    ?>
                                </span>
                            </div>
                            <p><strong><?php echo htmlspecialchars($q['question_text']); ?></strong></p>
                            <p><strong>Answer:</strong> <?php echo htmlspecialchars($q['answer_value'] ?? 'Not answered'); ?></p>
                            <p><strong>Marks:</strong> 
                                <?php 
                                if ($q['question_type'] === 'subjective' && $q['ai_score'] !== null) {
                                    echo number_format($q['ai_score'], 2) . '/' . $q['max_marks'];
                                } elseif ($isCorrect === 1) {
                                    echo $q['marks'] . '/' . $q['marks'];
                                } else {
                                    echo '0/' . $q['marks'];
                                }
                                ?>
                            </p>

                            <!-- AI Evaluation Criteria (for subjective questions) -->
                            <?php if ($q['question_type'] === 'subjective' && $q['ai_score'] !== null): ?>
                            <div class="evaluation-criteria">
                                <h6><i class="bi bi-robot"></i> AI Evaluation Criteria</h6>
                                <div class="criteria-item">
                                    <span class="criteria-label">Accuracy Score:</span>
                                    <span class="criteria-score"><?php echo $q['accuracy_score'] ?? 'N/A'; ?>/10</span>
                                </div>
                                <div class="criteria-item">
                                    <span class="criteria-label">Completeness Score:</span>
                                    <span class="criteria-score"><?php echo $q['completeness_score'] ?? 'N/A'; ?>/10</span>
                                </div>
                                <div class="criteria-item">
                                    <span class="criteria-label">Clarity Score:</span>
                                    <span class="criteria-score"><?php echo $q['clarity_score'] ?? 'N/A'; ?>/10</span>
                                </div>
                                <div class="criteria-item">
                                    <span class="criteria-label">Logic Score:</span>
                                    <span class="criteria-score"><?php echo $q['logic_score'] ?? 'N/A'; ?>/10</span>
                                </div>
                                <div class="criteria-item">
                                    <span class="criteria-label">Examples Score:</span>
                                    <span class="criteria-score"><?php echo $q['examples_score'] ?? 'N/A'; ?>/10</span>
                                </div>
                                <div class="criteria-item">
                                    <span class="criteria-label">Structure Score:</span>
                                    <span class="criteria-score"><?php echo $q['structure_score'] ?? 'N/A'; ?>/10</span>
                                </div>
                                <div class="criteria-item">
                                    <span class="criteria-label"><strong>Total AI Score:</strong></span>
                                    <span class="criteria-score"><strong><?php echo number_format($q['ai_total_score'], 2); ?>/<?php echo $q['ai_total_marks']; ?></strong></span>
                                </div>
                                <?php if ($q['feedback']): ?>
                                <div class="ai-feedback">
                                    <strong>AI Feedback:</strong>
                                    <p><?php echo htmlspecialchars($q['feedback']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($q['ai_provider']): ?>
                                <p class="mt-2" style="font-size: 0.85rem; color: var(--text-muted);">
                                    <i class="bi bi-info-circle"></i> Evaluated by <?php echo strtoupper($q['ai_provider']); ?> 
                                    (<?php echo htmlspecialchars($q['ai_model']); ?>)
                                </p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

