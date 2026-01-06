<div id="ai-task-modal" class="ai-modal-overlay">
    <div class="ai-modal-container">
        <!-- Header -->
        <div class="ai-modal-header">
            <h3 id="ai-task-title" class="ai-modal-title">AI 助手</h3>
            
            <!-- AI Text Toolbar -->
            <div id="ai-text-toolbar" class="ai-toolbar" style="display:none;">
                <button onclick="ChatManager.newChat()" class="ai-tool-btn" title="新对话"><i class="fas fa-plus"></i></button>
                <button onclick="ChatManager.toggleHistory()" class="ai-tool-btn" title="历史记录"><i class="fas fa-history"></i></button>
                <button onclick="ChatManager.exportChat()" class="ai-tool-btn" title="导出"><i class="fas fa-download"></i></button>
            </div>

            <button onclick="closeAiWindow()" class="ai-close-btn">&times;</button>
        </div>
        
        <!-- History Panel -->
        <div id="ai-history-panel" class="ai-history-panel" style="display:none;">
            <div class="history-header">
                <span>历史记录</span>
                <button onclick="ChatManager.toggleHistory()" style="background:none;border:none;cursor:pointer;color:var(--text-secondary)"><i class="fas fa-times"></i></button>
            </div>
            <div style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                <input type="text" placeholder="搜索标题..." style="width:100%; padding: 8px; border:1px solid var(--border-color); border-radius:4px; background:var(--bg-secondary); color:var(--text-primary);" oninput="ChatManager.filterHistory(this.value)">
            </div>
            <div id="history-list" class="history-list">
                <!-- History items will be populated here -->
            </div>
        </div>
        
        <!-- Main Layout -->
        <div class="ai-lab-layout">
            
            <!-- Left Sidebar: Controls -->
            <div class="ai-lab-sidebar">
                
                <!-- Video/Image URL Input -->
                <div id="vid-url-box" class="ai-input-group" style="display:none;">
                    <input id="ai-url" type="text" class="ai-input-field" 
                           placeholder="请输入图片或视频的 URL (视频/视觉模式专用)">
                </div>

                <!-- Prompt Input -->
                <textarea id="ai-prompt" class="ai-input-field ai-textarea" 
                          placeholder="描述您的需求（例如：优化这段代码、分析视频内容、撰写文案）..."></textarea>
                
                <!-- Submit Button -->
                <button id="ai-btn" onclick="runAiTask()" class="ai-submit-btn">
                    <i class="fas fa-magic"></i> <span>开始执行</span>
                </button>

                <!-- Status Panel -->
                <div id="ai-status-panel" class="ai-status-panel" style="display:none;">
                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:10px;">
                        <span id="ai-status-text" style="color:var(--main-color-primary); font-weight:500;">AI 正在接入云端...</span>
                        <span id="ai-status-percent" style="font-family:monospace; font-weight:bold;">0%</span>
                    </div>
                    <div class="ai-progress-bar">
                        <div id="ai-progress-fill" class="ai-progress-fill" style="width:0%;"></div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Output -->
            <div class="ai-lab-main" style="display:flex; flex-direction:column; overflow:hidden;">
                <!-- Output Area (Top) -->
                <div id="ai-output" class="ai-output-area" style="flex:1; min-height:200px;">等待 AI 生成结果...</div>
                
                <!-- Monitor Panel (Bottom, New) -->
                <div id="ai-monitor" class="ai-monitor collapsed">
                    <div class="monitor-header" onclick="toggleMonitor()" style="cursor: pointer; user-select: none;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <h4><i class="fas fa-desktop"></i> 全流程监控中心</h4>
                            <span id="monitor-task-id" class="task-id">ID: PENDING</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px; margin-left:auto;">
                            <span style="font-size:12px; color:var(--text-secondary); font-weight:normal;">(点击此处折叠/展开)</span>
                            <i id="monitor-toggle-icon" class="fas fa-chevron-up"></i>
                        </div>
                    </div>
                    
                    <div class="monitor-body">
                        <div class="monitor-steps">
                            <div class="monitor-progress-line" id="monitor-line"></div>
                            <div class="step" id="step-req" data-step="req">
                                <div class="step-icon"><i class="fas fa-paper-plane"></i></div>
                                <div class="step-label">请求接收</div>
                            </div>
                            <div class="step" id="step-proc" data-step="proc">
                                <div class="step-icon"><i class="fas fa-cog"></i></div>
                                <div class="step-label">生成处理</div>
                            </div>
                            <div class="step" id="step-res" data-step="res">
                                <div class="step-icon"><i class="fas fa-check"></i></div>
                                <div class="step-label">结果返回</div>
                            </div>
                        </div>

                        <div class="monitor-metrics">
                            <div class="metric">
                                <span class="label">当前状态</span>
                                <span id="metric-status" class="value">IDLE</span>
                            </div>
                            <div class="metric">
                                <span class="label">执行耗时</span>
                                <span id="metric-time" class="value">0.0s</span>
                            </div>
                            <div class="metric">
                                <span class="label">任务进度</span>
                                <div class="progress-mini">
                                    <div id="metric-progress" class="fill" style="width:0%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="monitor-logs">
                            <div id="monitor-log-list" class="log-list"></div>
                        </div>
                    </div>
                </div>

                <div class="ai-disclaimer">
                    <i class="fas fa-shield-alt"></i> 算法生成内容，仅供技术交流参考
                </div>
            </div>
        </div>
    </div>
</div>
