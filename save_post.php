<?php
session_start();
require_once __DIR__ . '/config/db.php';

// 安全检查：没登录的不准存数据
if (!isset($_SESSION['admin_id'])) {
    die("未授权的操作");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];      // activity 或 resource
    $title = $_POST['title'];    // 标题
    $cover = $_POST['cover'];    // 图床封面
    $content = $_POST['content']; // 富文本正文

    if ($type == 'activity') {
        // 存入活动表
        $stmt = $conn->prepare("INSERT INTO activities (title, cover, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $cover, $content);
    } else {
        // 存入资源表
        $stmt = $conn->prepare("INSERT INTO resources (title, cover, content, link) VALUES (?, ?, ?, ?)");
        $link = $_POST['cover']; 
        $stmt->bind_param("ssss", $title, $cover, $content, $link);
    }

    if ($stmt->execute()) {
        // 发布成功，直接跳回后台，防止页面卡死
        header("Location: admin_dashboard.php?msg=success");
    } else {
        echo "发布失败: " . $conn->error;
    }
}