<?php
session_start();
require_once __DIR__ . '/config/db.php';

$id = $_POST['id'];
$type = $_POST['type'];
$title = $_POST['title'];
$cover = $_POST['cover'];
$content = $_POST['content'];
$table = ($type == 'activity') ? 'activities' : 'resources';

if (empty($id)) {
    // 新增
    $stmt = $conn->prepare("INSERT INTO $table (title, cover, content) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $cover, $content);
} else {
    // 更新
    $stmt = $conn->prepare("UPDATE $table SET title=?, cover=?, content=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $cover, $content, $id);
}

if ($stmt->execute()) {
    header("Location: admin_dashboard.php?msg=success");
} else {
    echo "操作失败: " . $conn->error;
}