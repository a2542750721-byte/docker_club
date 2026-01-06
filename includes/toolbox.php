<!-- Styles are loaded in index.php head -->
<!-- Toolbox Menu Modal -->
<div id="toolbox-menu-modal" class="ai-modal-overlay">
    <div class="ai-modal-container">
        <button onclick="closeToolboxMenu()" class="ai-close-btn" style="position:absolute; right:20px; top:15px;">&times;</button>
        
        <div id="main-menu">
            <h3 class="ai-modal-title" style="justify-content:center; margin-bottom:25px;">
                <i class="fas fa-tools"></i> 极客工具箱
            </h3>
            <div class="toolbox-grid">
                <div onclick="safeCall('openOnlineIDE')" class="toolbox-card">
                    <i class="fas fa-code"></i>
                    <h4>在线 IDE</h4>
                </div>
                <div onclick="safeCall('showAiSubMenu')" class="toolbox-card">
                    <i class="fas fa-robot"></i>
                    <h4>AI 实验室</h4>
                </div>
                <div onclick="safeCall('openConverterWindow')" class="toolbox-card">
                    <i class="fas fa-exchange-alt"></i>
                    <h4>文件转换</h4>
                </div>
                <div onclick="safeCall('openWebTorrentWindow')" class="toolbox-card">
                    <i class="fas fa-share-alt"></i>
                    <h4>WebTorrent</h4>
                </div>
                <div onclick="safeCall('openCyberChefWindow')" class="toolbox-card">
                    <i class="fas fa-user-secret"></i>
                    <h4>CyberChef</h4>
                </div>
                <div onclick="safeCall('openFfmpegWindow')" class="toolbox-card">
                    <i class="fas fa-file-video"></i>
                    <h4>FFmpeg</h4>
                </div>
                <div onclick="safeCall('openOcrWindow')" class="toolbox-card">
                    <i class="fas fa-eye"></i>
                    <h4>图片OCR</h4>
                </div>
                <!-- 
                    v86 虚拟机功能暂时关闭 
                    关闭原因：正在进行内核安全加固与性能优化 
                    预计恢复时间：2026-02-01 
                -->
                <div onclick="safeCall('openV86Window')" class="toolbox-card" style="display: none;">
                    <i class="fas fa-desktop"></i>
                    <h4>v86 虚拟机</h4>
                </div>
                <div onclick="safeCall('openRogueGameWindow')" class="toolbox-card" style="color: #ff3333;">
                    <i class="fas fa-gamepad"></i>
                    <h4>赛博肉鸽</h4>
                </div>
            </div>
        </div>

        <div id="ai-sub-menu" style="display:none;">
            <div style="display:flex; align-items:center; margin-bottom:20px;">
                <button onclick="safeCall('backToMainMenu')" style="border:none; background:none; color:var(--main-color-primary); cursor:pointer; font-size:16px;">
                    <i class="fas fa-arrow-left"></i> 返回
                </button>
                <h3 class="ai-modal-title" style="flex:1; justify-content:center;">选择 AI 功能</h3>
            </div>
            <div class="toolbox-grid">
                <div onclick="safeCall('openAiWindow', 'text')" class="toolbox-card">
                    <i class="fas fa-comment-dots"></i>
                    <h4>文生文字</h4>
                </div>
                <div onclick="safeCall('openAiWindow', 'image')" class="toolbox-card">
                    <i class="fas fa-paint-brush"></i>
                    <h4>文生图片</h4>
                </div>
                <div onclick="safeCall('openAiWindow', 'video')" class="toolbox-card">
                    <i class="fas fa-video"></i>
                    <h4>图生视频</h4>
                </div>
                <div onclick="safeCall('openAiWindow', 'debug')" class="toolbox-card" style="color:#ff4757;">
                    <i class="fas fa-bug" style="color:#ff4757;"></i>
                    <h4>代码诊断</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<!-- Converter Modal -->
