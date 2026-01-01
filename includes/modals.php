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
        
        <button onclick="closeDetailModal()" class="modal-close">&times;</button>
        
        <div id="fullArticleContent" class="article-content">
            </div>
        
    </div>
</div>