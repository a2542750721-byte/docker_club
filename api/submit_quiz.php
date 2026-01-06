<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$username = $input['username'] ?? 'Anonymous';
$answers = $input['answers'] ?? [];
$time_taken = $input['time_taken'] ?? 0;

$score = 0;
$total_questions = count($answers);

if (empty($answers)) {
    echo json_encode(['score' => 0, 'total' => 0, 'time_taken' => $time_taken]);
    exit;
}

// Calculate score
// We need to fetch correct answers for the submitted questions
$ids = array_keys($answers);
if (empty($ids)) {
     echo json_encode(['score' => 0, 'total' => 0, 'time_taken' => $time_taken]);
     exit;
}

$review_data = []; // Store wrong answers details

foreach ($answers as $q_id => $user_answer) {
    if (!isset($questions_data[$q_id])) continue;
    
    $q = $questions_data[$q_id];
    $is_correct = false;
    $correct_val = '';
    
    if ($q['type'] === 'practical') {
        $correct_val = $q['answer_key'];
        if (trim($user_answer) === trim($q['answer_key'])) {
            $is_correct = true;
        }
    } else {
        // We need to fetch option text to display correct answer, but we only fetched basic info.
        // Let's refactor the SQL to fetch question_text and options too for review.
        // Wait, fetching options here is heavy? No, it's just text.
        // Re-doing the loop logic below after updating SQL.
    }
}

// --- Refactored Fetch Logic ---
$sql = "SELECT id, question_text, options, correct_option, type, answer_key, explanation FROM competition_questions WHERE id IN ($id_placeholders)";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$questions_data = [];
while ($row = $result->fetch_assoc()) {
    $row['options'] = json_decode($row['options']); // Decode JSON options
    $questions_data[$row['id']] = $row;
}

foreach ($answers as $q_id => $user_answer) {
    if (!isset($questions_data[$q_id])) continue;
    
    $q = $questions_data[$q_id];
    $is_correct = false;
    $correct_display = '';
    $user_display = $user_answer;
    
    if ($q['type'] === 'practical') {
        $correct_display = $q['answer_key'];
        if (trim($user_answer) === trim($q['answer_key'])) {
            $is_correct = true;
        }
    } else {
        // Choice
        $opts = $q['options'];
        $correct_idx = intval($q['correct_option']);
        $user_idx = intval($user_answer);
        
        $correct_display = isset($opts[$correct_idx]) ? chr(65+$correct_idx) . ". " . $opts[$correct_idx] : "Unknown";
        $user_display = isset($opts[$user_idx]) ? chr(65+$user_idx) . ". " . $opts[$user_idx] : "Unanswered";
        
        if ($user_idx === $correct_idx) {
            $is_correct = true;
        }
    }
    
    if ($is_correct) {
        $score++;
    } else {
        $review_data[] = [
            'question_text' => $q['question_text'],
            'user_answer' => $user_display,
            'correct_answer' => $correct_display,
            'explanation' => $q['explanation']
        ];
    }
}

// Data masking for username
$masked_username = mb_substr($username, 0, 1) . '***' . (mb_strlen($username) > 1 ? mb_substr($username, -1) : '');

// Insert result
$stmt = $conn->prepare("INSERT INTO competition_results (username, score, total_questions, time_taken) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siii", $masked_username, $score, $total_questions, $time_taken);
$stmt->execute();

echo json_encode([
    'score' => $score,
    'total' => $total_questions,
    'time_taken' => $time_taken,
    'review' => $review_data
]);
?>