<div id="converter-modal" class="ai-modal-overlay">
    <div class="ai-modal-container">
        <div class="ai-modal-header">
            <h3 class="ai-modal-title"><i class="fas fa-microchip"></i> 极客万能转换站</h3>
            <button onclick="closeConverter()" class="ai-close-btn">&times;</button>
        </div>

        <div class="converter-layout">
            <div class="converter-sidebar">
                <div>
                    <label class="converter-label">媒体与文件</label>
                    <select id="conv-cat-file" class="converter-select" onchange="updateConvUI('file')">
                        <option value="img2base64">图片 转 Base64</option>
                        <option value="img-format">图片格式转换 (JPG/PNG/WEBP)</option>
                        <option value="img-gray">图片一键去色 (黑白)</option>
                        <option value="docx2pdf">DOCX 预览/转 PDF</option>
                    </select>
                </div>
                
                <div>
                    <label class="converter-label">开发者数据</label>
                    <select id="conv-cat-text" class="converter-select" onchange="updateConvUI('text')">
                        <option value="json-prettify">JSON 格式化/美化</option>
                        <option value="json2yaml">JSON ↔ YAML 互转</option>
                        <option value="md2html">Markdown 转 HTML</option>
                        <option value="unix-time">Unix 时间戳 ↔ 时间</option>
                        <option value="url-codec">URL 编码/解码</option>
                        <option value="base64-text">文本 Base64 编解码</option>
                        <option value="case-convert">命名风格转换 (驼峰/下划线)</option>
                        <option value="word-count">全能字数统计</option>
                        <option value="color-convert">颜色 HEX ↔ RGB</option>
                    </select>
                </div>
            </div>

            <div class="converter-main">
                <div id="conv-upload-wrapper">
                    <div id="drop-zone" class="drop-zone" onclick="document.getElementById('file-input').click()">
                        <input type="file" id="file-input" style="display:none">
                        <i class="fas fa-file-upload" style="font-size:48px; color:var(--main-color-primary); margin-bottom:15px;"></i>
                        <p style="margin:0; font-weight:500;">点击或拖拽文件到这里</p>
                        <p style="font-size:12px; color:var(--text-secondary); margin-top:8px;">支持主流图片格式及 .docx</p>
                    </div>
                </div>

                <div id="conv-input-wrapper" style="display:none;">
                    <textarea id="conv-input-text" class="ai-input-field ai-textarea" placeholder="在此输入需要处理的原始数据..."></textarea>
                    <button class="ai-submit-btn" onclick="processTextConvert()" style="margin-top:15px;">立即执行转换</button>
                </div>

                <div id="converter-result" style="display:none; margin-top:25px; border-top:2px solid var(--border-color); padding-top:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span style="font-size:13px; color:#27ae60; font-weight:bold;"><i class="fas fa-check-circle"></i> 处理成功：</span>
                        <span id="result-info" style="font-size:11px; color:var(--text-secondary);"></span>
                    </div>
                    <textarea id="convert-output" class="ai-output-area" readonly style="height:180px;"></textarea>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:15px;">
                        <button class="ai-submit-btn" onclick="copyConvertResult()" style="background:transparent; border:1px solid var(--main-color-primary); color:var(--main-color-primary);"><i class="far fa-copy"></i> 复制结果</button>
                        <button class="ai-submit-btn" id="download-btn" style="display:none;"><i class="fas fa-download"></i> 下载结果文件</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WebTorrent Modal -->
