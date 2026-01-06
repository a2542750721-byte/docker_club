<?php
// modules/init_admin.php
// 用途：批量生成管理员账号
require_once __DIR__ . '/../config/db.php';

// Helper: Generate secure random password
function generateStrongPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

echo "<h3>批量生成管理员账号 (共10个)</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; text-align: left;'>";
echo "<tr><th>序号</th><th>用户名</th><th>密码 (请妥善保存)</th><th>状态</th></tr>";

// Generate 10 admins
for ($i = 1; $i <= 10; $i++) {
    $username = 'admin_' . str_pad($i, 2, '0', STR_PAD_LEFT); // admin_01, admin_02...
    $password = generateStrongPassword(16); // 16位强密码
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if exists
    $check = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        // Update password if exists (Optional, but good for reset)
        $update = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $update->bind_param("ss", $password_hash, $username);
        $update->execute();
        $status = "<span style='color:orange'>已更新密码</span>";
    } else {
        // Insert new
        $insert = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $insert->bind_param("ss", $username, $password_hash);
        $insert->execute();
        $status = "<span style='color:green'>创建成功</span>";
    }
    
    echo "<tr>";
    echo "<td>$i</td>";
    echo "<td>$username</td>";
    echo "<td style='font-family: monospace; color: #d63384;'>$password</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><a href='../login.php'>点击去登录</a>";
?>