<?php
/**
 * AI Service Class
 * Handles AI API integration for subjective question evaluation
 * Supports OpenAI, Anthropic, and other AI providers
 */

class AIService {
    private $apiKey;
    private $apiUrl;
    private $model;
    private $provider;
    
    // Supported providers
    const PROVIDER_GEMINI = 'gemini';
    const PROVIDER_GROQ = 'groq';
    const PROVIDER_PERPLEXITY = 'perplexity';
    const PROVIDER_CUSTOM = 'custom';
    
    // Default models (kept for backward compatibility, but not used)
    const MODEL_GEMINI_3_PRO = 'gemini-3-pro';
    const MODEL_GEMINI_15_PRO = 'gemini-1.5-pro';
    
    public function __construct($provider = self::PROVIDER_GEMINI, $apiKey = null, $model = null) {
        $this->provider = $provider;
        
        // Get API key from environment or config
        $this->apiKey = $apiKey ?? $this->getApiKey($provider);
        
        // Set default model based on provider
        if ($model) {
            $this->model = $model;
        } else {
            $this->model = $this->getDefaultModel($provider);
        }
        
        // Set API URL based on provider
        $this->apiUrl = $this->getApiUrl($provider);
    }
    
    /**
     * Get API key from environment or config file
     */
    private function getApiKey($provider) {
        // Try environment variable first
        $envKey = strtoupper($provider) . '_API_KEY';
        $envValue = getenv($envKey);
        if ($envValue !== false && !empty($envValue)) {
            return $envValue;
        }
        
        // Try config file
        $configFile = __DIR__ . '/../config/ai_config.php';
        if (file_exists($configFile)) {
            try {
                $config = @include $configFile;
                if (is_array($config) && isset($config[$provider]['api_key'])) {
                    $apiKey = trim($config[$provider]['api_key']);
                    return !empty($apiKey) ? $apiKey : null;
                }
            } catch (Exception $e) {
                // Silently fail, will throw error later
                return null;
            }
        }
        
        // Return null if not found (will throw error when used)
        return null;
    }
    
    /**
     * Get default model for provider
     */
    private function getDefaultModel($provider) {
        $defaults = [
            // Use gemini-2.5-flash as default (fastest, with fallback to next model if unavailable)
            self::PROVIDER_GEMINI => 'gemini-2.5-flash',
            // Use llama-3.3-70b-versatile as default (ultra-fast inference)
            self::PROVIDER_GROQ => 'llama-3.3-70b-versatile',
            // Use sonar-pro as default (best accuracy with web search)
            self::PROVIDER_PERPLEXITY => 'sonar-pro',
            self::PROVIDER_CUSTOM => 'gemini-2.5-flash' // Default to Gemini
        ];
        
        return $defaults[$provider] ?? 'gemini-2.5-flash';
    }
    
    /**
     * Get fallback models for a provider
     * Returns list of models to try in order if default fails
     */
    private function getFallbackModels($provider) {
        $configFile = __DIR__ . '/../config/ai_config.php';
        if (file_exists($configFile)) {
            $config = @include $configFile;
            if (isset($config[$provider]['fallback_models']) && is_array($config[$provider]['fallback_models'])) {
                return $config[$provider]['fallback_models'];
            }
        }
        
        // Default fallback models if not in config
        $defaultFallbacks = [
            self::PROVIDER_GEMINI => [
                'gemini-2.5-flash',
                'gemini-2.5-pro',
                'gemini-2.0-flash',
                'gemini-1.5-pro',
                'gemini-1.5-flash'
            ]
        ];
        
        return $defaultFallbacks[$provider] ?? [];
    }
    
    /**
     * Get API URL for provider
     */
    private function getApiUrl($provider) {
        $urls = [
            // Try v1 first (more stable), fallback to v1beta if needed
            // Some models might need version suffixes like -001, -002, etc.
            self::PROVIDER_GEMINI => 'https://generativelanguage.googleapis.com/v1/models/{model}:generateContent',
            // Groq uses OpenAI-compatible API
            self::PROVIDER_GROQ => 'https://api.groq.com/openai/v1/chat/completions',
            // Perplexity uses OpenAI-compatible API
            self::PROVIDER_PERPLEXITY => 'https://api.perplexity.ai/chat/completions',
            self::PROVIDER_CUSTOM => 'https://api.groq.com/openai/v1/chat/completions' // Default to Groq format
        ];
        
        return $urls[$provider] ?? $urls[self::PROVIDER_GEMINI];
    }
    
