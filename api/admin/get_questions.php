<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$sql = "SELECT * FROM competition_questions ORDER BY id DESC";
$result = $conn->query($sql);

$questions = [];
while ($row = $result->fetch_assoc()) {
    $row['options'] = json_decode($row['options']);
    $questions[] = $row;
}

echo json_encode($questions);
?>
