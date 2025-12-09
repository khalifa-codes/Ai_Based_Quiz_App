<?php
/**
 * Student Quiz Submission API
 * Handles quiz submission including auto-submissions from security violations
 * Integrates AI evaluation for subjective questions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Increase execution time for AI evaluation (can take time)
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if student is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['quiz_id']) || !isset($input['answers'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Quiz ID and answers are required']);
    exit();
}

$quizId = intval($input['quiz_id']);
$answers = $input['answers']; // Should be an object/array of question_id => answer

// Validate answers array is not empty (unless auto-submit)
if (empty($answers) || !is_array($answers)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Answers are required',
        'errors' => ['Answers array cannot be empty. Please provide at least one answer.']
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

$questions = isset($input['questions']) ? $input['questions'] : []; // Question metadata (type, max_marks, etc.)
$autoSubmit = isset($input['auto_submit']) ? (bool)$input['auto_submit'] : false;
$reason = isset($input['reason']) ? $input['reason'] : '';
$aiProvider = isset($input['ai_provider']) ? $input['ai_provider'] : 'gemini'; // AI provider to use
$aiModel = isset($input['ai_model']) ? $input['ai_model'] : null; // Specific AI model (optional)

// Include AI service for subjective question evaluation
$aiServicePath = __DIR__ . '/../../includes/ai_service.php';
if (file_exists($aiServicePath)) {
    require_once $aiServicePath;
    require_once __DIR__ . '/../../config/ai_config.php';
}

// If questions array is empty, fetch from database
if (empty($questions)) {
    error_log("⚠️ Questions array is empty, fetching from database...");
    require_once __DIR__ . '/../../config/database.php';
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("
            SELECT id, question_text, question_type, marks, max_marks, criteria
            FROM questions
            WHERE quiz_id = ?
            ORDER BY question_order ASC
        ");
        $stmt->execute([$quizId]);
        $dbQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Normalize questions format
        foreach ($dbQuestions as $q) {
            $criteriaData = null;
            if (!empty($q['criteria'])) {
                $criteriaData = json_decode($q['criteria'], true);
            }
            
            $questions[] = [
                'id' => $q['id'],
                'question' => $q['question_text'],
                'question_text' => $q['question_text'],
                'type' => $q['question_type'],
                'question_type' => $q['question_type'],
                'max_marks' => $q['max_marks'] ?? $q['marks'] ?? 1,
                'marks' => $q['marks'] ?? 1,
                'criteria' => $criteriaData
            ];
        }
        error_log("✅ Fetched " . count($questions) . " questions from database");
    } catch (Exception $e) {
        error_log("Error fetching questions from database: " . $e->getMessage());
    }
}

// Debug logging
error_log("Submit Quiz API - Quiz ID: {$quizId}, Answers: " . count($answers) . ", Questions: " . count($questions));

// TODO: Replace this with your actual database submission logic
// This is a placeholder - you should:
// 1. Validate quiz_id exists and student has access
// 2. Validate answers format
// 3. Save answers to database
// 4. Evaluate subjective questions with AI
// 5. Store AI evaluation results
// 6. Calculate total score
// 7. Mark quiz as submitted
// 8. Return success response with evaluation results

// AI Evaluation Results Storage
$aiEvaluationResults = [];

// Debug logging
error_log("AI Evaluation Check: class_exists(AIService)=" . (class_exists('AIService') ? 'YES' : 'NO') . ", questions_count=" . count($questions ?? []));

// Process each answer and evaluate subjective questions
if (class_exists('AIService') && !empty($questions)) {
    error_log("Starting AI evaluation for " . count($questions) . " questions");
    try {
        $aiService = new AIService($aiProvider, null, $aiModel);
        error_log("AIService initialized with provider: {$aiProvider}");
        
        foreach ($answers as $questionId => $answer) {
            // Find question metadata
            $questionData = null;
            foreach ($questions as $q) {
                if (isset($q['id']) && $q['id'] == $questionId) {
                    $questionData = $q;
                    break;
                }
            }
            
            // Only evaluate subjective questions (type: 'subjective', 'essay', 'long_answer', etc.)
            if ($questionData) {
                // Check both 'type' and 'question_type' fields
                $questionType = strtolower($questionData['type'] ?? $questionData['question_type'] ?? '');
                $isSubjective = in_array($questionType, ['subjective', 'essay', 'long_answer', 'short_answer', 'descriptive']);
                
                if ($isSubjective && !empty(trim($answer))) {
                    $questionText = $questionData['question'] ?? $questionData['question_text'] ?? '';
                    $maxMarks = isset($questionData['max_marks']) ? intval($questionData['max_marks']) : (isset($questionData['marks']) ? intval($questionData['marks']) : 10);
                    // Extract criteria and model answer from questionData
                    $criteria = ['accuracy', 'completeness', 'clarity', 'logic', 'examples', 'structure'];
                    $modelAnswer = null;
                    
                    // Handle criteria - can be array, JSON string, or object with criteria key
                    if (isset($questionData['criteria']) && !empty($questionData['criteria'])) {
                        $criteriaData = is_string($questionData['criteria']) ? json_decode($questionData['criteria'], true) : $questionData['criteria'];
                        
                        if (is_array($criteriaData)) {
                            // Check if it's an object with 'criteria' and 'model_answer' keys
                            if (isset($criteriaData['criteria']) && is_array($criteriaData['criteria'])) {
                                $criteria = $criteriaData['criteria'];
                            } elseif (isset($criteriaData[0]) && is_string($criteriaData[0])) {
                                // It's an array of criteria strings
                                $criteria = $criteriaData;
                            }
                            
                            // Extract model answer
                            if (isset($criteriaData['model_answer']) && !empty($criteriaData['model_answer'])) {
                                $modelAnswer = $criteriaData['model_answer'];
                            }
                        }
                    }
                    
                    // Also check model_answer in metadata or direct field
                    if (!$modelAnswer) {
                        if (isset($questionData['model_answer']) && !empty($questionData['model_answer'])) {
                            $modelAnswer = $questionData['model_answer'];
                        } elseif (isset($questionData['metadata']) && !empty($questionData['metadata'])) {
                            $metadata = is_string($questionData['metadata']) ? json_decode($questionData['metadata'], true) : $questionData['metadata'];
                            if (isset($metadata['model_answer']) && !empty($metadata['model_answer'])) {
                                $modelAnswer = $metadata['model_answer'];
                            }
                        }
                    }
                    
                    // Log for debugging
                    error_log("Evaluating Question {$questionId}: Type={$questionType}, MaxMarks={$maxMarks}, HasModelAnswer=" . ($modelAnswer ? 'YES' : 'NO') . ", Criteria=" . implode(',', $criteria));
                    
                    try {
                        // Set timeout for this evaluation
                        $startTime = microtime(true);
                        
                        // Evaluate answer using AI with model answer
                        $evaluation = $aiService->evaluateAnswer($questionText, $answer, $maxMarks, $criteria, $aiModel, $modelAnswer);
                        
                        $endTime = microtime(true);
                        $duration = round(($endTime - $startTime), 2);
                        
                        // AI service returns 'total_marks' as the score given by AI
                        // This is the marks awarded, not the maximum marks
                        $marksAwarded = isset($evaluation['total_marks']) ? floatval($evaluation['total_marks']) : 0;
                        
                        // Ensure marks are within valid range
                        if ($marksAwarded < 0) {
                            $marksAwarded = 0;
                        }
                        if ($marksAwarded > $maxMarks) {
                            $marksAwarded = $maxMarks;
                        }
                        
                        // Store evaluation result
                        $aiEvaluationResults[$questionId] = [
                            'question_id' => $questionId,
                            'marks' => $marksAwarded,
                            'total_marks' => $maxMarks,
                            'feedback' => $evaluation['feedback'] ?? '',
                            'criteria_scores' => $evaluation['criteria_scores'] ?? [],
                            'provider' => $aiProvider,
                            'model' => $aiModel ?? $aiService->getDefaultModel($aiProvider),
                            'evaluated_at' => date('Y-m-d H:i:s')
                        ];
                        
                        // Log for debugging
                        error_log("✅ AI Evaluation Success - Question {$questionId}: Awarded {$marksAwarded}/{$maxMarks} marks (Duration: {$duration}s)");
                    } catch (Exception $e) {
                        // Log error but continue with other questions
                        $errorMsg = $e->getMessage();
                        error_log("❌ AI evaluation failed for question {$questionId}: {$errorMsg}");
                        
                        // Check if it's a timeout
                        if (stripos($errorMsg, 'timeout') !== false || stripos($errorMsg, 'execution time') !== false) {
                            error_log("⚠️ TIMEOUT: AI evaluation took too long for question {$questionId}");
                        }
                        
                        $aiEvaluationResults[$questionId] = [
                            'question_id' => $questionId,
                            'error' => 'AI evaluation failed: ' . $errorMsg,
                            'marks' => 0,
                            'total_marks' => $maxMarks
                        ];
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("AI service initialization failed: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        // Continue without AI evaluation
    }
} else {
    error_log("AI evaluation SKIPPED: AIService class not found OR questions array is empty");
    if (empty($questions)) {
        error_log("Questions array is empty! Questions data was not sent from frontend.");
    }
}

// Calculate total AI marks
$totalAiMarks = 0;
$totalMaxMarks = 0;
foreach ($aiEvaluationResults as $result) {
    if (!isset($result['error'])) {
        $totalAiMarks += $result['marks'] ?? 0;
        $totalMaxMarks += $result['total_marks'] ?? 0;
    }
}

// Save to database
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $conn->beginTransaction();
    
    $studentId = intval($_SESSION['user_id']);
    $submissionId = isset($input['submission_id']) ? intval($input['submission_id']) : null;
    
    // Get or create submission
    if (!$submissionId) {
        // Find existing submission
        $stmt = $conn->prepare("SELECT id FROM quiz_submissions WHERE quiz_id = ? AND student_id = ? AND status = 'in_progress' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$quizId, $studentId]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        $submissionId = $submission ? $submission['id'] : null;
    }
    
    if (!$submissionId) {
        // Create new submission
        $stmt = $conn->prepare("
            INSERT INTO quiz_submissions (quiz_id, student_id, started_at, status)
            VALUES (?, ?, NOW(), 'in_progress')
        ");
        $stmt->execute([$quizId, $studentId]);
        $submissionId = $conn->lastInsertId();
    }
    
    // Calculate time taken
    $stmt = $conn->prepare("SELECT started_at FROM quiz_submissions WHERE id = ?");
    $stmt->execute([$submissionId]);
    $submissionData = $stmt->fetch(PDO::FETCH_ASSOC);
    $timeTaken = $submissionData ? (time() - strtotime($submissionData['started_at'])) : 0;
    
    // Save each answer
    foreach ($answers as $questionId => $answer) {
        $stmt = $conn->prepare("
            INSERT INTO student_answers (submission_id, question_id, answer_value, is_postponed)
            VALUES (?, ?, ?, FALSE)
            ON DUPLICATE KEY UPDATE answer_value = VALUES(answer_value), is_postponed = FALSE
        ");
        $stmt->execute([$submissionId, $questionId, $answer]);
        
        // Get answer_id for AI evaluation
        $stmt = $conn->prepare("SELECT id FROM student_answers WHERE submission_id = ? AND question_id = ?");
        $stmt->execute([$submissionId, $questionId]);
        $answerData = $stmt->fetch(PDO::FETCH_ASSOC);
        $answerId = $answerData['id'];
        
        // Save AI evaluation if exists
        if (isset($aiEvaluationResults[$questionId]) && !isset($aiEvaluationResults[$questionId]['error'])) {
            $eval = $aiEvaluationResults[$questionId];
            $criteriaScoresJson = json_encode($eval['criteria_scores'] ?? []);
            
            $stmt = $conn->prepare("
                INSERT INTO ai_evaluations (
                    answer_id, question_id, submission_id,
                    ai_provider, ai_model, total_score, total_marks,
                    feedback, criteria_scores, evaluated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    ai_provider = VALUES(ai_provider),
                    ai_model = VALUES(ai_model),
                    total_score = VALUES(total_score),
                    total_marks = VALUES(total_marks),
                    feedback = VALUES(feedback),
                    criteria_scores = VALUES(criteria_scores),
                    evaluated_at = NOW()
            ");
            $stmt->execute([
                $answerId,
                $questionId,
                $submissionId,
                $eval['provider'] ?? $aiProvider,
                $eval['model'] ?? ($aiModel ?? 'default'),
                $eval['marks'] ?? 0,
                $eval['total_marks'] ?? 0,
                $eval['feedback'] ?? '',
                $criteriaScoresJson
            ]);
            
            // Update answer with AI score
            $stmt = $conn->prepare("UPDATE student_answers SET ai_score = ? WHERE id = ?");
            $stmt->execute([$eval['marks'] ?? 0, $answerId]);
        }
    }
    
    // Calculate MCQ scores
    $stmt = $conn->prepare("
        SELECT SUM(q.marks) as total_marks,
               SUM(CASE WHEN qo.is_correct = 1 THEN q.marks ELSE 0 END) as total_score
        FROM student_answers sa
        INNER JOIN questions q ON sa.question_id = q.id
        LEFT JOIN question_options qo ON q.id = qo.question_id AND sa.answer_value = qo.option_value
        WHERE sa.submission_id = ? AND q.question_type IN ('multiple_choice', 'true_false')
    ");
    $stmt->execute([$submissionId]);
    $mcqResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $mcqScore = floatval($mcqResult['total_score'] ?? 0);
    $mcqTotalMarks = floatval($mcqResult['total_marks'] ?? 0);
    
    // Calculate total score (MCQ + AI)
    $totalScore = $mcqScore + $totalAiMarks;
    $totalQuizMarks = $mcqTotalMarks + $totalMaxMarks;
    $percentage = $totalQuizMarks > 0 ? round(($totalScore / $totalQuizMarks) * 100, 2) : 0;
    $aiPercentage = $totalMaxMarks > 0 ? round(($totalAiMarks / $totalMaxMarks) * 100, 2) : 0;
    
    // Update submission with final results
    $status = $autoSubmit ? 'auto_submitted' : 'submitted';
    $stmt = $conn->prepare("
        UPDATE quiz_submissions
        SET status = ?,
            submitted_at = NOW(),
            time_taken = ?,
            total_score = ?,
            percentage = ?,
            total_ai_marks = ?,
            total_max_marks = ?,
            ai_percentage = ?,
            ai_provider = ?,
            ai_model = ?,
            auto_submitted = ?,
            auto_submit_reason = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $status,
        $timeTaken,
        $totalScore,
        $percentage,
        $totalAiMarks,
        $totalMaxMarks,
        $aiPercentage,
        $aiProvider,
        $aiModel ?? 'default',
        $autoSubmit ? 1 : 0,
        $reason,
        $submissionId
    ]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Quiz submitted successfully',
        'quiz_id' => $quizId,
        'submission_id' => $submissionId,
        'auto_submit' => $autoSubmit,
        'reason' => $reason,
        'ai_evaluation' => [
            'evaluated' => !empty($aiEvaluationResults),
            'total_marks' => $totalAiMarks,
            'total_max_marks' => $totalMaxMarks,
            'percentage' => $aiPercentage,
            'results' => $aiEvaluationResults,
            'provider' => $aiProvider,
            'model' => $aiModel ?? 'default'
        ],
        'overall' => [
            'total_score' => $totalScore,
            'total_marks' => $totalQuizMarks,
            'percentage' => $percentage,
            'mcq_score' => $mcqScore,
            'mcq_marks' => $mcqTotalMarks
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Quiz submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to submit quiz: ' . $e->getMessage()
    ]);
}

