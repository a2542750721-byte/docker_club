<?php
/**
 * Competition Center Modal Component
 * 
 * Description: Main frontend component for the Competition Center (Practice, Quiz, Admin).
 * Included by: footer.php or index.php
 * Author: Trae AI Assistant
 * Date: 2026-01-02
 * Version: 2.0 (Refactored)
 */
?>

<!-- Modal Backdrop -->
<div id="competition-menu-modal" class="comp-modal-overlay" style="display: none;">
    <!-- Modal Container -->
    <div class="comp-modal-container">
        
        <!-- Sidebar Navigation -->
        <aside class="comp-sidebar">
            <div class="comp-sidebar-header">
                <div class="comp-logo">
                    <i class="fas fa-trophy"></i>
                    <span>竞赛中心</span>
                </div>
                <button class="comp-close-mobile" onclick="toggleCompSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="comp-nav-list">
                <div class="comp-nav-group">
                    <span class="comp-nav-label">学习 & 提升</span>
                    <button class="comp-nav-item active" onclick="CompApp.switchTab('practice')">
                        <i class="fas fa-book-reader"></i> 练习模式
                    </button>
                    <button class="comp-nav-item" onclick="CompApp.switchTab('quiz')">
                        <i class="fas fa-stopwatch"></i> 测验模式
                    </button>
                </div>

                <div class="comp-nav-group">
                    <span class="comp-nav-label">后台管理</span>
                    <button class="comp-nav-item" onclick="CompApp.switchTab('admin')">
                        <i class="fas fa-layer-group"></i> 题库管理
                    </button>
                </div>
            </nav>

            <div class="comp-sidebar-footer">
                <div id="comp-auth-status" class="comp-auth-status">
                    <!-- Loaded via JS -->
                    <i class="fas fa-spinner fa-spin"></i> 检查状态...
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="comp-main">
            <!-- Mobile Header -->
            <div class="comp-mobile-header">
                <button onclick="toggleCompSidebar()"><i class="fas fa-bars"></i></button>
                <span>竞赛中心</span>
            </div>

            <!-- Close Button (Desktop) -->
            <button class="comp-close-desktop" onclick="closeCompetitionMenu()">
                <i class="fas fa-times"></i>
            </button>

            <!-- MODULE 1: PRACTICE MODE -->
            <section id="view-practice" class="comp-view active">
                <header class="comp-view-header">
                    <div class="comp-view-title">
                        <h2>练习模式</h2>
                        <span class="comp-subtitle">自由刷题 · 错题标记 · 进度保存</span>
                    </div>
                    <div class="comp-toolbar">
                        <select id="practice-bank" class="comp-select" onchange="CompApp.syncBank(this.value)">
                            <option value="Python">Python 题库</option>
                            <option value="Web">Web 安全</option>
                            <option value="Algorithm">算法竞赛</option>
                        </select>
                        <select id="practice-diff" class="comp-select" onchange="CompApp.loadPracticeQuestion()">
                            <option value="">全部难度</option>
                            <option value="easy">简单</option>
                            <option value="medium">中等</option>
                            <option value="hard">困难</option>
                        </select>
                    </div>
                </header>

                <div class="comp-view-body has-footer" id="practice-container">
                    <!-- Question Card will be injected here -->
                    <div class="comp-placeholder">
                        <button class="comp-btn comp-btn-primary" onclick="CompApp.loadPracticeQuestion()">开始练习</button>
                    </div>
                </div>

                <div class="comp-fixed-footer">
                     <button class="comp-btn comp-btn-outline" onclick="CompApp.loadPracticeQuestion()">下一题 <i class="fas fa-arrow-right"></i></button>
                </div>
            </section>

            <!-- MODULE 2: QUIZ MODE -->
            <section id="view-quiz" class="comp-view">
                <header class="comp-view-header">
                    <div class="comp-view-title">
                        <h2>测验模式</h2>
                        <span class="comp-subtitle">模拟考试 · 倒计时 · 成绩报告</span>
                    </div>
                    <div style="display:flex; gap:20px; align-items:center;">
                        <div id="quiz-progress" class="comp-badge" style="display:none; font-size:14px;">
                            第 <span id="quiz-current-idx">1</span> / <span id="quiz-total-count">10</span> 题
                        </div>
                        <div id="quiz-timer" class="comp-timer" style="display:none;">
                            <i class="fas fa-clock"></i> <span id="quiz-time-display">00:00</span>
                        </div>
                    </div>
                </header>

                <div class="comp-view-body has-footer">
                    <!-- Setup Screen -->
                    <div id="quiz-setup-screen" class="comp-centered-card">
                        <h3 class="card-title">考前设置</h3>
                        
                        <div class="comp-form-group">
                            <label>考生昵称</label>
                            <input type="text" id="quiz-username" class="comp-input" placeholder="请输入您的名字">
                        </div>

                        <div class="comp-form-group">
                            <label>选择题库</label>
                            <select id="quiz-bank" class="comp-select full-width" onchange="CompApp.syncBank(this.value)">
                                <option value="Python">Python 题库</option>
                                <option value="Web">Web 安全</option>
                                <option value="Algorithm">算法竞赛</option>
                            </select>
                        </div>

                        <div class="comp-form-group">
                            <label>组卷方式</label>
                            <div class="comp-radio-group">
                                <label><input type="radio" name="quiz_mode" value="auto" checked onchange="CompApp.toggleQuizMode()"> 随机组卷</label>
                                <label><input type="radio" name="quiz_mode" value="manual" onchange="CompApp.toggleQuizMode()"> 手动选题</label>
                            </div>
                        </div>

                        <!-- Auto Mode Options -->
                        <div id="quiz-options-auto">
                            <div class="comp-grid-2">
                                <div class="comp-form-group">
                                    <label>难度</label>
                                    <select id="quiz-diff" class="comp-select full-width">
                                        <option value="">随机</option>
                                        <option value="easy">简单</option>
                                        <option value="medium">中等</option>
                                        <option value="hard">困难</option>
                                    </select>
                                </div>
                                <div class="comp-form-group">
                                    <label>题量</label>
                                    <select id="quiz-count" class="comp-select full-width">
                                        <option value="10">10 题</option>
                                        <option value="20">20 题</option>
                                        <option value="30">30 题</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Mode Options -->
                        <div id="quiz-options-manual" style="display:none;">
                            <div class="comp-info-box">
                                已选题目: <strong id="manual-select-count">0</strong> 题
                                <button class="comp-btn comp-btn-sm comp-btn-outline" onclick="CompApp.openSelector()">选择题目</button>
                            </div>
                        </div>

                        <button class="comp-btn comp-btn-primary full-width" onclick="CompApp.startQuiz()">开始测验</button>
                    </div>

                    <!-- Quiz Taking Screen -->
                    <div id="quiz-taking-screen" style="display:none;">
                        <div id="quiz-questions-list"></div>
                    </div>

                    <!-- Results Screen -->
                    <div id="quiz-result-screen" style="display:none; text-align: center; padding-top: 40px;">
                        <div class="comp-result-icon"><i class="fas fa-clipboard-check"></i></div>
                        <h2>考试结束</h2>
                        <div class="comp-result-stats">
                            <div class="stat-card">
                                <span class="label">得分</span>
                                <span class="value" id="res-score">-</span>
                            </div>
                            <div class="stat-card">
                                <span class="label">耗时</span>
                                <span class="value" id="res-time">-</span>
                            </div>
                        </div>
                        <div id="quiz-wrong-answers" class="comp-wrong-list"></div>
                        <div class="comp-action-bar centered">
                            <button class="comp-btn comp-btn-outline" onclick="CompApp.resetQuiz()">再测一次</button>
                        </div>
                    </div>
                </div>

                <!-- Quiz Fixed Footer -->
                <div id="quiz-footer" class="comp-fixed-footer between" style="display:none;">
                    <button class="comp-btn comp-btn-outline" id="quiz-prev-btn" onclick="CompApp.prevQuizQuestion()"><i class="fas fa-arrow-left"></i> 上一题</button>
                    <div>
                        <button class="comp-btn comp-btn-outline" id="quiz-next-btn" onclick="CompApp.nextQuizQuestion()">下一题 <i class="fas fa-arrow-right"></i></button>
                        <button class="comp-btn comp-btn-primary" id="quiz-submit-btn" onclick="CompApp.submitQuizWithConfirm()" style="display:none;">提交试卷 <i class="fas fa-check"></i></button>
                    </div>
                </div>
            </section>

            <!-- MODULE 3: ADMIN MODE -->
            <section id="view-admin" class="comp-view">
                <header class="comp-view-header">
                    <div class="comp-view-title">
                        <h2>题库管理</h2>
                    </div>
                    <div class="comp-toolbar">
                        <select id="admin-bank" class="comp-select" onchange="CompApp.syncBank(this.value)">
                            <option value="Python">Python 题库</option>
                            <option value="Web">Web 安全</option>
                            <option value="Algorithm">算法竞赛</option>
                        </select>
                        <button class="comp-btn comp-btn-outline" onclick="CompApp.exportData()"><i class="fas fa-download"></i> 导出</button>
                        <button class="comp-btn comp-btn-outline" onclick="document.getElementById('import-file').click()"><i class="fas fa-upload"></i> 导入</button>
                        <input type="file" id="import-file" hidden onchange="CompApp.importData(this)">
                        <button class="comp-btn comp-btn-primary" onclick="CompApp.openEditor()"><i class="fas fa-plus"></i> 新增题目</button>
                    </div>
                </header>

                <div class="comp-view-body">
                    <!-- List View -->
                    <div id="admin-list-view" class="comp-table-wrapper">
                        <table class="comp-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>类型</th>
                                    <th>难度</th>
                                    <th>题目预览</th>
                                    <th>标签</th>
                                    <th width="100">操作</th>
                                </tr>
                            </thead>
                            <tbody id="admin-table-body">
                                <!-- JS Populated -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Editor View (Overlay Card) -->
                    <div id="admin-editor-view" class="comp-overlay-card" style="display:none;">
                        <div class="card-header">
                            <h3 id="editor-title">编辑题目</h3>
                            <button class="close-btn" onclick="CompApp.closeEditor()">&times;</button>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="edit-id">
                            
                            <div class="comp-grid-3">
                                <div class="comp-form-group">
                                    <label>题库</label>
                                    <select id="edit-category" class="comp-select full-width">
                                        <option value="Python">Python</option>
                                        <option value="Web">Web 安全</option>
                                        <option value="Algorithm">算法竞赛</option>
                                    </select>
                                </div>
                                <div class="comp-form-group">
                                    <label>类型</label>
                                    <select id="edit-type" class="comp-select full-width" onchange="CompApp.toggleEditorType()">
                                        <option value="choice">选择题</option>
                                        <option value="practical">实操题</option>
                                    </select>
                                </div>
                                <div class="comp-form-group">
                                    <label>难度</label>
                                    <select id="edit-diff" class="comp-select full-width">
                                        <option value="easy">简单</option>
                                        <option value="medium">中等</option>
                                        <option value="hard">困难</option>
                                    </select>
                                </div>
                            </div>

                            <div class="comp-form-group">
                                <label>标签 (逗号分隔)</label>
                                <input type="text" id="edit-tags" class="comp-input" placeholder="如: 循环, 正则">
                            </div>

                            <div class="comp-form-group">
                                <label>题目描述</label>
                                <textarea id="edit-text" class="comp-textarea" rows="4"></textarea>
                            </div>

                            <!-- Choice Options -->
                            <div id="editor-choice-area">
                                <label class="section-label">选项设置</label>
                                <div class="comp-options-group">
                                    <div class="option-row"><span class="opt-label">A</span><input type="text" id="edit-opt0" class="comp-input"></div>
                                    <div class="option-row"><span class="opt-label">B</span><input type="text" id="edit-opt1" class="comp-input"></div>
                                    <div class="option-row"><span class="opt-label">C</span><input type="text" id="edit-opt2" class="comp-input"></div>
                                    <div class="option-row"><span class="opt-label">D</span><input type="text" id="edit-opt3" class="comp-input"></div>
                                </div>
                                <div class="comp-form-group">
                                    <label>正确答案</label>
                                    <select id="edit-correct" class="comp-select full-width highlight">
                                        <option value="0">选项 A</option>
                                        <option value="1">选项 B</option>
                                        <option value="2">选项 C</option>
                                        <option value="3">选项 D</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Practical Answer -->
                            <div id="editor-practical-area" style="display:none;">
                                <div class="comp-form-group">
                                    <label>Flag / 标准答案</label>
                                    <input type="text" id="edit-answer" class="comp-input code-font" placeholder="flag{...}">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="comp-btn comp-btn-outline" onclick="CompApp.closeEditor()">取消</button>
                            <button class="comp-btn comp-btn-primary" onclick="CompApp.saveQuestion()">保存</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<!-- Question Selector Modal (Nested) -->
