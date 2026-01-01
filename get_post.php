<?php
session_start();
require_once __DIR__ . '/config/db.php';

$id = $_GET['id'];
$type = $_GET['type'];
$table = ($type == 'activity') ? 'activities' : 'resources';

$stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_assoc());