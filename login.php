<?php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // 查询用户
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // 验证密码（假设你用了 password_hash 加密）
        // 如果现在是明文测试，可以用 if($pass == $row['password'])
        if (password_verify($pass, $row['password']) || $pass == $row['password']) {
            $_SESSION['admin_id'] = $row['id'];
            header("Location: admin_dashboard.php");
            exit;
        }
    }
    $error = "用户名或密码错误";
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - 创享网络信息协会</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='40' fill='%23002FA7'/><text x='50' y='60' text-anchor='middle' fill='white' font-size='40' font-family='Arial'>C</text></svg>">
</head>
<body>
    <div class="login-container">
        <div class="login-card flat-card">
            <div class="login-header">
                <h2 class="section-title">管理员登录</h2>
                <p class="section-subtitle">请输入您的凭据</p>
            </div>
            
            <form method="POST" class="login-form">
                <?php if(isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="username" class="form-label">账号</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="请输入账号" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">密码</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="请输入密码" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">登录</button>
            </form>
            
            <div class="login-footer">
                <a href="index.php" class="back-to-home">
                    <i class="fas fa-arrow-left"></i> 返回首页
                </a>
            </div>
        </div>
    </div>

    <style>
        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .login-card {
            background: var(--bg-primary);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-hover);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .login-header .section-title {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .login-form {
            margin: 1.5rem 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .input-with-icon input:focus {
            outline: none;
            border-color: var(--klein-blue);
            box-shadow: 0 0 0 2px rgba(0, 47, 167, 0.1);
        }
        
        .btn-full {
            width: 100%;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        
        .back-to-home {
            color: var(--text-secondary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }
        
        .back-to-home:hover {
            color: var(--klein-blue);
        }
        
        .error-message {
            background: #fee;
            color: #f44336;
            padding: 0.75rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            border: 1px solid #fcc;
            text-align: center;
        }
        
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
                padding: 10px;
            }
            
            .login-card {
                padding: 1.5rem;
            }
        }
    </style>
</body>
</html>