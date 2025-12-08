<?php
/**
 * Create Additional Quizzes
 * 3 Subjective Quizzes + 1 Objective Quiz
 * 
 * Usage: php scripts/create_additional_quizzes.php
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
    
    // Define quizzes
    $quizzes = [
        // Subjective Quiz 1
        [
            'title' => 'Computer Science Fundamentals Quiz 4',
            'subject' => 'Computer Science',
            'description' => 'Test your understanding of programming concepts and data structures',
            'type' => 'subjective',
            'questions' => [
                [
                    'question' => 'Explain the concept of polymorphism in object-oriented programming. Provide examples of compile-time and runtime polymorphism.',
                    'marks' => 2
                ],
                [
                    'question' => 'What is the difference between a binary tree and a binary search tree? Explain with examples.',
                    'marks' => 2
                ],
                [
                    'question' => 'Describe the concept of exception handling in programming. Why is it important?',
                    'marks' => 1
                ]
            ]
        ],
        // Subjective Quiz 2
        [
            'title' => 'Computer Science Fundamentals Quiz 5',
            'subject' => 'Computer Science',
            'description' => 'Test your knowledge of algorithms and system design',
            'type' => 'subjective',
            'questions' => [
                [
                    'question' => 'Explain the difference between linear search and binary search algorithms. When would you use each one?',
                    'marks' => 2
                ],
                [
                    'question' => 'What is the difference between a process and a thread? Explain their relationship in operating systems.',
                    'marks' => 2
                ],
                [
                    'question' => 'Describe the concept of caching. How does it improve system performance?',
                    'marks' => 1
                ]
            ]
        ],
        // Subjective Quiz 3
        [
            'title' => 'Computer Science Fundamentals Quiz 6',
            'subject' => 'Computer Science',
            'description' => 'Test your understanding of software engineering and web technologies',
            'type' => 'subjective',
            'questions' => [
                [
                    'question' => 'Explain the MVC (Model-View-Controller) architecture pattern. What are its advantages?',
                    'marks' => 2
                ],
                [
                    'question' => 'What is the difference between REST and SOAP APIs? When would you choose one over the other?',
                    'marks' => 2
                ],
                [
                    'question' => 'Describe the concept of version control. Why is Git important in software development?',
                    'marks' => 1
                ]
            ]
        ],
        // Objective Quiz
        [
            'title' => 'Computer Science MCQ Quiz 1',
            'subject' => 'Computer Science',
            'description' => 'Multiple choice questions on computer science fundamentals',
            'type' => 'objective',
            'questions' => [
                [
                    'question' => 'What does CPU stand for?',
                    'options' => [
                        'a' => 'Central Processing Unit',
                        'b' => 'Computer Personal Unit',
                        'c' => 'Central Program Utility',
                        'd' => 'Computer Processing Unit'
                    ],
                    'correct' => 'a',
                    'marks' => 1
                ],
                [
                    'question' => 'Which data structure follows LIFO (Last In First Out) principle?',
                    'options' => [
                        'a' => 'Queue',
                        'b' => 'Stack',
                        'c' => 'Array',
                        'd' => 'Linked List'
                    ],
                    'correct' => 'b',
                    'marks' => 1
                ],
                [
                    'question' => 'What is the time complexity of binary search in a sorted array?',
                    'options' => [
                        'a' => 'O(n)',
                        'b' => 'O(log n)',
                        'c' => 'O(n²)',
                        'd' => 'O(1)'
                    ],
                    'correct' => 'b',
                    'marks' => 1
                ],
                [
                    'question' => 'Which programming language is known for its use in web development?',
                    'options' => [
                        'a' => 'C++',
                        'b' => 'Java',
                        'c' => 'JavaScript',
                        'd' => 'Assembly'
                    ],
                    'correct' => 'c',
                    'marks' => 1
                ],
                [
                    'question' => 'What does HTML stand for?',
                    'options' => [
                        'a' => 'HyperText Markup Language',
                        'b' => 'High-level Text Markup Language',
                        'c' => 'Hyperlink and Text Markup Language',
                        'd' => 'Home Tool Markup Language'
                    ],
                    'correct' => 'a',
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
        $type = $quizData['type'];
        $questions = $quizData['questions'];
        $totalQuestions = count($questions);
        $totalMarks = 0;
        
        // Calculate total marks
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
        echo "   → Type: " . ucfirst($type) . "\n";
        echo "   → Duration: 10 minutes\n";
        echo "   → Total Marks: $totalMarks\n";
        echo "   → Questions: $totalQuestions\n";
        
        // Insert questions
        $questionOrder = 1;
        foreach ($questions as $qData) {
            $questionText = $qData['question'];
            $questionType = ($type === 'subjective') ? 'subjective' : 'multiple_choice';
            $marks = $qData['marks'];
            $maxMarks = $marks;
            
            // Insert question
            $stmt = $conn->prepare("
                INSERT INTO questions (
                    quiz_id, question_text, question_type, question_order, marks, max_marks, criteria
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $criteriaJson = null;
            if ($type === 'subjective') {
                // Evaluation criteria for subjective questions
                $criteriaJson = json_encode([
                    'accuracy' => 'Check if the answer is factually correct and technically accurate',
                    'completeness' => 'Assess if the answer addresses all parts of the question',
                    'clarity' => 'Evaluate how clear and well-structured the answer is',
                    'examples' => 'Check if relevant examples are provided where appropriate',
                    'depth' => 'Evaluate the depth of understanding demonstrated',
                    'technical_terminology' => 'Assess proper use of technical terms and concepts'
                ]);
            }
            
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
            
            // Insert options for multiple choice questions
            if ($type === 'objective' && isset($qData['options'])) {
                $options = array_keys($qData['options']);
                $correctAnswer = strtolower(trim($qData['correct']));
                
                foreach ($options as $index => $optionKey) {
                    $optionText = $qData['options'][$optionKey] ?? '';
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
        
        $createdQuizzes++;
        echo "\n";
    }
    
    // Commit transaction
    $conn->commit();
    
    echo str_repeat("=", 60) . "\n";
    echo "✅ SUCCESS: Created $createdQuizzes quizzes successfully!\n";
    echo str_repeat("=", 60) . "\n";
    echo "\nSummary:\n";
    echo "- 3 Subjective Quizzes (Computer Science)\n";
    echo "- 1 Objective Quiz (Computer Science - MCQ)\n";
    echo "- Each quiz: 10 minutes duration\n";
    echo "- Subjective quizzes: 3 questions, 5 total marks each\n";
    echo "- Objective quiz: 5 questions, 5 total marks\n";
    echo "- All quizzes are published and ready for students\n";
    echo "- AI evaluation enabled for subjective questions\n";
    echo "\nStudents can now access these quizzes from their dashboard.\n";
    
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