<div id="webtorrent-modal" class="ai-modal-overlay">
    <div class="ai-modal-container" style="max-width: 1100px; width: 95%;">
        <div class="ai-modal-header">
            <h3 class="ai-modal-title"><i class="fas fa-share-alt"></i> WebTorrent 极速传输</h3>
            <button onclick="closeWebTorrentWindow()" class="ai-close-btn">&times;</button>
        </div>

        <div class="webtorrent-layout" style="display: flex; gap: 20px; height: 700px;">
            <!-- Left: Seed/Upload -->
            <div class="wt-sidebar" style="width: 300px; display: flex; flex-direction: column; gap: 15px; border-right: 1px solid var(--border-color); padding-right: 20px;">
                <div class="wt-panel">
                    <h4 style="margin-bottom: 10px; color: var(--text-primary);"><i class="fas fa-upload"></i> 创建种子 (分享)</h4>
                    <div id="wt-drop-zone" class="drop-zone" onclick="document.getElementById('wt-file-input').click()" style="height: 120px; padding: 15px;">
                        <input type="file" id="wt-file-input" multiple style="display:none">
                        <i class="fas fa-file-medical" style="font-size:32px; color:var(--main-color-primary); margin-bottom:10px;"></i>
                        <p style="margin:0; font-size: 13px;">点击或拖拽文件分享</p>
                    </div>
                </div>

                <div class="wt-panel">
                    <h4 style="margin-bottom: 10px; color: var(--text-primary);"><i class="fas fa-download"></i> 下载资源</h4>
                    <input type="text" id="wt-magnet-input" class="ai-input-field" placeholder="输入 Magnet 链接或 InfoHash..." style="margin-bottom: 10px;">
                    <button class="ai-submit-btn" onclick="WebTorrentManager.addTorrentFromInput()">开始下载</button>
                </div>
                
                <div class="wt-stats" style="margin-top: auto; background: var(--bg-secondary); padding: 10px; border-radius: 6px; font-size: 12px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom: 5px;">
                        <span><i class="fas fa-arrow-down"></i> <span id="wt-down-speed">0 KB/s</span></span>
                        <span><i class="fas fa-arrow-up"></i> <span id="wt-up-speed">0 KB/s</span></span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span><i class="fas fa-server"></i> 节点: <span id="wt-num-peers">0</span></span>
                        <span><i class="fas fa-percent"></i> 进度: <span id="wt-ratio">0%</span></span>
                    </div>
                </div>
            </div>

            <!-- Right: Torrent List -->
            <div class="wt-main" style="flex: 1; display: flex; flex-direction: column;">
                <h4 style="margin-bottom: 15px; color: var(--text-primary);">WebTorrent 传输列表</h4>
                <div id="wt-torrent-list" style="flex: 1; overflow-y: auto; background: var(--bg-secondary); border-radius: 8px; padding: 10px;">
                    <!-- Torrent Item Template -->
                    <!-- 
                    <div class="wt-item" style="background: var(--bg-primary); padding: 10px; border-radius: 6px; margin-bottom: 10px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom: 5px;">
                            <span style="font-weight:bold;">Example_Video.mp4</span>
                            <span style="color:var(--text-secondary);">100 MB</span>
                        </div>
                        <div class="ai-progress-bar" style="height: 6px; margin-bottom: 5px;">
                            <div class="ai-progress-fill" style="width: 45%;"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size: 11px; color:var(--text-secondary);">
                            <span><i class="fas fa-spinner fa-spin"></i> 下载中... 45%</span>
                            <button style="border:none; background:none; color: #e74c3c; cursor: pointer;">取消</button>
                        </div>
                    </div>
                    -->
                    <div style="text-align: center; color: var(--text-secondary); margin-top: 50px;">
                        <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p>暂无传输任务</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CyberChef Modal -->
<div id="cyberchef-modal" class="ai-modal-overlay">
    <div class="ai-modal-container" style="max-width: 1400px; width: 98%; height: 95vh; padding: 0; overflow: hidden; display: flex; flex-direction: column;">
        <div class="ai-modal-header" style="padding: 15px 20px; border-bottom: 1px solid var(--border-color); background: var(--bg-primary);">
            <h3 class="ai-modal-title"><i class="fas fa-user-secret"></i> CyberChef - 网络瑞士军刀</h3>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button onclick="refreshCyberChef()" class="ai-close-btn" style="font-size: 16px; position: static; color: var(--text-secondary);"><i class="fas fa-sync-alt"></i></button>
                <button onclick="closeCyberChefWindow()" class="ai-close-btn" style="font-size: 24px; position: static;">&times;</button>
            </div>
        </div>
        <div style="flex: 1; position: relative; background: #fff;">
            <div id="cyberchef-loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-primary); z-index: 10;">
                <i class="fas fa-spinner fa-spin" style="font-size: 40px; color: var(--main-color-primary); margin-bottom: 15px;"></i>
                <p>正在加载 CyberChef 安全终端...</p>
            </div>
            <iframe id="cyberchef-frame" src="about:blank" style="width: 100%; height: 100%; border: none;" onload="document.getElementById('cyberchef-loading').style.display='none'"></iframe>
        </div>
    </div>
</div>

