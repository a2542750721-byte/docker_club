<?php
require_once __DIR__ . '/../config/db.php';

$sql = "CREATE TABLE IF NOT EXISTS practice_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    question_id INT NOT NULL,
    is_correct TINYINT(1) DEFAULT 0,
    is_marked TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_question (username, question_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table practice_progress created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
?>