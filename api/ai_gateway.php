<?php

header('Content-Type: application/json');

// Load configuration
$config = require __DIR__ . '/../config/ai_config.php';
$apiKey = $config['dashscope_api_key'];

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? '';
$prompt = $input['prompt'] ?? '';
$img_url = $input['img_url'] ?? '';

// Validation: Prompt is required unless it's a fetch_task request
if (!$prompt && $type !== 'fetch_task') {
    echo json_encode(['error' => 'Prompt cannot be empty']);
    exit;
}

$url = '';
$headers = [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
];
$postData = [];
$method = 'POST'; // Default method

switch ($type) {
    case 'text':
        $url = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions';
        
        // Handle conversation history
        $messages = [];
        $messages[] = ["role" => "system", "content" => "You are a helpful assistant."];
        
        if (!empty($input['history']) && is_array($input['history'])) {
            foreach ($input['history'] as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = ["role" => $msg['role'], "content" => $msg['content']];
                }
            }
        }
        
        // Add current prompt
        $messages[] = ["role" => "user", "content" => $prompt];

        $postData = [
            "model" => "qwen-plus",
            "messages" => $messages
        ];
        break;

    case 'image':
        $url = 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text2image/image-synthesis';
        $headers[] = 'X-DashScope-Async: enable';
        $postData = [
            "model" => "wanx-v1",
            "input" => [
                "prompt" => $prompt
            ],
            "parameters" => [
                "style" => "<auto>",
                "size" => "1024*1024",
                "n" => 1
            ]
        ];
        break;

    case 'video':
        $url = 'https://dashscope.aliyuncs.com/api/v1/services/aigc/video-generation/video-synthesis';
        $headers[] = 'X-DashScope-Async: enable'; 
        $postData = [
            "model" => "wan2.1-i2v-720p",
            "input" => [
                "prompt" => $prompt,
                "img_url" => $img_url
            ]
        ];
        break;
        
    case 'fetch_task':
        $taskId = $input['task_id'] ?? '';
        if (!$taskId) {
            echo json_encode(['error' => 'Task ID is required']);
            exit;
        }
        $url = "https://dashscope.aliyuncs.com/api/v1/tasks/$taskId";
        $method = 'GET';
        break;
    
    default:
        echo json_encode(['error' => 'Invalid type']);
        exit;
}

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
} else {
    curl_setopt($ch, CURLOPT_HTTPGET, true);
}

// Execute request
$response = curl_exec($ch);

// Handle errors
if (curl_errno($ch)) {
    echo json_encode(['error' => curl_error($ch)]);
} else {
    // Try to decode JSON to ensure we are returning valid JSON
    $decoded = json_decode($response);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        // If upstream returned non-JSON (e.g. HTML error), wrap it
        echo json_encode(['error' => 'Upstream API error: ' . $response]);
    } else {
        echo $response;
    }
}
curl_close($ch);
