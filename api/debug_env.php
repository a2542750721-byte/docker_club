<?php
header('Content-Type: text/plain');

echo "=== PHP Environment Debug Info ===\n";
echo "Hostname: " . gethostname() . "\n";
echo "Server IP: " . $_SERVER['SERVER_ADDR'] . "\n";
echo "Client IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n"; // This will prove if it's FPM or Apache
echo "PHP Version: " . phpversion() . "\n\n";

echo "=== Database Connection Check ===\n";
$db_host = getenv('DB_HOST') ?: 'db';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '000000';
$db_name = getenv('DB_NAME') ?: 'club_db';

echo "Target Host: $db_host\n";
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo "Connection Failed: " . $conn->connect_error . "\n";
} else {
    echo "Connection Successful!\n";
    echo "Server Info: " . $conn->server_info . "\n";
    $conn->close();
}

echo "\n=== \$_SERVER Variables ===\n";
print_r($_SERVER);
