class LiquidGlassElement {
    constructor(targetEl, options = {}) {
        this.target = targetEl;
        this.id = 'lg-' + Math.random().toString(36).substr(2, 5);
        this.canvasDPI = window.devicePixelRatio || 1;
        
        const rect = targetEl.getBoundingClientRect();
        this.width = rect.width;
        this.height = rect.height;
        
        this.init();
    }

    init() {
        this.createFilter();
        this.target.style.filter = `url(#${this.id}_filter) saturate(1.2) brightness(1.05)`;
        this.target.style.transition = 'transform 0.3s cubic-bezier(0.15, 0.85, 0.35, 1.2)';
        this.target.addEventListener('mousemove', (e) => this.update(e));
        this.target.addEventListener('mouseleave', () => this.reset());
    }

    createFilter() {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.style.display = 'none';
        svg.innerHTML = `
            <defs>
                <filter id="${this.id}_filter" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                    <feImage id="${this.id}_map" result="map" width="${this.width}" height="${this.height}" />
                    <feDisplacementMap in="SourceGraphic" in2="map" scale="0" xChannelSelector="R" yChannelSelector="G" />
                </filter>
            </defs>
        `;
        document.body.appendChild(svg);
        this.feImage = svg.querySelector(`#${this.id}_map`);
        this.feDisp = svg.querySelector('feDisplacementMap');
        
        this.canvas = document.createElement('canvas');
        this.canvas.width = this.width * this.canvasDPI;
        this.canvas.height = this.height * this.canvasDPI;
        this.ctx = this.canvas.getContext('2d');
    }

    update(e) {
        const rect = this.target.getBoundingClientRect();
        const mx = (e.clientX - rect.left) / rect.width;
        const my = (e.clientY - rect.top) / rect.height;
        
        this.renderMap(mx, my);
    }

    renderMap(mx, my) {
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
            const strength = Math.exp(-dist * 5);

            data[i] = (0.5 + dx * strength) * 255; 
            data[i+1] = (0.5 + dy * strength) * 255; 
            data[i+2] = 0;
            data[i+3] = 255;
        }

        this.ctx.putImageData(imgData, 0, 0);
        this.feImage.setAttributeNS('http://www.w3.org/1999/xlink', 'href', this.canvas.toDataURL());
        this.feDisp.setAttribute('scale', '40');
    }

    reset() {
        this.feDisp.setAttribute('scale', '0');
    }
}

// 自动初始化页面上的所有玻璃元素
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.liquid-glass-trigger').forEach(el => {
        new LiquidGlassElement(el);
    });
});