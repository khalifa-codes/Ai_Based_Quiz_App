<?php
/**
 * Create 5-Minute Basic Maths Quiz for Students
 * 5 questions, 5 minutes duration, 5 total marks (1 mark per question)
 * 
 * Usage: php scripts/create_maths_quiz.php
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Start transaction
    $conn->beginTransaction();
    
    // Get first available teacher ID
    $stmt = $conn->query("SELECT id FROM teachers LIMIT 1");
    $teacher = $stmt->fetch();
    $teacherId = $teacher ? (int)$teacher['id'] : 1;
    
    // Check if maths quiz already exists
    $stmt = $conn->prepare("SELECT id FROM quizzes WHERE title = 'Basic Maths Quiz - 5 Minutes' AND status = 'published'");
    $stmt->execute();
    $existingQuiz = $stmt->fetch();
    
    if ($existingQuiz) {
        echo "Maths quiz already exists with ID: " . $existingQuiz['id'] . "\n";
        echo "To create a new one, delete the existing quiz first.\n";
        $conn->rollBack();
        exit;
    }
    
    // Quiz data
    $title = "Basic Maths Quiz - 5 Minutes";
    $subject = "Mathematics";
    $description = "A quick 5-minute basic mathematics quiz for students";
    $duration = 5 * 60; // 5 minutes in seconds (300 seconds)
    $totalQuestions = 5;
    $totalMarks = 5; // 1 mark per question
    $status = 'published';
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
    
    // Basic Maths questions
    $questions = [
        [
            'question' => 'What is 5 + 3?',
            'type' => 'multiple_choice',
            'marks' => 1,
            'options' => [
                'a' => '6',
                'b' => '7',
                'c' => '8',
                'd' => '9'
            ],
            'correct_answer' => 'c'
        ],
        [
            'question' => 'What is 10 - 4?',
            'type' => 'multiple_choice',
            'marks' => 1,
            'options' => [
                'a' => '4',
                'b' => '5',
                'c' => '6',
                'd' => '7'
            ],
            'correct_answer' => 'c'
        ],
        [
            'question' => 'What is 3 × 4?',
            'type' => 'multiple_choice',
            'marks' => 1,
            'options' => [
                'a' => '10',
                'b' => '11',
                'c' => '12',
                'd' => '13'
            ],
            'correct_answer' => 'c'
        ],
        [
            'question' => 'What is 15 ÷ 3?',
            'type' => 'multiple_choice',
            'marks' => 1,
            'options' => [
                'a' => '3',
                'b' => '4',
                'c' => '5',
                'd' => '6'
            ],
            'correct_answer' => 'c'
        ],
        [
            'question' => 'What is 2² (2 squared)?',
            'type' => 'multiple_choice',
            'marks' => 1,
            'options' => [
                'a' => '2',
                'b' => '3',
                'c' => '4',
                'd' => '5'
            ],
            'correct_answer' => 'c'
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
    
    echo "\n✅ SUCCESS: Basic Maths Quiz created successfully!\n";
    echo "Quiz ID: $quizId\n";
    echo "Title: $title\n";
    echo "Subject: $subject\n";
    echo "Duration: 5 minutes (300 seconds)\n";
    echo "Total Questions: $totalQuestions\n";
    echo "Total Marks: $totalMarks (1 mark per question)\n";
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

