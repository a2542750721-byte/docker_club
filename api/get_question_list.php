<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT id, question_text, category, difficulty, type FROM competition_questions";
if ($category) {
    $sql .= " WHERE category = '" . $conn->real_escape_string($category) . "'";
}
$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

echo json_encode($questions);
?>