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
                        <div style="width:100%; height:200px; overflow:hidden; border-radius: 8px 8px 0 0;">
                            <img src="<?php echo $row['cover']; ?>" 
                                 style="width:100%; height:100%; object-fit:cover; transition: transform 0.3s ease;" 
                                 onmouseover="this.style.transform='scale(1.05)'" 
                                 onmouseout="this.style.transform='scale(1)'">
                        </div>
                        
                        <div class="card-body" style="padding:20px; display: flex; flex-direction: column; height: 180px;">
                            <h3 style="margin: 0 0 10px; font-size: 1.25rem; height: 1.5em; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>
                            <p style="color: #666; font-size: 0.95rem; line-height: 1.5; height: 4.5em; overflow: hidden; margin-bottom: 15px;">
                                <?php echo mb_substr(strip_tags($row['content']), 0, 60); ?>...
                            </p>
                            <div style="margin-top: auto;">
                                <button class="btn btn-primary" style="width: 100%;" onclick="openFullArticle(<?php echo $row['id']; ?>)">查看详情</button>
                            </div>
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
                    <div class="card" style="padding:25px; background:var(--card-bg); border-radius:12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                        <h4 style="margin-top: 0; color: var(--klein-blue); font-size: 1.15rem;"><?php echo htmlspecialchars($row['title']); ?></h4>
                        <p style="font-size: 0.9rem; color: #555; margin: 10px 0 20px;"><?php echo htmlspecialchars($row['content']); ?></p>
                        <a href="<?php echo $row['link']; ?>" target="_blank" class="btn btn-outline" style="display: block; text-align: center;">立即获取</a>
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

    let existingModal = document.getElementById('dynamic-article-modal');
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement('div');
    modal.id = 'dynamic-article-modal';
    
    Object.assign(modal.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100vw',
        height: '100vh',
        backgroundColor: 'rgba(0, 0, 0, 0.85)',
        zIndex: '2147483647',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        backdropFilter: 'blur(5px)',
        opacity: '0',
        transition: 'opacity 0.3s ease'
    });

    const card = document.createElement('div');
    Object.assign(card.style, {
        background: '#fff',
        width: '90%',
        maxWidth: '750px',
        maxHeight: '85vh',
        borderRadius: '16px',
        padding: '0', // 改为 0，因为标题和图片需要自定义间距
        position: 'relative',
        overflowY: 'auto',
        boxShadow: '0 20px 50px rgba(0,0,0,0.3)',
        transform: 'scale(0.95)',
        transition: 'transform 0.3s ease'
    });

    card.innerHTML = `
        <button onclick="closeDynamicModal()" style="position:fixed; top:20px; right:20px; background:rgba(255,255,255,0.2); border:none; font-size:32px; cursor:pointer; color:#fff; width:45px; height:45px; border-radius:50%; line-height:45px; text-align:center; z-index:10;">&times;</button>
        <div id="dynamic-content" style="padding:40px; color:#333;">
            <div style="text-align:center;">
                <h3 style="margin-bottom:10px;">🔄 正在获取内容...</h3>
            </div>
        </div>
    `;

    modal.appendChild(card);
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    requestAnimationFrame(() => {
        modal.style.opacity = '1';
        card.style.transform = 'scale(1)';
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeDynamicModal();
    });

    fetch('get_detail.php?id=' + id)
        .then(res => res.text())
        .then(text => {
            const contentBox = document.getElementById('dynamic-content');
            try {
                const data = JSON.parse(text);
                
                // 详情页图片建议使用 contain 模式，避免裁剪关键信息，但在列表中必须用 cover
                let coverHtml = data.cover ? 
                    `<div style="text-align:center; background:#f8f9fa; border-radius:12px; margin: 20px 0; padding:10px;">
                        <img src="${data.cover}" style="max-width:100%; max-height:400px; object-fit:contain; border-radius: 8px;">
                     </div>` : '';

                contentBox.innerHTML = `
                    <div style="text-align:left; line-height:1.8;">
                        <h1 style="color:#002FA7; margin-top:0; font-size: 2rem; border-bottom:2px solid #f0f0f0; padding-bottom:20px;">${data.title}</h1>
                        <div style="font-size:14px; color:#999; margin:15px 0; display: flex; align-items: center; gap: 10px;">
                            <span>📅 发布时间：${data.created_at || '未知'}</span>
                            <span style="background: #eef2ff; color: #002FA7; padding: 2px 8px; border-radius: 4px;">活动详情</span>
                        </div>
                        ${coverHtml}
                        <div style="font-size:17px; color:#444; margin-top: 25px;">
                            ${data.content}
                        </div>
                    </div>
                `;
            } catch (e) {
                contentBox.innerHTML = `<p style="color:red; text-align:center;">数据解析错误</p>`;
            }
        })
        .catch(err => {
            document.getElementById('dynamic-content').innerHTML = `<p style="color:red; text-align:center;">网络请求失败</p>`;
        });
}

function closeDynamicModal() {
    const modal = document.getElementById('dynamic-article-modal');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = 'auto';
        }, 300);
    }
}
</script>