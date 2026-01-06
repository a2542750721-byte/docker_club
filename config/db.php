<?php

$s_h = getenv('DB_HOST') ?: 'db';       // 主机地址
$s_u = getenv('DB_USER') ?: 'root';     // 数据库账号
$s_p = getenv('DB_PASS') ?: '000000'; // 数据库密码
$s_d = getenv('DB_NAME') ?: 'club_db';  // 数据库名

mysqli_report(MYSQLI_REPORT_OFF); 
$conn = @new mysqli($s_h, $s_u, $s_p, $s_d);
//检查连接状态
if ($conn->connect_error) {
    // 确保返回 JSON 错误而不是纯文本，以免破坏前端解析
    if (defined('DOING_AJAX') || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Database Connection Failed: " . $conn->connect_error . " (Host: $s_h)"
        ]);
        exit;
    }
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

// --- 竞赛相关表自动初始化 ---
$t_comp_questions = "CREATE TABLE IF NOT EXISTS competition_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    options JSON,
    correct_option INT,
    type VARCHAR(20) DEFAULT 'choice',
    answer_key VARCHAR(255) DEFAULT '',
    category VARCHAR(50) DEFAULT 'Python',
    difficulty VARCHAR(20) DEFAULT 'medium',
    tags VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$t_comp_progress = "CREATE TABLE IF NOT EXISTS practice_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    question_id INT NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    is_marked TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_question (username, question_id)
)";

$t_comp_results = "CREATE TABLE IF NOT EXISTS competition_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    time_taken INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($t_activities);
$conn->query($t_resources);
$conn->query($t_passwd);
$conn->query($t_comp_questions);
$conn->query($t_comp_progress);
$conn->query($t_comp_results);

$t_typing_scores = "CREATE TABLE IF NOT EXISTS typing_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL,
    wpm INT NOT NULL,
    accuracy INT NOT NULL,
    satisfaction TINYINT DEFAULT 5,
    mode VARCHAR(10) DEFAULT 'english',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($t_typing_scores);

// 检查并自动升级 competition_questions 表结构 (添加新字段)
$check_cols = $conn->query("SHOW COLUMNS FROM competition_questions LIKE 'difficulty'");
if ($check_cols && $check_cols->num_rows == 0) {
    $conn->query("ALTER TABLE competition_questions ADD COLUMN difficulty VARCHAR(20) DEFAULT 'medium'");
    $conn->query("ALTER TABLE competition_questions ADD COLUMN tags VARCHAR(255) DEFAULT ''");
    $conn->query("ALTER TABLE competition_questions ADD COLUMN type VARCHAR(20) DEFAULT 'choice'");
    $conn->query("ALTER TABLE competition_questions ADD COLUMN answer_key VARCHAR(255) DEFAULT ''");
}

