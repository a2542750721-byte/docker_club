<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$mode = $_GET['mode'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT username, wpm, accuracy, created_at, mode FROM typing_scores WHERE 1=1";

// Mode Filter
if ($mode !== 'all') {
    $sql .= " AND mode = '" . $conn->real_escape_string($mode) . "'";
}

// Search Filter
if (!empty($search)) {
    $sql .= " AND username LIKE '%" . $conn->real_escape_string($search) . "%'";
}

// Sort by WPM Desc, then Accuracy Desc
$sql .= " ORDER BY wpm DESC, accuracy DESC LIMIT 100";

$result = $conn->query($sql);

$data = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $data]);
?>