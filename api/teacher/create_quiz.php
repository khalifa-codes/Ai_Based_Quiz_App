<?php
/**
 * Create Quiz API Endpoint
 * Handles quiz creation with questions and options
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once '../../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit();
    }
    
    // Validate required fields
    $requiredFields = ['title', 'duration', 'total_marks', 'questions'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit();
        }
    }
    
    $teacherId = (int)$_SESSION['user_id'];
    $organizationId = isset($_SESSION['teacher_org_id']) ? (int)$_SESSION['teacher_org_id'] : null;
    
    // Get quiz data
    $title = trim($input['title']);
    $subject = isset($input['subject']) ? trim($input['subject']) : null;
    $description = isset($input['description']) ? trim($input['description']) : $title;
    $duration = (int)$input['duration']; // in minutes, convert to seconds
    $totalMarks = (int)$input['total_marks'];
    $quizCode = isset($input['quiz_code']) ? trim($input['quiz_code']) : null;
    $quizType = isset($input['quiz_type']) ? trim($input['quiz_type']) : 'objective';
    // Handle status - can be string 'published'/'draft' or boolean
    $status = 'draft';
    if (isset($input['status'])) {
        if (is_string($input['status'])) {
            $status = $input['status'];
        } elseif (is_bool($input['status']) && $input['status']) {
            $status = 'published';
        } elseif ($input['status'] === 'published' || $input['status'] === true) {
            $status = 'published';
        }
    }
    $aiProvider = isset($input['ai_provider']) ? trim($input['ai_provider']) : 'gemini';
    $aiModel = isset($input['ai_model']) ? trim($input['ai_model']) : null;
    $criteria = isset($input['criteria']) ? $input['criteria'] : [];
    
    // Get questions
    $questions = $input['questions'];
    $totalQuestions = count($questions);
    
    if ($totalQuestions === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'At least one question is required']);
        exit();
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Insert quiz
        $stmt = $conn->prepare("
            INSERT INTO quizzes (
                title, subject, description, duration, total_questions, total_marks,
                created_by, organization_id, ai_provider, ai_model, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $durationSeconds = $duration * 60; // Convert minutes to seconds
        
        $stmt->execute([
            $title,
            $subject,
            $description,
            $durationSeconds,
            $totalQuestions,
            $totalMarks,
            $teacherId,
            $organizationId,
            $aiProvider,
            $aiModel,
            $status
        ]);
        
        $quizId = $conn->lastInsertId();
        
        // Insert questions
        $questionOrder = 1;
        foreach ($questions as $questionData) {
            $questionText = trim($questionData['question']);
            $questionType = isset($questionData['type']) ? $questionData['type'] : ($quizType === 'subjective' ? 'subjective' : 'multiple_choice');
            $marks = isset($questionData['points']) ? (int)$questionData['points'] : (isset($questionData['marks']) ? (int)$questionData['marks'] : 1);
            $maxMarks = isset($questionData['max_marks']) ? (int)$questionData['max_marks'] : $marks;
            
            // Build criteria JSON for subjective questions
            $criteriaJson = null;
            if ($questionType === 'subjective' && !empty($criteria)) {
                $criteriaJson = json_encode($criteria);
            }
            
            $stmt = $conn->prepare("
                INSERT INTO questions (
                    quiz_id, question_text, question_type, question_order, marks, max_marks, criteria
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $quizId,
                $questionText,
                $questionType,
                $questionOrder,
                $marks,
                $maxMarks,
                $criteriaJson
            ]);
            
            $questionId = $conn->lastInsertId();
            
            // Insert options for multiple choice questions
            if ($questionType === 'multiple_choice' || $questionType === 'objective') {
                $options = ['a', 'b', 'c', 'd'];
                $correctAnswer = isset($questionData['correct_answer']) ? strtolower(trim($questionData['correct_answer'])) : '';
                
                foreach ($options as $index => $optionKey) {
                    $optionText = isset($questionData["option_$optionKey"]) ? trim($questionData["option_$optionKey"]) : '';
                    $isCorrect = ($optionKey === $correctAnswer) ? 1 : 0;
                    
                    if (!empty($optionText)) {
                        $stmt = $conn->prepare("
                            INSERT INTO question_options (
                                question_id, option_text, option_value, is_correct, option_order
                            ) VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $questionId,
                            $optionText,
                            $optionKey,
                            $isCorrect,
                            $index + 1
                        ]);
                    }
                }
            }
            
            $questionOrder++;
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Quiz created successfully',
            'data' => [
                'quiz_id' => $quizId,
                'title' => $title,
                'total_questions' => $totalQuestions,
                'status' => $status
            ]
        ]);
        
    } catch (PDOException $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Quiz Creation Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Quiz Creation Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
}
?>