<!-- FFmpeg Modal -->
<div id="ffmpeg-modal" class="ai-modal-overlay">
    <div class="ai-modal-container" style="max-width: 1000px; width: 95%;">
        <div class="ai-modal-header">
            <h3 class="ai-modal-title"><i class="fas fa-file-video"></i> FFmpeg.wasm 视频处理</h3>
            <button onclick="closeFfmpegWindow()" class="ai-close-btn">&times;</button>
        </div>

        <div class="ffmpeg-layout" style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Input Section -->
                <div class="ffmpeg-panel" style="background: var(--bg-secondary); padding: 20px; border-radius: 10px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-file-import"></i> 1. 输入文件</h4>
                    <div id="ffmpeg-drop-zone" class="drop-zone" onclick="document.getElementById('ffmpeg-file-input').click()" style="height: 100px; border-style: dashed;">
                        <input type="file" id="ffmpeg-file-input" style="display:none">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: var(--main-color-primary);"></i>
                        <p id="ffmpeg-file-name" style="margin: 10px 0 0; font-size: 13px;">选择或拖拽视频/音频文件</p>
                    </div>
                </div>

                <!-- Command Section -->
                <div class="ffmpeg-panel" style="background: var(--bg-secondary); padding: 20px; border-radius: 10px;">
                    <h4 style="margin-bottom: 15px;"><i class="fas fa-terminal"></i> 2. 处理命令</h4>
                    <select id="ffmpeg-preset" class="converter-select" style="margin-bottom: 10px;" onchange="FfmpegManager.applyPreset()">
                        <option value="">-- 选择预设命令 --</option>
                        <option value="-i input -vf scale=1280:720 output.mp4">转码为 720p (MP4)</option>
                        <option value="-i input -vn -ab 128k output.mp3">提取音频 (MP3)</option>
                        <option value="-i input -vf reverse output.mp4">视频倒放</option>
                        <option value="-i input -vf \"transpose=1\" output.mp4">顺时针旋转 90 度</option>
                        <option value="-i input -t 5 -f gif output.gif">截取 5 秒制作 GIF</option>
                    </select>
                    <input type="text" id="ffmpeg-command" class="ai-input-field" placeholder="ffmpeg -i input ..." value="-i input output.mp4">
                </div>
            </div>

            <!-- Progress & Logs -->
            <div class="ffmpeg-panel" style="background: #000; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px; color: #00ff00; height: 200px; overflow-y: auto;" id="ffmpeg-logs">
                <div>[System] FFmpeg.wasm 准备就绪...</div>
            </div>

            <div style="display: flex; gap: 15px; align-items: center;">
                <button id="ffmpeg-run-btn" class="ai-submit-btn" style="flex: 1;" onclick="FfmpegManager.run()">
                    <i class="fas fa-play"></i> 开始处理 (本地 Wasm 加速)
                </button>
                <div id="ffmpeg-status" style="font-size: 13px; color: var(--text-secondary);">
                    状态: 等待中
                </div>
            </div>

            <!-- Result Section -->
            <div id="ffmpeg-result" style="display: none; background: var(--bg-secondary); padding: 15px; border-radius: 8px; text-align: center;">
                <h4 style="margin-bottom: 10px; color: #2ecc71;"><i class="fas fa-check-circle"></i> 处理完成！</h4>
                <video id="ffmpeg-output-video" controls style="max-width: 100%; max-height: 300px; border-radius: 5px; margin-bottom: 15px; display: none;"></video>
                <div id="ffmpeg-output-info" style="font-size: 12px; margin-bottom: 10px;"></div>
                <button class="ai-submit-btn" style="background: #27ae60;" onclick="FfmpegManager.download()">
                    <i class="fas fa-download"></i> 下载结果文件
                </button>
            </div>
        </div>
    </div>
</div>

