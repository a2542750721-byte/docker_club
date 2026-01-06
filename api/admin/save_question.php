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

if (!$input) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$text = $input['question_text'];
$options = json_encode($input['options']); // Array of 4 strings
$correct = intval($input['correct_option']);
$category = $input['category'];
$type = isset($input['type']) ? $input['type'] : 'choice';
$answer_key = isset($input['answer_key']) ? $input['answer_key'] : '';
$difficulty = isset($input['difficulty']) ? $input['difficulty'] : 'medium';
$tags = isset($input['tags']) ? $input['tags'] : '';
$id = isset($input['id']) ? intval($input['id']) : null;

if ($id) {
    // Update
    $stmt = $conn->prepare("UPDATE competition_questions SET question_text=?, options=?, correct_option=?, category=?, type=?, answer_key=?, difficulty=?, tags=? WHERE id=?");
    $stmt->bind_param("ssisssssi", $text, $options, $correct, $category, $type, $answer_key, $difficulty, $tags, $id);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO competition_questions (question_text, options, correct_option, category, type, answer_key, difficulty, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssss", $text, $options, $correct, $category, $type, $answer_key, $difficulty, $tags);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}
?>
