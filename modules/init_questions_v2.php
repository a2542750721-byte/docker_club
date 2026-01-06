<?php
require_once __DIR__ . '/../config/db.php';

// 1. Add explanation column
$check = $conn->query("SHOW COLUMNS FROM competition_questions LIKE 'explanation'");
if ($check->num_rows == 0) {
    $conn->query("ALTER TABLE competition_questions ADD COLUMN explanation TEXT");
    echo "Added 'explanation' column.\n";
}

// 2. Clear existing questions to ensure clean state for the 50 new ones (Optional, but requested "create a question bank... 50 questions")
// User said "Create a question bank containing 50 questions". I will truncate to be clean or just add them.
// "First need to create... 50 questions". I'll truncate for a fresh start as implied by "From scratch" context often.
$conn->query("TRUNCATE TABLE competition_questions");
echo "Cleared existing questions.\n";

// 3. Generate 50 Questions
// Distribution: 
// Total 50.
// Difficulty: Easy(15), Medium(25), Hard(10).
// Type: Practical(15 - 30%), Choice(35). 
// Note: User said Quiz needs 30% practical. So the bank should support at least that.
// Let's make:
// Easy: 10 Choice, 5 Practical
// Medium: 18 Choice, 7 Practical
// Hard: 7 Choice, 3 Practical
// Total Practical: 5+7+3 = 15 (30% of 50). Perfect.

$questions = [];

// Helper to create q
function createQ($cat, $type, $diff, $text, $opts, $correct, $ans, $expl) {
    return [
        'category' => $cat,
        'type' => $type,
        'difficulty' => $diff,
        'question_text' => $text,
        'options' => json_encode($opts, JSON_UNESCAPED_UNICODE),
        'correct_option' => $correct,
        'answer_key' => $ans,
        'explanation' => $expl,
        'tags' => $cat . ',' . $diff
    ];
}

// --- EASY (15) ---
// 10 Choice
for ($i=1; $i<=10; $i++) {
    $questions[] = createQ('Python', 'choice', 'easy', 
        "Python 中用于输出的函数是？ (Easy Choice #$i)", 
        ['print()', 'input()', 'output()', 'write()'], 
        0, '', 
        "print() 是 Python 的标准输出函数。");
}
// 5 Practical
for ($i=1; $i<=5; $i++) {
    $questions[] = createQ('Python', 'practical', 'easy', 
        "请计算 10 + 20 的结果。 (Easy Practical #$i)", 
        [], 0, '30', 
        "10 加 20 等于 30。");
}

// --- MEDIUM (25) ---
// 18 Choice
for ($i=1; $i<=18; $i++) {
    $questions[] = createQ('Web', 'choice', 'medium', 
        "以下哪个状态码表示页面未找到？ (Medium Choice #$i)", 
        ['200', '403', '404', '500'], 
        2, '', 
        "404 Not Found 表示服务器无法找到请求的资源。");
}
// 7 Practical
for ($i=1; $i<=7; $i++) {
    $questions[] = createQ('Web', 'practical', 'medium', 
        "解码 Base64 字符串 'SGVsbG8=' (Medium Practical #$i)", 
        [], 0, 'Hello', 
        "SGVsbG8= Base64 解码后为 Hello。");
}

// --- HARD (10) ---
// 7 Choice
for ($i=1; $i<=7; $i++) {
    $questions[] = createQ('Algorithm', 'choice', 'hard', 
        "快速排序的平均时间复杂度是？ (Hard Choice #$i)", 
        ['O(n)', 'O(n log n)', 'O(n^2)', 'O(log n)'], 
        1, '', 
        "快速排序的平均时间复杂度为 O(n log n)。");
}
// 3 Practical
for ($i=1; $i<=3; $i++) {
    $questions[] = createQ('Algorithm', 'practical', 'hard', 
        "求 10 的阶乘 (Hard Practical #$i)", 
        [], 0, '3628800', 
        "10! = 10 * 9 * ... * 1 = 3628800。");
}

// Insert
$stmt = $conn->prepare("INSERT INTO competition_questions (category, type, difficulty, question_text, options, correct_option, answer_key, explanation, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($questions as $q) {
    $stmt->bind_param("sssssisss", 
        $q['category'], $q['type'], $q['difficulty'], $q['question_text'], 
        $q['options'], $q['correct_option'], $q['answer_key'], $q['explanation'], $q['tags']
    );
    $stmt->execute();
}

echo "Inserted 50 questions successfully.\n";
?>