    /**
     * List available Gemini models
     * Useful for debugging which models are actually available
     */
    public function listGeminiModels() {
        if ($this->provider !== self::PROVIDER_GEMINI) {
            throw new Exception("listGeminiModels() can only be called for Gemini provider");
        }
        
        if (!$this->apiKey) {
            throw new Exception("API key is required to list models");
        }
        
        // Try v1beta first (has more models)
        $urls = [
            'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($this->apiKey),
            'https://generativelanguage.googleapis.com/v1/models?key=' . urlencode($this->apiKey)
        ];
        
        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            // SSL configuration
            if ($this->shouldVerifySSL()) {
                $certPath = $this->getCACertPath();
                if ($certPath) {
                    curl_setopt($ch, CURLOPT_CAINFO, $certPath);
                }
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if (PHP_VERSION_ID < 80000) {
                curl_close($ch);
            }
            
            if (!$error && $httpCode === 200) {
                $data = json_decode($response, true);
                if (isset($data['models'])) {
                    return $data['models'];
                }
            }
        }
        
        throw new Exception("Failed to list models. HTTP Code: {$httpCode}, Error: {$error}");
    }
    
    /**
     * List available OpenAI models
     * Useful for debugging which models are actually available
     */
    public function listOpenAIModels() {
        if ($this->provider !== self::PROVIDER_OPENAI) {
            throw new Exception("listOpenAIModels() can only be called for OpenAI provider");
        }
        
        if (!$this->apiKey) {
            throw new Exception("API key is required to list models");
        }
        
        $url = 'https://api.openai.com/v1/models';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        // SSL configuration
        if ($this->shouldVerifySSL()) {
            $certPath = $this->getCACertPath();
            if ($certPath) {
                curl_setopt($ch, CURLOPT_CAINFO, $certPath);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }
        
        if ($error) {
            throw new Exception("API request failed: {$error}");
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "API returned status code {$httpCode}";
            $errorType = $errorData['error']['type'] ?? 'unknown';
            $errorCode = $errorData['error']['code'] ?? null;
            
            // Build detailed error message
            $detailedError = "Failed to list models: {$errorMessage}";
            if ($errorCode) {
                $detailedError .= " (Code: {$errorCode})";
            }
            
            throw new Exception($detailedError);
        }
        
        $data = json_decode($response, true);
        if (isset($data['data'])) {
            return $data['data'];
        }
        
        throw new Exception("Invalid API response format");
    }
    
    /**
     * Get CA certificate path for SSL verification
     * Returns path to certificate bundle or null to use system default
     * For development on Windows, SSL verification may be disabled
     */
    private function getCACertPath() {
        // Try common certificate bundle locations
        $possiblePaths = [
            __DIR__ . '/../cacert.pem', // Project-specific bundle
            __DIR__ . '/cacert.pem',    // In includes folder
            'C:/php/extras/ssl/cacert.pem', // Windows PHP
            'C:/xampp/apache/bin/curl-ca-bundle.crt', // XAMPP
            ini_get('curl.cainfo'), // PHP ini setting
            sys_get_temp_dir() . '/cacert.pem' // Temp directory
        ];
        
        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }
        
        // If no certificate bundle found, return null
        // This will use system default or we can disable verification for development
        return null;
    }
    
    /**
     * Check if SSL verification should be enabled
     * For development, can be disabled if certificate bundle not available
     */
    private function shouldVerifySSL() {
        // Check if certificate bundle exists
        $certPath = $this->getCACertPath();
        if ($certPath && file_exists($certPath)) {
            return true;
        }
        
        // For development, allow disabling SSL verification
        // In production, you should always have a certificate bundle
        $configFile = __DIR__ . '/../config/ai_config.php';
        if (file_exists($configFile)) {
            $config = @include $configFile;
            if (isset($config['disable_ssl_verification']) && $config['disable_ssl_verification'] === true) {
                return false; // SSL verification disabled in config
            }
        }
        
        // Default: try to verify, but will fail gracefully if cert not found
        return true;
    }
    
