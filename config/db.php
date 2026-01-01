<?php

$s_h = getenv('DB_HOST') ?: 'db';       // 主机地址
$s_u = getenv('DB_USER') ?: 'root';     // 数据库账号
$s_p = getenv('DB_PASS') ?: '000000'; // 数据库密码
$s_d = getenv('DB_NAME') ?: 'club_db';  // 数据库名

mysqli_report(MYSQLI_REPORT_OFF); 
$conn = new mysqli($s_h, $s_u, $s_p, $s_d);
//检查连接状态
if ($conn->connect_error) {
    die("数据库连接失败，请检查容器状态: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$t_activities = "CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,      -- 文章标题
    cover VARCHAR(255),               -- 封面图（图床链接）
    content LONGTEXT,                 -- 富文本长内容
    link VARCHAR(255) DEFAULT '',     -- 备用链接（比如活动报名地址）
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// --- 资源表：重点在于 content 是普通文本，link 是下载地址 ---
$t_resources = "CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,      -- 资源名称
    cover VARCHAR(255),               -- 资源预览图（图床链接）
    content TEXT,                     -- 资源简介（短一点）
    link TEXT NOT NULL,               -- 下载链接（图床/云盘链接）
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$t_passwd = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- 存储加密后的哈希值
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($t_activities);
$conn->query($t_resources);
$conn->query($t_passwd);

