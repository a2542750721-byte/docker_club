<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="questions_export_' . date('Y-m-d') . '.json"');

$category = isset($_GET['category']) ? $_GET['category'] : '';
$sql = "SELECT * FROM competition_questions";
if ($category) {
    $sql .= " WHERE category = '" . $conn->real_escape_string($category) . "'";
}

$result = $conn->query($sql);
$questions = [];
while ($row = $result->fetch_assoc()) {
    $row['options'] = json_decode($row['options']);
    $questions[] = $row;
}

echo json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
