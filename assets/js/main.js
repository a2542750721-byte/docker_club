// 主题切换功能
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const root = document.documentElement;
    
    // 检测系统主题偏好
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // 检查本地存储中的主题设置（用户选择优先级更高）
    const savedTheme = localStorage.getItem('theme');
    
    // 彩蛋计数器
    let themeToggleCount = 0;
    let lastToggleTime = 0;
    
    // 女神异闻录5主题彩蛋计数器
    let persona5ToggleCount = 0;
    let lastPersona5ToggleTime = 0;
    
    // 应用主题：优先使用用户选择，其次使用系统偏好
    if (savedTheme) {
        if (savedTheme === 'dark-mode') {
            root.classList.add('dark-mode');
        } else if (savedTheme === 'lakers-theme') {
            root.classList.add('lakers-theme');
        } else if (savedTheme === 'persona5-theme') {
            root.classList.add('persona5-theme');
        } else {
            root.classList.remove('dark-mode');
        }
    } else if (prefersDarkScheme.matches) {
        root.classList.add('dark-mode');
    }
    
    // 添加主题切换事件
    themeToggle.addEventListener('click', function() {
        // 检查是否在短时间内再次点击（彩蛋触发）
        const currentTime = Date.now();
        if (currentTime - lastToggleTime < 1000) { // 1秒内再次点击
            themeToggleCount++;
        } else {
            themeToggleCount = 1; // 重置计数
        }
        lastToggleTime = currentTime;
        
        // 检查是否达到15次点击，触发彩蛋
        if (themeToggleCount >= 15) {
            root.classList.add('lakers-theme');
            localStorage.setItem('theme', 'lakers-theme');
            themeToggleCount = 0; // 重置计数
            // 显示提示信息
            alert('🎉 恭喜你发现了彩蛋！网站已切换到湖人队紫金主题！再点一下切换回正常模式');
            return;
        }
        
        // 正常的主题切换
        root.classList.toggle('dark-mode');
        // 移除其他主题（如果存在）
        root.classList.remove('lakers-theme');
        root.classList.remove('persona5-theme');
        // 保存主题设置到本地存储
        if (root.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark-mode');
        } else {
            localStorage.setItem('theme', 'light-mode');
        }
        window.dispatchEvent(new Event('themeChanged'));
    });
    
    // 添加女神异闻录5主题彩蛋事件
    const logoText = document.getElementById('logo-text');
    if (logoText) {
        logoText.addEventListener('click', function() {
            // 检查是否在短时间内再次点击（彩蛋触发）
            const currentTime = Date.now();
            if (currentTime - lastPersona5ToggleTime < 1000) { // 1秒内再次点击
                persona5ToggleCount++;
            } else {
                persona5ToggleCount = 1; // 重置计数
            }
            lastPersona5ToggleTime = currentTime;
            
            // 检查是否达到10次点击，触发女神异闻录5主题彩蛋
            if (persona5ToggleCount >= 100) {
                root.classList.add('persona5-theme');
                localStorage.setItem('theme', 'persona5-theme');
                persona5ToggleCount = 0; // 重置计数
                // 显示提示信息
                alert('🎉 恭喜你发现了彩蛋！网站已切换到女神异闻录5主题！');
                return;
            }
        });
    }
    
    // 监听系统主题变化
    prefersDarkScheme.addEventListener('change', function(e) {
        // 如果用户没有手动选择主题，则跟随系统偏好
        if (!localStorage.getItem('theme')) {
            if (e.matches) {
                root.classList.add('dark-mode');
            } else {
                root.classList.remove('dark-mode');
            }
        }
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

    // 工具箱滚动位置记忆
    const toolboxGrid = document.querySelector('.toolbox-grid');
    if (toolboxGrid) {
        // 恢复滚动位置
        const savedScrollPos = sessionStorage.getItem('toolboxScrollPos');
        if (savedScrollPos) {
            toolboxGrid.scrollTop = parseInt(savedScrollPos);
        }

        // 监听滚动并保存位置
        let scrollTimeout;
        toolboxGrid.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                sessionStorage.setItem('toolboxScrollPos', toolboxGrid.scrollTop);
            }, 100);
        }, { passive: true });
    }

