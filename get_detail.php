<?php
require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 联合查询（或者根据业务逻辑只查活动表，这里以 activities 为例）
    $stmt = $conn->prepare("SELECT title, content, cover, created_at FROM activities WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["title" => "未找到", "content" => "该文章不存在或已被删除。"]);
    }
}