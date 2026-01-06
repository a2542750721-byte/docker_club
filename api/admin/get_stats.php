<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Aggregates
$sql_agg = "SELECT 
    COUNT(*) as total_attempts,
    AVG(time_taken) as avg_time,
    SUM(score) as total_score,
    SUM(total_questions) as total_possible
    FROM competition_results";
$agg = $conn->query($sql_agg)->fetch_assoc();

$avg_rate = 0;
if ($agg['total_possible'] > 0) {
    $avg_rate = ($agg['total_score'] / $agg['total_possible']) * 100;
}

// Recent results for Table and Charts
$sql_recent = "SELECT * FROM competition_results ORDER BY created_at DESC LIMIT 20";
$res_recent = $conn->query($sql_recent);
$recent = [];
while ($row = $res_recent->fetch_assoc()) {
    $recent[] = $row;
}

echo json_encode([
    'stats' => [
        'total_attempts' => $agg['total_attempts'],
        'avg_time' => round($agg['avg_time'], 1),
        'avg_rate' => round($avg_rate, 1)
    ],
    'recent' => $recent
]);
?>
