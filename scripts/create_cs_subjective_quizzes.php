<?php
/**
 * Create 3 Computer Science Subjective Quizzes for Testing
 * Each quiz: 5 marks total, 10 minutes duration, 3 questions
 * 
 * Usage: php scripts/create_cs_subjective_quizzes.php
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get first available teacher ID
    $stmt = $conn->query("SELECT id FROM teachers LIMIT 1");
    $teacher = $stmt->fetch();
    $teacherId = $teacher ? (int)$teacher['id'] : 1;
    
    // Start transaction
    $conn->beginTransaction();
    
    // Define 3 Computer Science subjective quizzes
    $quizzes = [
        [
            'title' => 'Computer Science Fundamentals Quiz 1',
            'subject' => 'Computer Science',
            'description' => 'Test your understanding of basic computer science concepts',
            'questions' => [
                [
                    'question' => 'Explain the difference between RAM and ROM. What are their primary uses in a computer system?',
                    'marks' => 2
                ],
                [
                    'question' => 'Describe the concept of algorithm complexity. What is the difference between time complexity and space complexity? Provide an example.',
                    'marks' => 2
                ],
                [
                    'question' => 'What is object-oriented programming? Explain the four main principles of OOP with brief examples.',
                    'marks' => 1
                ]
            ]
        ],
        [
            'title' => 'Computer Science Fundamentals Quiz 2',
            'subject' => 'Computer Science',
            'description' => 'Test your knowledge of programming and data structures',
            'questions' => [
                [
                    'question' => 'Explain the difference between a stack and a queue data structure. When would you use each one? Provide real-world examples.',
                    'marks' => 2
                ],
                [
                    'question' => 'What is recursion? Explain with an example how recursion works. What are the advantages and disadvantages of using recursion?',
                    'marks' => 2
                ],
                [
                    'question' => 'Describe the difference between compiled and interpreted programming languages. Give examples of each type.',
                    'marks' => 1
                ]
            ]
        ],
        [
            'title' => 'Computer Science Fundamentals Quiz 3',
            'subject' => 'Computer Science',
            'description' => 'Test your understanding of databases and networking',
            'questions' => [
                [
                    'question' => 'Explain the difference between SQL and NoSQL databases. When would you choose one over the other? Provide use cases for each.',
                    'marks' => 2
                ],
                [
                    'question' => 'What is the OSI model? Explain the seven layers and their functions in network communication.',
                    'marks' => 2
                ],
                [
                    'question' => 'Describe the difference between HTTP and HTTPS. Why is HTTPS important for web security?',
                    'marks' => 1
                ]
            ]
        ]
    ];
    
    $createdQuizzes = 0;
    
    foreach ($quizzes as $quizIndex => $quizData) {
        $quizNumber = $quizIndex + 1;
        $title = $quizData['title'];
        $subject = $quizData['subject'];
        $description = $quizData['description'];
        $questions = $quizData['questions'];
        $totalQuestions = count($questions);
        $totalMarks = 0;
        foreach ($questions as $q) {
            $totalMarks += $q['marks'];
        }
        $duration = 10 * 60; // 10 minutes in seconds
        
        // Check if quiz already exists
        $stmt = $conn->prepare("SELECT id FROM quizzes WHERE title = ? AND status = 'published'");
        $stmt->execute([$title]);
        $existingQuiz = $stmt->fetch();
        
        if ($existingQuiz) {
            echo "Quiz $quizNumber ($title) already exists. Skipping...\n";
            continue;
        }
        
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
            null,
            'gemini',
            null,
            'published'
        ]);
        
        $quizId = $conn->lastInsertId();
        echo "✅ Quiz $quizNumber created: $title (ID: $quizId)\n";
        echo "   → Duration: 10 minutes\n";
        echo "   → Total Marks: $totalMarks\n";
        echo "   → Questions: $totalQuestions\n";
        
        // Insert questions
        $questionOrder = 1;
        foreach ($questions as $qData) {
            $questionText = $qData['question'];
            $questionType = 'subjective';
            $marks = $qData['marks'];
            $maxMarks = $qData['marks'];
            
            // Evaluation criteria for subjective questions
            $criteriaJson = json_encode([
                'accuracy' => 'Check if the answer is factually correct and technically accurate',
                'completeness' => 'Assess if the answer addresses all parts of the question',
                'clarity' => 'Evaluate how clear and well-structured the answer is',
                'examples' => 'Check if relevant examples are provided where appropriate',
                'depth' => 'Evaluate the depth of understanding demonstrated',
                'technical_terminology' => 'Assess proper use of technical terms and concepts'
            ]);
            
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
                $criteriaJson
            ]);
            
            $questionId = $conn->lastInsertId();
            echo "   → Question $questionOrder inserted (ID: $questionId, Marks: $marks)\n";
            
            $questionOrder++;
        }
        
        $createdQuizzes++;
        echo "\n";
    }
    
    // Commit transaction
    $conn->commit();
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ SUCCESS: Created $createdQuizzes Computer Science subjective quizzes!\n";
    echo str_repeat("=", 60) . "\n";
    echo "\nSummary:\n";
    echo "- 3 Subjective Quizzes (Computer Science)\n";
    echo "- Each quiz: 10 minutes duration\n";
    echo "- Each quiz: 3 questions, 5 total marks\n";
    echo "- All quizzes are published and ready for students\n";
    echo "- AI evaluation enabled for all questions\n";
    echo "\nStudents can now access these quizzes from their dashboard.\n";
    echo "AI will automatically evaluate the subjective answers after submission.\n";
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>

