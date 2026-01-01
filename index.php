<?php require_once __DIR__  . '/config/db.php'; ?>
<?php require_once __DIR__ . '/includes/functions.php';?>
<?php include __DIR__ . '/includes/header.php'; ?>

    <section id="activities" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">近期活动</h2>
                <p class="section-subtitle">参与我们的活动，与同学们一起学习成长</p>
            </div>

            <div class="news-grid"> 
                <?php
                $result = $conn->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 3");
                while($row = $result->fetch_assoc()): ?>
                    <div class="card flat-card">
                        <img src="<?php echo $row['cover']; ?>" style="width:100%; height:200px; object-fit:contain; background:#f5f5f5;">
                        <div class="card-body" style="padding:15px;">
                            <h3><?php echo $row['title']; ?></h3>
                            <p><?php echo mb_substr(strip_tags($row['content']), 0, 50); ?>...</p>
                            <button class="btn btn-primary" onclick="openFullArticle(<?php echo $row['id']; ?>)">查看详情</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section id="resources" class="section section-alt">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">学习资源</h2>
                <p class="section-subtitle">精选优质学习资料，助力技能提升</p>
            </div>
            <div class="resources-grid">
                <?php
                $res = $conn->query("SELECT * FROM resources ORDER BY created_at DESC LIMIT 4");
                while($row = $res->fetch_assoc()): ?>
                    <div class="card" style="padding:20px; background:var(--card-bg); border-radius:10px;">
                        <h4><?php echo $row['title']; ?></h4>
                        <p><?php echo $row['content']; ?></p>
                        <a href="<?php echo $row['link']; ?>" target="_blank" class="btn btn-outline">立即获取</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

<?php include __DIR__ . '/includes/modals.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
// 定义打开函数
function openFullArticle(id) {
    console.log("启动动态弹窗，文章ID:", id);

    // 1. 检查页面上是否已经有这个动态弹窗了，如果有先删掉
    let existingModal = document.getElementById('dynamic-article-modal');
    if (existingModal) {
        existingModal.remove();
    }

    // 2. 凭空创建一个全新的 DIV (避开 modals.php 的干扰)
    const modal = document.createElement('div');
    modal.id = 'dynamic-article-modal'; // 给它一个新的 ID
    
    // 3. 给它加上无敌的内联样式 (避开 styles.css 的干扰)
    Object.assign(modal.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100vw',
        height: '100vh',
        backgroundColor: 'rgba(0, 0, 0, 0.85)', // 深黑背景
        zIndex: '2147483647', // 浏览器允许的最大层级
        display: 'flex', // 弹性布局居中
        justifyContent: 'center',
        alignItems: 'center',
        backdropFilter: 'blur(5px)', // 背景模糊
        opacity: '0', // 初始透明，为了做动画
        transition: 'opacity 0.3s ease' // 渐显动画
    });

    // 4. 创建白色卡片容器
    const card = document.createElement('div');
    Object.assign(card.style, {
        background: '#fff',
        width: '90%',
        maxWidth: '700px',
        maxHeight: '80vh',
        borderRadius: '12px',
        padding: '30px',
        position: 'relative',
        overflowY: 'auto',
        boxShadow: '0 10px 30px rgba(0,0,0,0.5)',
        transform: 'scale(0.95)',
        transition: 'transform 0.3s ease'
    });

    // 5. 设置加载中的内容
    card.innerHTML = `
        <button onclick="closeDynamicModal()" style="position:absolute; top:15px; right:20px; background:none; border:none; font-size:28px; cursor:pointer; color:#666;">&times;</button>
        <div id="dynamic-content" style="padding:20px; text-align:center; color:#333;">
            <h3 style="margin-bottom:10px;">🔄 正在获取内容...</h3>
            <p style="color:#666;">ID: ${id}</p>
        </div>
    `;

    // 6. 组装并放到页面最外层
    modal.appendChild(card);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden'; // 锁死滚动条

    // 7. 触发动画（让它显示出来）
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
        card.style.transform = 'scale(1)';
    });

    // 8. 点击背景关闭
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeDynamicModal();
    });

    // 9. 发送请求获取数据
    fetch('get_detail.php?id=' + id)
        .then(res => res.text()) // 先按文本接收
        .then(text => {
            const contentBox = document.getElementById('dynamic-content');
            try {
                const data = JSON.parse(text);
                
                // 构建图片 HTML (强制完整显示)
                let coverHtml = data.cover ? 
                    `<div style="text-align:center; background:#f5f5f5; border-radius:8px; margin-bottom:15px; padding:5px;">
                        <img src="${data.cover}" style="max-width:100%; max-height:300px; object-fit:contain; display:block; margin:0 auto;">
                     </div>` : '';

                // 填充真正的内容
                contentBox.innerHTML = `
                    <div style="text-align:left; line-height:1.8;">
                        <h2 style="color:#002FA7; margin-top:0; border-bottom:1px solid #eee; padding-bottom:15px;">${data.title}</h2>
                        <div style="font-size:12px; color:#999; margin:10px 0;">发布时间：${data.created_at || '未知'}</div>
                        ${coverHtml}
                        <div style="font-size:16px; color:#333;">
                            ${data.content}
                        </div>
                    </div>
                `;
            } catch (e) {
                console.error("解析失败:", text);
                contentBox.innerHTML = `<p style="color:red; text-align:center;">数据解析错误<br>后端返回内容：<br>${text.substring(0, 100)}...</p>`;
            }
        })
        .catch(err => {
            document.getElementById('dynamic-content').innerHTML = `<p style="color:red; text-align:center;">网络请求失败: ${err.message}</p>`;
        });
}

// 关闭函数
function closeDynamicModal() {
    const modal = document.getElementById('dynamic-article-modal');
    if (modal) {
        modal.style.opacity = '0'; // 消失动画
        setTimeout(() => {
            modal.remove(); // 彻底从页面删除
            document.body.style.overflow = 'auto'; // 恢复滚动条
        }, 300);
    }
}
</script>