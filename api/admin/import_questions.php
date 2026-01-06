<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

$count = 0;
$errors = [];

$stmt = $conn->prepare("INSERT INTO competition_questions (question_text, options, correct_option, category, type, answer_key, difficulty, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($input as $index => $q) {
    // Basic validation
    if (empty($q['question_text']) || empty($q['category'])) {
        $errors[] = "Row $index: Missing required fields";
        continue;
    }

    $text = $q['question_text'];
    $options = is_array($q['options']) ? json_encode($q['options']) : $q['options']; // Handle both array and string (if already json)
    // If it's a string, ensure it's valid JSON, else default to empty array
    if (is_string($options)) {
        $decoded = json_decode($options);
        if ($decoded === null) $options = '[]';
    }
    
    $correct = isset($q['correct_option']) ? intval($q['correct_option']) : 0;
    $category = $q['category'];
    $type = isset($q['type']) ? $q['type'] : 'choice';
    $answer_key = isset($q['answer_key']) ? $q['answer_key'] : '';
    $difficulty = isset($q['difficulty']) ? $q['difficulty'] : 'medium';
    $tags = isset($q['tags']) ? $q['tags'] : '';

    $stmt->bind_param("ssisssss", $text, $options, $correct, $category, $type, $answer_key, $difficulty, $tags);
    
    if ($stmt->execute()) {
        $count++;
    } else {
        $errors[] = "Row $index: " . $stmt->error;
    }
}

echo json_encode(['success' => true, 'count' => $count, 'errors' => $errors]);
?>