    /**
     * Evaluate a subjective answer using AI
     * 
     * @param string $question The question text
     * @param string $studentAnswer The student's answer
     * @param int $maxMarks Maximum marks for this question
     * @param array $criteria Evaluation criteria (accuracy, completeness, clarity, etc.)
     * @param string $model Optional model override
     * @param string $modelAnswer Optional model/teacher's answer for reference
     * @return array Evaluation result with marks, feedback, and criteria scores
     */
    public function evaluateAnswer($question, $studentAnswer, $maxMarks = 20, $criteria = [], $model = null, $modelAnswer = null) {
        if (!$this->apiKey) {
            throw new Exception('API key not configured. Please set your AI API key in config/ai_config.php or environment variable.');
        }
        
        // Use provided model or default
        $modelToUse = $model ?? $this->model;
        
        // Default criteria if not provided
        if (empty($criteria)) {
            $criteria = ['accuracy', 'completeness', 'clarity', 'logic', 'examples', 'structure'];
        }
        
        // Build evaluation prompt
        $prompt = $this->buildEvaluationPrompt($question, $studentAnswer, $maxMarks, $criteria, $modelAnswer);
        
        // Call AI API based on provider
        switch ($this->provider) {
            case self::PROVIDER_GEMINI:
                return $this->evaluateWithGemini($prompt, $modelToUse, $maxMarks, $criteria);
            case self::PROVIDER_GROQ:
                return $this->evaluateWithGroq($prompt, $modelToUse, $maxMarks, $criteria);
            case self::PROVIDER_PERPLEXITY:
                return $this->evaluateWithPerplexity($prompt, $modelToUse, $maxMarks, $criteria);
            default:
                // Default to Gemini if provider not recognized
                return $this->evaluateWithGemini($prompt, $modelToUse, $maxMarks, $criteria);
        }
    }
    
    /**
     * Build evaluation prompt for AI
     */
    private function buildEvaluationPrompt($question, $studentAnswer, $maxMarks, $criteria, $modelAnswer = null) {
        $criteriaDescriptions = [
            'accuracy' => 'How accurate and correct is the answer compared to the model answer? Check if key concepts, facts, and explanations are correct.',
            'completeness' => 'How complete is the answer? Does it cover all aspects mentioned in the model answer? Are important points missing?',
            'clarity' => 'How clear and well-explained is the answer? Is it easy to understand? Compare the clarity with the model answer.',
            'logic' => 'How logical and well-reasoned is the answer? Is the flow of ideas coherent? Does it follow a logical structure like the model answer?',
            'examples' => 'Are relevant examples and evidence provided? Compare with the examples in the model answer. Are examples appropriate and well-explained?',
            'structure' => 'How well-structured and organized is the answer? Compare the organization with the model answer. Is there a clear introduction, body, and conclusion?'
        ];
        
        $criteriaList = '';
        foreach ($criteria as $criterion) {
            $description = $criteriaDescriptions[$criterion] ?? ucfirst($criterion);
            $criteriaList .= "- {$criterion}: {$description}\n";
        }
        
        // Helper prompt for AI to understand evaluation logic
        $helperPrompt = "EVALUATION GUIDELINES:
1. Compare the student's answer with the model answer provided below
2. Identify what the student got right and what they missed
3. Award marks based on how well the student's answer matches the key points in the model answer
4. Be fair: If the student expresses the same idea in different words, still give credit
5. Be strict: If important concepts from the model answer are missing, deduct marks accordingly
6. For each criterion, award marks proportionally based on how well the student's answer aligns with the model answer
7. The total marks should reflect the overall quality compared to the model answer
8. Provide specific feedback pointing out what matches the model answer and what doesn't";
        
        $prompt = "You are an expert educational evaluator. Your task is to evaluate a student's answer by comparing it with a model answer (teacher's reference answer) and provide detailed feedback.

QUESTION:
{$question}

MODEL ANSWER (Teacher's Reference Answer):
" . ($modelAnswer ? $modelAnswer : "No model answer provided. Evaluate based on general knowledge and the criteria below.") . "

STUDENT ANSWER:
{$studentAnswer}

MAXIMUM MARKS: {$maxMarks}

{$helperPrompt}

EVALUATION CRITERIA:
{$criteriaList}

EVALUATION INSTRUCTIONS:
1. Compare the student's answer with the model answer point by point
2. Identify which key concepts, facts, and explanations from the model answer are present in the student's answer
3. Identify which important points from the model answer are missing or incorrect in the student's answer
4. Award marks based on how well the student's answer covers the content and quality of the model answer
5. For each criterion, evaluate how the student's answer measures up against the model answer
6. Provide specific feedback that references both the model answer and the student's answer

Please evaluate the answer based on the criteria above and provide:
1. Total marks (out of {$maxMarks}) - based on comparison with model answer
2. Individual scores for each criterion (distribute marks proportionally based on model answer alignment)
3. Detailed feedback explaining:
   - What the student got right (matching model answer)
   - What the student missed or got wrong (compared to model answer)
   - Specific areas for improvement (referencing model answer)

Respond in JSON format with the following structure:
{
    \"total_marks\": <number>,
    \"criteria_scores\": {
        \"accuracy\": <number>,
        \"completeness\": <number>,
        \"clarity\": <number>,
        \"logic\": <number>,
        \"examples\": <number>,
        \"structure\": <number>
    },
    \"feedback\": \"<detailed feedback text comparing student answer with model answer>\",
    \"strengths\": [\"<strength1 matching model answer>\", \"<strength2 matching model answer>\"],
    \"improvements\": [\"<improvement1 referencing model answer>\", \"<improvement2 referencing model answer>\"],
    \"model_answer_alignment\": \"<brief summary of how well student answer aligns with model answer>\"
}

IMPORTANT:
- The sum of criteria scores should approximately equal the total marks
- Be consistent: If the student answer closely matches the model answer, award high marks
- Be fair: Different wording but same meaning should still get credit
- Be specific: Reference actual content from both answers in your feedback";

        return $prompt;
    }
    
