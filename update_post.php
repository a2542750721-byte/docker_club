<?php
session_start();
// 安全拦截：必须是登录管理员才能操作
if (!isset($_SESSION['admin_id'])) {
    exit("Unauthorized access");
}

require_once __DIR__ . '/config/db.php';

// 获取并安全化输入
$id = $_POST['id'] ?? '';
$type = $_POST['type'] ?? 'activity';
$title = $_POST['title'] ?? '';
$cover = $_POST['cover'] ?? '';
$content = $_POST['content'] ?? '';

// 根据类型选择表名
$table = ($type == 'activity') ? 'activities' : 'resources';

if (empty($id)) {
    // 新增模式
    $stmt = $conn->prepare("INSERT INTO $table (title, cover, content) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $cover, $content);
} else {
    // 更新模式
    $stmt = $conn->prepare("UPDATE $table SET title=?, cover=?, content=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $cover, $content, $id);
}

if ($stmt->execute()) {
    // 操作成功，重定向回后台首页
    header("Location: admin_dashboard.php?msg=success");
    exit;
} else {
    echo "操作失败: " . $conn->error;
}