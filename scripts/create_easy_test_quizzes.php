<?php
/**
 * Create 3 Easy Test Quizzes with Model Answers
 * These quizzes are designed to test AI evaluation with model answers
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Get a teacher ID (use first available teacher)
    $stmt = $conn->query("SELECT id FROM teachers LIMIT 1");
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacher) {
        die("Error: No teacher found. Please create a teacher first.\n");
    }
    
    $teacherId = $teacher['id'];
    
    $quizzes = [
        [
            'title' => 'Easy General Knowledge Quiz',
            'subject' => 'General Knowledge',
            'description' => 'A simple quiz to test basic knowledge',
            'duration' => 10, // 10 minutes
            'questions' => [
                [
                    'question' => 'What is the capital of France?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => 'The capital of France is Paris. Paris is located in the north-central part of France and is the largest city in the country. It is known for its rich history, culture, and landmarks like the Eiffel Tower and the Louvre Museum.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ],
                [
                    'question' => 'What are the primary colors?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => 'The primary colors are red, blue, and yellow. These are colors that cannot be created by mixing other colors together. All other colors can be made by mixing these three primary colors in different combinations.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ],
                [
                    'question' => 'What is 2 + 2?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => '2 + 2 equals 4. This is a basic addition problem where we combine two units with another two units to get a total of four units.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ]
            ]
        ],
        [
            'title' => 'Easy Science Quiz',
            'subject' => 'Science',
            'description' => 'Basic science questions for testing',
            'duration' => 10,
            'questions' => [
                [
                    'question' => 'What is water made of?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => 'Water is made of two hydrogen atoms and one oxygen atom, which is written as H2O. This is a chemical compound where hydrogen and oxygen combine to form water molecules.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ],
                [
                    'question' => 'What planet do we live on?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => 'We live on planet Earth. Earth is the third planet from the Sun and is the only known planet that supports life. It has water, atmosphere, and suitable temperature for living organisms.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ],
                [
                    'question' => 'What do plants need to grow?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => 'Plants need sunlight, water, air (carbon dioxide), and nutrients from soil to grow. Sunlight provides energy through photosynthesis, water helps transport nutrients, and soil provides essential minerals.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ]
            ]
        ],
        [
            'title' => 'Easy History Quiz',
            'subject' => 'History',
            'description' => 'Simple history questions',
            'duration' => 10,
            'questions' => [
                [
                    'question' => 'Who was the first President of the United States?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => 'George Washington was the first President of the United States. He served from 1789 to 1797 and is known as the "Father of His Country" for his leadership during the American Revolutionary War and in establishing the new nation.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ],
                [
                    'question' => 'In which year did World War II end?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => 'World War II ended in 1945. The war officially ended on September 2, 1945, when Japan surrendered. This marked the end of one of the deadliest conflicts in human history.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ],
                [
                    'question' => 'What is the Great Wall of China?',
                    'type' => 'subjective',
                    'marks' => 5,
                    'max_marks' => 5,
                    'model_answer' => 'The Great Wall of China is a series of fortifications built across the northern borders of China. It was constructed over many centuries to protect Chinese states from invasions. It is one of the most famous landmarks in the world and is visible from space.',
                    'criteria' => ['accuracy', 'completeness', 'clarity']
                ]
            ]
        ]
    ];
    
    foreach ($quizzes as $quizIndex => $quizData) {
        // Insert quiz
        $stmt = $conn->prepare("
            INSERT INTO quizzes (
                title, subject, description, duration, total_questions, total_marks,
                created_by, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'published', NOW())
        ");
        
        $totalQuestions = count($quizData['questions']);
        $totalMarks = array_sum(array_column($quizData['questions'], 'max_marks'));
        
        $stmt->execute([
            $quizData['title'],
            $quizData['subject'],
            $quizData['description'],
            $quizData['duration'] * 60, // Convert minutes to seconds
            $totalQuestions,
            $totalMarks,
            $teacherId
        ]);
        
        $quizId = $conn->lastInsertId();
        echo "✅ Quiz created: {$quizData['title']} (ID: $quizId)\n";
        
        // Insert questions with model answers
        foreach ($quizData['questions'] as $order => $questionData) {
            // Prepare criteria JSON with model_answer
            $criteriaData = [
                'criteria' => $questionData['criteria'],
                'model_answer' => $questionData['model_answer']
            ];
            $criteriaJson = json_encode($criteriaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            $stmt = $conn->prepare("
                INSERT INTO questions (
                    quiz_id, question_text, question_type, question_order,
                    marks, max_marks, criteria, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $quizId,
                $questionData['question'],
                $questionData['type'],
                $order + 1,
                $questionData['marks'],
                $questionData['max_marks'],
                $criteriaJson
            ]);
            
            $questionId = $conn->lastInsertId();
            echo "  ✓ Question {$questionId}: " . substr($questionData['question'], 0, 50) . "...\n";
            echo "    Model Answer: " . substr($questionData['model_answer'], 0, 60) . "...\n";
        }
        
        echo "\n";
    }
    
    echo "🎉 Successfully created 3 easy test quizzes with model answers!\n";
    echo "\n";
    echo "Quiz IDs:\n";
    $stmt = $conn->query("SELECT id, title FROM quizzes ORDER BY id DESC LIMIT 3");
    $recentQuizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($recentQuizzes as $q) {
        echo "  - Quiz ID {$q['id']}: {$q['title']}\n";
    }
    echo "\n";
    echo "These quizzes have:\n";
    echo "  ✓ Model answers for AI comparison\n";
    echo "  ✓ Easy questions for testing\n";
    echo "  ✓ Proper criteria setup\n";
    echo "  ✓ 5 marks per question (15 total marks per quiz)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

