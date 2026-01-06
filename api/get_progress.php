<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$username = $_GET['username'] ?? '';

if (empty($username)) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT question_id, is_correct, is_marked FROM practice_progress WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$progress = [];
while ($row = $result->fetch_assoc()) {
    $progress[$row['question_id']] = [
        'is_correct' => (bool)$row['is_correct'],
        'is_marked' => (bool)$row['is_marked']
    ];
}

echo json_encode($progress);
?>