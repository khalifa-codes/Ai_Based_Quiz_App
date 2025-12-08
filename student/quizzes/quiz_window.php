<?php 
require_once '../auth_check.php';
require_once '../../config/database.php';
require_once '../../includes/security_helpers.php';

$quizId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$studentId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Validate inputs
if ($quizId <= 0 || $studentId <= 0) {
    header('Location: available_quizzes.php');
    exit;
}

$quiz = null;
$submission = null;
$questions = [];
$firstQuestion = null;
$firstQuestionOptions = [];
$serverTime = time();
$csrfToken = generateCSRFToken();
$timeRemaining = 0;
$submittedAnswers = [];
$postponedQuestions = [];

try {
        $dbInstance = Database::getInstance();
        if (!$dbInstance) {
            throw new Exception('Database instance could not be created');
        }
        $conn = $dbInstance->getConnection();
        if (!$conn) {
            throw new Exception('Database connection could not be established');
        }
        
        $stmt = $conn->prepare("
            SELECT qs.id as submission_id, qs.started_at, qs.status, qs.quiz_id,
                   q.id as quiz_id, q.title, q.subject, q.duration, q.total_questions, 
                   q.ai_provider, q.ai_model
            FROM quiz_submissions qs
            JOIN quizzes q ON qs.quiz_id = q.id
            WHERE qs.quiz_id = ? AND qs.student_id = ? AND qs.status = 'in_progress'
            ORDER BY qs.started_at DESC
            LIMIT 1
        ");
        $stmt->execute([$quizId, $studentId]);
        $submission = $stmt->fetch();
        
        if ($submission) {
            $quiz = [
                'id' => $submission['quiz_id'],
                'title' => $submission['title'],
                'subject' => $submission['subject'],
                'duration' => $submission['duration'],
                'total_questions' => $submission['total_questions'],
                'ai_provider' => $submission['ai_provider'] ?? 'gemini',
                'ai_model' => $submission['ai_model'] ?? null
            ];
            
            // Use server time consistently - started_at is in database timezone (UTC)
            $startTime = !empty($submission['started_at']) ? strtotime($submission['started_at']) : false;
            if ($startTime === false || $startTime <= 0) {
                $startTime = $serverTime; // Fallback to current time if parsing fails
            }
            $elapsed = max(0, $serverTime - $startTime);
            $timeRemaining = max(0, $submission['duration'] - $elapsed);
            
            $stmt = $conn->prepare("
                SELECT id, question_text, question_type, question_order, marks, max_marks, criteria
                FROM questions
                WHERE quiz_id = ?
                ORDER BY question_order ASC
            ");
            $stmt->execute([$quizId]);
            $questions = $stmt->fetchAll();
            
            foreach ($questions as &$question) {
                if (!empty($question['criteria']) && is_string($question['criteria'])) {
                    $decoded = json_decode($question['criteria'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $question['criteria'] = $decoded;
                    } else {
                        $question['criteria'] = null;
                    }
                }
                
                if ($question['question_type'] === 'multiple_choice' || $question['question_type'] === 'true_false') {
                    $stmt = $conn->prepare("
                        SELECT option_text, option_value, is_correct, option_order
                        FROM question_options
                        WHERE question_id = ?
                        ORDER BY option_order ASC, option_value ASC
                    ");
                    $stmt->execute([$question['id']]);
                    $question['options'] = $stmt->fetchAll();
                } else {
                    $question['options'] = [];
                }
            }
            unset($question);
            
            if (count($questions) > 0) {
                $firstQuestion = $questions[0];
                // Use options already fetched in the loop above (no redundant query needed)
                if (isset($firstQuestion['options'])) {
                    $firstQuestionOptions = $firstQuestion['options'];
                } else {
                    $firstQuestionOptions = [];
                }
            }
            
            $stmt = $conn->prepare("
                SELECT question_id, answer_value, is_postponed
                FROM student_answers
                WHERE submission_id = ?
            ");
            $stmt->execute([$submission['submission_id']]);
            while ($row = $stmt->fetch()) {
                $submittedAnswers[$row['question_id']] = $row['answer_value'];
                if ($row['is_postponed']) {
                    $postponedQuestions[] = $row['question_id'];
                }
            }
            
        } else {
            $stmt = $conn->prepare("
                SELECT id, title, subject, duration, total_questions, status, ai_provider, ai_model
                FROM quizzes
                WHERE id = ? AND status = 'published'
            ");
            $stmt->execute([$quizId]);
            $quiz = $stmt->fetch();
            
            if (!$quiz) {
                header('Location: available_quizzes.php');
                exit;
            }
            
            header('Location: quiz_instructions.php?id=' . $quizId);
            exit;
        }
        
} catch (Exception $e) {
    error_log("Error loading quiz data: " . $e->getMessage());
    header('Location: available_quizzes.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination - Student Panel</title>
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
    <script src="../assets/js/quiz-security.js"></script>
    <style>
        body.quiz-fullscreen {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .quiz-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: var(--bg-primary);
            z-index: 9999;
            display: flex;
            flex-direction: column;
        }
        .quiz-header {
            background: var(--bg-secondary);
            border-bottom: 2px solid var(--border-color);
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        .quiz-title-section h2 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .quiz-title-section p {
            margin: 0.2rem 0 0 0;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .quiz-timer {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.25rem;
            background: var(--bg-primary);
            border: 2px solid var(--primary-color);
            border-radius: 12px;
        }
        .timer-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .timer-display {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            font-family: 'Courier New', monospace;
        }
        .timer-display.warning {
            color: var(--warning-color);
        }
        .timer-display.danger {
            color: var(--danger-color);
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .quiz-progress {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 0.6rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        .progress-bar-container {
            flex: 1;
            height: 8px;
            background: var(--bg-tertiary);
            border-radius: 4px;
            margin-right: 1rem;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: var(--primary-color);
            transition: width 0.3s ease;
            border-radius: 4px;
        }
        .question-counter {
            font-size: 0.9rem;
            color: var(--text-secondary);
            white-space: nowrap;
        }
        .quiz-content {
            flex: 1;
            overflow-y: auto;
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .question-card {
            max-width: 900px;
            width: 100%;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 1.5rem 0;
        }
        .question-number {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .question-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .options-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .option-item {
            margin-bottom: 1rem;
        }
        .option-label {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--bg-secondary);
            user-select: none;
        }
        .option-label:hover {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        .option-label.selected {
            border-color: var(--primary-color);
            background: var(--primary-light);
        }
        .option-radio {
            margin-right: 1rem;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .option-text {
            flex: 1;
            font-size: 1rem;
            color: var(--text-primary);
        }
        .quiz-actions {
            background: var(--bg-secondary);
            border-top: 2px solid var(--border-color);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        .quiz-nav-buttons {
            display: flex;
            gap: 1rem;
        }
        .quiz-container * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        .question-text, .option-text {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        .quiz-container button:focus,
        .quiz-container input:focus,
        .quiz-container .option-label:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
        }
        .quiz-container button:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body class="quiz-fullscreen">
    <div class="quiz-container" id="quizContainer">
        <div class="quiz-header">
            <div class="quiz-title-section">
                <h2 id="quizTitle"><?php echo htmlspecialchars($quiz['title'] ?? 'Quiz'); ?></h2>
                <p id="quizSubject"><?php echo htmlspecialchars($quiz['subject'] ?? ''); ?></p>
            </div>
            <div class="quiz-timer">
                <span class="timer-label">Time Remaining:</span>
                <span class="timer-display" id="timerDisplay"><?php 
                    $minutes = floor($timeRemaining / 60);
                    $seconds = $timeRemaining % 60;
                    echo sprintf('%02d:%02d', $minutes, $seconds);
                ?></span>
            </div>
        </div>

        <div class="quiz-progress">
            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar" style="width: <?php 
                    $submittedCount = count($submittedAnswers);
                    $progress = count($questions) > 0 ? ($submittedCount / count($questions)) * 100 : 0;
                    echo $progress;
                ?>%;"></div>
            </div>
            <div class="question-counter">
                Question <span id="currentQuestionNum">1</span> of <span id="totalQuestions"><?php echo count($questions); ?></span>
            </div>
        </div>

        <div class="quiz-content">
            <div class="question-card">
                <div class="question-number">
                    Question <span id="cardQuestionNum">1</span> of 
                    <span id="cardTotalQuestions"><?php echo count($questions); ?></span>
                </div>
                <div class="question-text" id="questionText">
                    <?php 
                    if ($firstQuestion) {
                        echo htmlspecialchars($firstQuestion['question_text']);
                    } else {
                        echo 'Loading question...';
                    }
                    ?>
                </div>
                <ul class="options-list" id="optionsList">
                    <?php if ($firstQuestion): ?>
                        <?php if ($firstQuestion['question_type'] === 'multiple_choice' || $firstQuestion['question_type'] === 'true_false'): ?>
                            <?php foreach ($firstQuestionOptions as $option): ?>
                                <?php 
                                $isSubmitted = isset($submittedAnswers[$firstQuestion['id']]);
                                $submittedAnswer = $submittedAnswers[$firstQuestion['id']] ?? null;
                                $isSelected = ($submittedAnswer === $option['option_value']);
                                ?>
                                <li class="option-item">
                                    <label class="option-label <?php echo $isSelected ? 'selected' : ''; ?>">
                                        <input type="radio" name="answer" value="<?php echo htmlspecialchars($option['option_value']); ?>" 
                                               class="option-radio" <?php echo $isSubmitted ? 'disabled' : ''; ?> 
                                               <?php echo $isSelected ? 'checked' : ''; ?>>
                                        <span class="option-text"><?php echo htmlspecialchars($option['option_text']); ?></span>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="option-item">
                                <textarea name="answer" class="form-control" rows="5" 
                                          <?php echo isset($submittedAnswers[$firstQuestion['id']]) ? 'disabled' : ''; ?>
                                          placeholder="Type your answer here..."><?php 
                                    echo isset($submittedAnswers[$firstQuestion['id']]) ? htmlspecialchars($submittedAnswers[$firstQuestion['id']]) : '';
                                ?></textarea>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="option-item">No questions available</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="quiz-actions">
            <button type="button" class="btn btn-outline-warning" id="postponeBtn">
                <i class="bi bi-pause-circle"></i> Postpone
            </button>
            <button type="button" class="btn btn-success" id="submitBtn">
                <i class="bi bi-check-circle"></i> Submit
            </button>
        </div>
    </div>

    <script src="../../assets/js/admin-functions.js"></script>
    <script>
        // ============================================
        // QUIZ DATA INITIALIZATION
        // ============================================
        <?php if (!$submission || !$quiz): ?>
            window.location.href = 'available_quizzes.php';
        <?php else: ?>
        const quizData = {
            id: <?php echo isset($quiz['id']) ? $quiz['id'] : 0; ?>,
            submissionId: <?php echo isset($submission['id']) ? $submission['id'] : (isset($submission['submission_id']) ? $submission['submission_id'] : 0); ?>,
            title: <?php echo json_encode($quiz['title'] ?? 'Quiz'); ?>,
            subject: <?php echo json_encode($quiz['subject'] ?? ''); ?>,
            duration: <?php echo isset($quiz['duration']) ? $quiz['duration'] : 0; ?>,
            totalQuestions: <?php echo count($questions); ?>,
            currentQuestion: 1,
            currentQuestionId: <?php echo $firstQuestion ? $firstQuestion['id'] : 'null'; ?>,
            questions: <?php echo json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            questionMap: {},
            answers: {},
            submitted: <?php echo json_encode($submittedAnswers); ?>,
            postponed: <?php echo json_encode($postponedQuestions); ?>,
            aiProvider: <?php echo json_encode($quiz['ai_provider'] ?? 'gemini'); ?>,
            aiModel: <?php echo json_encode($quiz['ai_model'] ?? null); ?>,
            startTime: <?php 
                if (isset($submission['started_at']) && !empty($submission['started_at'])) {
                    $startTimestamp = strtotime($submission['started_at']);
                    echo $startTimestamp !== false ? $startTimestamp : time();
                } else {
                    echo time();
                }
            ?>,
            serverTime: <?php echo $serverTime; ?>,
            timeRemaining: <?php echo $timeRemaining; ?>,
            csrfToken: <?php echo json_encode($csrfToken); ?>
        };
        
        quizData.questions.forEach((q, index) => {
            quizData.questionMap[q.id] = q;
            quizData.questionMap[index + 1] = q;
        });
        <?php endif; ?>

        // ============================================
        // TIMER MANAGEMENT
        // ============================================
        let timeRemaining = quizData.timeRemaining || 0;
        let timerInterval = null;
        let validationInterval = null;
        let serverTimeOffset = quizData.serverTime - Math.floor(Date.now() / 1000);

        function startTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            if (validationInterval) {
                clearInterval(validationInterval);
            }
            
            const currentServerTime = Math.floor(Date.now() / 1000) + serverTimeOffset;
            const elapsed = currentServerTime - quizData.startTime;
            timeRemaining = Math.max(0, quizData.duration - elapsed);
            updateTimerDisplay();
            
            timerInterval = setInterval(function() {
                const currentServerTime = Math.floor(Date.now() / 1000) + serverTimeOffset;
                const elapsed = currentServerTime - quizData.startTime;
                timeRemaining = Math.max(0, quizData.duration - elapsed);
                updateTimerDisplay();
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                    if (validationInterval) {
                        clearInterval(validationInterval);
                        validationInterval = null;
                    }
                    if (typeof window.autoSubmitQuiz === 'function') {
                        window.autoSubmitQuiz('time_up');
                    }
                }
            }, 1000);
            
            validateSessionWithServer();
            validationInterval = setInterval(function() {
                validateSessionWithServer();
            }, 30000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const timerDisplay = document.getElementById('timerDisplay');
            if (timerDisplay) {
                timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                timerDisplay.classList.remove('warning', 'danger');
                if (timeRemaining <= 300) {
                    timerDisplay.classList.add('danger');
                } else if (timeRemaining <= 900) {
                    timerDisplay.classList.add('warning');
                }
            }
        }

        async function validateSessionWithServer() {
            if (!quizData.submissionId) return;
            
            try {
                const response = await fetch('../../api/student/quiz/validate-session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        submission_id: quizData.submissionId,
                        csrf_token: quizData.csrfToken
                    })
                });
                
                const data = await response.json();
                
                if (!data.success || !data.valid) {
                    if (data.data && data.data.time_expired) {
                        clearInterval(timerInterval);
                        clearInterval(validationInterval);
                        autoSubmitQuiz('time_expired');
                    } else {
                        window.location.href = `submit_quiz.php?quiz_id=${quizData.id}&auto_submit=1&reason=session_invalid`;
                    }
                    return;
                }
                
                if (data.time_remaining !== undefined) {
                    timeRemaining = data.time_remaining;
                    const currentClientTime = Math.floor(Date.now() / 1000);
                    const expectedServerTime = quizData.startTime + (quizData.duration - timeRemaining);
                    serverTimeOffset = expectedServerTime - currentClientTime;
                    updateTimerDisplay();
                }
            } catch (error) {
                // Fail silently
            }
        }

        // ============================================
        // QUESTION MANAGEMENT
        // ============================================
        function getCurrentQuestionId() {
            return quizData.currentQuestionId || (quizData.questions[quizData.currentQuestion - 1]?.id);
        }

        function getNextUnansweredQuestion() {
            for (let i = 0; i < quizData.questions.length; i++) {
                const question = quizData.questions[i];
                if (!quizData.submitted[question.id] && !quizData.postponed.includes(question.id)) {
                    return { question: question, index: i + 1 };
                }
            }
            if (quizData.postponed.length > 0) {
                const postponedId = quizData.postponed[0];
                const question = quizData.questionMap[postponedId];
                if (question) {
                    const index = quizData.questions.findIndex(q => q.id === postponedId) + 1;
                    return { question: question, index: index };
                }
            }
            return null;
        }

        function loadQuestionByData(question, questionNum) {
            quizData.currentQuestion = questionNum;
            quizData.currentQuestionId = question.id;
            
            document.getElementById('currentQuestionNum').textContent = questionNum;
            document.getElementById('totalQuestions').textContent = quizData.totalQuestions;
            document.getElementById('cardQuestionNum').textContent = questionNum;
            document.getElementById('cardTotalQuestions').textContent = quizData.totalQuestions;
            document.getElementById('questionText').textContent = question.question_text;
            
            const submittedCount = Object.keys(quizData.submitted).length;
            const progress = (submittedCount / quizData.totalQuestions) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
            
            const isSubmitted = quizData.submitted[question.id] !== undefined;
            
            const optionsList = document.getElementById('optionsList');
            optionsList.innerHTML = '';
            
            if (question.question_type === 'multiple_choice' || question.question_type === 'true_false') {
                question.options.forEach(option => {
                    const li = document.createElement('li');
                    li.className = 'option-item';
                    li.innerHTML = `
                        <label class="option-label">
                            <input type="radio" name="answer" value="${option.option_value}" class="option-radio" ${isSubmitted ? 'disabled' : ''}>
                            <span class="option-text">${option.option_text}</span>
                        </label>
                    `;
                    optionsList.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.className = 'option-item';
                li.innerHTML = `
                    <textarea name="answer" class="form-control" rows="5" ${isSubmitted ? 'disabled' : ''} placeholder="Type your answer here..."></textarea>
                `;
                optionsList.appendChild(li);
            }
            
            if (isSubmitted) {
                const submittedAnswer = quizData.submitted[question.id];
                if (question.question_type === 'multiple_choice' || question.question_type === 'true_false') {
                    const submittedRadio = document.querySelector(`input[value="${submittedAnswer}"]`);
                    if (submittedRadio) {
                        submittedRadio.checked = true;
                        submittedRadio.closest('.option-label').classList.add('selected');
                    }
                } else {
                    const textarea = document.querySelector('textarea[name="answer"]');
                    if (textarea) {
                        textarea.value = submittedAnswer;
                    }
                }
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('postponeBtn').disabled = true;
            } else if (quizData.answers[question.id]) {
                const savedAnswer = quizData.answers[question.id];
                if (question.question_type === 'multiple_choice' || question.question_type === 'true_false') {
                    const savedRadio = document.querySelector(`input[value="${savedAnswer}"]`);
                    if (savedRadio) {
                        savedRadio.checked = true;
                        savedRadio.closest('.option-label').classList.add('selected');
                    }
                } else {
                    const textarea = document.querySelector('textarea[name="answer"]');
                    if (textarea) {
                        textarea.value = savedAnswer;
                    }
                }
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('postponeBtn').disabled = false;
            } else {
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('postponeBtn').disabled = false;
            }
            
            setupOptionListeners();
        }

        function saveCurrentAnswer() {
            const questionId = getCurrentQuestionId();
            if (!questionId) return;
            
            const selectedRadio = document.querySelector('input[name="answer"]:checked');
            const textarea = document.querySelector('textarea[name="answer"]');
            
            if (selectedRadio) {
                quizData.answers[questionId] = selectedRadio.value;
            } else if (textarea) {
                quizData.answers[questionId] = textarea.value.trim();
            }
        }

        function setupOptionListeners() {
            document.querySelectorAll('.option-label').forEach(label => {
                label.onclick = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const questionId = getCurrentQuestionId();
                    if (questionId && quizData.submitted[questionId] !== undefined) {
                        return;
                    }
                    
                    document.querySelectorAll('.option-label').forEach(l => l.classList.remove('selected'));
                    this.classList.add('selected');
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        saveCurrentAnswer();
                    }
                };
            });
            
            document.querySelectorAll('input[type="radio"].option-radio').forEach(radio => {
                radio.onchange = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const questionId = getCurrentQuestionId();
                    if (questionId && quizData.submitted[questionId] !== undefined) {
                        this.checked = false;
                        return;
                    }
                    
                    document.querySelectorAll('.option-label').forEach(l => l.classList.remove('selected'));
                    if (this.checked) {
                        const label = this.closest('.option-label');
                        if (label) {
                            label.classList.add('selected');
                        }
                        saveCurrentAnswer();
                    }
                };
            });
        }

        // ============================================
        // SUBMIT QUESTION
        // ============================================
        async function submitQuestion() {
            const submitBtn = document.getElementById('submitBtn');
            if (!submitBtn || submitBtn.disabled) return;
            
            const questionId = getCurrentQuestionId();
            if (!questionId) {
                alert('Question ID not found.');
                return;
            }
            
            let answer = null;
            const selectedRadio = document.querySelector('input[name="answer"]:checked');
            const textarea = document.querySelector('textarea[name="answer"]');
            
            if (selectedRadio) {
                answer = selectedRadio.value;
            } else if (textarea) {
                // Get raw value first, then trim for validation
                answer = textarea.value;
            }
            
            // Validate answer exists
            if (!answer || (typeof answer === 'string' && answer.trim().length === 0)) {
                alert('Please select or enter an answer before submitting.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                return;
            }
            
            // Trim answer before sending (but keep original for display)
            if (typeof answer === 'string') {
                answer = answer.trim();
            }
            
            if (quizData.submitted[questionId]) {
                alert('This question has already been submitted.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                return;
            }
            
            // Validate required fields before disabling button
            if (!quizData.submissionId || !questionId) {
                alert('Missing required information. Please refresh the page and try again.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                return;
            }
            
            submitBtn.disabled = true;
            if (submitBtn.textContent) {
                submitBtn.textContent = 'Submitting...';
            }
            
            try {
                // Ensure answer is a string and not empty
                const answerToSend = (typeof answer === 'string') ? answer.trim() : String(answer || '');
                
                if (answerToSend.length === 0) {
                    alert('Please enter an answer before submitting.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        if (submitBtn.textContent) {
                            submitBtn.textContent = 'Submit';
                        }
                    }
                    return;
                }
                
                const response = await fetch('../../api/student/quiz/submit-question.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        submission_id: quizData.submissionId,
                        question_id: questionId,
                        answer: answerToSend,
                        csrf_token: quizData.csrfToken
                    })
                });
                
                let data;
                try {
                    data = await response.json();
                } catch (jsonError) {
                    // If response is not JSON, get text
                    const text = await response.text();
                    throw new Error(`Server error (${response.status}): ${text || 'Invalid response'}`);
                }
                
                if (!response.ok) {
                    // Get error message from response if available
                    const errorMsg = data && data.message 
                        ? data.message 
                        : (data && data.errors && data.errors.length > 0 
                            ? data.errors.join(', ') 
                            : `HTTP error! status: ${response.status}`);
                    throw new Error(errorMsg);
                }
                
                if (!data.success) {
                    const errorMsg = data.message || (data.errors && data.errors.length > 0 ? data.errors.join(', ') : 'Failed to submit question');
                    alert(errorMsg);
                    if (data.data && data.data.auto_submitted) {
                        window.location.href = `submit_quiz.php?quiz_id=${quizData.id}&auto_submit=1&reason=time_expired`;
                        return;
                    }
                    submitBtn.disabled = false;
                    return;
                }
                
                quizData.submitted[questionId] = answerToSend;
                timeRemaining = data.data.time_remaining || timeRemaining;
                
                const postponedIndex = quizData.postponed.indexOf(questionId);
                if (postponedIndex > -1) {
                    quizData.postponed.splice(postponedIndex, 1);
                }
                
                sessionStorage.setItem('quizSubmittedAnswers', JSON.stringify(quizData.submitted));
                sessionStorage.setItem('quizPostponedQuestions', JSON.stringify(quizData.postponed));
                
                if (data.data.all_submitted) {
                    // All questions submitted, automatically finalize
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        if (submitBtn.textContent) {
                            submitBtn.textContent = 'Submitting...';
                        }
                    }
                    await finalizeQuiz();
                } else {
                    // Re-enable button for next question
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        if (submitBtn.textContent) {
                            submitBtn.textContent = 'Submit';
                        }
                    }
                    if (data.data.next_question) {
                        const nextQuestion = data.data.next_question;
                        const existingIndex = quizData.questions.findIndex(q => q.id === nextQuestion.id);
                        if (existingIndex >= 0) {
                            quizData.questions[existingIndex] = nextQuestion;
                            quizData.questionMap[nextQuestion.id] = nextQuestion;
                            loadQuestionByData(nextQuestion, existingIndex + 1);
                        } else {
                            quizData.questions.push(nextQuestion);
                            quizData.questionMap[nextQuestion.id] = nextQuestion;
                            loadQuestionByData(nextQuestion, quizData.questions.length);
                        }
                    } else {
                        const nextQ = getNextUnansweredQuestion();
                        if (nextQ) {
                            loadQuestionByData(nextQ.question, nextQ.index);
                        } else {
                            if (quizData.postponed.length > 0) {
                                const postponedId = quizData.postponed[0];
                                const question = quizData.questionMap[postponedId];
                                if (question) {
                                    const index = quizData.questions.findIndex(q => q.id === postponedId) + 1;
                                    loadQuestionByData(question, index);
                                }
                            } else {
                                alert('All questions have been submitted.');
                                await finalizeQuiz();
                            }
                        }
                    }
                }
            } catch (error) {
                // Always re-enable button on error
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (submitBtn.textContent) {
                        submitBtn.textContent = 'Submit';
                    }
                }
                
                if (typeof console !== 'undefined' && console.error) {
                    console.error('Submit question error:', error);
                }
                
                // Show user-friendly error message
                let errorMsg = 'An error occurred. Please try again.';
                if (error.message) {
                    errorMsg = error.message;
                }
                
                alert(errorMsg + '\n\nIf the problem persists, please refresh the page and try again.');
            }
        }

        // ============================================
        // POSTPONE QUESTION
        // ============================================
        async function postponeQuestion() {
            if (typeof quizData === 'undefined' || !quizData) {
                alert('Quiz data not loaded. Please refresh the page.');
                return;
            }
            
            const postponeBtn = document.getElementById('postponeBtn');
            if (!postponeBtn || postponeBtn.disabled) return;
            
            const questionId = getCurrentQuestionId();
            if (!questionId) {
                alert('Question ID not found.');
                return;
            }
            
            if (quizData.submitted[questionId]) {
                alert('This question has already been submitted and cannot be postponed.');
                return;
            }
            
            let answer = null;
            const selectedRadio = document.querySelector('input[name="answer"]:checked');
            const textarea = document.querySelector('textarea[name="answer"]');
            
            if (selectedRadio) {
                answer = selectedRadio.value;
            } else if (textarea) {
                answer = textarea.value.trim();
            }
            
            postponeBtn.disabled = true;
            
            try {
                const response = await fetch('../../api/student/quiz/postpone-question.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        submission_id: quizData.submissionId,
                        question_id: questionId,
                        answer: answer,
                        csrf_token: quizData.csrfToken
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.success) {
                    alert(data.message || 'Failed to postpone question');
                    if (data.data && data.data.auto_submitted) {
                        window.location.href = `submit_quiz.php?quiz_id=${quizData.id}&auto_submit=1&reason=time_expired`;
                        return;
                    }
                    postponeBtn.disabled = false;
                    return;
                }
                
                if (!quizData.postponed.includes(questionId)) {
                    quizData.postponed.push(questionId);
                }
                if (answer) {
                    quizData.answers[questionId] = answer;
                }
                timeRemaining = data.data.time_remaining || timeRemaining;
                
                sessionStorage.setItem('quizPostponedQuestions', JSON.stringify(quizData.postponed));
                sessionStorage.setItem('quizAnswers', JSON.stringify(quizData.answers));
                
                if (data.data.next_question) {
                    const nextQuestion = data.data.next_question;
                    const existingIndex = quizData.questions.findIndex(q => q.id === nextQuestion.id);
                    if (existingIndex >= 0) {
                        quizData.questions[existingIndex] = nextQuestion;
                        quizData.questionMap[nextQuestion.id] = nextQuestion;
                        loadQuestionByData(nextQuestion, existingIndex + 1);
                    } else {
                        quizData.questions.push(nextQuestion);
                        quizData.questionMap[nextQuestion.id] = nextQuestion;
                        loadQuestionByData(nextQuestion, quizData.questions.length);
                    }
                } else {
                    const nextQ = getNextUnansweredQuestion();
                    if (nextQ) {
                        loadQuestionByData(nextQ.question, nextQ.index);
                    } else {
                        if (quizData.postponed.length > 0) {
                            const postponedId = quizData.postponed[0];
                            const question = quizData.questionMap[postponedId];
                            if (question) {
                                const index = quizData.questions.findIndex(q => q.id === postponedId) + 1;
                                loadQuestionByData(question, index);
                            }
                        } else {
                            if (confirm('All questions have been submitted. Do you want to submit the examination?')) {
                                await finalizeQuiz();
                            }
                        }
                    }
                }
            } catch (error) {
                postponeBtn.disabled = false;
                alert('An error occurred. Please try again.');
            }
        }

        // ============================================
        // FINALIZE QUIZ
        // ============================================
        async function finalizeQuiz() {
            saveCurrentAnswer();
            
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            if (validationInterval) {
                clearInterval(validationInterval);
            }
            
            sessionStorage.setItem('quizSubmitted', 'true');
            sessionStorage.setItem('quizAnswers', JSON.stringify(quizData.submitted));
            
            try {
                const response = await fetch('../../api/student/quiz/finalize.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        submission_id: quizData.submissionId,
                        csrf_token: quizData.csrfToken
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.success) {
                    // Even if finalize fails, try to redirect with saved answers
                    alert(data.message || 'Failed to finalize quiz, but your answers are saved. Redirecting...');
                    sessionStorage.removeItem('quizStarted');
                    sessionStorage.removeItem('submissionId');
                    sessionStorage.removeItem('quizSubmittedAnswers');
                    sessionStorage.removeItem('quizPostponedQuestions');
                    sessionStorage.removeItem('quizAnswers');
                    window.location.href = `submit_quiz.php?quiz_id=${quizData.id}`;
                    return;
                }
                
                const allAnswers = {};
                Object.keys(quizData.submitted).forEach(qId => {
                    allAnswers[qId] = quizData.submitted[qId];
                });
                
                // Normalize questions data for API (convert question_type to type, question_text to question)
                const normalizedQuestions = quizData.questions.map(q => {
                    let criteriaData = q.criteria ? (typeof q.criteria === 'string' ? JSON.parse(q.criteria) : q.criteria) : null;
                    
                    // Extract model_answer from criteria if it exists
                    let modelAnswer = null;
                    if (criteriaData) {
                        if (typeof criteriaData === 'object' && criteriaData !== null) {
                            if (criteriaData.model_answer) {
                                modelAnswer = criteriaData.model_answer;
                            } else if (Array.isArray(criteriaData) && criteriaData.length > 0 && typeof criteriaData[0] === 'object' && criteriaData[0].model_answer) {
                                modelAnswer = criteriaData[0].model_answer;
                            }
                        }
                    }
                    
                    return {
                        id: q.id,
                        question: q.question_text || q.question,
                        question_text: q.question_text || q.question,
                        type: q.question_type || q.type,
                        question_type: q.question_type || q.type,
                        max_marks: q.max_marks || q.marks || 1,
                        marks: q.marks || 1,
                        criteria: criteriaData,
                        model_answer: modelAnswer || q.model_answer || null
                    };
                });
                
                // Debug: Log what we're sending
                if (typeof console !== 'undefined' && console.log) {
                    console.log('Submitting quiz with:', {
                        quiz_id: quizData.id,
                        submission_id: quizData.submissionId,
                        answers_count: Object.keys(allAnswers).length,
                        questions_count: normalizedQuestions.length,
                        questions: normalizedQuestions
                    });
                }
                
                // Submit quiz with AI evaluation (don't wait for it to complete)
                fetch('../../api/student/submit_quiz.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        quiz_id: quizData.id,
                        submission_id: quizData.submissionId,
                        answers: allAnswers,
                        questions: normalizedQuestions,
                        ai_provider: quizData.aiProvider || 'gemini',
                        ai_model: quizData.aiModel || null,
                        auto_submit: false,
                        reason: ''
                    })
                }).then(submitResponse => {
                    if (submitResponse.ok) {
                        return submitResponse.json();
                    }
                    return null;
                }).then(submitResult => {
                    if (submitResult && submitResult.success && submitResult.ai_evaluation) {
                        sessionStorage.setItem('aiEvaluationResults', JSON.stringify(submitResult.ai_evaluation));
                    }
                }).catch(err => {
                    // Ignore errors in AI submission - it will be processed on result page
                    if (typeof console !== 'undefined' && console.error) {
                        console.error('AI submission error (non-critical):', err);
                    }
                });
                
                sessionStorage.removeItem('quizStarted');
                sessionStorage.removeItem('submissionId');
                sessionStorage.removeItem('quizSubmittedAnswers');
                sessionStorage.removeItem('quizPostponedQuestions');
                sessionStorage.removeItem('quizAnswers');
                
                // Redirect to result page
                const redirectUrl = (data.data && data.data.redirect_url) 
                    ? data.data.redirect_url 
                    : `submit_quiz.php?quiz_id=${quizData.id}`;
                window.location.href = redirectUrl;
            } catch (error) {
                if (typeof console !== 'undefined' && console.error) {
                    console.error('Finalize quiz error:', error);
                }
                // Even if there's an error, try to redirect to result page
                sessionStorage.removeItem('quizStarted');
                sessionStorage.removeItem('submissionId');
                sessionStorage.removeItem('quizSubmittedAnswers');
                sessionStorage.removeItem('quizPostponedQuestions');
                sessionStorage.removeItem('quizAnswers');
                
                // Show error but still redirect
                alert('There was an issue finalizing the quiz, but your answers have been saved. Redirecting to results...');
                window.location.href = `submit_quiz.php?quiz_id=${quizData.id}`;
            }
        }

        // ============================================
        // AUTO SUBMIT QUIZ
        // ============================================
        async function autoSubmitQuiz(reason) {
            if (sessionStorage.getItem('quizSubmitted') === 'true') {
                return;
            }
            
            if (typeof saveCurrentAnswer === 'function') {
                saveCurrentAnswer();
            }
            
            const allAnswers = Object.assign({}, quizData.submitted, quizData.answers);
            
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            
            sessionStorage.setItem('quizSubmitted', 'true');
            sessionStorage.setItem('submitReason', reason || 'time_up');
            sessionStorage.setItem('quizAnswers', JSON.stringify(allAnswers));
            
            if (reason === 'time_up' || !reason) {
                alert('Time is up! Your examination is being submitted automatically.');
            }
            
            const submitUrl = 'submit_quiz.php?quiz_id=' + quizData.id + '&auto_submit=1&reason=' + (reason || 'time_up');
            
            try {
                if (quizData.submissionId) {
                    await fetch('../../api/student/quiz/finalize.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            submission_id: quizData.submissionId
                        })
                    });
                }
                
                const submitResponse = await fetch('../../api/student/submit_quiz.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        quiz_id: quizData.id,
                        submission_id: quizData.submissionId,
                        answers: allAnswers,
                        questions: quizData.questions,
                        ai_provider: quizData.aiProvider || 'gemini',
                        ai_model: quizData.aiModel || null,
                        auto_submit: true,
                        reason: reason || 'time_up'
                    })
                });
                
                if (submitResponse.ok) {
                    const submitResult = await submitResponse.json();
                    if (submitResult.success && submitResult.ai_evaluation) {
                        sessionStorage.setItem('aiEvaluationResults', JSON.stringify(submitResult.ai_evaluation));
                    }
                }
            } catch (error) {
                // Ignore
            }
            
            window.location.replace(submitUrl);
            
            setTimeout(function() {
                if (window.location.pathname.indexOf('submit_quiz.php') === -1) {
                    window.location.replace(submitUrl);
                }
            }, 1000);
        }

        // ============================================
        // EXPOSE FUNCTIONS GLOBALLY
        // ============================================
        window.autoSubmitQuiz = autoSubmitQuiz;
        window.quizData = quizData;
        window.saveCurrentAnswer = saveCurrentAnswer;
        window.timerInterval = timerInterval;
        window.submitQuestion = submitQuestion;
        window.postponeQuestion = postponeQuestion;

        // ============================================
        // INITIALIZATION
        // ============================================
        function initQuiz() {
            if (!quizData.id || !quizData.submissionId) {
                window.location.href = 'available_quizzes.php';
                return;
            }
            
            sessionStorage.setItem('quizStarted', 'true');
            sessionStorage.setItem('submissionId', quizData.submissionId);
            sessionStorage.setItem('quizStartTime', Date.now().toString());
            sessionStorage.removeItem('quizSubmitted');
            sessionStorage.removeItem('submitReason');
            sessionStorage.removeItem('submitMessage');
            sessionStorage.removeItem('refreshAttempt');
            
            try {
                const element = document.documentElement;
                if (element.requestFullscreen) {
                    element.requestFullscreen().catch(() => {});
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }
            } catch (e) {
                // Ignore
            }
            
            startTimer();
            
            if (quizData.questions.length > 0) {
                const firstQuestionId = quizData.currentQuestionId || quizData.questions[0].id;
                const isSubmitted = quizData.submitted[firstQuestionId] !== undefined;
                
                const submitBtn = document.getElementById('submitBtn');
                const postponeBtn = document.getElementById('postponeBtn');
                if (submitBtn && postponeBtn) {
                    submitBtn.disabled = isSubmitted;
                    postponeBtn.disabled = isSubmitted;
                }
                
                setupOptionListeners();
            }
        }

        function attachButtonListeners() {
            const submitBtn = document.getElementById('submitBtn');
            const postponeBtn = document.getElementById('postponeBtn');
            
            if (!submitBtn || !postponeBtn) {
                setTimeout(attachButtonListeners, 100);
                return;
            }
            
            submitBtn.onclick = async function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (this.disabled) return false;
                try {
                    await submitQuestion();
                } catch (error) {
                    this.disabled = false;
                    alert('An error occurred. Please try again.');
                }
                return false;
            };
            
            postponeBtn.onclick = async function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (this.disabled) return false;
                try {
                    await postponeQuestion();
                } catch (error) {
                    this.disabled = false;
                    alert('An error occurred. Please try again.');
                }
                return false;
            };
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initQuiz();
                setTimeout(attachButtonListeners, 100);
            });
        } else {
            initQuiz();
            setTimeout(attachButtonListeners, 100);
        }
        
        // Backup initialization
        window.addEventListener('load', function() {
            if (!timerInterval) {
                try {
                    startTimer();
                } catch (e) {
                    // Ignore
                }
            }
            attachButtonListeners();
        });
    </script>
</body>
</html>