    /**
     * Evaluate using OpenAI API
     */
    private function evaluateWithOpenAI($prompt, $model, $maxMarks, $criteria) {
        $ch = curl_init($this->apiUrl);
        
        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert educational evaluator. Always respond with valid JSON only, no additional text.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3, // Lower temperature for more consistent evaluation
            'max_tokens' => 1500,
            'response_format' => ['type' => 'json_object'] // Force JSON response
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 60
        ]);
        
        // Handle SSL verification
        if ($this->shouldVerifySSL()) {
            $certPath = $this->getCACertPath();
            if ($certPath) {
                curl_setopt($ch, CURLOPT_CAINFO, $certPath);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            // For development only - disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // curl_close() is deprecated in PHP 8.5+, but still works
        // Resources are automatically closed when they go out of scope
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }
        
        if ($error) {
            // Provide more detailed error information
            $detailedError = "API request failed: {$error}";
            if (stripos($error, 'SSL') !== false || stripos($error, 'certificate') !== false) {
                $detailedError .= " | This appears to be an SSL certificate issue. Check your SSL configuration in config/ai_config.php";
            } elseif (stripos($error, 'timeout') !== false) {
                $detailedError .= " | Request timed out. The API might be slow or unreachable.";
            } elseif (stripos($error, 'resolve') !== false || stripos($error, 'host') !== false) {
                $detailedError .= " | Cannot resolve hostname. Check your internet connection.";
            }
            throw new Exception($detailedError);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "API returned status code {$httpCode}";
            $errorType = $errorData['error']['type'] ?? 'unknown';
            $errorCode = $errorData['error']['code'] ?? null;
            
            // Build detailed error message
            $detailedError = "OpenAI API error";
            if ($errorCode) {
                $detailedError .= " (Code: {$errorCode})";
            }
            $detailedError .= ": {$errorMessage}";
            
            // Add helpful context for common errors
            if ($errorType === 'insufficient_quota' || stripos($errorMessage, 'quota') !== false) {
                $detailedError .= " | Your account has exceeded its quota or has no credits. Check billing at https://platform.openai.com/account/billing";
            } elseif ($errorType === 'invalid_api_key' || stripos($errorMessage, 'authentication') !== false) {
                $detailedError .= " | Invalid API key. Verify at https://platform.openai.com/api-keys";
            } elseif (stripos($errorMessage, 'rate_limit') !== false) {
                $detailedError .= " | Rate limit exceeded. Wait a few minutes or check limits at https://platform.openai.com/account/limits";
            }
            
            throw new Exception($detailedError);
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['choices'][0]['message']['content'])) {
            throw new Exception("Invalid API response format");
        }
        
        $content = $result['choices'][0]['message']['content'];
        $evaluation = json_decode($content, true);
        
        if (!$evaluation) {
            throw new Exception("Failed to parse AI evaluation response");
        }
        
        // Validate and format response
        return $this->formatEvaluationResult($evaluation, $maxMarks, $criteria);
    }
    
    /**
     * Evaluate using Groq API (OpenAI-compatible, ultra-fast inference)
     */
    private function evaluateWithGroq($prompt, $model, $maxMarks, $criteria) {
        // Groq uses OpenAI-compatible API, so we can reuse similar structure
        $ch = curl_init($this->apiUrl);
        
        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert educational evaluator. Always respond with valid JSON only, no additional text.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3, // Lower temperature for more consistent evaluation
            'max_tokens' => 1500,
            'response_format' => ['type' => 'json_object'] // Force JSON response
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 60
        ]);
        
        // Handle SSL verification
        if ($this->shouldVerifySSL()) {
            $certPath = $this->getCACertPath();
            if ($certPath) {
                curl_setopt($ch, CURLOPT_CAINFO, $certPath);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            // For development only - disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }
        
        if ($error) {
            throw new Exception("API request failed: {$error}");
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "API returned status code {$httpCode}";
            $errorType = $errorData['error']['type'] ?? 'unknown';
            $errorCode = $errorData['error']['code'] ?? null;
            
            // Build detailed error message
            $detailedError = "Groq API error";
            if ($errorCode) {
                $detailedError .= " (Code: {$errorCode})";
            }
            $detailedError .= ": {$errorMessage}";
            
            // Add helpful context for common errors
            if ($errorType === 'insufficient_quota' || stripos($errorMessage, 'quota') !== false) {
                $detailedError .= " | Your account has exceeded its quota. Check usage at https://console.groq.com/";
            } elseif ($errorType === 'invalid_api_key' || stripos($errorMessage, 'authentication') !== false) {
                $detailedError .= " | Invalid API key. Verify at https://console.groq.com/keys";
            } elseif (stripos($errorMessage, 'rate_limit') !== false) {
                $detailedError .= " | Rate limit exceeded. Groq has generous limits, wait a moment and try again";
            }
            
            throw new Exception($detailedError);
        }
        
        $result = json_decode($response, true);
        
        // Debug: Log response structure if it's not what we expect
        if (!isset($result['choices'][0]['message']['content'])) {
            // Try to provide helpful debugging info
            $debugInfo = "Response keys: " . implode(', ', array_keys($result ?? []));
            if (isset($result['choices'])) {
                $debugInfo .= " | Choices count: " . count($result['choices']);
                if (isset($result['choices'][0])) {
                    $debugInfo .= " | First choice keys: " . implode(', ', array_keys($result['choices'][0]));
                    if (isset($result['choices'][0]['message'])) {
                        $debugInfo .= " | Message keys: " . implode(', ', array_keys($result['choices'][0]['message']));
                    }
                }
            }
            throw new Exception("Invalid API response format. " . $debugInfo . " | Full response: " . substr(json_encode($result), 0, 500));
        }
        
        $content = $result['choices'][0]['message']['content'];
        
        // Try to parse JSON from content
        $evaluation = json_decode($content, true);
        
        // If direct JSON decode fails, try to extract JSON from text (might be wrapped in markdown)
        if (!$evaluation && is_string($content)) {
            // Try to extract JSON from markdown code blocks
            if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $matches)) {
                $evaluation = json_decode($matches[1], true);
            } elseif (preg_match('/(\{.*\})/s', $content, $matches)) {
                // Try to find JSON object in the text
                $evaluation = json_decode($matches[1], true);
            }
        }
        
        if (!$evaluation) {
            throw new Exception("Failed to parse AI evaluation response. Content received: " . substr($content, 0, 500));
        }
        
        // Validate and format response
        return $this->formatEvaluationResult($evaluation, $maxMarks, $criteria);
    }
    
    /**
     * Evaluate using Perplexity Pro API (OpenAI-compatible, with web search capabilities)
     */
    private function evaluateWithPerplexity($prompt, $model, $maxMarks, $criteria) {
        // Perplexity uses OpenAI-compatible API, similar to Groq
        $ch = curl_init($this->apiUrl);
        
        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert educational evaluator. Always respond with valid JSON only, no additional text.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3, // Lower temperature for more consistent evaluation
            'max_tokens' => 1500
            // Note: Perplexity doesn't support response_format parameter, rely on prompt for JSON
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 60
        ]);
        
        // Handle SSL verification
        if ($this->shouldVerifySSL()) {
            $certPath = $this->getCACertPath();
            if ($certPath) {
                curl_setopt($ch, CURLOPT_CAINFO, $certPath);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            // For development only - disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }
        
        if ($error) {
            throw new Exception("API request failed: {$error}");
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "API returned status code {$httpCode}";
            $errorType = $errorData['error']['type'] ?? 'unknown';
            $errorCode = $errorData['error']['code'] ?? null;
            
            // Build detailed error message
            $detailedError = "Perplexity API error";
            if ($errorCode) {
                $detailedError .= " (Code: {$errorCode})";
            }
            $detailedError .= ": {$errorMessage}";
            
            // Add helpful context for common errors
            if ($errorType === 'insufficient_quota' || stripos($errorMessage, 'quota') !== false) {
                $detailedError .= " | Your account has exceeded its quota. Check usage at https://www.perplexity.ai/settings/api";
            } elseif ($errorType === 'invalid_api_key' || stripos($errorMessage, 'authentication') !== false) {
                $detailedError .= " | Invalid API key. Verify at https://www.perplexity.ai/settings/api";
            } elseif (stripos($errorMessage, 'rate_limit') !== false) {
                $detailedError .= " | Rate limit exceeded. Check your Perplexity Pro subscription limits";
            }
            
            throw new Exception($detailedError);
        }
        
        $result = json_decode($response, true);
        
        // Debug: Log response structure if it's not what we expect
        if (!isset($result['choices'][0]['message']['content'])) {
            // Try to provide helpful debugging info
            $debugInfo = "Response keys: " . implode(', ', array_keys($result ?? []));
            if (isset($result['choices'])) {
                $debugInfo .= " | Choices count: " . count($result['choices']);
                if (isset($result['choices'][0])) {
                    $debugInfo .= " | First choice keys: " . implode(', ', array_keys($result['choices'][0]));
                    if (isset($result['choices'][0]['message'])) {
                        $debugInfo .= " | Message keys: " . implode(', ', array_keys($result['choices'][0]['message']));
                    }
                }
            }
            throw new Exception("Invalid API response format. " . $debugInfo . " | Full response: " . substr(json_encode($result), 0, 500));
        }
        
        $content = $result['choices'][0]['message']['content'];
        
        // Try to parse JSON from content
        $evaluation = json_decode($content, true);
        
        // If direct JSON decode fails, try to extract JSON from text (might be wrapped in markdown)
        if (!$evaluation && is_string($content)) {
            // Try to extract JSON from markdown code blocks
            if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $matches)) {
                $evaluation = json_decode($matches[1], true);
            } elseif (preg_match('/(\{.*\})/s', $content, $matches)) {
                // Try to find JSON object in the text
                $evaluation = json_decode($matches[1], true);
            }
        }
        
        if (!$evaluation) {
            throw new Exception("Failed to parse AI evaluation response. Content received: " . substr($content, 0, 500));
        }
        
        // Validate and format response
        return $this->formatEvaluationResult($evaluation, $maxMarks, $criteria);
    }
    
    /**
     * Evaluate using Anthropic API
     */
    private function evaluateWithAnthropic($prompt, $model, $maxMarks, $criteria) {
        $ch = curl_init($this->apiUrl);
        
        $data = [
            'model' => $model,
            'max_tokens' => 1500,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT => 60
        ]);
        
        // Handle SSL verification
        if ($this->shouldVerifySSL()) {
            $certPath = $this->getCACertPath();
            if ($certPath) {
                curl_setopt($ch, CURLOPT_CAINFO, $certPath);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            // For development only - disable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // curl_close() is deprecated in PHP 8.5+, but still works
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }
        
        if ($error) {
            throw new Exception("API request failed: {$error}");
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "API returned status code {$httpCode}";
            throw new Exception("API error: {$errorMessage}");
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['content'][0]['text'])) {
            throw new Exception("Invalid API response format");
        }
        
        $content = $result['content'][0]['text'];
        $evaluation = json_decode($content, true);
        
        if (!$evaluation) {
            throw new Exception("Failed to parse AI evaluation response");
        }
        
        // Validate and format response
        return $this->formatEvaluationResult($evaluation, $maxMarks, $criteria);
    }
    
    /**
     * Evaluate using Google Gemini API
     */
    private function evaluateWithGemini($prompt, $model, $maxMarks, $criteria) {
        // Get fallback models from config
        $fallbackModels = $this->getFallbackModels(self::PROVIDER_GEMINI);
        
        // If model is the default, use fallback list; otherwise try the requested model first
        $defaultModel = $this->getDefaultModel(self::PROVIDER_GEMINI);
        if ($model === $defaultModel && !empty($fallbackModels)) {
            // Use fallback list starting with default model (gemini-2.5-flash)
            $modelsToTry = $fallbackModels;
        } else {
            // Try requested model first, then fallbacks
            $modelsToTry = array_unique(array_merge([$model], $fallbackModels));
        }
        
        // First, try to get available models from the API to verify what's actually available
        $availableModels = null;
        try {
            $availableModels = $this->listGeminiModels();
        } catch (Exception $e) {
            // If listing fails, continue with fallback approach
        }
        
        // If we have available models, prioritize models that are actually available
        if ($availableModels && !empty($availableModels)) {
            $availableModelNames = [];
            foreach ($availableModels as $availableModel) {
                $modelName = $availableModel['name'] ?? '';
                $methods = $availableModel['supportedGenerationMethods'] ?? [];
                
                if (in_array('generateContent', $methods)) {
                    $availableModelNames[] = basename($modelName);
                }
            }
            
            // Reorder modelsToTry to prioritize available models
            $prioritizedModels = [];
            $otherModels = [];
            
            foreach ($modelsToTry as $modelToTry) {
                // Check if this model or a variant is available
                $found = false;
                foreach ($availableModelNames as $availableName) {
                    if ($modelToTry === $availableName || 
                        strpos($availableName, $modelToTry) !== false ||
                        strpos($modelToTry, $availableName) !== false) {
                        $prioritizedModels[] = $modelToTry;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $otherModels[] = $modelToTry;
                }
            }
            
            $modelsToTry = array_merge($prioritizedModels, $otherModels);
        }
        
        // Try each model in order (with fallback chain)
        $apiVersions = ['v1beta', 'v1']; // Try v1beta first (has newer models)
        $lastError = null;
        $triedModels = [];
        
        foreach ($apiVersions as $apiVersion) {
            foreach ($modelsToTry as $actualModel) {
                $triedModels[] = "{$apiVersion}/{$actualModel}";
                try {
                    $baseUrl = str_replace('/v1/', "/{$apiVersion}/", $this->apiUrl);
                    $baseUrl = str_replace('/v1beta/', "/{$apiVersion}/", $baseUrl);
                    $url = str_replace('{model}', $actualModel, $baseUrl);
                    $url .= '?key=' . urlencode($this->apiKey);
                    
                    $result = $this->makeGeminiRequest($url, $prompt, $maxMarks, $criteria, $apiVersion);
                    return $result; // Success!
                } catch (Exception $e) {
                    $lastError = $e;
                    // Continue to next model in fallback chain
                    continue;
                }
            }
        }
        
        // Provide helpful error message
        $errorMsg = "Failed to find working model. Last error: " . $lastError->getMessage();
        $errorMsg .= "\nTried models in order: " . implode(' → ', array_unique($triedModels));
        if ($availableModels) {
            $supportedModels = [];
            foreach ($availableModels as $m) {
                if (in_array('generateContent', $m['supportedGenerationMethods'] ?? [])) {
                    $supportedModels[] = basename($m['name']);
                }
            }
            if (!empty($supportedModels)) {
                $errorMsg .= "\nAvailable models that support generateContent: " . implode(', ', $supportedModels);
            }
        }
        throw new Exception($errorMsg);
    }
    
    /**
     * Make a Gemini API request
     */
    private function makeGeminiRequest($url, $prompt, $maxMarks, $criteria, $apiVersion = 'v1') {
        $ch = curl_init($url);
        
        $generationConfig = [
            'temperature' => 0.3,
            'maxOutputTokens' => 1500
        ];
        
        // responseMimeType is only available in v1beta
        if ($apiVersion === 'v1beta') {
            $generationConfig['responseMimeType'] = 'application/json';
        }
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => $generationConfig,
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_NONE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_NONE'
                ]
            ]
        ];
        
        // Get timeout from config or use default
        $configFile = __DIR__ . '/../config/ai_config.php';
        $timeout = 120; // Default 2 minutes
        if (file_exists($configFile)) {
            $config = @include $configFile;
            if (isset($config['timeout']) && is_numeric($config['timeout'])) {
                $timeout = intval($config['timeout']);
            }
        }
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 30 // Connection timeout
        ]);
        
        // Handle SSL verification
        if ($this->shouldVerifySSL()) {
            $certPath = $this->getCACertPath();
            if ($certPath) {
                curl_setopt($ch, CURLOPT_CAINFO, $certPath);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            // For development only - disable SSL verification
            // WARNING: This is insecure and should only be used in development
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        // curl_close() is deprecated in PHP 8.5+, but still works
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }
        
        if ($error) {
            throw new Exception("API request failed: {$error}");
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            
            // Get detailed error message
            if (isset($errorData['error'])) {
                $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
                $errorCode = $errorData['error']['code'] ?? $httpCode;
                
                // Check if it's a model not found error - throw exception to try next model
                if (strpos($errorMessage, 'model') !== false || strpos($errorMessage, 'not found') !== false || strpos($errorMessage, '404') !== false) {
                    throw new Exception("Model not found: {$errorMessage}");
                }
                
                throw new Exception("Gemini API error (Code {$errorCode}): {$errorMessage}");
            }
            
            throw new Exception("API returned status code {$httpCode}. Response: " . substr($response, 0, 500));
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            // Check if there's a safety rating that blocked the response
            if (isset($result['candidates'][0]['safetyRatings'])) {
                $blockedReasons = [];
                foreach ($result['candidates'][0]['safetyRatings'] as $rating) {
                    if ($rating['blocked'] ?? false) {
                        $blockedReasons[] = $rating['category'];
                    }
                }
                if (!empty($blockedReasons)) {
                    throw new Exception("Response was blocked by safety filters: " . implode(', ', $blockedReasons));
                }
            }
            
            throw new Exception("Invalid API response format. Response structure: " . json_encode(array_keys($result)));
        }
        
        $content = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Try to parse JSON from response
        // Gemini might return JSON wrapped in markdown code blocks or as plain text
        $content = preg_replace('/```json\s*/i', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);
        
        // Try to extract JSON if it's embedded in text
        // Look for JSON object pattern { ... }
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $content, $matches)) {
            $content = $matches[0];
        }
        
        $evaluation = json_decode($content, true);
        
        if (!$evaluation) {
            // If JSON parsing fails, try to extract just the JSON part
            $jsonStart = strpos($content, '{');
            $jsonEnd = strrpos($content, '}');
            if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
                $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                $evaluation = json_decode($jsonContent, true);
            }
            
            if (!$evaluation) {
                throw new Exception("Failed to parse AI evaluation response. The AI might not have returned valid JSON. Raw response: " . substr($content, 0, 300));
            }
        }
        
        // Validate and format response
        return $this->formatEvaluationResult($evaluation, $maxMarks, $criteria);
    }
    
    /**
     * Format and validate evaluation result
     */
    private function formatEvaluationResult($evaluation, $maxMarks, $criteria) {
        // Ensure all required fields exist
        $result = [
            'total_marks' => isset($evaluation['total_marks']) ? floatval($evaluation['total_marks']) : 0,
            'criteria_scores' => [],
            'feedback' => $evaluation['feedback'] ?? 'No feedback provided.',
            'strengths' => $evaluation['strengths'] ?? [],
            'improvements' => $evaluation['improvements'] ?? [],
            'model_used' => $this->model,
            'provider' => $this->provider,
            'evaluated_at' => date('Y-m-d H:i:s')
        ];
        
        // Process criteria scores
        $criteriaScores = $evaluation['criteria_scores'] ?? [];
        foreach ($criteria as $criterion) {
            $result['criteria_scores'][$criterion] = isset($criteriaScores[$criterion]) 
                ? floatval($criteriaScores[$criterion]) 
                : 0;
        }
        
        // Validate total marks doesn't exceed max
        if ($result['total_marks'] > $maxMarks) {
            $result['total_marks'] = $maxMarks;
        }
        
        // Ensure total marks is not negative
        if ($result['total_marks'] < 0) {
            $result['total_marks'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Batch evaluate multiple answers
     */
    public function evaluateBatch($evaluations) {
        $results = [];
        
        foreach ($evaluations as $index => $evaluation) {
            try {
                $result = $this->evaluateAnswer(
                    $evaluation['question'],
                    $evaluation['answer'],
                    $evaluation['max_marks'] ?? 20,
                    $evaluation['criteria'] ?? []
                );
                $results[$index] = [
                    'success' => true,
                    'result' => $result
                ];
            } catch (Exception $e) {
                $results[$index] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
}

