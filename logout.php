<?php
session_start();

// 1. 清空所有 Session 变量
$_SESSION = array();

// 2. 如果使用了 Cookie 存储 Session ID，也将其销毁（增强安全性）
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// 3. 彻底销毁 Session 会话
session_destroy();

// 4. 跳转回登录页面或首页
header("Location: login.php");
exit;
?>