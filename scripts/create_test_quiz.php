<?php
/**
 * Create 5-Minute Test Quiz for Students
 * This script creates a test quiz with 5 questions and 5 minutes duration
 * 
 * Usage: Run this script once to create the test quiz
 * php scripts/create_test_quiz.php
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Get first available teacher ID (or use 1 as default)
    $stmt = $conn->query("SELECT id FROM teachers LIMIT 1");
    $teacher = $stmt->fetch();
    $teacherId = $teacher ? (int)$teacher['id'] : 1;
    
    // Check if test quiz already exists
    $stmt = $conn->prepare("SELECT id FROM quizzes WHERE title = '5-Minute Test Quiz' AND status = 'published'");
    $stmt->execute();
    $existingQuiz = $stmt->fetch();
    
    if ($existingQuiz) {
        echo "Test quiz already exists with ID: " . $existingQuiz['id'] . "\n";
        echo "To create a new one, delete the existing quiz first.\n";
        $conn->rollBack();
        exit;
    }
    
    // Quiz data
    $title = "5-Minute Test Quiz";
    $subject = "General Knowledge";
    $description = "A quick 5-minute test quiz for students to practice";
    $duration = 5 * 60; // 5 minutes in seconds (300 seconds)
    $totalQuestions = 5;
    $totalMarks = 25; // 5 marks per question
    $status = 'published'; // Published so students can access it
    $aiProvider = 'gemini';
    $aiModel = null;
    $organizationId = null;
    
    // Insert quiz
    $stmt = $conn->prepare("
        INSERT INTO quizzes (
            title, subject, description, duration, total_questions, total_marks,
            created_by, organization_id, ai_provider, ai_model, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $title,
        $subject,
        $description,
        $duration,
        $totalQuestions,
        $totalMarks,
        $teacherId,
        $organizationId,
        $aiProvider,
        $aiModel,
        $status
    ]);
    
    $quizId = $conn->lastInsertId();
    echo "Quiz created with ID: $quizId\n";
    
    // Sample questions for the test quiz
    $questions = [
        [
            'question' => 'What is the capital of France?',
            'type' => 'multiple_choice',
            'marks' => 5,
            'options' => [
                'a' => 'London',
                'b' => 'Berlin',
                'c' => 'Paris',
                'd' => 'Madrid'
            ],
            'correct_answer' => 'c'
        ],
        [
            'question' => 'Which planet is known as the Red Planet?',
            'type' => 'multiple_choice',
            'marks' => 5,
            'options' => [
                'a' => 'Venus',
                'b' => 'Mars',
                'c' => 'Jupiter',
                'd' => 'Saturn'
            ],
            'correct_answer' => 'b'
        ],
        [
            'question' => 'What is 2 + 2?',
            'type' => 'multiple_choice',
            'marks' => 5,
            'options' => [
                'a' => '3',
                'b' => '4',
                'c' => '5',
                'd' => '6'
            ],
            'correct_answer' => 'b'
        ],
        [
            'question' => 'Which is the largest ocean?',
            'type' => 'multiple_choice',
            'marks' => 5,
            'options' => [
                'a' => 'Atlantic Ocean',
                'b' => 'Indian Ocean',
                'c' => 'Arctic Ocean',
                'd' => 'Pacific Ocean'
            ],
            'correct_answer' => 'd'
        ],
        [
            'question' => 'What is the chemical symbol for water?',
            'type' => 'multiple_choice',
            'marks' => 5,
            'options' => [
                'a' => 'H2O',
                'b' => 'CO2',
                'c' => 'O2',
                'd' => 'NaCl'
            ],
            'correct_answer' => 'a'
        ]
    ];
    
    // Insert questions
    $questionOrder = 1;
    foreach ($questions as $questionData) {
        $questionText = $questionData['question'];
        $questionType = $questionData['type'];
        $marks = $questionData['marks'];
        $maxMarks = $marks;
        
        // Insert question
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
            null
        ]);
        
        $questionId = $conn->lastInsertId();
        echo "Question $questionOrder inserted with ID: $questionId\n";
        
        // Insert options for multiple choice questions
        if ($questionType === 'multiple_choice') {
            $options = ['a', 'b', 'c', 'd'];
            $correctAnswer = strtolower(trim($questionData['correct_answer']));
            
            foreach ($options as $index => $optionKey) {
                $optionText = $questionData['options'][$optionKey] ?? '';
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
            echo "  Options inserted for question $questionOrder\n";
        }
        
        $questionOrder++;
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "\n✅ SUCCESS: Test quiz created successfully!\n";
    echo "Quiz ID: $quizId\n";
    echo "Title: $title\n";
    echo "Duration: 5 minutes (300 seconds)\n";
    echo "Total Questions: $totalQuestions\n";
    echo "Total Marks: $totalMarks\n";
    echo "Status: Published (students can access it)\n";
    echo "\nStudents can now access this quiz from their dashboard.\n";
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>

