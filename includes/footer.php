<footer class="footer">
    <div class="container">
        <div class="footer-container">
            <div class="footer-section footer-info">
                <h3>创想网络信息协会</h3>
                <p>探索计算机世界的无限可能，与志同道合的伙伴一起成长</p>
                <div style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
                    <a href="javascript:void(0)" onclick="safeCall('openToolboxMenu')" class="footer-toolbox-btn enhanced-btn">
                        <i class="fas fa-toolbox"></i> 工具箱
                    </a>
                    <a href="javascript:void(0)" onclick="safeCall('openCompetitionMenu')" class="footer-toolbox-btn enhanced-btn competition-btn">
                        <i class="fas fa-trophy"></i> 竞赛准备
                    </a>
                    <a href="javascript:void(0)" onclick="safeCall('openTypingMenu')" class="footer-toolbox-btn enhanced-btn typing-btn">
                        <i class="fas fa-keyboard"></i> 打字练习
                    </a>
                </div>
            </div>
            <div class="footer-section">
                <h4>快速链接</h4>
                <a href="#home" class="footer-btn"><i class="fas fa-home"></i> 首页</a>
                <a href="#activities" class="footer-btn"><i class="fas fa-calendar-alt"></i> 活动</a>
                <a href="#news" class="footer-btn"><i class="fas fa-newspaper"></i> 新闻</a>
                <a href="#resources" class="footer-btn"><i class="fas fa-download"></i> 资源</a>
            </div>
            <div class="footer-section">
                <h4>联系我们</h4>
                <p><i class="fas fa-envelope"></i> a2542750721@163.com</p>
                <p><i class="fas fa-phone"></i> 18311422016</p>
                <p><i class="fas fa-map-marker-alt"></i> 后街创想网络信息协会办公室</p>
            </div>
            <div class="footer-section">
                <h4>后台管理</h4>
                <a href="admin_dashboard.php" class="footer-btn"><i class="fas fa-cog"></i> 管理入口</a>
            </div>
            </div>
        <div class="footer-bottom">
            <p>&copy; 2025 创想网络信息协会. 保留所有权利.</p>
        </div>
    </div>
</footer>

<button id="back-to-top" class="back-to-top" onclick="safeCall('scrollToTop')" aria-label="返回顶部">
    <i class="fas fa-arrow-up"></i>
</button>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof AOS !== 'undefined') {
            AOS.init({ 
                duration: 1000, 
                once: true,
                disable: 'mobile' // Optional: disable on mobile if it causes issues
            });
        }
    });
</script>
<script src="assets/js/main.js"></script>
<script>
    document.addEventListener('mousemove', e => {
        document.documentElement.style.setProperty('--x', e.clientX + 'px');
        document.documentElement.style.setProperty('--y', e.clientY + 'px');
    });
</script>
<?php include_once __DIR__ . '/competition_modal.php'; ?>
<?php include_once __DIR__ . '/typing_modal.php'; ?>
</body>
</html>
<script>
class LiquidGlassEngine {
    constructor(el) {
        this.el = el;
        this.id = 'glass-filter-' + Math.floor(Math.random() * 100000);
        this.canvasDPI = window.devicePixelRatio || 1;
        this.init();
    }

    init() {
        const rect = this.el.getBoundingClientRect();
        this.width = rect.width;
        this.height = rect.height;

        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("class", "lg-svg-defs");
        svg.innerHTML = `
            <defs>
                <filter id="${this.id}" filterUnits="userSpaceOnUse">
                    <feImage id="${this.id}-map" result="map" width="${this.width}" height="${this.height}" />
                    <feDisplacementMap in="SourceGraphic" in2="map" scale="0" xChannelSelector="R" yChannelSelector="G" />
                </filter>
            </defs>`;
        document.body.appendChild(svg);

        this.feImage = svg.querySelector(`#${this.id}-map`);
        this.feDisp = svg.querySelector('feDisplacementMap');
        
        this.canvas = document.createElement('canvas');
        this.canvas.width = this.width * this.canvasDPI;
        this.canvas.height = this.height * this.canvasDPI;
        this.ctx = this.canvas.getContext('2d');

        this.el.style.filter = `url(#${this.id}) saturate(1.1)`;
        
        this.el.addEventListener('mousemove', (e) => this.render(e));
        this.el.addEventListener('mouseleave', () => this.reset());
    }

    render(e) {
        const rect = this.el.getBoundingClientRect();
        const mx = (e.clientX - rect.left) / rect.width;
        const my = (e.clientY - rect.top) / rect.height;
        
        const w = this.canvas.width;
        const h = this.canvas.height;
        const imgData = this.ctx.createImageData(w, h);
        const data = imgData.data;

        for (let i = 0; i < data.length; i += 4) {
            const x = (i / 4) % w / w;
            const y = Math.floor(i / 4 / w) / h;
            const dx = x - mx;
            const dy = y - my;
            const dist = Math.sqrt(dx*dx + dy*dy);
            const strength = Math.exp(-dist * 5); // 核心物理公式

            data[i] = (0.5 + dx * strength) * 255;   // R = X位移
            data[i+1] = (0.5 + dy * strength) * 255; // G = Y位移
            data[i+3] = 255;
        }

        this.ctx.putImageData(imgData, 0, 0);
        this.feImage.setAttributeNS('http://www.w3.org/1999/xlink', 'href', this.canvas.toDataURL());
        this.feDisp.setAttribute('scale', '35');
    }

    reset() {
        this.feDisp.setAttribute('scale', '0');
    }
}
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.liquid-glass-trigger').forEach(card => {
        new LiquidGlassEngine(card);
    });
});
</script>