<div id="comp-selector-modal" class="comp-modal-overlay z-high" style="display:none;">
    <div class="comp-modal-container sm">
        <div class="comp-view-header">
            <h3>选择题目</h3>
            <button class="comp-close-desktop" onclick="CompApp.closeSelector()">&times;</button>
        </div>
        <div class="comp-view-body scrollable">
            <table class="comp-table">
                <thead><tr><th><input type="checkbox" onchange="CompApp.toggleSelectAll(this)"></th><th>题目</th><th>难度</th></tr></thead>
                <tbody id="selector-body"></tbody>
            </table>
        </div>
        <div class="comp-footer-bar">
            <span>已选: <b id="selector-total">0</b></span>
            <button class="comp-btn comp-btn-primary" onclick="CompApp.closeSelector()">确认</button>
        </div>
    </div>
</div>

<style>
:root {
    --c-primary: #0066ff;
    --c-bg: #f8fafc;
    --c-surface: #ffffff;
    --c-text-main: #1e293b;
    --c-text-sub: #64748b;
    --c-border: #e2e8f0;
    --c-success: #10b981;
    --c-danger: #ef4444;
    --c-warning: #f59e0b;
    
    /* Shared Window System Variables */
    --win-radius: 12px;
    --win-shadow: 0 10px 30px rgba(0,0,0,0.15);
    --win-border: 1px solid var(--c-border);
    --win-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dark-mode :root {
    --c-bg: #0f172a;
    --c-surface: #1e293b;
    --c-text-main: #f1f5f9;
    --c-text-sub: #94a3b8;
    --c-border: #334155;
}

.comp-modal-overlay {
    --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
    --radius: 8px;
}

/* Modal Layout */
.comp-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.5); z-index: 1000;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(2px);
}
.comp-modal-overlay.z-high { z-index: 1100; }

