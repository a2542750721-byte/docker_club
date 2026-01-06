<div id="modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div id="modal-body"></div>
    </div>
</div>
<div id="qr-popup" class="qr-popup">
    <div class="qr-popup-content">
        <div class="qr-popup-header">
            <h3 id="qr-title">扫码关注</h3>
            <button class="qr-close" onclick="closeQRPopup()">&times;</button>
        </div>
        <div class="qr-popup-body">
            <img id="qr-image" src="" alt="二维码">
            <p id="qr-description">扫描二维码加入我们</p>
        </div>
    </div>
</div>
<div id="articleDetailModal" class="modal-overlay">
    <div class="modal-card">
        <!-- Progress Bar Container -->
        <div id="reading-progress-container" class="reading-progress-container">
            <div id="reading-progress-bar" class="reading-progress-bar"></div>
            <div id="reading-progress-buffer" class="reading-progress-buffer"></div>
            <div id="reading-progress-slider" class="reading-progress-slider"></div>
        </div>

        <button onclick="closeDetailModal()" class="modal-close">&times;</button>
        
        <div id="fullArticleContent" class="article-content">
        </div>
        
    </div>
</div>
<style>
.reading-progress-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: #f0f0f0;
    cursor: pointer;
    z-index: 10;
    border-radius: 12px 12px 0 0;
}
.reading-progress-bar {
    height: 100%;
    background: #1890ff;
    width: 0%;
    position: absolute;
    top: 0;
    left: 0;
    border-radius: 12px 0 0 0;
    transition: width 0.1s linear;
}
.reading-progress-buffer {
    height: 100%;
    background: #e8e8e8;
    width: 0%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: -1;
    transition: width 0.3s ease;
}
.reading-progress-slider {
    width: 12px;
    height: 12px;
    background: #1890ff;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 0%;
    transform: translate(-50%, -50%);
    box-shadow: 0 0 4px rgba(0,0,0,0.2);
    cursor: grab;
    z-index: 11;
    transition: left 0.1s linear;
}
.reading-progress-slider:active {
    cursor: grabbing;
    transform: translate(-50%, -50%) scale(1.2);
}
.modal-card {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
}
</style>