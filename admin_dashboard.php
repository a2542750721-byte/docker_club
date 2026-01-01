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
    <title>后台管理系统 - 创享网络信息协会</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); }
        .modal-content { background: var(--bg-primary); margin: 5% auto; border-radius: 12px; width: 90%; max-width: 850px; max-height: 85vh; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; background: var(--bg-primary); z-index: 10; }
        .modal-body { padding: 1.5rem; }
        .close { cursor: pointer; font-size: 2rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-label { display: block; margin-bottom: 0.5rem; color: var(--text-primary); font-weight: 500; }
        .form-input, .form-select { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-secondary); color: var(--text-primary); box-sizing: border-box; }
        .editor-container { border: 1px solid var(--border-color) !important; border-radius: 8px !important; overflow: hidden; }
        .manage-table { width: 100%; border-collapse: collapse; }
        .manage-table th, .manage-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); color: var(--text-primary); }
    </style>
</head>

<body class="admin-dashboard">
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" style="text-decoration:none; color:inherit;"><h2>创享网络信息协会</h2></a>
            <button id="theme-toggle" class="theme-toggle"><i class="fas fa-adjust"></i></button>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <div class="admin-stats-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:30px;">
                <div class="card flat-card" style="padding:20px; text-align:center; background:var(--bg-primary); border-radius:12px;">
                    <h3>近期活动</h3><p style="font-size:24px; color:var(--klein-blue);"><?php echo $count_act; ?></p>
                </div>
                <div class="card flat-card" style="padding:20px; text-align:center; background:var(--bg-primary); border-radius:12px;">
                    <h3>学习资源</h3><p style="font-size:24px; color:var(--klein-blue);"><?php echo $count_res; ?></p>
                </div>
            </div>

            <div class="admin-buttons" style="display:flex; gap:15px; justify-content:center;">
                <button onclick="openPostModal()" class="btn btn-primary">发布新内容</button>
                <button onclick="openManageModal()" class="btn btn-secondary">管理内容</button>
                <a href="logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> 安全退出
                </a>
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
                <form action="update_post.php" method="POST">
                    <input type="hidden" name="id" id="postId"> <div class="form-group">
                        <label class="form-label">内容类型</label>
                        <select name="type" id="postType" class="form-select">
                            <option value="activity">近期活动</option>
                            <option value="resource">学习资源</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">标题</label>
                        <input type="text" name="title" id="postTitleInput" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">封面图片链接</label>
                        <input type="text" name="cover" id="postCoverInput" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">正文内容</label>
                        <div id="editor-container" class="editor-container">
                            <div id="toolbar-container" style="border-bottom:1px solid #eee;"></div>
                            <div id="content-container" style="height:300px;"></div>
                        </div>
                        <input type="hidden" name="content" id="hiddenContent">
                    </div>
                    <div style="text-align:right;">
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
                <table class="manage-table">
                    <thead><tr><th>类型</th><th>标题</th><th>操作</th></tr></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id, title, 'activity' as type FROM activities UNION SELECT id, title, 'resource' as type FROM resources ORDER BY id DESC";
                        $res = $conn->query($sql);
                        while($item = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $item['type']=='activity'?'活动':'资源'; ?></td>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td>
                                <button onclick="editPost(<?php echo $item['id']; ?>, '<?php echo $item['type']; ?>')" style="color:var(--klein-blue); border:none; background:none; cursor:pointer; font-weight:bold; margin-right:10px;">编辑</button>
                                <button onclick="deletePost(<?php echo $item['id']; ?>, '<?php echo $item['type']; ?>')" style="color:red; border:none; background:none; cursor:pointer;">删除</button>
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

        function openPostModal() {
            document.getElementById('postId').value = ''; // 清空ID表示新增
            document.getElementById('modalTitle').innerText = '发布新内容';
            document.getElementById('postModal').style.display = 'block';
            initEditor();
            editor.setHtml(''); // 清空编辑器
        }

        // 核心功能：编辑
        function editPost(id, type) {
            closeManageModal();
            fetch(`get_post.php?id=${id}&type=${type}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('postId').value = id;
                    document.getElementById('postType').value = type;
                    document.getElementById('postTitleInput').value = data.title;
                    document.getElementById('postCoverInput').value = data.cover;
                    document.getElementById('modalTitle').innerText = '编辑内容';
                    document.getElementById('postModal').style.display = 'block';
                    initEditor();
                    editor.setHtml(data.content); // 将数据库内容填入编辑器
                });
        }

        function closePostModal() { document.getElementById('postModal').style.display = 'none'; }
        function openManageModal() { document.getElementById('manageModal').style.display = 'block'; }
        function closeManageModal() { document.getElementById('manageModal').style.display = 'none'; }
        function deletePost(id, type) { if(confirm('确定删除？')) window.location.href=`delete_post.php?id=${id}&type=${type}`; }
        
        window.onclick = function(e) { if(e.target.className === 'modal') { closePostModal(); closeManageModal(); } }
    </script>
</body>
</html>