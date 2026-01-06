<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? $_GET['category'] : '';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
$limit = isset($_GET['count']) ? intval($_GET['count']) : 10;
$ids = isset($_GET['ids']) ? $_GET['ids'] : '';

if ($limit < 1) $limit = 10;
if ($limit > 50) $limit = 50;

// Build query
$sql = "SELECT id, question_text, options, correct_option, category, type FROM competition_questions";
$params = [];
$types = "";

if ($ids) {
    // Manual selection mode
    $id_array = explode(',', $ids);
    $id_array = array_filter($id_array, function($v) { return is_numeric($v) && intval($v) > 0; });
    
    if (empty($id_array)) {
        echo json_encode([]);
        exit;
    }
    
    // Securely construct IN clause
    $placeholders = implode(',', array_fill(0, count($id_array), '?'));
    $sql .= " WHERE id IN ($placeholders)";
    $params = $id_array;
    $types = str_repeat('i', count($id_array));
    
    // No order by RAND() for manual? Maybe keep them in order of ID or input? 
    // Usually manual selection implies specific set. Randomizing order within that set is good for quiz.
    $sql .= " ORDER BY RAND()"; 
} else {
    // Auto generation mode
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
    
    $sql .= " ORDER BY RAND() LIMIT ?";
    $params[] = $limit;
    $types .= "i";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $row['options'] = json_decode($row['options']);
    
    // Randomize options if it's a choice question
    if ($row['type'] === 'choice' && is_array($row['options'])) {
        $options = $row['options'];
        
        $shuffled_options = [];
        foreach ($options as $idx => $opt_text) {
            $shuffled_options[] = ['id' => $idx, 'text' => $opt_text];
        }
        shuffle($shuffled_options);
        $row['options'] = $shuffled_options; 
    }
    
    // Remove correct_option from output
    unset($row['correct_option']);
    $questions[] = $row;
}

echo json_encode($questions);
?>