<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.1.9/p5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.globe.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.topology.min.js"></script>

<div id="vanta-bg" style="position:fixed !important; top:0 !important; left:0 !important; width:100% !important; height:100% !important; z-index:-1 !important; pointer-events:none !important;"></div>

<script>
let vantaEffect = null;

function initBackground() {
    if (vantaEffect) vantaEffect.destroy();

    const isDark = document.documentElement.classList.contains('dark-mode');
    const bgType = localStorage.getItem('bg-type') || 'globe'; // 默认星球
    const bgElement = document.getElementById('vanta-bg');

    // 根据主题调整混合模式
    if (isDark) {
        bgElement.style.mixBlendMode = 'normal';
        bgElement.style.opacity = '1';
    } else {
        bgElement.style.mixBlendMode = 'normal';
        bgElement.style.opacity = '1';
    }

    if (bgType === 'globe') {
        vantaEffect = VANTA.GLOBE({
            el: "#vanta-bg",
            mouseControls: true,
            touchControls: true,
            minHeight: 200.0,
            minWidth: 200.0,
            scale: 1.0,
            color: 0x002fa7,
            color2: isDark ? 0xffffff : 0x002fa7,
            backgroundColor: isDark ? 0x000000 : 0xffffff,
            size: 1.2
        });
    } else {
        vantaEffect = VANTA.TOPOLOGY({
            el: "#vanta-bg",
            mouseControls: true,
            touchControls: true,
            minHeight: 200.0,
            minWidth: 200.0,
            scale: 1.0,
            color: isDark ? 0x002fa7 : 0x3c3cd1,
            backgroundColor: isDark ? 0x000000 : 0xffffff
        });
    }
}

// 初始化加载
document.addEventListener('DOMContentLoaded', initBackground);

// 监听主题切换（由 main.js 触发 CustomEvent）
window.addEventListener('themeChanged', initBackground);
// 监听背景切换
window.addEventListener('bgChanged', initBackground);

/**
 * 验证背景层显示优先级
 * 确保 vanta-bg 的 z-index 是页面中最高的
 */
(function verifyBackgroundPriority() {
    setTimeout(() => {
        const bg = document.getElementById('vanta-bg');
        if (bg) {
            const zIndex = window.getComputedStyle(bg).zIndex;
            console.log(`[Background Manager] Background z-index: ${zIndex}`);
            if (zIndex === '-1') {
                console.log('[Background Manager] Priority: Background (Verified)');
            } else {
                console.warn(`[Background Manager] Priority: Not Background (Current: ${zIndex})`);
            }
        }
    }, 1000);
})();
</script>