<!-- OCR Modal -->
<div id="ocr-modal" class="ai-modal-overlay">
    <div class="ai-modal-container" style="max-width: 900px; width: 95%;">
        <div class="ai-modal-header">
            <h3 class="ai-modal-title"><i class="fas fa-eye"></i> 图片OCR 识别</h3>
            <button onclick="closeOcrWindow()" class="ai-close-btn">&times;</button>
        </div>

        <div class="ocr-layout" style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Upload & Preview -->
                <div class="ocr-panel" style="background: var(--bg-secondary); padding: 20px; border-radius: 10px; display: flex; flex-direction: column; gap: 15px;">
                    <h4 style="margin-bottom: 5px;"><i class="fas fa-image"></i> 1. 上传图片</h4>
                    <div id="ocr-drop-zone" class="drop-zone" onclick="document.getElementById('ocr-file-input').click()" style="height: 150px; border-style: dashed; position: relative; overflow: hidden;">
                        <input type="file" id="ocr-file-input" style="display:none" accept="image/*">
                        <div id="ocr-upload-prompt">
                            <i class="fas fa-file-image" style="font-size: 40px; color: var(--main-color-primary);"></i>
                            <p style="margin: 10px 0 0; font-size: 13px;">点击或拖拽图片文件</p>
                        </div>
                        <img id="ocr-preview" style="display:none; max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <select id="ocr-lang" class="converter-select" style="flex: 1;">
                            <option value="chi_sim">简体中文 + 英文</option>
                            <option value="eng">仅英文 (English)</option>
                            <option value="chi_tra">繁体中文</option>
                            <option value="jpn">日文 (Japanese)</option>
                        </select>
                        <button class="ai-submit-btn" style="flex: 1;" onclick="OcrManager.recognize()">
                            <i class="fas fa-search"></i> 开始识别
                        </button>
                    </div>
                </div>

                <!-- Result -->
                <div class="ocr-panel" style="background: var(--bg-secondary); padding: 20px; border-radius: 10px; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h4 style="margin: 0;"><i class="fas fa-file-alt"></i> 2. 识别结果</h4>
                        <button onclick="OcrManager.copyResult()" class="ai-close-btn" style="position: static; font-size: 14px; color: var(--main-color-primary);">
                            <i class="far fa-copy"></i> 复制
                        </button>
                    </div>
                    <textarea id="ocr-result-text" class="ai-output-area" readonly placeholder="识别出的文字将显示在这里..." style="flex: 1; height: 210px; min-height: 210px;"></textarea>
                </div>
            </div>

            <!-- Progress Bar -->
            <div id="ocr-progress-container" style="display: none;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; color: var(--text-secondary);">
                    <span id="ocr-status-text">正在初始化 OCR 引擎...</span>
                    <span id="ocr-percent">0%</span>
                </div>
                <div class="ai-progress-bar" style="height: 8px; background: var(--border-color); border-radius: 4px; overflow: hidden;">
                    <div id="ocr-progress-fill" class="ai-progress-fill" style="width: 0%; height: 100%; background: var(--main-color-primary); transition: width 0.3s;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- v86 Modal -->
