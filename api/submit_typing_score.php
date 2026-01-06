<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid Request Method']);
    exit;
}

// Check Daily Limit (3 per IP)
$ip = $_SERVER['REMOTE_ADDR'];
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) FROM typing_scores WHERE ip_address = ? AND DATE(created_at) = ?");
$stmt->bind_param("ss", $ip, $today);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count >= 3) {
    echo json_encode(['success' => false, 'message' => '今日提交次数已达上限 (3次)']);
    exit;
}

// Input Validation
$input = json_decode(file_get_contents('php://input'), true);

$username = trim($input['username'] ?? '');
$wpm = intval($input['wpm'] ?? 0);
$acc = intval($input['accuracy'] ?? 0);
$sat = intval($input['satisfaction'] ?? 5);
$mode = trim($input['mode'] ?? 'english');
$timestamp_log = $input['timestamp_log'] ?? []; // Array of timestamps

// Basic Validation
if (mb_strlen($username) > 20 || mb_strlen($username) < 1) {
    echo json_encode(['success' => false, 'message' => '名字长度需在1-20字之间']);
    exit;
}

if ($wpm > 300) { // Super human check
    echo json_encode(['success' => false, 'message' => '异常数据：速度过快']);
    exit;
}

// Timestamp Interval Check (Anti-Cheat)
// If provided, check if intervals are inhumanly consistent or fast (e.g. < 20ms consistently)
if (!empty($timestamp_log) && is_array($timestamp_log) && count($timestamp_log) > 10) {
    $intervals = [];
    $suspicious_fast = 0;
    for ($i = 1; $i < count($timestamp_log); $i++) {
        $diff = $timestamp_log[$i] - $timestamp_log[$i-1];
        if ($diff < 30) $suspicious_fast++; // Less than 30ms is very fast for typing
    }
    
    // If more than 50% of keystrokes are inhumanly fast
    if ($suspicious_fast > count($timestamp_log) * 0.5) {
         echo json_encode(['success' => false, 'message' => '系统检测到异常输入行为，成绩无效']);
         exit;
    }
}

// Insert Score
$stmt = $conn->prepare("INSERT INTO typing_scores (username, wpm, accuracy, satisfaction, mode, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("siisss", $username, $wpm, $acc, $sat, $mode, $ip);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '成绩提交成功！']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $conn->error]);
}
$stmt->close();
?>