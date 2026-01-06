<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); exit;
}
require_once __DIR__ . '/config/db.php';

$count_act = $conn->query("SELECT COUNT(*) as total FROM activities")->fetch_assoc()['total'];
$count_res = $conn->query("SELECT COUNT(*) as total FROM resources")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理系统 - 创享网络信息协会</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* 预览框样式 */
        .cover-preview-wrapper {
            width: 100%;
            height: 200px;
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            margin-top: 10px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed var(--border-color);
        }
        .cover-preview-wrapper img {
            width: 100%; height: 100%; object-fit: cover; display: none;
        }
        /* 表格内图片样式 */
        .admin-table-img {
            width: 50px; height: 30px; object-fit: cover; border-radius: 4px;
        }
    </style>
</head>

<body class="admin-dashboard">
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo"><a href="index.php" style="text-decoration: none;"><h2>创享网络信息协会</h2></a></div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link">首页</a></li>
                <li class="nav-item"><a href="admin_dashboard.php" class="nav-link active">管理后台</a></li>
            </ul>
            <div style="display: flex; align-items: center; gap: 15px;">
                <button id="theme-toggle" class="theme-toggle"><i class="fas fa-adjust"></i></button>
            </div>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <div class="admin-stats-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:30px;">
                <div class="card flat-card" style="padding:20px; text-align:center;">
                    <h3>近期活动</h3><p class="stat-value"><?php echo $count_act; ?> 条</p>
                </div>
                <div class="card flat-card" style="padding:20px; text-align:center;">
                    <h3>学习资源</h3><p class="stat-value"><?php echo $count_res; ?> 个</p>
                </div>
            </div>

            <div class="admin-actions" style="text-align:center;">
                <div class="admin-buttons" style="display:flex; justify-content:center; gap:15px;">
                    <button onclick="openPostModal()" class="btn btn-primary"><i class="fas fa-plus"></i> 发布新内容</button>
                    <button onclick="openManageModal()" class="btn btn-secondary"><i class="fas fa-cog"></i> 管理内容</button>
                    <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i> 安全退出</a>
                </div>
            </div>
        </div>
    </section>

    <div id="postModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">发布新内容</h3>
                <span class="close" onclick="closePostModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form action="update_post.php" method="POST" id="postForm">
                    <input type="hidden" name="id" id="postId">
                    <div class="form-group">
                        <label class="form-label">内容类型</label>
                        <select name="type" id="postType" class="form-select">
                            <option value="activity">近期活动</option>
                            <option value="resource">学习资源</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">标题</label>
                        <input type="text" name="title" id="postTitle" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">封面图片链接</label>
                        <input type="text" name="cover" id="postCover" class="form-input" oninput="previewImg(this.value)">
                        <div class="cover-preview-wrapper">
                            <img id="img-pre" src="">
                            <span id="pre-text">16:9 裁剪预览区</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">正文</label>
                        <div id="editor-container" class="editor-container">
                            <div id="toolbar-container"></div>
                            <div id="content-container" style="height:300px;"></div>
                        </div>
                        <input type="hidden" name="content" id="hiddenContent">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">保存提交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="manageModal" class="modal">
        <div class="modal-content" style="max-width:900px;">
            <div class="modal-header"><h3>内容管理</h3><span class="close" onclick="closeManageModal()">&times;</span></div>
            <div class="modal-body">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border-color); text-align:left;">
                            <th style="padding:10px;">标题</th>
                            <th style="padding:10px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT id, title, 'activity' as type FROM activities UNION SELECT id, title, 'resource' as type FROM resources ORDER BY id DESC");
                        while($row = $res->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:10px;"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td style="padding:10px;">
                                <button onclick="editPost(<?php echo $row['id']; ?>, '<?php echo $row['type']; ?>')" style="color:var(--klein-blue); cursor:pointer; background:none; border:none; margin-right:10px;">编辑</button>
                                <button onclick="deletePost(<?php echo $row['id']; ?>, '<?php echo $row['type']; ?>')" style="color:red; cursor:pointer; background:none; border:none;">删除</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet">
    <script src="https://unpkg.com/@wangeditor/editor@latest/dist/index.js"></script>
    <script>
        const { createEditor, createToolbar } = window.wangEditor;
        let editor = null;

        function initEditor() {
            if (editor) return;
            editor = createEditor({
                selector: '#content-container',
                config: { onChange(editor) { document.getElementById('hiddenContent').value = editor.getHtml(); } }
            });
            createToolbar({ editor, selector: '#toolbar-container' });
        }

        function previewImg(url) {
            const img = document.getElementById('img-pre');
            const txt = document.getElementById('pre-text');
            if(url) { img.src = url; img.style.display='block'; txt.style.display='none'; }
            else { img.style.display='none'; txt.style.display='block'; }
        }

        function openPostModal() {
            document.getElementById('postId').value = '';
            document.getElementById('postForm').reset();
            document.getElementById('modalTitle').innerText = '发布新内容';
            previewImg('');
            document.getElementById('postModal').style.display='block';
            initEditor(); editor.setHtml('');
        }

        function editPost(id, type) {
            closeManageModal();
            fetch(`get_post.php?id=${id}&type=${type}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('postId').value = id;
                    document.getElementById('postType').value = type;
                    document.getElementById('postTitle').value = data.title;
                    document.getElementById('postCover').value = data.cover;
                    previewImg(data.cover);
                    document.getElementById('modalTitle').innerText = '编辑内容';
                    document.getElementById('postModal').style.display='block';
                    initEditor(); editor.setHtml(data.content);
                });
        }

        function deletePost(id, type) { if(confirm('确定删除？')) window.location.href=`delete_post.php?id=${id}&type=${type}`; }
        function closePostModal() { document.getElementById('postModal').style.display='none'; }
        function openManageModal() { document.getElementById('manageModal').style.display='block'; }
        function closeManageModal() { document.getElementById('manageModal').style.display='none'; }
        window.onclick = function(e) { if(e.target.className === 'modal') { closePostModal(); closeManageModal(); } }
    </script>
</body>
</html>