<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $title = $_POST['title'];
    $cover = $_POST['cover'];

    if ($type == 'activity') {
        $content = $_POST['content'];
        $stmt = $conn->prepare("INSERT INTO activities (title, cover, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $cover, $content);
    } else {
        $link = $_POST['link'];
        $desc = $_POST['desc'];
        $stmt = $conn->prepare("INSERT INTO resources (title, cover, content, link) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $cover, $desc, $link);
    }

    $stmt->execute();
    echo "发布成功！<a href='index.php'>返回首页</a>";
}