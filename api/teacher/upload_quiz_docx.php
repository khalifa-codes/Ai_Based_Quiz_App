<?php
/**
 * Upload Quiz DOCX API Endpoint
 * Handles DOCX file upload and processes it using Python script
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
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

// Configuration
$uploadDir = __DIR__ . '/../../storage/uploads/';
$jsonDir = __DIR__ . '/../../storage/quiz_json/';
$pythonScript = __DIR__ . '/../../scripts/process_quiz_docx.py';
$allowedExtensions = ['docx'];
$maxFileSize = 10 * 1024 * 1024; // 10MB

// Create directories if they don't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
if (!is_dir($jsonDir)) {
    mkdir($jsonDir, 0755, true);
}

// Check if file was uploaded
if (!isset($_FILES['quiz_file']) || $_FILES['quiz_file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $error = $_FILES['quiz_file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $errorMessages[$error] ?? 'Unknown upload error';
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'File upload failed: ' . $message
    ]);
    exit();
}

$file = $_FILES['quiz_file'];

// Validate file extension
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file type. Only .docx files are allowed.'
    ]);
    exit();
}

// Validate file size
if ($file['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'File size exceeds maximum allowed size of ' . ($maxFileSize / 1024 / 1024) . 'MB'
    ]);
    exit();
}

// Generate unique filename
$filename = uniqid('quiz_', true) . '_' . time() . '.docx';
$uploadPath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save uploaded file'
    ]);
    exit();
}

// Process file with Python script
try {
    // Escape file path for shell execution
    $escapedPath = escapeshellarg($uploadPath);
    $escapedJsonDir = escapeshellarg($jsonDir);
    
    // Build command (try python3 first, fallback to python for Windows)
    $pythonCmd = 'python3';
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $pythonCmd = 'python'; // Windows uses 'python'
    }
    
    $command = sprintf(
        '%s %s %s %s 2>&1',
        $pythonCmd,
        escapeshellarg($pythonScript),
        $escapedPath,
        $escapedJsonDir
    );
    
    // Execute Python script
    $output = [];
    $returnCode = 0;
    exec($command, $output, $returnCode);
    
    $outputString = implode("\n", $output);
    
    // Check return code
    if ($returnCode !== 0) {
        // Extract error message from output
        $errorMessage = $outputString;
        if (preg_match('/ERROR: (.+)/', $outputString, $matches)) {
            $errorMessage = $matches[1];
        }
        
        // Delete uploaded file on error
        @unlink($uploadPath);
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to process DOCX file: ' . $errorMessage,
            'details' => $outputString
        ]);
        exit();
    }
    
    // Extract JSON file path from output
    $jsonFilePath = null;
    if (preg_match('/Saved to (.+\.json)/', $outputString, $matches)) {
        $jsonFilePath = $matches[1];
    } elseif (preg_match('/storage\/quiz_json\/(.+\.json)/', $outputString, $matches)) {
        $jsonFilePath = $jsonDir . $matches[1];
    }
    
    // Read JSON file to return quiz data
    $quizData = null;
    if ($jsonFilePath && file_exists($jsonFilePath)) {
        $quizData = json_decode(file_get_contents($jsonFilePath), true);
    }
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Quiz DOCX processed successfully',
        'data' => [
            'uploaded_file' => $filename,
            'json_file' => $jsonFilePath ? basename($jsonFilePath) : null,
            'quiz' => $quizData,
            'questions_count' => $quizData ? count($quizData['questions'] ?? []) : 0
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    // Delete uploaded file on error
    @unlink($uploadPath);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing file: ' . $e->getMessage()
    ]);
}

