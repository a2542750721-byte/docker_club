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
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/responsive.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

<body class="admin-dashboard">
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" style="text-decoration:none; color:inherit;"><h2>创享后台管理</h2></a>
            <div style="display:flex; align-items:center; gap:20px;">
                <span style="color:var(--text-secondary);">欢迎您, 管理员</span>
                <button id="theme-toggle" class="theme-toggle"><i class="fas fa-adjust"></i></button>
            </div>
        </div>
    </nav>

    <section class="section">
        <div class="container">
            <div class="admin-stats-grid">
                <div class="card flat-card stat-card" style="border-left:5px solid var(--admin-blue);">
                    <h3>近期活动总数</h3>
                    <p style="color:var(--admin-blue);"><?php echo $count_act; ?></p>
                </div>
                <div class="card flat-card stat-card" style="border-left:5px solid #28a745;">
                    <h3>学习资源总数</h3>
                    <p style="color:#28a745;"><?php echo $count_res; ?></p>
                </div>
                <div class="card flat-card stat-card" style="border-left:5px solid #ffc107;">
                    <h3>题库题目总数</h3>
                    <p style="color:#ffc107;"><?php echo $count_ques; ?></p>
                </div>
            </div>

            <div class="admin-buttons">
                <button onclick="openPostModal()" class="btn btn-primary"><i class="fas fa-plus"></i> 发布新内容</button>
                <button onclick="openManageModal()" class="btn btn-secondary"><i class="fas fa-tasks"></i> 内容管理</button>
                <button onclick="openStatsModal()" class="btn btn-secondary" style="background-color: #6c757d; border-color: #6c757d;"><i class="fas fa-chart-line"></i> 测验分析</button>
                <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i> 安全退出</a>
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
                        <select name="type" id="postType" class="form-select" onchange="toggleLinkField()">
                            <option value="activity">近期活动 (Activity)</option>
                            <option value="resource">学习资源 (Resource)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">标题</label>
                        <input type="text" name="title" id="postTitleInput" class="form-input" placeholder="请输入吸引人的标题" required>
                    </div>

                    <div id="linkGroup" class="form-group" style="display:none;">
                        <label class="form-label">资源访问链接 (Link)</label>
                        <input type="url" name="link" id="postLinkInput" class="form-input" placeholder="https://example.com/download">
                    </div>

                    <div class="form-group">
                        <label class="form-label">封面图片链接</label>
                        <input type="text" name="cover" id="postCoverInput" class="form-input" placeholder="粘贴图片URL" oninput="updatePreview(this.value)">
                        <div class="preview-box" id="previewBox">
                            <span id="previewText" style="color:#999;">封面预览 (16:9)</span>
                            <img id="previewImg" src="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">正文内容</label>
                        <div id="editor-container" class="editor-container">
                            <div id="toolbar-container"></div>
                            <div id="content-container" style="height:350px;"></div>
                        </div>
                        <input type="hidden" name="content" id="hiddenContent">
                    </div>

                    <div style="text-align:right; margin-top:20px;">
                        <button type="button" class="btn btn-secondary" onclick="closePostModal()" style="margin-right:10px;">取消</button>
                        <button type="submit" class="btn btn-primary" style="padding:10px 40px;">确认保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="manageModal" class="modal">
        <div class="modal-content" style="max-width:900px;">
            <div class="modal-header"><h3>内容管理列表</h3><span class="close" onclick="closeManageModal()">&times;</span></div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="manage-table">
                        <thead><tr><th>类型</th><th>标题</th><th style="text-align:center;">操作</th></tr></thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, title, 'activity' as type FROM activities UNION SELECT id, title, 'resource' as type FROM resources ORDER BY id DESC";
                            $res = $conn->query($sql);
                            while($item = $res->fetch_assoc()): ?>
                            <tr>
                                <td><span style="padding:4px 8px; border-radius:4px; font-size:12px; background:<?php echo $item['type']=='activity'?'#e7f0ff':'#e6ffed'; ?>; color:<?php echo $item['type']=='activity'?'#0056b3':'#28a745'; ?>;">
                                    <?php echo $item['type']=='activity'?'活动':'资源'; ?></span></td>
                                <td style="font-weight:500;"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td style="text-align:center;">
                                    <button onclick="editPost(<?php echo $item['id']; ?>, '<?php echo $item['type']; ?>')" style="color:var(--admin-blue); border:none; background:none; cursor:pointer; font-weight:bold; margin-right:15px;"><i class="fas fa-edit"></i> 编辑</button>
                                    <button onclick="deletePost(<?php echo $item['id']; ?>, '<?php echo $item['type']; ?>')" style="color:#dc3545; border:none; background:none; cursor:pointer; font-weight:bold;"><i class="fas fa-trash"></i> 删除</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Management Modal Removed (Moved to Competition Menu) -->

    <!-- Stats Modal -->
    <div id="statsModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h3>测验数据分析</h3>
                <span class="close" onclick="closeStatsModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div class="card" style="padding: 20px; background: var(--bg-secondary);">
                        <h4>平均得分率</h4>
                        <div style="height: 250px;">
                            <canvas id="scoreChart"></canvas>
                        </div>
                    </div>
                    <div class="card" style="padding: 20px; background: var(--bg-secondary);">
                        <h4>测验参与统计</h4>
                         <div style="height: 250px;">
                            <canvas id="participationChart"></canvas>
                        </div>
                    </div>
                </div>
                <div>
                    <h4>最近测验记录</h4>
                    <div class="table-responsive">
                        <table class="manage-table">
                            <thead><tr><th>用户</th><th>得分</th><th>用时</th><th>提交时间</th></tr></thead>
                            <tbody id="recentResultsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet">
    <script src="https://unpkg.com/@wangeditor/editor@latest/dist/index.js"></script>
    <script>
        const { createEditor, createToolbar } = window.wangEditor;
        let editor = null;

        function initEditor() {
            if (editor) return;
            editor = createEditor({
                selector: '#content-container',
                config: { 
                    placeholder: '在这里输入详细内容...',
                    onChange(editor) { document.getElementById('hiddenContent').value = editor.getHtml(); } 
                }
            });
            createToolbar({ editor, selector: '#toolbar-container' });
        }

        function toggleLinkField() {
            const type = document.getElementById('postType').value;
            document.getElementById('linkGroup').style.display = (type === 'resource') ? 'block' : 'none';
        }

        function updatePreview(url) {
            const img = document.getElementById('previewImg');
            const txt = document.getElementById('previewText');
            if(url) { img.src = url; img.style.display = 'block'; txt.style.display = 'none'; }
            else { img.style.display = 'none'; txt.style.display = 'block'; }
        }

        function openPostModal() {
            document.getElementById('postForm').reset();
            document.getElementById('postId').value = '';
            document.getElementById('modalTitle').innerText = '发布新内容';
            updatePreview('');
            toggleLinkField();
            document.getElementById('postModal').style.display = 'block';
            initEditor(); editor.setHtml('');
        }

        function editPost(id, type) {
            closeManageModal();
            fetch(`get_post.php?id=${id}&type=${type}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('postId').value = id;
                    document.getElementById('postType').value = type;
                    document.getElementById('postTitleInput').value = data.title;
                    document.getElementById('postCoverInput').value = data.cover || '';
                    document.getElementById('postLinkInput').value = data.link || '';
                    updatePreview(data.cover);
                    toggleLinkField();
                    document.getElementById('modalTitle').innerText = '编辑内容';
                    document.getElementById('postModal').style.display = 'block';
                    initEditor(); editor.setHtml(data.content);
                });
        }

        function closePostModal() { document.getElementById('postModal').style.display = 'none'; }
        function openManageModal() { document.getElementById('manageModal').style.display = 'block'; }
        function closeManageModal() { document.getElementById('manageModal').style.display = 'none'; }
        function deletePost(id, type) { if(confirm('警告：确定要永久删除这条内容吗？')) window.location.href=`delete_post.php?id=${id}&type=${type}`; }
        
        window.onclick = function(e) { 
            if(e.target.className === 'modal') { 
                closePostModal(); 
                closeManageModal();
                // closeQuestionModal(); // Moved
                closeStatsModal();
            } 
        }

        // --- Competition Management moved to competition_modal.php ---

        // --- Stats ---
        let scoreChartInstance = null;
        let participationChartInstance = null;

        function openStatsModal() {
            document.getElementById('statsModal').style.display = 'block';
            loadStats();
        }

        function closeStatsModal() {
            document.getElementById('statsModal').style.display = 'none';
        }

        function loadStats() {
            fetch('api/admin/get_stats.php')
                .then(res => res.json())
                .then(data => {
                    // Render Table
                    const tbody = document.getElementById('recentResultsBody');
                    tbody.innerHTML = '';
                    data.recent.forEach(r => {
                        const m = Math.floor(r.time_taken / 60);
                        const s = r.time_taken % 60;
                        tbody.innerHTML += `
                            <tr>
                                <td>${r.username}</td>
                                <td style="color:${r.score/r.total_questions > 0.6 ? 'green' : 'red'}">${r.score}/${r.total_questions}</td>
                                <td>${m}m ${s}s</td>
                                <td>${r.created_at}</td>
                            </tr>
                        `;
                    });

                    // Render Charts
                    renderCharts(data);
                });
        }

        function renderCharts(data) {
            const ctxScore = document.getElementById('scoreChart').getContext('2d');
            const ctxPart = document.getElementById('participationChart').getContext('2d');

            if (scoreChartInstance) scoreChartInstance.destroy();
            if (participationChartInstance) participationChartInstance.destroy();

            // Prepare data for recent scores line chart
            // Reverse to show oldest to newest left to right
            const recentReversed = [...data.recent].reverse();
            const labels = recentReversed.map(r => r.username);
            const scores = recentReversed.map(r => (r.score / r.total_questions) * 100);

            scoreChartInstance = new Chart(ctxScore, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '得分率 (%)',
                        data: scores,
                        borderColor: '#002FA7',
                        tension: 0.1
                    }]
                },
                options: { maintainAspectRatio: false }
            });

            // Participation - Pie Chart (Pass vs Fail)
            // Let's calculate pass/fail from recent 20 (approx) or just dummy logic if we don't have enough data
            let pass = 0, fail = 0;
            data.recent.forEach(r => {
                if ((r.score / r.total_questions) >= 0.6) pass++; else fail++;
            });

            participationChartInstance = new Chart(ctxPart, {
                type: 'doughnut',
                data: {
                    labels: ['及格 (>=60%)', '未及格 (<60%)'],
                    datasets: [{
                        data: [pass, fail],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: { maintainAspectRatio: false }
            });
        }

    </script>
</body>
</html>