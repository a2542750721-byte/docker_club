<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config/db.php';

// 1. 接收所有可能的字段
$id = $_POST['id'] ?? '';
$type = $_POST['type'] ?? 'activity';
$title = $_POST['title'] ?? '';
$cover = $_POST['cover'] ?? '';
$content = $_POST['content'] ?? '';
$link = $_POST['link'] ?? ''; // 新增：接收资源链接

$table = ($type == 'activity') ? 'activities' : 'resources';

if (empty($id)) {
    // ---- 新增逻辑 ----
    if ($type == 'resource') {
        // 如果是资源，需要插入 4 个字段 (title, cover, content, link)
        $stmt = $conn->prepare("INSERT INTO resources (title, cover, content, link) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $cover, $content, $link);
    } else {
        // 如果是活动，插入 3 个字段
        $stmt = $conn->prepare("INSERT INTO activities (title, cover, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $cover, $content);
    }
} else {
    // ---- 更新逻辑 ----
    if ($type == 'resource') {
        $stmt = $conn->prepare("UPDATE resources SET title=?, cover=?, content=?, link=? WHERE id=?");
        $stmt->bind_param("ssssi", $title, $cover, $content, $link, $id);
    } else {
        $stmt = $conn->prepare("UPDATE activities SET title=?, cover=?, content=? WHERE id=?");
        $stmt->bind_param("sssi", $title, $cover, $content, $id);
    }
}

if ($stmt->execute()) {
    header("Location: admin_dashboard.php?msg=success");
} else {
    echo "操作失败: " . $conn->error;
}