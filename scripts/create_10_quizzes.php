<?php
/**
 * Create 10 Quizzes for Testing
 * 7 Objective quizzes + 3 Subjective quizzes
 * Each quiz: 10 minutes duration
 * 
 * Usage: php scripts/create_10_quizzes.php
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
    
    // Define all quizzes
    $quizzes = [
        // Objective Quizzes (1-7)
        [
            'title' => 'General Knowledge Quiz 1',
            'subject' => 'General Knowledge',
            'description' => 'Test your general knowledge',
            'type' => 'objective',
            'questions' => [
                ['question' => 'What is the capital of India?', 'options' => ['a' => 'Mumbai', 'b' => 'Delhi', 'c' => 'Kolkata', 'd' => 'Chennai'], 'correct' => 'b'],
                ['question' => 'Which is the largest planet in our solar system?', 'options' => ['a' => 'Earth', 'b' => 'Jupiter', 'c' => 'Saturn', 'd' => 'Neptune'], 'correct' => 'b'],
                ['question' => 'What is 15 + 25?', 'options' => ['a' => '35', 'b' => '40', 'c' => '45', 'd' => '50'], 'correct' => 'b'],
                ['question' => 'Which ocean is the largest?', 'options' => ['a' => 'Atlantic', 'b' => 'Pacific', 'c' => 'Indian', 'd' => 'Arctic'], 'correct' => 'b'],
                ['question' => 'What is the chemical symbol for Gold?', 'options' => ['a' => 'Go', 'b' => 'Gd', 'c' => 'Au', 'd' => 'Ag'], 'correct' => 'c']
            ]
        ],
        [
            'title' => 'Mathematics Quiz 1',
            'subject' => 'Mathematics',
            'description' => 'Basic mathematics questions',
            'type' => 'objective',
            'questions' => [
                ['question' => 'What is 12 × 8?', 'options' => ['a' => '90', 'b' => '96', 'c' => '100', 'd' => '104'], 'correct' => 'b'],
                ['question' => 'What is 144 ÷ 12?', 'options' => ['a' => '10', 'b' => '11', 'c' => '12', 'd' => '13'], 'correct' => 'c'],
                ['question' => 'What is 5²?', 'options' => ['a' => '20', 'b' => '25', 'c' => '30', 'd' => '35'], 'correct' => 'b'],
                ['question' => 'What is 100 - 37?', 'options' => ['a' => '61', 'b' => '62', 'c' => '63', 'd' => '64'], 'correct' => 'c'],
                ['question' => 'What is 7 × 9?', 'options' => ['a' => '61', 'b' => '62', 'c' => '63', 'd' => '64'], 'correct' => 'c']
            ]
        ],
        [
            'title' => 'Science Quiz 1',
            'subject' => 'Science',
            'description' => 'Basic science questions',
            'type' => 'objective',
            'questions' => [
                ['question' => 'What is H2O?', 'options' => ['a' => 'Hydrogen', 'b' => 'Oxygen', 'c' => 'Water', 'd' => 'Carbon Dioxide'], 'correct' => 'c'],
                ['question' => 'Which gas do plants absorb from the atmosphere?', 'options' => ['a' => 'Oxygen', 'b' => 'Carbon Dioxide', 'c' => 'Nitrogen', 'd' => 'Hydrogen'], 'correct' => 'b'],
                ['question' => 'What is the speed of light?', 'options' => ['a' => '300,000 km/s', 'b' => '150,000 km/s', 'c' => '450,000 km/s', 'd' => '600,000 km/s'], 'correct' => 'a'],
                ['question' => 'Which planet is closest to the Sun?', 'options' => ['a' => 'Venus', 'b' => 'Mercury', 'c' => 'Earth', 'd' => 'Mars'], 'correct' => 'b'],
                ['question' => 'What is the hardest natural substance?', 'options' => ['a' => 'Gold', 'b' => 'Iron', 'c' => 'Diamond', 'd' => 'Platinum'], 'correct' => 'c']
            ]
        ],
        [
            'title' => 'History Quiz 1',
            'subject' => 'History',
            'description' => 'World history questions',
            'type' => 'objective',
            'questions' => [
                ['question' => 'In which year did World War II end?', 'options' => ['a' => '1943', 'b' => '1944', 'c' => '1945', 'd' => '1946'], 'correct' => 'c'],
                ['question' => 'Who painted the Mona Lisa?', 'options' => ['a' => 'Van Gogh', 'b' => 'Picasso', 'c' => 'Leonardo da Vinci', 'd' => 'Michelangelo'], 'correct' => 'c'],
                ['question' => 'Which country built the Great Wall?', 'options' => ['a' => 'Japan', 'b' => 'China', 'c' => 'India', 'd' => 'Korea'], 'correct' => 'b'],
                ['question' => 'Who discovered America?', 'options' => ['a' => 'Vasco da Gama', 'b' => 'Christopher Columbus', 'c' => 'Marco Polo', 'd' => 'Ferdinand Magellan'], 'correct' => 'b'],
                ['question' => 'In which year did India gain independence?', 'options' => ['a' => '1945', 'b' => '1946', 'c' => '1947', 'd' => '1948'], 'correct' => 'c']
            ]
        ],
        [
            'title' => 'Geography Quiz 1',
            'subject' => 'Geography',
            'description' => 'World geography questions',
            'type' => 'objective',
            'questions' => [
                ['question' => 'Which is the longest river in the world?', 'options' => ['a' => 'Amazon', 'b' => 'Nile', 'c' => 'Ganges', 'd' => 'Mississippi'], 'correct' => 'b'],
                ['question' => 'Which is the highest mountain in the world?', 'options' => ['a' => 'K2', 'b' => 'Mount Everest', 'c' => 'Kangchenjunga', 'd' => 'Lhotse'], 'correct' => 'b'],
                ['question' => 'Which continent is the largest?', 'options' => ['a' => 'Africa', 'b' => 'Asia', 'c' => 'North America', 'd' => 'Europe'], 'correct' => 'b'],
                ['question' => 'Which is the smallest country in the world?', 'options' => ['a' => 'Monaco', 'b' => 'Vatican City', 'c' => 'San Marino', 'd' => 'Liechtenstein'], 'correct' => 'b'],
                ['question' => 'Which desert is the largest?', 'options' => ['a' => 'Gobi', 'b' => 'Sahara', 'c' => 'Antarctic', 'd' => 'Arabian'], 'correct' => 'c']
            ]
        ],
        [
            'title' => 'English Grammar Quiz',
            'subject' => 'English',
            'description' => 'English grammar and vocabulary',
            'type' => 'objective',
            'questions' => [
                ['question' => 'What is the past tense of "go"?', 'options' => ['a' => 'goed', 'b' => 'went', 'c' => 'gone', 'd' => 'going'], 'correct' => 'b'],
                ['question' => 'Which is a synonym for "happy"?', 'options' => ['a' => 'Sad', 'b' => 'Joyful', 'c' => 'Angry', 'd' => 'Tired'], 'correct' => 'b'],
                ['question' => 'What is the plural of "child"?', 'options' => ['a' => 'childs', 'b' => 'children', 'c' => 'childes', 'd' => 'child'], 'correct' => 'b'],
                ['question' => 'Which word is a noun?', 'options' => ['a' => 'Run', 'b' => 'Beautiful', 'c' => 'Table', 'd' => 'Quickly'], 'correct' => 'c'],
                ['question' => 'What is the opposite of "brave"?', 'options' => ['a' => 'Strong', 'b' => 'Cowardly', 'c' => 'Bold', 'd' => 'Fearless'], 'correct' => 'b']
            ]
        ],
        [
            'title' => 'Computer Science Quiz',
            'subject' => 'Computer Science',
            'description' => 'Basic computer science questions',
            'type' => 'objective',
            'questions' => [
                ['question' => 'What does CPU stand for?', 'options' => ['a' => 'Central Processing Unit', 'b' => 'Computer Personal Unit', 'c' => 'Central Program Unit', 'd' => 'Computer Processing Unit'], 'correct' => 'a'],
                ['question' => 'What is 1 KB equal to?', 'options' => ['a' => '1000 bytes', 'b' => '1024 bytes', 'c' => '100 bytes', 'd' => '1024 bits'], 'correct' => 'b'],
                ['question' => 'Which programming language is used for web development?', 'options' => ['a' => 'C++', 'b' => 'Java', 'c' => 'JavaScript', 'd' => 'Python'], 'correct' => 'c'],
                ['question' => 'What does HTML stand for?', 'options' => ['a' => 'HyperText Markup Language', 'b' => 'HighText Markup Language', 'c' => 'HyperText Markdown Language', 'd' => 'HighText Markdown Language'], 'correct' => 'a'],
                ['question' => 'What is the full form of RAM?', 'options' => ['a' => 'Random Access Memory', 'b' => 'Read Access Memory', 'c' => 'Random Available Memory', 'd' => 'Read Available Memory'], 'correct' => 'a']
            ]
        ],
        
        // Subjective Quizzes (8-10)
        [
            'title' => 'Essay Writing Quiz',
            'subject' => 'English',
            'description' => 'Write essays on given topics (AI Evaluation)',
            'type' => 'subjective',
            'questions' => [
                ['question' => 'Write a short essay (100-150 words) on "The Importance of Education"'],
                ['question' => 'Explain in your own words (100-150 words): "What is Climate Change and why is it important?"'],
                ['question' => 'Describe the benefits of reading books (100-150 words)'],
                ['question' => 'Write about your favorite hobby and why you enjoy it (100-150 words)'],
                ['question' => 'Explain the importance of physical exercise in daily life (100-150 words)']
            ]
        ],
        [
            'title' => 'Mathematics Problem Solving',
            'subject' => 'Mathematics',
            'description' => 'Solve mathematical problems with explanations (AI Evaluation)',
            'type' => 'subjective',
            'questions' => [
                ['question' => 'Explain step by step: How do you solve 25 × 48? Show your working.'],
                ['question' => 'A rectangle has length 12 cm and width 8 cm. Calculate its area and perimeter. Show your calculations.'],
                ['question' => 'If a train travels 240 km in 3 hours, what is its average speed? Explain your answer.'],
                ['question' => 'Solve: If 3x + 5 = 20, what is the value of x? Show each step.'],
                ['question' => 'A shopkeeper bought 50 apples for ₹500. He sold each apple for ₹15. Calculate his profit. Show your working.']
            ]
        ],
        [
            'title' => 'Science Explanation Quiz',
            'subject' => 'Science',
            'description' => 'Explain scientific concepts in detail (AI Evaluation)',
            'type' => 'subjective',
            'questions' => [
                ['question' => 'Explain the water cycle in your own words (100-150 words).'],
                ['question' => 'What is photosynthesis? Explain how plants make their food (100-150 words).'],
                ['question' => 'Describe the difference between renewable and non-renewable energy sources (100-150 words).'],
                ['question' => 'Explain why we see different phases of the moon (100-150 words).'],
                ['question' => 'What is gravity? Explain how it affects objects on Earth (100-150 words).']
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
        $totalMarks = $totalQuestions; // 1 mark per question
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
        
        // Insert questions
        $questionOrder = 1;
        foreach ($questions as $qData) {
            $questionText = $qData['question'];
            $questionType = ($type === 'subjective') ? 'subjective' : 'multiple_choice';
            $marks = 1;
            $maxMarks = 1;
            
            // Insert question
            $stmt = $conn->prepare("
                INSERT INTO questions (
                    quiz_id, question_text, question_type, question_order, marks, max_marks, criteria
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $criteriaJson = null;
            if ($type === 'subjective') {
                // Add evaluation criteria for subjective questions
                $criteriaJson = json_encode([
                    'content_quality' => 'Evaluate the quality and depth of the answer',
                    'accuracy' => 'Check if the answer is factually correct',
                    'completeness' => 'Assess if the answer addresses all parts of the question',
                    'clarity' => 'Evaluate how clear and well-structured the answer is',
                    'word_count' => 'Check if the answer meets the word count requirement (100-150 words)'
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
            
            // Insert options only for multiple choice questions
            if ($type === 'objective' && isset($qData['options'])) {
                $options = ['a', 'b', 'c', 'd'];
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
        
        echo "   → $totalQuestions questions inserted\n";
        $createdQuizzes++;
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ SUCCESS: Created $createdQuizzes quizzes successfully!\n";
    echo str_repeat("=", 60) . "\n";
    echo "\nSummary:\n";
    echo "- 7 Objective Quizzes (Multiple Choice)\n";
    echo "- 3 Subjective Quizzes (AI Evaluation)\n";
    echo "- Each quiz: 10 minutes duration\n";
    echo "- Each quiz: 5 questions, 5 total marks\n";
    echo "- All quizzes are published and ready for students\n";
    echo "\nStudents can now access these quizzes from their dashboard.\n";
    
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

