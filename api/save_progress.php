<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['username']) || !isset($input['progress'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$username = $input['username'];
$progress = $input['progress']; // Array of { question_id, is_correct, is_marked }

if (empty($progress)) {
    echo json_encode(['success' => true]);
    exit;
}

// Prepare statement for upsert
$sql = "INSERT INTO practice_progress (username, question_id, is_correct, is_marked) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        is_correct = VALUES(is_correct), 
        is_marked = VALUES(is_marked)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Database error: ' . $conn->error]);
    exit;
}

$success = true;
foreach ($progress as $item) {
    if (!isset($item['question_id'])) continue;
    
    $q_id = $item['question_id'];
    $is_correct = isset($item['is_correct']) ? (int)$item['is_correct'] : 0;
    $is_marked = isset($item['is_marked']) ? (int)$item['is_marked'] : 0;
    
    $stmt->bind_param("siii", $username, $q_id, $is_correct, $is_marked);
    if (!$stmt->execute()) {
        $success = false;
        break;
    }
}

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to save some progress']);
}
?>