.comp-modal-container {
    width: 1200px; height: 85vh; max-width: 95%;
    background: var(--c-bg); border-radius: var(--win-radius);
    display: flex; overflow: hidden;
    box-shadow: var(--win-shadow);
    border: var(--win-border);
    position: relative;
    transition: var(--win-transition);
}
.comp-modal-container.sm { width: 600px; height: 70vh; flex-direction: column; background: var(--c-surface); }

/* Sidebar */
.comp-sidebar {
    width: 260px; background: var(--c-surface);
    border-right: 1px solid var(--c-border);
    display: flex; flex-direction: column;
}
.comp-sidebar-header {
    padding: 24px; border-bottom: 1px solid var(--c-border);
    display: flex; justify-content: space-between; align-items: center;
}
.comp-logo { font-size: 18px; font-weight: 700; color: var(--c-primary); display: flex; gap: 10px; align-items: center; }
.comp-nav-list { flex: 1; padding: 20px; overflow-y: auto; }
.comp-nav-group { margin-bottom: 24px; }
.comp-nav-label { font-size: 12px; color: var(--c-text-sub); font-weight: 600; text-transform: uppercase; display: block; margin-bottom: 8px; padding-left: 12px; }
.comp-nav-item {
    width: 100%; text-align: left; padding: 12px 16px;
    border: none; background: transparent; border-radius: var(--radius);
    color: var(--c-text-main); cursor: pointer; font-size: 14px;
    display: flex; align-items: center; gap: 10px;
    transition: all 0.2s;
}
.comp-nav-item:hover { background: #f1f5f9; }
.comp-nav-item.active { background: #eff6ff; color: var(--c-primary); font-weight: 600; }
.comp-sidebar-footer { padding: 20px; border-top: 1px solid var(--c-border); }
.comp-auth-status { font-size: 13px; color: var(--c-text-sub); display: flex; align-items: center; gap: 8px; }

/* Main Content */
.comp-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; position: relative; }
.comp-mobile-header { display: none; padding: 15px; background: var(--c-surface); border-bottom: 1px solid var(--c-border); align-items: center; gap: 15px; }
.comp-close-desktop { position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; color: var(--c-text-sub); cursor: pointer; z-index: 10; }

/* Views */
.comp-view { display: none; flex-direction: column; height: 100%; animation: fadeIn 0.2s ease; }
.comp-view.active { display: flex; }
.comp-view-header {
    padding: 24px 32px; background: var(--c-surface);
    border-bottom: 1px solid var(--c-border);
    display: flex; justify-content: space-between; align-items: center;
}
.comp-view-title h2 { margin: 0; font-size: 20px; color: var(--c-text-main); }
.comp-subtitle { font-size: 13px; color: var(--c-text-sub); margin-top: 4px; display: block; }
.comp-view-body { flex: 1; overflow-y: auto; padding: 32px; }

/* Components */
.comp-btn { padding: 8px 16px; border-radius: 6px; border: 1px solid transparent; cursor: pointer; font-size: 14px; transition: all 0.2s; }
.comp-btn-primary { background: var(--c-primary); color: #fff; }
.comp-btn-primary:hover { background: #0056d6; }
.comp-btn-outline { background: transparent; border-color: var(--c-border); color: var(--c-text-main); }
.comp-btn-outline:hover { border-color: var(--c-text-sub); }
.comp-select, .comp-input, .comp-textarea {
    padding: 8px 12px; border: 1px solid var(--c-border); border-radius: 6px;
    font-size: 14px; outline: none; transition: border-color 0.2s;
}
.comp-select:focus, .comp-input:focus, .comp-textarea:focus { border-color: var(--c-primary); }
.full-width { width: 100%; }

/* Cards & Layouts */
.comp-centered-card { max-width: 500px; margin: 0 auto; background: var(--c-surface); padding: 32px; border-radius: 12px; border: 1px solid var(--c-border); }
.comp-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.comp-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
.comp-form-group { margin-bottom: 20px; }
.comp-form-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; color: var(--c-text-main); }

/* Tables */
.comp-table-wrapper { background: var(--c-surface); border-radius: 8px; border: 1px solid var(--c-border); overflow: hidden; }
.comp-table { width: 100%; border-collapse: collapse; }
.comp-table th { background: #f8fafc; padding: 12px 16px; text-align: left; font-size: 13px; color: var(--c-text-sub); font-weight: 600; border-bottom: 1px solid var(--c-border); }
.comp-table td { padding: 12px 16px; border-bottom: 1px solid var(--c-border); color: var(--c-text-main); font-size: 14px; }
.comp-table tr:last-child td { border-bottom: none; }

/* Editor Overlay */
.comp-overlay-card {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    width: 800px; max-width: 90%; background: var(--c-surface);
    border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    border: 1px solid var(--c-border);
    display: flex; flex-direction: column; max-height: 90%;
}
.card-header, .card-footer { padding: 20px 24px; border-bottom: 1px solid var(--c-border); display: flex; justify-content: space-between; align-items: center; }
.card-footer { border-top: 1px solid var(--c-border); border-bottom: none; justify-content: flex-end; gap: 12px; }
.card-body { padding: 24px; overflow-y: auto; }

/* Fixed Footer */
.comp-fixed-footer {
    position: absolute; bottom: 0; left: 0; width: 100%;
    padding: 16px 32px; background: var(--c-surface);
    border-top: 1px solid var(--c-border);
    display: flex; justify-content: flex-end; align-items: center; gap: 12px;
    box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.05);
    z-index: 100;
}
.comp-fixed-footer.between { justify-content: space-between; }
.comp-view-body.has-footer { padding-bottom: 90px; }

.comp-code-result {
    background: #1e1e1e; color: #10b981; 
    padding: 12px; border-radius: 6px; 
    font-family: monospace; min-height: 48px;
    display: flex; align-items: center;
    border: 1px solid #333;
    white-space: pre-wrap; word-break: break-all;
}
.comp-code-result.empty { color: #666; font-style: italic; }

/* Utilities */
.text-success { color: var(--c-success); }
.text-danger { color: var(--c-danger); }
.comp-badge { padding: 2px 8px; border-radius: 4px; font-size: 12px; background: #eff6ff; color: var(--c-primary); }

/* Unified Scrollbar System */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 20px;
    border: 2px solid transparent;
    background-clip: content-box;
    transition: background 0.3s;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
    background-clip: content-box;
}
.dark-mode ::-webkit-scrollbar-thumb {
    background: #475569;
}
.dark-mode ::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Scroll Smoothing & Inertia */
.comp-view-body, .ai-output-area, .ai-lab-main, .history-list {
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
}

.comp-view-body { 
    min-height: 600px; 
    overflow-y: auto; 
}
.comp-nav-list { scroll-behavior: smooth; }

/* Mobile Responsive */
@media (max-width: 768px) {
    .comp-modal-container { width: 100%; height: 100%; border-radius: 0; flex-direction: column; }
    .comp-sidebar { position: absolute; height: 100%; z-index: 10; transform: translateX(-100%); transition: transform 0.3s; }
    .comp-sidebar.active { transform: translateX(0); box-shadow: 5px 0 15px rgba(0,0,0,0.1); }
    .comp-mobile-header { display: flex; }
    .comp-close-desktop { display: none; }
    .comp-view-header { padding: 16px; flex-direction: column; align-items: flex-start; gap: 12px; }
    .comp-toolbar { width: 100%; display: flex; gap: 8px; overflow-x: auto; }
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<script>
/**
 * Competition Center Logic
 * Namespace: CompApp
 */
const CompApp = {
    state: {
        bank: 'Python',
        user: localStorage.getItem('comp_username') || '',
        progress: {},
        quizData: [],
        manualSelection: new Set(),
        isAdmin: false
    },

    init: function() {
        // Initialize immediately
        this.checkAuth();
        this.syncBank('Python');
        // Load progress but don't block
        this.loadProgress();
        // Force load initial view
        this.switchTab('practice');
    },

    // --- Navigation ---
    switchTab: function(tabName) {
        if (tabName === 'admin' && !this.state.isAdmin) {
            // Check auth state again just in case it wasn't loaded
            if (this.state.isAdmin === undefined) {
                 // Retry after a short delay if auth is still loading
                 setTimeout(() => this.switchTab(tabName), 100);
                 return;
            }
            if (!this.state.isAdmin) {
                if(confirm('管理员功能需要登录，是否去登录？')) window.location.href = 'login.php';
                return;
            }
        }

        document.querySelectorAll('.comp-nav-item').forEach(el => el.classList.remove('active'));
        // Simple logic to highlight based on onclick attribute matching, simplified for this rewrite
        const buttons = document.querySelectorAll('.comp-nav-item');
        if(tabName === 'practice') buttons[0].classList.add('active');
        if(tabName === 'quiz') buttons[1].classList.add('active');
        if(tabName === 'admin') buttons[2].classList.add('active');

        document.querySelectorAll('.comp-view').forEach(el => el.classList.remove('active'));
        document.getElementById('view-' + tabName).classList.add('active');

        if (tabName === 'admin') this.loadAdminQuestions();
    },

    // --- Data Sync ---
    syncBank: function(bank) {
        this.state.bank = bank;
        // Sync all bank selects
        document.querySelectorAll('#practice-bank, #quiz-bank, #admin-bank, #edit-category').forEach(el => el.value = bank);
        
        // Reload current view context
        const activeTab = document.querySelector('.comp-view.active').id;
        if (activeTab === 'view-practice') this.loadPracticeQuestion();
        if (activeTab === 'view-admin') this.loadAdminQuestions();
    },

    checkAuth: function() {
        fetch('api/check_auth.php')
            .then(r => r.json())
            .then(data => {
                this.state.isAdmin = data.is_admin;
                const statusEl = document.getElementById('comp-auth-status');
                if (data.is_admin) {
                    statusEl.innerHTML = '<span class="text-success"><i class="fas fa-user-shield"></i> 管理员已登录</span>';
                } else {
                    statusEl.innerHTML = '<span><i class="fas fa-user"></i> 访客模式</span> <a href="login.php" style="margin-left:auto; color:var(--c-primary)">登录</a>';
                }
            });
    },

    loadProgress: function() {
        fetch(`api/get_progress.php?username=${this.state.user || 'guest'}`)
            .then(r => r.json())
            .then(data => {
                this.state.progress = data;
                this.updateStats();
            });
    },

    updateStats: function() {
        let correct = 0; // Simplified
        Object.values(this.state.progress).forEach(p => { if(p.is_correct) correct++; });
        const el = document.getElementById('stat-correct');
        if (el) el.innerText = correct;
    },

    // --- Practice Mode ---
    loadPracticeQuestion: function() {
        const container = document.getElementById('practice-container');
        const diff = document.getElementById('practice-diff').value;
        container.innerHTML = '<div style="text-align:center; padding:40px;"><i class="fas fa-spinner fa-spin"></i> 加载中...</div>';

        fetch(`api/get_question.php?mode=practice&category=${this.state.bank}&difficulty=${diff}`)
            .then(r => r.json())
            .then(q => {
                if(q.error) {
                    container.innerHTML = '<div style="text-align:center; color:#666;">暂无题目</div>';
                    return;
                }
                this.state.currentQ = q;
                
                // Render Question
                const isMarked = this.state.progress[q.id]?.is_marked;
                let html = `
                    <div class="comp-centered-card" style="max-width: 800px;">
                        <div class="comp-form-group" style="display:flex; justify-content:space-between;">
                            <div>
                                <span class="comp-badge">${q.category}</span>
                                <span class="comp-badge" style="background:#f1f5f9; color:#64748b">${q.difficulty || '一般'}</span>
                            </div>
                            <button class="comp-btn comp-btn-outline" onclick="CompApp.toggleMark(${q.id}, this)" style="border:none; font-size:18px; color:${isMarked?'#f59e0b':'#ccc'}" title="标记为错题/收藏">
                                <i class="${isMarked?'fas':'far'} fa-star"></i>
                            </button>
                        </div>
                        <h3 style="margin-bottom:24px; font-size:18px;">${q.question_text}</h3>
                        <div class="options-area">`;
                
                if(q.type === 'practical') {
                    // Practical Question UI
                    html += `
                        <div style="background:#1e1e1e; border-radius:6px; padding:20px; margin-bottom:15px; text-align:center;">
                            <div style="color:#d4d4d4; margin-bottom:15px;">
                                <i class="fas fa-code" style="font-size:48px; color:#3b82f6; margin-bottom:10px;"></i>
                                <p>本题需要编写代码求解</p>
                            </div>
                            <button class="comp-btn comp-btn-primary" onclick="openOnlineIDE()">
                                <i class="fas fa-external-link-alt"></i> 打开在线 IDE 编程
                            </button>
                            <p style="font-size:12px; color:#858585; margin-top:10px;">提示：代码运行结果将自动同步到下方。</p>
                        </div>
                        <div class="comp-form-group">
                            <label>代码运行结果 (自动同步)</label>
                            <div id="prac-answer-display" class="comp-code-result empty">等待运行...</div>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button class="comp-btn comp-btn-primary" onclick="CompApp.checkAnswer('practical', '${q.answer_key}')"><i class="fas fa-check"></i> 提交验证</button>
                            <button class="comp-btn comp-btn-outline" onclick="CompApp.askAI(${q.id})" id="ai-help-btn-${q.id}" title="获取AI解答与解析">
                                <i class="fas fa-robot"></i> AI解答
                            </button>
                        </div>
                    `;
                } else {
                    q.options.forEach((opt, idx) => {
                        html += `
                            <div class="comp-option-card" onclick="CompApp.checkAnswer('choice', ${idx}, ${q.correct_option}, this)" 
                                 style="padding:12px; border:1px solid var(--c-border); margin-bottom:12px; border-radius:6px; cursor:pointer;">
                                <b>${String.fromCharCode(65+idx)}.</b> ${opt}
                            </div>
                        `;
                    });
                     // Add AI Help Button for Choice Questions too
                    html += `
                        <div style="margin-top:15px; text-align:right;">
                             <button class="comp-btn comp-btn-outline" onclick="CompApp.askAI(${q.id})" id="ai-help-btn-${q.id}" title="获取AI解答与解析">
                                <i class="fas fa-robot"></i> AI解答
                            </button>
                        </div>
                    `;
                }
                
                html += `</div>
                        <div id="prac-feedback" style="margin-top:16px; display:none; padding: 15px; border-radius: 6px; background: #f8fafc; border: 1px solid var(--c-border);"></div>
                    </div>`;
                container.innerHTML = html;
            });
    },

    askAI: function(qId) {
        const btn = document.getElementById(`ai-help-btn-${qId}`);
        if(btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 思考中...';
        }

        const q = this.state.currentQ;
        // Prepare prompt with strict format request
        let prompt = `请直接给出以下题目的正确答案（选项或代码思路）和简短解析（不超过30字）。
输出格式严格如下：
题干: [题目内容]
选项: [选项列表，如果没有则不显示]
解析: [正确选项分析或代码思路](限30字内)

题目：${q.question_text}\n`;
        
        if(q.type === 'choice') {
            prompt += `选项：\n${q.options.map((o,i)=> String.fromCharCode(65+i)+'. '+o).join('\n')}`;
        }

        // Call AI Window
        if (typeof safeCall === 'function') {
             safeCall('openAiWindow', 'text');
        } else if (typeof openAiWindow === 'function') {
             openAiWindow('text');
        }
        
        // Wait for AI window and auto-fill
        setTimeout(() => {
            const aiInput = document.getElementById('ai-prompt'); // Correct ID based on user snippet
            if(aiInput) {
                aiInput.value = prompt;
                
                // Trigger send
                const sendBtn = document.getElementById('ai-btn'); // Correct ID based on user snippet
                if(sendBtn) sendBtn.click();
            }
            
            // Restore button state
            if(btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-robot"></i> AI解答';
            }
        }, 500);
    },

    // --- Answer Logic Upgrades ---
    checkAnswer: function(type, val1, val2, el) {
        let isCorrect = false;
        let score = 0;
        let userOutput = '';
        let feedbackHtml = '';
        
        if (type === 'practical') {
            const resultDiv = document.getElementById('prac-answer-display');
            userOutput = (resultDiv ? resultDiv.innerText : '').trim();
            const expected = (val1 || '').trim();
            
            // 1. Correctness (60%) - Fuzzy matching
            const correctness = this.fuzzyMatch(userOutput, expected);
            
            // 2. Integrity (30%) - Keyword checking
            const integrity = this.checkIntegrity(userOutput, this.state.currentQ.explanation);
            
            // 3. Normalization (10%) - Length and structure
            const normalization = userOutput.length > 0 ? 1 : 0;
            
            score = Math.round((correctness * 60) + (integrity * 30) + (normalization * 10));
            isCorrect = score >= 60;

            feedbackHtml = `
                <div class="score-container" style="margin-bottom:15px; padding:15px; background:var(--c-bg); border-radius:8px; border:1px solid var(--c-border);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span style="font-weight:600; color:var(--c-text-main);">综合评分：<span style="color:${score >= 60 ? 'var(--c-success)' : 'var(--c-danger)'}; font-size:24px;">${score}</span> 分</span>
                        <span class="comp-badge" style="background:${score >= 60 ? '#dcfce7' : '#fee2e2'}; color:${score >= 60 ? '#166534' : '#991b1b'}">${score >= 60 ? '及格' : '需改进'}</span>
                    </div>
                    <div style="font-size:12px; color:var(--c-text-sub); display:flex; gap:15px;">
                        <span>正确性: ${Math.round(correctness*100)}%</span>
                        <span>完整性: ${Math.round(integrity*100)}%</span>
                        <span>规范性: ${Math.round(normalization*100)}%</span>
                    </div>
                </div>
                <div class="diff-view-container" style="margin-top:15px;">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; font-size:13px;">
                        <div>
                            <div style="margin-bottom:4px; color:var(--c-text-sub);">您的输出</div>
                            <pre style="background:#fef2f2; border:1px solid #fee2e2; padding:10px; border-radius:4px; white-space:pre-wrap; min-height:60px;">${userOutput || '(空)'}</pre>
                        </div>
                        <div>
                            <div style="margin-bottom:4px; color:var(--c-text-sub);">预期输出</div>
                            <pre style="background:#f0fdf4; border:1px solid #dcfce7; padding:10px; border-radius:4px; white-space:pre-wrap; min-height:60px;">${expected}</pre>
                        </div>
                    </div>
                </div>
            `;
        } else {
            isCorrect = (val1 === val2);
            if (el) el.style.background = isCorrect ? '#dcfce7' : '#fee2e2';
            score = isCorrect ? 100 : 0;
            feedbackHtml = isCorrect 
                ? '<div class="text-success" style="font-weight:bold; margin-bottom:8px;"><i class="fas fa-check"></i> 回答正确</div>'
                : '<div class="text-danger" style="font-weight:bold; margin-bottom:8px;"><i class="fas fa-times"></i> 回答错误</div>';
        }

        const fb = document.getElementById('prac-feedback');
        fb.style.display = 'block';
        
        // Add Explanation
        if (this.state.currentQ.explanation) {
            feedbackHtml += `
                <div style="margin-top:10px; padding-top:10px; border-top:1px solid var(--c-border);">
                    <strong style="color:var(--c-text-main);">解析：</strong>
                    <p style="margin:5px 0 0 0; color:var(--c-text-sub); font-size:14px; line-height:1.5;">${this.state.currentQ.explanation}</p>
                </div>
            `;
        }
        
        fb.innerHTML = feedbackHtml;

        // Save Progress
        fetch('api/save_progress.php', {
            method: 'POST',
            body: JSON.stringify({
                username: this.state.user || 'guest',
                progress: [{ question_id: this.state.currentQ.id, is_correct: isCorrect?1:0, is_marked: isCorrect?0:1, score: score }]
            })
        });
        this.loadProgress();
    },

    fuzzyMatch: function(s1, s2) {
        if (!s1 || !s2) return 0;
        if (s1 === s2) return 1;
        
        // Simple Levenshtein distance based similarity
        const track = Array(s2.length + 1).fill(null).map(() => Array(s1.length + 1).fill(null));
        for (let i = 0; i <= s1.length; i += 1) track[0][i] = i;
        for (let j = 0; j <= s2.length; j += 1) track[j][0] = j;
        for (let j = 1; j <= s2.length; j += 1) {
            for (let i = 1; i <= s1.length; i += 1) {
                const indicator = s1[i - 1] === s2[j - 1] ? 0 : 1;
                track[j][i] = Math.min(
                    track[j][i - 1] + 1,
                    track[j - 1][i] + 1,
                    track[j - 1][i - 1] + indicator
                );
            }
        }
        const distance = track[s2.length][s1.length];
        return 1 - (distance / Math.max(s1.length, s2.length));
    },

    checkIntegrity: function(content, explanation) {
        if (!content) return 0;
        const keywords = ['print', 'def', 'import', 'return', 'class', 'for', 'if', 'while'];
        let found = 0;
        keywords.forEach(k => { if(content.includes(k)) found++; });
        return Math.min(1, found / 2);
    },

    // --- Printing ---
    printResults: function() {
        const q = this.state.currentQ;
        const resultDiv = document.getElementById('prac-answer-display');
        const userOutput = resultDiv ? resultDiv.innerText : '未运行';
        const expected = q.answer_key || '暂无标准答案';
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head>
                <title>实操题作答报告 - ${q.id}</title>
                <style>
                    body { font-family: -apple-system, sans-serif; padding: 40px; color: #333; }
                    .print-header { text-align: center; border-bottom: 2px solid #0066ff; padding-bottom: 20px; margin-bottom: 30px; }
                    .print-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px; }
                    .print-col { border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: #fcfcfc; }
                    .print-col h4 { margin: 0 0 10px 0; color: #0066ff; border-bottom: 1px solid #eee; padding-bottom: 5px; }
                    pre { white-space: pre-wrap; font-family: monospace; font-size: 13px; background: #f5f5f5; padding: 10px; border-radius: 4px; }
                    .print-footer { text-align: center; font-size: 12px; color: #999; margin-top: 50px; }
                    @media print { .no-print { display: none; } }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h2>实操题作答报告</h2>
                    <p>题目 ID: ${q.id} | 分类: ${q.category} | 时间: ${new Date().toLocaleString()}</p>
                </div>
                <div class="print-grid">
                    <div class="print-col">
                        <h4>题目内容</h4>
                        <div style="font-size:14px; line-height:1.6;">${q.question_text}</div>
                    </div>
                    <div class="print-col">
                        <h4>您的作答结果</h4>
                        <pre>${userOutput}</pre>
                    </div>
                    <div class="print-col">
                        <h4>标准参考答案</h4>
                        <pre>${expected}</pre>
                    </div>
                </div>
                <div style="margin-bottom:30px; padding:20px; border:1px solid #eee; border-radius:8px;">
                    <h4>解析与说明</h4>
                    <div style="font-size:14px; line-height:1.6; color:#666;">${q.explanation || '暂无解析'}</div>
                </div>
                <div class="no-print" style="text-align:center;">
                    <button onclick="window.print()" style="padding:10px 20px; background:#0066ff; color:#fff; border:none; border-radius:4px; cursor:pointer;">确认打印</button>
                </div>
                <div class="print-footer">
                    生成于 创享网络信息协会 官方学习平台
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
    },

    toggleMark: function(id, btn) {
        const icon = btn.querySelector('i');
        const isMarked = !icon.classList.contains('fas'); // Toggle logic
        
        icon.className = isMarked ? 'fas fa-star' : 'far fa-star';
        btn.style.color = isMarked ? '#f59e0b' : '#ccc';
        
        // Save mark state
        fetch('api/save_progress.php', {
            method: 'POST',
            body: JSON.stringify({
                username: this.state.user || 'guest',
                progress: [{ question_id: id, is_marked: isMarked?1:0 }]
            })
        });
        
        // Update local state
        if(!this.state.progress[id]) this.state.progress[id] = {};
        this.state.progress[id].is_marked = isMarked;
    },

    // --- Quiz Mode ---
    toggleQuizMode: function() {
        const mode = document.querySelector('input[name="quiz_mode"]:checked').value;
        document.getElementById('quiz-options-auto').style.display = mode === 'auto' ? 'block' : 'none';
        document.getElementById('quiz-options-manual').style.display = mode === 'manual' ? 'block' : 'none';
    },

    openSelector: function() {
        document.getElementById('comp-selector-modal').style.display = 'flex';
        // Fetch list
        fetch('api/get_question_list.php?category=' + this.state.bank)
            .then(r => r.json())
            .then(data => {
                const tbody = document.getElementById('selector-body');
                tbody.innerHTML = '';
                data.forEach(q => {
                    tbody.innerHTML += `
                        <tr>
                            <td><input type="checkbox" value="${q.id}" onchange="CompApp.updateSelection(this)"></td>
                            <td>${q.question_text.substring(0,40)}...</td>
                            <td>${q.difficulty||'-'}</td>
                        </tr>
                    `;
                });
            });
    },

    updateSelection: function(cb) {
        if(cb.checked) this.state.manualSelection.add(cb.value);
        else this.state.manualSelection.delete(cb.value);
        document.getElementById('selector-total').innerText = this.state.manualSelection.size;
        document.getElementById('manual-select-count').innerText = this.state.manualSelection.size;
    },

    closeSelector: function() {
        document.getElementById('comp-selector-modal').style.display = 'none';
    },

    startQuiz: function() {
        const mode = document.querySelector('input[name="quiz_mode"]:checked').value;
        let url = `api/get_quiz.php?category=${this.state.bank}`;
        
        if (mode === 'manual') {
            if(this.state.manualSelection.size === 0) return alert('请先选择题目');
            url += `&ids=${Array.from(this.state.manualSelection).join(',')}`;
        } else {
            const count = document.getElementById('quiz-count').value;
            const diff = document.getElementById('quiz-diff').value;
            url += `&count=${count}&difficulty=${diff}`;
        }

        fetch(url).then(r => r.json()).then(data => {
            if(data.error || data.length === 0) return alert('生成试卷失败');
            this.state.quizData = data;
            this.renderQuizPaper();
        });
    },

    renderQuizPaper: function() {
        document.getElementById('quiz-setup-screen').style.display = 'none';
        document.getElementById('quiz-taking-screen').style.display = 'block';
        document.getElementById('quiz-timer').style.display = 'block';
        document.getElementById('quiz-footer').style.display = 'flex';
        document.getElementById('quiz-progress').style.display = 'inline-block';
        
        this.state.quizIdx = 0;
        this.state.quizAnswers = {};
        document.getElementById('quiz-total-count').innerText = this.state.quizData.length;

        this.showQuizQuestion(0);

        // Start Timer (Simple 10 min)
        this.quizStartTime = Date.now();
        if(this.timerInt) clearInterval(this.timerInt);
        this.timerInt = setInterval(() => {
            const left = 600 - Math.floor((Date.now() - this.quizStartTime)/1000);
            if(left <= 0) this.submitQuiz();
            const m = Math.floor(left/60).toString().padStart(2,'0');
            const s = (left%60).toString().padStart(2,'0');
            document.getElementById('quiz-time-display').innerText = `${m}:${s}`;
        }, 1000);
    },

    showQuizQuestion: function(idx) {
        if(idx < 0 || idx >= this.state.quizData.length) return;
        
        this.state.quizIdx = idx;
        const q = this.state.quizData[idx];
        const list = document.getElementById('quiz-questions-list');
        
        // Update Progress
        document.getElementById('quiz-current-idx').innerText = idx + 1;
        
        // Update Buttons
        document.getElementById('quiz-prev-btn').style.visibility = idx === 0 ? 'hidden' : 'visible';
        
        // Next vs Submit
        if(idx === this.state.quizData.length - 1) {
            document.getElementById('quiz-next-btn').style.display = 'none';
            document.getElementById('quiz-submit-btn').style.display = 'inline-block';
        } else {
            document.getElementById('quiz-next-btn').style.display = 'inline-block';
            document.getElementById('quiz-submit-btn').style.display = 'none';
        }

        // Render Question
        let html = `<div class="comp-centered-card" style="margin-bottom:20px;">
            <h4>${idx+1}. ${q.question_text}</h4>
            <div class="quiz-opts">`;
        
        const savedAns = this.state.quizAnswers[q.id];

        if (q.type === 'practical') {
            html += `
                <div style="background:#1e1e1e; border-radius:6px; padding:15px; margin-bottom:15px;">
                     <button class="comp-btn comp-btn-sm comp-btn-primary" onclick="openOnlineIDE()">
                        <i class="fas fa-code"></i> 打开 IDE
                    </button>
                    <span style="color:#888; font-size:12px; margin-left:10px;">运行代码后结果将自动保存</span>
                </div>
                <div class="comp-form-group">
                    <label>答案 (自动同步)</label>
                    <div id="quiz-ans-${q.id}" class="comp-code-result ${savedAns ? '' : 'empty'}">${savedAns || '等待运行...'}</div>
                </div>
                <!-- Diff View Container -->
                <div id="quiz-diff-${q.id}" style="display:none; margin-top:10px;"></div>
            `;
        } else {
            q.options.forEach((opt, oIdx) => {
                const checked = savedAns === opt.id ? 'checked' : ''; 
                html += `<label style="display:block; padding:8px; cursor:pointer;">
                            <input type="radio" name="q_${q.id}" value="${opt.id}" ${checked} 
                            onchange="CompApp.saveQuizAnswer(${q.id}, '${opt.id}')"> 
                            ${opt.text}
                        </label>`;
            });
        }
        html += `</div></div>`;
        list.innerHTML = html;
    },

    saveQuizAnswer: function(qId, val) {
        this.state.quizAnswers[qId] = val;
        // Update display if it exists
        const display = document.getElementById('quiz-ans-' + qId);
        if(display) {
            display.innerText = val;
            display.classList.remove('empty');
        }
    },

    prevQuizQuestion: function() {
        this.showQuizQuestion(this.state.quizIdx - 1);
    },

    nextQuizQuestion: function() {
        this.showQuizQuestion(this.state.quizIdx + 1);
    },

    submitQuizWithConfirm: function() {
        const total = this.state.quizData.length;
        // Filter out empty answers
        let valid = 0;
        for(let k in this.state.quizAnswers) {
            if(this.state.quizAnswers[k] && this.state.quizAnswers[k].toString().trim() !== '') valid++;
        }

        if(valid < total) {
            if(!confirm(`您还有 ${total - valid} 道题未完成，确定要交卷吗？`)) return;
        } else {
            if(!confirm('确认提交试卷？')) return;
        }
        this.submitQuiz();
    },

    submitQuiz: function() {
        clearInterval(this.timerInt);
        
        fetch('api/submit_quiz.php', {
            method: 'POST',
            body: JSON.stringify({
                username: document.getElementById('quiz-username').value || 'Guest',
                answers: this.state.quizAnswers,
                time_taken: Math.floor((Date.now() - this.quizStartTime)/1000)
            })
        }).then(r => r.json()).then(res => {
            document.getElementById('quiz-taking-screen').style.display = 'none';
            document.getElementById('quiz-timer').style.display = 'none';
            document.getElementById('quiz-footer').style.display = 'none';
            document.getElementById('quiz-progress').style.display = 'none';

            document.getElementById('quiz-result-screen').style.display = 'block';
            document.getElementById('res-score').innerText = `${res.score} / ${res.total}`;
            document.getElementById('res-time').innerText = Math.floor(res.time_taken/60) + '分' + (res.time_taken%60) + '秒';
            
            // Show Wrong Answers
            const wrongContainer = document.getElementById('quiz-wrong-answers');
            wrongContainer.innerHTML = '<h4 style="margin:20px 0 10px; text-align:left;">错题回顾</h4>';
            
            if (res.review && res.review.length > 0) {
                res.review.forEach(item => {
                    wrongContainer.innerHTML += `
                        <div class="comp-centered-card" style="margin-bottom:15px; text-align:left; border-left:4px solid #ef4444;">
                            <div style="font-weight:bold; margin-bottom:8px;">${item.question_text}</div>
                            <div style="font-size:14px; color:#64748b; margin-bottom:5px;">您的答案: <span class="text-danger">${item.user_answer}</span></div>
                            <div style="font-size:14px; color:#10b981; margin-bottom:10px;">正确答案: ${item.correct_answer}</div>
                            ${item.type === 'practical' ? `
                                <div style="margin-top:5px; background:#fff; border:1px solid #fee2e2; border-radius:4px; padding:8px; font-size:12px;">
                                    <strong>差异对比：</strong>
                                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:4px;">
                                        <div style="background:#f8f8f8; padding:5px;"><div style="color:#999;border-bottom:1px solid #eee;margin-bottom:2px;">您的输出</div><pre style="white-space:pre-wrap;margin:0;color:#ef4444;">${item.user_answer}</pre></div>
                                        <div style="background:#f0fdf4; padding:5px;"><div style="color:#999;border-bottom:1px solid #eee;margin-bottom:2px;">标准输出</div><pre style="white-space:pre-wrap;margin:0;color:#10b981;">${item.correct_answer}</pre></div>
                                    </div>
                                </div>
                            ` : ''}
                            <div style="background:#f8fafc; padding:10px; font-size:13px; color:#475569; margin-top:8px;">
                                <strong>解析:</strong> ${item.explanation || '暂无解析'}
                            </div>
                        </div>
                    `;
                });
            } else if (res.score === res.total) {
                wrongContainer.innerHTML += '<div style="color:#10b981; margin-bottom:20px;">🎉 全对！太棒了！</div>';
            } else {
                wrongContainer.innerHTML += '<div style="color:#64748b;">暂无法显示错题详情。</div>';
            }
        });
    },
    
    resetQuiz: function() {
        document.getElementById('quiz-result-screen').style.display = 'none';
        document.getElementById('quiz-setup-screen').style.display = 'block';
    },

    // --- Admin Mode ---
    loadAdminQuestions: function() {
        const tbody = document.getElementById('admin-table-body');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center">加载中...</td></tr>';
        fetch('api/admin/get_questions.php').then(r => r.json()).then(data => {
            tbody.innerHTML = '';
            data.forEach(q => {
                tbody.innerHTML += `
                    <tr>
                        <td>${q.id}</td>
                        <td>${q.type==='practical'?'实操':'选择'}</td>
                        <td>${q.difficulty}</td>
                        <td>${q.question_text.substring(0,30)}</td>
                        <td>${q.tags||'-'}</td>
                        <td>
                            <button class="comp-btn comp-btn-outline" onclick='CompApp.openEditor(${JSON.stringify(q)})'><i class="fas fa-edit"></i></button>
                            <button class="comp-btn comp-btn-outline text-danger" onclick="CompApp.deleteQ(${q.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        });
    },

    openEditor: function(q = null) {
        document.getElementById('admin-editor-view').style.display = 'flex';
        if (q) {
            document.getElementById('editor-title').innerText = '编辑题目';
            document.getElementById('edit-id').value = q.id;
            document.getElementById('edit-text').value = q.question_text;
            document.getElementById('edit-category').value = q.category;
            document.getElementById('edit-type').value = q.type || 'choice';
            document.getElementById('edit-diff').value = q.difficulty || 'easy';
            document.getElementById('edit-tags').value = q.tags || '';
            // Populate options/answer logic omitted for brevity, but needed
            if (q.type === 'choice') {
                if(q.options) {
                    q.options.forEach((o,i) => document.getElementById('edit-opt'+i).value = o);
                    document.getElementById('edit-correct').value = q.correct_option;
                }
            } else {
                document.getElementById('edit-answer').value = q.answer_key;
            }
        } else {
            document.getElementById('editor-title').innerText = '新增题目';
            document.getElementById('edit-id').value = '';
            document.getElementById('edit-text').value = '';
        }
        this.toggleEditorType();
    },

    closeEditor: function() {
        document.getElementById('admin-editor-view').style.display = 'none';
    },

    toggleEditorType: function() {
        const type = document.getElementById('edit-type').value;
        document.getElementById('editor-choice-area').style.display = type === 'choice' ? 'block' : 'none';
        document.getElementById('editor-practical-area').style.display = type === 'practical' ? 'block' : 'none';
    },

    saveQuestion: function() {
        const type = document.getElementById('edit-type').value;
        const data = {
            id: document.getElementById('edit-id').value,
            category: document.getElementById('edit-category').value,
            type: type,
            difficulty: document.getElementById('edit-diff').value,
            tags: document.getElementById('edit-tags').value,
            question_text: document.getElementById('edit-text').value
        };
        
        if (type === 'choice') {
            data.options = [0,1,2,3].map(i => document.getElementById('edit-opt'+i).value);
            data.correct_option = document.getElementById('edit-correct').value;
        } else {
            data.answer_key = document.getElementById('edit-answer').value;
            data.options = [];
        }

        fetch('api/admin/save_question.php', { method: 'POST', body: JSON.stringify(data) })
            .then(r => r.json()).then(res => {
                if(res.success) { this.closeEditor(); this.loadAdminQuestions(); }
                else alert('保存失败');
            });
    },

    deleteQ: function(id) {
        if(confirm('确定删除?')) {
            fetch(`api/admin/delete_question.php?id=${id}`).then(()=>this.loadAdminQuestions());
        }
    }
};

// Global Exposure for Onclick Events
window.CompApp = CompApp;

function openCompetitionMenu() {
    document.getElementById('competition-menu-modal').style.display = 'flex';
    CompApp.init();
    CompApp.switchTab('practice');
}

function closeCompetitionMenu() {
    document.getElementById('competition-menu-modal').style.display = 'none';
}

function toggleCompSidebar() {
    document.querySelector('.comp-sidebar').classList.toggle('active');
}
</script>
