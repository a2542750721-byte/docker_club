<?php require_once __DIR__ . '/config/db.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>发布新内容 - 创享网络信息协会</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='40' fill='%23002FA7'/><text x='50' y='60' text-anchor='middle' fill='white' font-size='40' font-family='Arial'>C</text></svg>">
    <script>
        // Apply theme before page renders to prevent FOUC
        (function() {
            // Check for saved theme in localStorage
            const savedTheme = localStorage.getItem('theme');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Apply theme: prioritize user selection, fallback to system preference
            if (savedTheme) {
                if (savedTheme === 'dark-mode') {
                    document.documentElement.classList.add('dark-mode');
                }
            } else if (prefersDarkScheme.matches) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php" style="text-decoration: none;">
                    <h2>创享网络信息协会</h2>
                </a>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">首页</a>
                </li>
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link">管理后台</a>
                </li>
                <li class="nav-item">
                    <a href="admin.php" class="nav-link active">发布内容</a>
                </li>
            </ul>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <button id="theme-toggle" class="theme-toggle" aria-label="切换主题">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </div>
    </nav>

    <section class="section admin-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">发布新内容</h2>
                <p class="section-subtitle">创建新的活动或学习资源</p>
            </div>
            
            <div class="admin-content">
                <div class="form-card flat-card">
                    <form action="save.php" method="POST" id="postForm">
                        <div class="form-group">
                            <label for="typeSelect" class="form-label">内容类型</label>
                            <select name="type" id="typeSelect" class="form-select" onchange="toggleMode()">
                                <option value="activity">近期活动 (长文章)</option>
                                <option value="resource">学习资源 (链接)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title" class="form-label">标题</label>
                            <input type="text" name="title" id="title" class="form-input" placeholder="请输入标题" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cover" class="form-label">封面图片链接</label>
                            <input type="text" name="cover" id="cover" class="form-input" placeholder="图床图片链接">
                        </div>
                        
                        <div id="activity-fields">
                            <div class="form-group">
                                <label class="form-label">活动内容</label>
                                <div id="editor-toolbar" class="editor-toolbar" style="border: 1px solid var(--border-color); border-bottom: none;"></div>
                        <div id="editor-text" class="editor-container" style="height:450px; border: 1px solid var(--border-color); border-top: none;"></div>
                                <input type="hidden" name="content" id="hiddenContent">
                            </div>
                        </div>

                        <div id="resource-fields" style="display:none;">
                            <div class="form-group">
                                <label for="link" class="form-label">下载链接</label>
                                <input type="text" name="link" id="link" class="form-input" placeholder="下载链接 (如网盘或图床文件)">
                            </div>
                            
                            <div class="form-group">
                                <label for="desc" class="form-label">简短描述</label>
                                <textarea name="desc" id="desc" class="form-textarea" placeholder="简短描述..." rows="4"></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> 立即发布
                            </button>
                            <a href="admin_dashboard.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> 返回管理后台
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script src="https://unpkg.com/@wangeditor/editor@latest/dist/index.js"></script>
    <script>
        const { createEditor, createToolbar } = window.wangEditor;
        const editor = createEditor({
            selector: '#editor-text',
            html: '',
            onChange(editor) { 
                document.getElementById('hiddenContent').value = editor.getHtml(); 
            }
        });
        createToolbar({ 
            editor, 
            selector: '#editor-toolbar' 
        });

        function toggleMode() {
            const type = document.getElementById('typeSelect').value;
            document.getElementById('activity-fields').style.display = type === 'activity' ? 'block' : 'none';
            document.getElementById('resource-fields').style.display = type === 'resource' ? 'block' : 'none';
        }
        
        // 初始化时设置正确的显示状态
        toggleMode();
        
        // 主题切换功能
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;
            
            // 检查本地存储中的主题设置
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark-mode') {
                body.classList.add('dark-mode');
            }
            
            themeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');
                // 保存主题设置到本地存储
                if (body.classList.contains('dark-mode')) {
                    localStorage.setItem('theme', 'dark-mode');
                } else {
                    localStorage.setItem('theme', 'light-mode');
                }
            });
        });
    </script>
    
    <style>
        .form-card {
            background: var(--bg-primary);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
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
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--klein-blue);
            box-shadow: 0 0 0 2px rgba(0, 47, 167, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--klein-blue);
            box-shadow: 0 0 0 2px rgba(0, 47, 167, 0.1);
        }
        
        .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
            resize: vertical;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: var(--klein-blue);
            box-shadow: 0 0 0 2px rgba(0, 47, 167, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .form-actions .btn {
            flex: 1;
            min-width: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .editor-toolbar {
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
        }
        
        .editor-container {
            border-radius: 0 0 var(--border-radius) var(--border-radius) !important;
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }
    </style>
</body>
</html>