// 背景风格切换功能
const bgToggle = document.getElementById('bg-style-toggle');
if (bgToggle) {
    bgToggle.addEventListener('click', function() {
        let currentBg = localStorage.getItem('bg-type') || 'globe';
        let nextBg = (currentBg === 'globe') ? 'topology' : 'globe';
        
        localStorage.setItem('bg-type', nextBg);
        
        // 触发自定义事件，通知背景脚本重绘
        window.dispatchEvent(new Event('bgChanged'));
        
        // 按钮点击动画
        bgToggle.style.transform = 'scale(0.9)';
        setTimeout(() => bgToggle.style.transform = 'scale(1)', 100);
    });
}
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
    const progressBar = document.getElementById('reading-progress-bar');
    const progressSlider = document.getElementById('reading-progress-slider');
    const progressBuffer = document.getElementById('reading-progress-buffer');
    
    // 重置进度条
    if (progressBar) progressBar.style.width = '0%';
    if (progressSlider) progressSlider.style.left = '0%';
    if (progressBuffer) {
        progressBuffer.style.width = '0%';
        // 模拟缓冲动画
        let bufferWidth = 0;
        const bufferInterval = setInterval(() => {
            bufferWidth += Math.random() * 10;
            if (bufferWidth > 100) {
                bufferWidth = 100;
                clearInterval(bufferInterval);
            }
            progressBuffer.style.width = bufferWidth + '%';
        }, 200);
    }

    if (!modal || !contentBox) return;

    // 显示弹窗并清空旧内容
    modal.style.display = 'flex';
    // 强制重绘以触发动画
    modal.offsetHeight; 
    modal.classList.add('active');
    
    document.body.style.overflow = 'hidden'; // 开启弹窗时禁止页面滚动
    contentBox.innerHTML = '<div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:300px; color:var(--text-secondary);"><i class="fas fa-spinner fa-spin" style="font-size:40px; margin-bottom:15px; color:var(--main-color-primary);"></i><p>正在加载精彩内容...</p></div>';

    fetch('get_detail.php?id=' + id)
        .then(response => {
            if (!response.ok) throw new Error('网络响应错误');
            return response.json();
        })
        .then(data => {
            if (data.title === "未找到") {
                contentBox.innerHTML = `<div style="text-align:center; padding:50px;"><i class="fas fa-exclamation-circle" style="font-size:48px; color:#ff4757; margin-bottom:15px;"></i><p style="color:var(--text-primary); font-size:18px;">${data.content}</p></div>`;
                return;
            }

            // 处理封面图：如果没有图片，使用默认图片；如果是相对路径，确保显示正确
            const defaultImage = "data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22800%22%20height%3D%22400%22%20viewBox%3D%220%200%20800%20400%22%20fill%3D%22%23f0f0f0%22%3E%3Crect%20width%3D%22800%22%20height%3D%22400%22%20%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20dominant-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20font-family%3D%22sans-serif%22%20font-size%3D%2224%22%20fill%3D%22%23aaa%22%3E%E6%9A%82%E6%97%A0%E5%B0%81%E9%9D%A2%3C%2Ftext%3E%3C%2Fsvg%3E";
            
            let coverSrc = data.cover;
            if (!coverSrc) {
                coverSrc = defaultImage;
            }
            
            // 构建HTML
            const dateStr = data.created_at || '刚刚';
            
            contentBox.innerHTML = `
                <div class="article-detail">
                    <div class="article-header">
                        <h1 class="article-title">${data.title}</h1>
                        <div class="article-meta">
                            <span><i class="far fa-clock"></i> ${dateStr}</span>
                            <span><i class="far fa-user"></i> 管理员</span>
                        </div>
                    </div>
                    
                    <div class="article-cover-wrapper">
                        <img src="${coverSrc}" alt="${data.title}" class="article-cover" onerror="this.src='${defaultImage}'; this.onerror=null;">
                    </div>
                    
                    <div class="article-body">
                        ${data.content}
                    </div>
                    
                    <div class="article-footer">
                        <p>--- 到底啦 ---</p>
                    </div>
                </div>
            `;
            
            // Fix images in content
            const contentImages = contentBox.querySelectorAll('.article-body img');
            contentImages.forEach(img => {
                img.setAttribute('crossorigin', 'anonymous'); // Try to request with CORS
                img.onerror = function() {
                    this.src = defaultImage;
                    this.onerror = null;
                    // Remove crossorigin on fallback to ensure it loads
                    this.removeAttribute('crossorigin');
                };
            });

            // 初始化进度条交互
            initProgressBar();
        })
        .catch(err => {
            console.error(err);
            contentBox.innerHTML = `<div style="text-align:center; padding:50px;"><i class="fas fa-wifi" style="font-size:48px; color:#ff4757; margin-bottom:15px;"></i><p style="color:var(--text-primary);">加载失败，请检查网络后重试。</p><button onclick="openFullArticle(${id})" class="btn btn-primary" style="margin-top:15px;">重试</button></div>`;
        });
}

