<div id="ide-modal" class="ai-modal-overlay" style="display: none;">
    <div class="ai-modal-container" style="width: 95vw; height: 95vh; max-width: none;">
        <div class="ai-modal-header">
            <span class="ai-modal-title">
                <i class="fas fa-code"></i> Cloud IDE <small style="font-size: 12px; margin-left: 10px; opacity: 0.7;">(Powered by CodeMirror 6)</small>
            </span>
            <div class="ide-toolbar">
                <button onclick="ideManager.saveFile()" class="ide-run-btn" title="Save (Ctrl+S)">
                    <i class="fas fa-save"></i> 保存
                </button>
                <button id="run-btn" onclick="runCode()" class="ide-run-btn" disabled>
                    <i class="fas fa-play"></i> 运行
                </button>
                <button onclick="closeIDE()" class="ai-close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="ide-layout" style="display: flex; flex: 1; overflow: hidden;">
            <!-- Sidebar: File Tree -->
            <div class="ide-sidebar" style="width: 250px; background: var(--bg-secondary); border-right: 1px solid var(--border-color); display: flex; flex-direction: column;">
                <div style="padding: 10px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: bold; font-size: 12px;">资源管理器</span>
                    <div style="display: flex; gap: 5px;">
                        <i class="fas fa-file-medical" style="cursor: pointer;" onclick="ideManager.createFile()" title="新建文件"></i>
                        <i class="fas fa-folder-plus" style="cursor: pointer;" onclick="ideManager.createFolder()" title="新建文件夹"></i>
                        <i class="fas fa-sync-alt" style="cursor: pointer;" onclick="ideManager.refreshTree()" title="刷新"></i>
                    </div>
                </div>
                <div id="file-tree" class="ide-file-tree" style="flex: 1; overflow-y: auto; padding: 5px;">
                    <!-- Tree items will be injected here -->
                    <div style="text-align: center; margin-top: 20px; color: var(--text-secondary);">
                        <i class="fas fa-spinner fa-spin"></i> 加载中...
                    </div>
                </div>
            </div>
            
            <!-- Main: Editor -->
            <div class="ide-main" style="flex: 1; display: flex; flex-direction: column; overflow: hidden; position: relative;">
                <!-- Tabs (Optional, for now just current file info) -->
                <div id="editor-status-bar" style="padding: 5px 15px; background: var(--bg-primary); border-bottom: 1px solid var(--border-color); font-size: 12px; color: var(--text-secondary); display: flex; justify-content: space-between;">
                    <span id="current-file-path">未选择文件</span>
                    <span id="editor-cursor-info">Ln 1, Col 1</span>
                </div>
                
                <div id="editor-container" style="flex: 1; overflow: hidden;"></div>
                
                <!-- Console (Collapsible) -->
                <div id="ide-console-wrapper" style="height: 150px; border-top: 1px solid var(--border-color); display: flex; flex-direction: column;">
                    <div style="padding: 5px 10px; background: var(--bg-secondary); border-bottom: 1px solid var(--border-color); font-size: 11px; font-weight: bold; display: flex; justify-content: space-between;">
                        <span><i class="fas fa-terminal"></i> 终端 / 输出</span>
                        <i class="fas fa-chevron-down" style="cursor: pointer;" onclick="document.getElementById('ide-console-wrapper').style.height = document.getElementById('ide-console-wrapper').offsetHeight > 30 ? '30px' : '150px'"></i>
                    </div>
                    <div id="ide-console" style="flex: 1; background: #000; color: #0f0; padding: 10px; font-family: monospace; overflow-y: auto; font-size: 12px;">Waiting for output...</div>
                </div>
            </div>
        </div>
    </div>
</div>
