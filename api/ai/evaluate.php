<?php
/**
 * AI Evaluation API Endpoint
 * Handles AI-powered evaluation of subjective answers
 */

// Turn off error display, but log errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Include AI service
$aiServicePath = __DIR__ . '/../../includes/ai_service.php';
if (!file_exists($aiServicePath)) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'AI service file not found. Please check file paths.'
    ]);
    exit();
}

require_once $aiServicePath;

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['question']) || !isset($input['answer'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Question and answer are required'
    ]);
    exit();
}

$question = trim($input['question']);
$answer = trim($input['answer']);
$maxMarks = isset($input['max_marks']) ? intval($input['max_marks']) : 20;
$criteria = isset($input['criteria']) && is_array($input['criteria']) 
    ? $input['criteria'] 
    : ['accuracy', 'completeness', 'clarity', 'logic', 'examples', 'structure'];
$provider = isset($input['provider']) ? $input['provider'] : 'gemini';
$model = isset($input['model']) ? $input['model'] : null;

// Validate inputs
if (empty($question)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Question cannot be empty']);
    exit();
}

if (empty($answer)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Answer cannot be empty']);
    exit();
}

if ($maxMarks <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Max marks must be greater than 0']);
    exit();
}

try {
    // Clear any output that might have been generated
    ob_clean();
    
    // Initialize AI service
    $aiService = new AIService($provider, null, $model);
    
    // Evaluate answer
    $result = $aiService->evaluateAnswer($question, $answer, $maxMarks, $criteria, $model);
    
    // Return success response
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $result
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Evaluation failed: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Error $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