// 进度条交互逻辑
function initProgressBar() {
    const contentBox = document.getElementById('fullArticleContent');
    const container = document.getElementById('reading-progress-container');
    const progressBar = document.getElementById('reading-progress-bar');
    const slider = document.getElementById('reading-progress-slider');
    
    if (!contentBox || !container) return;

    // 滚动监听
    contentBox.onscroll = function() {
        const scrollTop = contentBox.scrollTop;
        const scrollHeight = contentBox.scrollHeight - contentBox.clientHeight;
        const progress = (scrollTop / scrollHeight) * 100;
        
        if (progressBar) progressBar.style.width = progress + '%';
        if (slider) slider.style.left = progress + '%';
    };

    // 点击跳转
    container.onclick = function(e) {
        const rect = container.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const width = rect.width;
        const percentage = clickX / width;
        
        const scrollHeight = contentBox.scrollHeight - contentBox.clientHeight;
        contentBox.scrollTo({
            top: scrollHeight * percentage,
            behavior: 'smooth'
        });
    };

    // 拖动滑块
    let isDragging = false;
    
    slider.onmousedown = function(e) {
        isDragging = true;
        document.body.style.userSelect = 'none'; // 防止选中文本
        e.stopPropagation(); // 防止触发点击跳转
    };

    document.onmousemove = function(e) {
        if (!isDragging) return;
        
        const rect = container.getBoundingClientRect();
        let moveX = e.clientX - rect.left;
        
        // 限制范围
        if (moveX < 0) moveX = 0;
        if (moveX > rect.width) moveX = rect.width;
        
        const percentage = moveX / rect.width;
        const scrollHeight = contentBox.scrollHeight - contentBox.clientHeight;
        
        contentBox.scrollTop = scrollHeight * percentage;
        // 实时更新UI由onscroll处理，但为了流畅性也可以在这里直接设置
    };

    document.onmouseup = function() {
        if (isDragging) {
            isDragging = false;
            document.body.style.userSelect = 'auto';
        }
    };
    
    // 键盘控制
    document.onkeydown = function(e) {
        const modal = document.getElementById('articleDetailModal');
        if (modal.style.display !== 'flex') return;
        
        const scrollHeight = contentBox.scrollHeight - contentBox.clientHeight;
        const step = scrollHeight * 0.1; // 每次移动10%
        
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            contentBox.scrollBy({ top: step, behavior: 'smooth' });
            e.preventDefault();
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            contentBox.scrollBy({ top: -step, behavior: 'smooth' });
            e.preventDefault();
        }
    };
}

// 对应的关闭函数
function closeDetailModal() {
    const modal = document.getElementById('articleDetailModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active');
        document.body.style.overflow = 'auto'; // 恢复页面滚动
    }
}
