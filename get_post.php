<?php
session_start();
// 安全拦截
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/config/db.php';

// 设置返回头为 JSON
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? 'activity';
$table = ($type == 'activity') ? 'activities' : 'resources';

$stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Not found']);
}