<div id="v86-modal" class="ai-modal-overlay">
    <div class="ai-modal-container" style="max-width: 1100px; width: 95%; height: 85vh; display: flex; flex-direction: column;">
        <div class="ai-modal-header">
            <h3 class="ai-modal-title"><i class="fas fa-desktop"></i> v86 x86 浏览器虚拟机</h3>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button onclick="safeCall('V86Manager.restart')" class="ai-close-btn" style="position: static; font-size: 16px; color: var(--text-secondary);" title="重启"><i class="fas fa-redo"></i></button>
                <button onclick="safeCall('closeV86Window')" class="ai-close-btn" style="position: static;">&times;</button>
            </div>
        </div>

        <div class="v86-layout" style="flex: 1; display: flex; gap: 20px; min-height: 0; padding-bottom: 20px;">
            <!-- Left: Terminal/Screen -->
            <div class="v86-main" style="flex: 1; background: #000; border-radius: 8px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <div id="v86-screen" style="white-space: pre; font-family: monospace; font-size: 14px; line-height: 1.2;">
                    <div style="white-space: pre; font-family: monospace; font-size: 14px; line-height: 1.2;"></div>
                    <canvas id="v86-canvas" style="display: none;"></canvas>
                </div>
                
                <div id="v86-loading" style="position: absolute; color: #fff; display: flex; flex-direction: column; align-items: center;">
                    <i class="fas fa-circle-notch fa-spin" style="font-size: 40px; margin-bottom: 15px;"></i>
                    <p>等待系统启动...</p>
                </div>
            </div>

            <!-- Right: Controls -->
            <div class="v86-sidebar" style="width: 280px; display: flex; flex-direction: column; gap: 15px;">
                <div class="v86-panel" style="background: var(--bg-secondary); padding: 15px; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px;"><i class="fas fa-cog"></i> 虚拟机设置</h4>
                    <label style="font-size: 12px; color: var(--text-secondary);">选择镜像:</label>
                    <select id="v86-os-select" class="converter-select" style="margin-top: 5px; margin-bottom: 15px;" onchange="safeCall('V86Manager.updateOSInfo')">
                        <option value="linux">Linux (Buildroot 31MB)</option>
                        <option value="kolibrios">KolibriOS (图形化 1.44MB)</option>
                        <option value="freedos">FreeDOS (DOS 兼容)</option>
                        <option value="custom">自定义 ISO/IMG...</option>
                    </select>

                    <div id="v86-custom-upload" style="display: none; margin-bottom: 15px;">
                        <input type="file" id="v86-file-input" class="ai-input-field" style="font-size: 11px;">
                    </div>

                    <button id="v86-start-btn" class="ai-submit-btn" onclick="safeCall('V86Manager.start')">
                        <i class="fas fa-play"></i> 启动虚拟机
                    </button>
                </div>

                <div class="v86-panel" style="background: var(--bg-secondary); padding: 15px; border-radius: 8px; flex: 1;">
                    <h4 style="margin-bottom: 10px;"><i class="fas fa-info-circle"></i> 状态信息</h4>
                    <div style="font-size: 12px; line-height: 1.8; color: var(--text-secondary);">
                        <div>CPU: x86 (JIT 加速)</div>
                        <div>内存: 128MB</div>
                        <div>网络: 已模拟 (仅出站)</div>
                        <div style="margin-top: 10px; padding: 10px; background: rgba(0,0,0,0.05); border-radius: 4px; color: var(--text-primary);">
                            <i class="fas fa-keyboard"></i> 提示: 点击左侧黑屏区域以捕获键盘焦点。
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rogue Game Modal -->
<!-- Rogue Game Modal (Cyberpunk Styled) -->
<div id="rogue-game-modal" class="ai-modal-overlay" style="backdrop-filter: blur(8px); background: rgba(0,0,0,0.8);">
    <div class="ai-modal-container rogue-window-glow" style="max-width: 1200px; width: 95%; height: 85vh; padding: 0; overflow: hidden; display: flex; flex-direction: column; background: #000; border: 1px solid #00f3ff; position: relative; box-shadow: 0 0 30px rgba(0, 243, 255, 0.2);">
        <!-- Scanline Effect Overlay -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03)); z-index: 5; pointer-events: none; background-size: 100% 4px, 3px 100%;"></div>
        
        <div class="ai-modal-header cyber-header" style="padding: 15px 25px; border-bottom: 2px solid #00f3ff; background: #050505; position: relative; z-index: 10;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div class="header-status-light"></div>
                <h3 class="ai-modal-title glitch-text" style="color: #00f3ff; font-family: 'Courier New', monospace; letter-spacing: 2px; margin: 0; text-shadow: 0 0 10px #00f3ff;">
                    <i class="fas fa-gamepad"></i> NEON BINDING: SYSTEM FAILURE v2.0
                </h3>
            </div>
            <div style="display: flex; gap: 15px; align-items: center;">
                 <button onclick="safeCall('RogueGameManager.restart')" class="cyber-btn" title="重启系统"><i class="fas fa-redo"></i></button>
                 <button onclick="safeCall('closeRogueGameWindow')" class="cyber-btn btn-close" style="color: #ff3333; border-color: #ff3333;">&times;</button>
            </div>
        </div>

        <div style="flex: 1; position: relative; background: #000; z-index: 6;">
            <div id="rogue-game-loading" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #000; z-index: 20; color: #00f3ff;">
                <div class="loading-scanner"></div>
                <i class="fas fa-circle-notch fa-spin" style="font-size: 50px; margin-bottom: 20px; text-shadow: 0 0 15px #00f3ff;"></i>
                <p style="font-family: 'Courier New', monospace; letter-spacing: 4px; animation: pulse 1.5s infinite;">INITIALIZING NEON SYSTEMS...</p>
                <div id="loading-status" style="font-size: 12px; color: #555; margin-top: 10px;">CORE_INIT: OK | ASSET_SYNC: PENDING</div>
            </div>
            <iframe id="rogue-game-frame" src="about:blank" style="width: 100%; height: 100%; border: none;" onload="document.getElementById('rogue-game-loading').style.display='none'"></iframe>
        </div>
        
        <!-- Footer Info -->
        <div style="padding: 5px 15px; background: #050505; border-top: 1px solid #222; font-size: 10px; color: #444; font-family: monospace; display: flex; justify-content: space-between; z-index: 10;">
            <span>STATUS: SECURE CONNECTION ESTABLISHED</span>
            <span>ENCRYPTION: AES-256-GCM</span>
        </div>
    </div>
