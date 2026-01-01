<?php
session_start();
require_once __DIR__ . '/config/db.php';

// 安全第一：检查登录
if (!isset($_SESSION['admin_id'])) {
    die("未授权的操作");
}

// 获取参数
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($id > 0 && ($type == 'activity' || $type == 'resource')) {
    // 确定表名
    $table = ($type == 'activity') ? 'activities' : 'resources';
    
    // 执行删除
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // 删除成功后跳回后台，并带上提示参数
        header("Location: admin_dashboard.php?status=deleted");
    } else {
        echo "删除错误: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "无效请求";
}