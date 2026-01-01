// 主题切换功能
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const root = document.documentElement;
    
    // 添加主题切换事件
    themeToggle.addEventListener('click', function() {
        root.classList.toggle('dark-mode');
    });
    
    // 返回顶部按钮功能
    const backToTopButton = document.getElementById('back-to-top');
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'flex';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
    
    // 移动端导航菜单切换
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    navToggle.addEventListener('click', function() {
        navToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    });
    
    // 导航链接点击后关闭移动端菜单
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });
});

// 返回顶部功能
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// 模态框功能
function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

// QR弹窗功能
function closeQRPopup() {
    document.getElementById('qr-popup').style.display = 'none';
}

// 调试工具功能
function openDebugTool() {
    alert('调试工具功能在纯静态版本中不可用');
}
// 重构后的详情打开函数
function openFullArticle(id) {
    const modal = document.getElementById('articleDetailModal');
    const contentBox = document.getElementById('fullArticleContent');

    if (!modal || !contentBox) return;

    // 显示弹窗并清空旧内容
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // 开启弹窗时禁止页面滚动
    contentBox.innerHTML = '<div style="text-align:center; padding:3rem;"><i class="fas fa-spinner fa-spin"></i> 正在加载内容...</div>';

    fetch('get_detail.php?id=' + id)
        .then(response => {
            if (!response.ok) throw new Error('网络响应错误');
            return response.json();
        })
        .then(data => {
            if (data.title === "未找到") {
                contentBox.innerHTML = `<p style="color:red; text-align:center;">${data.content}</p>`;
                return;
            }

            let coverHtml = data.cover ? `<img src="${data.cover}" alt="封面">` : '';
            
            contentBox.innerHTML = `
                <div class="article-detail">
                    <h1 style="color:#002FA7; margin-bottom:0.5rem;">${data.title}</h1>
                    <p style="font-size:0.85rem; color:#888; margin-bottom:1.5rem;">发布于：${data.created_at}</p>
                    ${coverHtml}
                    <div class="article-body" style="line-height:1.8; color:#444;">
                        ${data.content}
                    </div>
                </div>
            `;
        })
        .catch(err => {
            console.error(err);
            contentBox.innerHTML = '<div style="text-align:center; color:red; padding:2rem;">加载失败，请刷新重试。</div>';
        });
}

// 对应的关闭函数
function closeDetailModal() {
    const modal = document.getElementById('articleDetailModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // 恢复页面滚动
    }
}
