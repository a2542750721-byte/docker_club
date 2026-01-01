<?php
// 处理登录、注册、注销的逻辑
session_start();

require_once __DIR__ . '/../includes/functions.php';

// 用户登录处理
function login($username, $password) {
    global $pdo;
    
    // 防止SQL注入
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}

// 用户注册处理
function register($username, $email, $password) {
    global $pdo;
    
    // 检查用户名是否已存在
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => '用户名已存在'];
    }
    
    // 检查邮箱是否已存在
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => '邮箱已被注册'];
    }
    
    // 密码加密
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // 插入新用户
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $result = $stmt->execute([$username, $email, $password_hash]);
    
    if ($result) {
        return ['success' => true, 'message' => '注册成功'];
    } else {
        return ['success' => false, 'message' => '注册失败'];
    }
}

// 用户注销
function logout() {
    session_destroy();
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    return true;
}

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (login($username, $password)) {
            echo json_encode(['success' => true, 'message' => '登录成功']);
        } else {
            echo json_encode(['success' => false, 'message' => '用户名或密码错误']);
        }
    } elseif ($action === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => '两次输入的密码不一致']);
        } else {
            $result = register($username, $email, $password);
            echo json_encode($result);
        }
    } elseif ($action === 'logout') {
        if (logout()) {
            echo json_encode(['success' => true, 'message' => '已成功注销']);
        } else {
            echo json_encode(['success' => false, 'message' => '注销失败']);
        }
    }
}
?>