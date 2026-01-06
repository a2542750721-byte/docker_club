<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? $_GET['category'] : '';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';

// Build query
$sql = "SELECT id, question_text, options, correct_option, category, type, answer_key, difficulty, tags, explanation FROM competition_questions";
$params = [];
$types = "";

$conditions = [];
if ($category) {
    $conditions[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}
if ($difficulty) {
    $conditions[] = "difficulty = ?";
    $params[] = $difficulty;
    $types .= "s";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY RAND() LIMIT 1";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $row['options'] = json_decode($row['options']);
    
    // Randomize options if it's a choice question
    if ($row['type'] === 'choice' && is_array($row['options'])) {
        $options = $row['options'];
        $correct_content = $options[$row['correct_option']]; // Get the content of the correct answer
        
        shuffle($options); // Shuffle the array
        
        // Find new index of the correct answer
        $new_correct_index = array_search($correct_content, $options);
        
        $row['options'] = $options;
        $row['correct_option'] = $new_correct_index;
    }
    
    // Hide answer key for practical questions in practice mode? 
    // Usually practice mode shows immediate feedback, so we might need it.
    // But for security, maybe we should check answer on server?
    // The current implementation of practice mode checks on client side for 'choice', 
    // and checks practical on client side too (passing answer_key).
    // Ideally we should check on server, but to keep it simple and consistent with previous code:
    // We send correct_option and answer_key to client.
    
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'No questions found']);
}
?>