</div>

<style>
.rogue-window-glow {
    animation: window-entry 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.cyber-header {
    box-shadow: 0 5px 15px rgba(0, 243, 255, 0.1);
}

.header-status-light {
    width: 8px;
    height: 8px;
    background: #00f3ff;
    border-radius: 50%;
    box-shadow: 0 0 10px #00f3ff;
    animation: blink 1s infinite;
}

.cyber-btn {
    background: transparent;
    border: 1px solid #00f3ff;
    color: #00f3ff;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 4px;
}

.cyber-btn:hover {
    background: rgba(0, 243, 255, 0.1);
    box-shadow: 0 0 15px rgba(0, 243, 255, 0.3);
    transform: translateY(-2px);
}

.cyber-btn.btn-close:hover {
    background: rgba(255, 51, 51, 0.1);
    box-shadow: 0 0 15px rgba(255, 51, 51, 0.3);
}

.loading-scanner {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: #00f3ff;
    box-shadow: 0 0 15px #00f3ff;
    animation: scan 2s linear infinite;
    opacity: 0.5;
}

@keyframes scan {
    0% { top: 0; }
    100% { top: 100%; }
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

@keyframes window-entry {
    from { opacity: 0; transform: scale(0.9) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>

<?php 
    // Logic to detect local dependencies
    $assets_path = __DIR__ . '/../assets/js/lib';
    
    // Check for local availability
    $pyodide_local = file_exists($assets_path . '/pyodide/pyodide.js'); 
    $monaco_local = file_exists($assets_path . '/monaco-editor/min/vs/loader.js');
    $webtorrent_local = file_exists($assets_path . '/webtorrent/webtorrent.min.js');
    $ffmpeg_local = file_exists($assets_path . '/ffmpeg/ffmpeg.min.js');
    $tesseract_local = file_exists($assets_path . '/tesseract/tesseract.min.js');
    $v86_local = file_exists($assets_path . '/v86/libv86.js');

    // URLs (Prefer unpkg.com as it's often more stable for these specific libs)
    $v86_lib_url = $v86_local 
        ? '/assets/js/lib/v86/libv86.js' 
        : 'https://unpkg.com/v86@0.7.0/build/libv86.js';

    $pyodide_url = $pyodide_local
        ? '/assets/js/lib/pyodide/pyodide.js'
        : 'https://cdn.jsdelivr.net/pyodide/v0.29.0/full/pyodide.js';
        
    $monaco_loader_url = $monaco_local
        ? '/assets/js/lib/monaco-editor/min/vs/loader.js'
        : 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.34.1/min/vs/loader.min.js';

    $monaco_base_url = $monaco_local
        ? '/assets/js/lib/monaco-editor/min/vs'
        : 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.34.1/min/vs';
        
    $webtorrent_url = $webtorrent_local
        ? '/assets/js/lib/webtorrent/webtorrent.min.js'
        : 'https://unpkg.com/webtorrent@2.4.8/dist/webtorrent.min.js';

    $ffmpeg_url = $ffmpeg_local
        ? '/assets/js/lib/ffmpeg/ffmpeg.min.js'
        : 'https://unpkg.com/@ffmpeg/ffmpeg@0.11.6/dist/ffmpeg.min.js';

    $tesseract_url = $tesseract_local
        ? '/assets/js/lib/tesseract/tesseract.min.js'
        : 'https://unpkg.com/tesseract.js@5.0.3/dist/tesseract.min.js';
?>

<script>
    // Global Security Context Flag
    const IS_SECURE_CONTEXT = window.isSecureContext;

    // Centralized IDE Configuration
    window.IDE_CONFIG = {
        isSecureContext: IS_SECURE_CONTEXT,
        origin: window.location.origin,
        paths: {
            monacoBase: "<?php echo $monaco_base_url; ?>",
            pyodideIndex: "<?php echo $pyodide_local ? '/assets/js/lib/pyodide/' : 'https://cdn.jsdelivr.net/pyodide/v0.29.0/full/'; ?>",
            ffmpegCore: "<?php echo $ffmpeg_local ? '/assets/js/lib/ffmpeg/ffmpeg-core.js' : 'https://unpkg.com/@ffmpeg/core@0.11.6/dist/ffmpeg-core.js'; ?>",
            tesseractScript: "<?php echo $tesseract_url; ?>",
            v86Lib: "<?php echo $v86_lib_url; ?>",
            v86Wasm: "<?php echo $v86_local ? '/assets/js/lib/v86/v86.wasm' : 'https://unpkg.com/v86@0.7.0/build/v86.wasm'; ?>",
            v86Bios: "<?php echo $v86_local ? '/assets/js/lib/v86/seabios.bin' : 'https://unpkg.com/v86@0.7.0/bios/seabios.bin'; ?>",
            v86VgaBios: "<?php echo $v86_local ? '/assets/js/lib/v86/vgabios.bin' : 'https://unpkg.com/v86@0.7.0/bios/vgabios.bin'; ?>"
        },
        flags: {
            isPyodideLocal: <?php echo $pyodide_local ? 'true' : 'false'; ?>,
            isMonacoLocal: <?php echo $monaco_local ? 'true' : 'false'; ?>,
            isWebTorrentLocal: <?php echo $webtorrent_local ? 'true' : 'false'; ?>,
            isFfmpegLocal: <?php echo $ffmpeg_local ? 'true' : 'false'; ?>,
            isV86Local: <?php echo $v86_local ? 'true' : 'false'; ?>
        },
        timeouts: {
            init: 15000 // 15s timeout
        }
    };

    /**
     * Safe function caller to prevent ReferenceErrors while scripts are loading
     */
    function safeCall(fnPath, ...args) {
        const parts = fnPath.split('.');
        let context = window;
        let fn = window;

        for (const part of parts) {
            if (context === undefined || context === null) break;
            context = context[part];
        }
        
        fn = context;
        // Reset context to the parent object for apply
        context = window;
        if (parts.length > 1) {
            context = window;
            for (let i = 0; i < parts.length - 1; i++) {
                context = context[parts[i]];
            }
        }

        if (typeof fn === 'function') {
            return fn.apply(context, args);
        } else {
            console.warn(`[Toolbox] ${fnPath} is not defined yet or not a function.`);
        }
    }
    
    // Legacy support (to be removed after full refactor)
    window.monacoBaseUrl = window.IDE_CONFIG.paths.monacoBase;
    window.pyodideLocal = window.IDE_CONFIG.flags.isPyodideLocal;
    
    console.log("[Toolbox] Config loaded:", window.IDE_CONFIG);
</script>

<?php 
    include_once __DIR__ . '/ai_modules/ide.php'; 
    include_once __DIR__ . '/ai_modules/ai_lab.php';

    // Standardized version management
    $script_version = '1.0.6'; // Increment version to bust cache
    $v = "?v=" . $script_version;
    $time_v = "?v=" . time(); 
    
    // For external CDN URLs, we don't need to append ?v= if they are already versioned
    $webtorrent_final_url = $webtorrent_local ? ($webtorrent_url . $v) : $webtorrent_url;
    // Use the correctly determined URL from above (local or CDN)
    $pyodide_final_url = $pyodide_local ? ($pyodide_url . $v) : $pyodide_url;
    $ffmpeg_final_url = $ffmpeg_local ? ($ffmpeg_url . $v) : $ffmpeg_url;
    $v86_final_url = $v86_local ? ($v86_lib_url . $v) : $v86_lib_url;
    $tesseract_final_url = $tesseract_local ? ($tesseract_url . $v) : $tesseract_url;
?>

<!-- Load Pyodide FIRST to avoid AMD/RequireJS conflict with Monaco -->
<script src="<?php echo $pyodide_final_url; ?>"></script>
<script src="<?php echo $webtorrent_final_url; ?>"></script>
<script src="<?php echo $ffmpeg_final_url; ?>"></script>
<script src="<?php echo $v86_final_url; ?>"></script>
<script src="<?php echo $tesseract_final_url; ?>"></script>
<script src="includes/ai_modules/scripts.js<?php echo $time_v; ?>"></script>
