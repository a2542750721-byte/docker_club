<?php
/**
 * Typing Practice Modal Component
 * 
 * Description: Frontend component for the Typing Practice feature.
 * Included by: footer.php
 * Author: Trae AI Assistant
 * Date: 2026-01-04
 */
?>

<!-- Modal Backdrop -->
<div id="typing-menu-modal" class="comp-modal-overlay" style="display: none;">
    <!-- Modal Container -->
    <div class="comp-modal-container typing-modal-container">
        
        <!-- Main Content Area -->
        <main class="comp-main">
            <!-- Header -->
            <header class="comp-view-header">
                <div class="comp-view-title">
                    <h2 style="display:flex; align-items:center; gap:10px;">
                        <i class="fas fa-keyboard" style="color:var(--c-primary)"></i> 
                        打字练习
                    </h2>
                    <span class="comp-subtitle">沉浸式跟打 · 实时反馈</span>
                </div>
                <div class="comp-toolbar">
                    <div class="typing-controls" id="typing-controls-panel">
                        <select id="typing-mode" class="comp-select" onchange="TypingApp.changeMode()">
                            <option value="english">英文练习</option>
                            <option value="chinese">中文练习</option>
                        </select>
                        <select id="typing-time-limit" class="comp-select" onchange="TypingApp.restart()">
                            <option value="60">1 分钟</option>
                            <option value="180">3 分钟</option>
                            <option value="300">5 分钟</option>
                            <option value="600" selected>10 分钟</option>
                        </select>
                    </div>

                    <div class="typing-stats-row">
                        <div class="typing-stat-item">
                            <span class="label">速度 (WPM)</span>
                            <span class="value" id="typing-wpm">0</span>
                        </div>
                        <div class="typing-stat-item">
                            <span class="label">准确率</span>
                            <span class="value" id="typing-acc">100%</span>
                        </div>
                        <div class="typing-stat-item">
                            <span class="label">剩余时间</span>
                            <span class="value" id="typing-time">10:00</span>
                        </div>
                    </div>
                    <button class="comp-btn comp-btn-outline" onclick="TypingApp.restart()">
                        <i class="fas fa-redo"></i> 重来
                    </button>
                    <button class="comp-close-desktop" onclick="closeTypingMenu()" style="position:static; margin-left:10px;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </header>

            <div class="comp-view-body" style="display:flex; flex-direction:column; gap:20px; height: 100%; position: relative;">
                
                <!-- Typing Board (Active State) -->
                <div id="typing-board" class="typing-board" onclick="document.getElementById('typing-input').focus()">
                    
                    <!-- Layer 1: Background Text (The guide) -->
                    <div id="typing-display" class="typing-layer text-layer"></div>

                    <!-- Layer 2: Hidden Input (Captures keystrokes) -->
                    <textarea id="typing-input" class="typing-layer input-layer" 
                        spellcheck="false" autocomplete="off" autocapitalize="off"></textarea>
                        
                    <!-- Visual Cursor -->
                    <div id="typing-cursor" class="custom-cursor"></div>
                    
                    <!-- Overlay Message (Start/End) -->
                    <div id="typing-overlay-msg" class="typing-overlay-msg">
                        <i class="fas fa-mouse-pointer"></i> 点击此处开始练习
                    </div>
                </div>

                <!-- Result & Submission Panel (Hidden by default) -->
                <div id="typing-result-panel" class="typing-result-panel" style="display: none;">
                    <div class="result-header">
                        <h3><i class="fas fa-flag-checkered"></i> 练习完成</h3>
                        <p class="result-summary">WPM: <span id="res-wpm">0</span> | 准确率: <span id="res-acc">0%</span></p>
                    </div>

                    <div class="submission-form">
                        <div class="form-group">
                            <label>您的姓名 (必填)</label>
                            <input type="text" id="sub-name" class="comp-input" maxlength="20" placeholder="请输入您的姓名 (1-20字)">
                        </div>
                        <div class="form-group">
                            <label>本次练习满意度</label>
                            <div class="rating-selector" id="sub-rating">
                                <i class="fas fa-star" data-val="1"></i>
                                <i class="fas fa-star" data-val="2"></i>
                                <i class="fas fa-star" data-val="3"></i>
                                <i class="fas fa-star" data-val="4"></i>
                                <i class="fas fa-star active" data-val="5"></i>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button class="comp-btn comp-btn-primary" id="btn-submit-score" onclick="TypingApp.submitScore()">提交成绩</button>
                            <button class="comp-btn comp-btn-outline" onclick="TypingApp.restart()">再练一次</button>
                        </div>
                    </div>

                    <!-- Leaderboard -->
                    <div class="leaderboard-section">
                        <div class="lb-header">
                            <h4><i class="fas fa-trophy"></i> 排行榜 (Top 100)</h4>
                            <div class="lb-search">
                                <input type="text" id="lb-search-input" placeholder="搜索姓名..." onkeyup="TypingApp.loadLeaderboard()">
                                <button class="comp-btn-sm" onclick="TypingApp.loadLeaderboard()"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="lb-table-wrapper">
                            <table class="comp-table">
                                <thead>
                                    <tr>
                                        <th>排名</th>
                                        <th>姓名</th>
                                        <th>WPM</th>
                                        <th>准确率</th>
                                        <th>模式</th>
                                        <th>日期</th>
                                    </tr>
                                </thead>
                                <tbody id="lb-body">
                                    <tr><td colspan="6" class="text-center">加载中...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<style>
.typing-modal-container {
    width: 1200px;
    height: 800px;
    max-width: 95vw;
    max-height: 95vh;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
}

.comp-main {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.comp-view-body {
    flex: 1;
    overflow: hidden;
}

/* Typing Board Styles (Same as before) */
.typing-board {
    flex: 1;
    position: relative;
    background: var(--c-bg);
    border: 2px solid var(--c-border);
    border-radius: 12px;
    padding: 30px;
    overflow: hidden;
    cursor: text;
    transition: all 0.3s ease;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.02);
}

.typing-board:focus-within {
    border-color: var(--c-primary);
    box-shadow: 0 0 0 4px rgba(var(--c-primary-rgb), 0.1), inset 0 0 20px rgba(0,0,0,0.02);
}

.typing-layer {
    font-family: 'JetBrains Mono', 'Consolas', 'Fira Code', monospace;
    font-size: 24px;
    line-height: 1.6;
    letter-spacing: 0.5px;
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    resize: none;
    background: transparent;
    padding: 0;
    margin: 0;
    white-space: pre-wrap;
    word-break: break-all;
}

.text-layer {
    position: relative;
    color: var(--c-text-sub);
    z-index: 1;
    overflow-y: auto;
    scrollbar-width: thin;
}

.input-layer {
    position: absolute;
    top: 30px; left: 30px;
    width: calc(100% - 60px);
    height: calc(100% - 60px);
    z-index: 2;
    opacity: 0;
    color: transparent;
    caret-color: transparent;
}

.t-char { position: relative; transition: color 0.1s; }
.t-char.pending { opacity: 0.5; }
.t-char.correct { color: var(--c-text-main); opacity: 1; }
.t-char.incorrect { color: #ff4757; text-decoration: underline; opacity: 1; }
.t-char.current::after {
    content: ''; position: absolute; left: -1px; bottom: 0;
    width: 2px; height: 1.2em; background-color: var(--c-primary);
    animation: blink 1s infinite;
}
.t-char.current.space::after { left: 0; }
@keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

.typing-overlay-msg {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    background: rgba(var(--c-bg-rgb), 0.8); backdrop-filter: blur(5px);
    padding: 15px 30px; border-radius: 30px; font-size: 16px; color: var(--c-text-sub);
    pointer-events: none; z-index: 10; display: flex; align-items: center; gap: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: opacity 0.3s;
}
.typing-board:focus-within .typing-overlay-msg { opacity: 0; }

/* Result Panel */
.typing-result-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: var(--c-bg);
    border-radius: 12px;
    padding: 30px;
    overflow-y: auto;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.result-header { text-align: center; margin-bottom: 30px; }
.result-header h3 { font-size: 28px; color: var(--c-primary); margin-bottom: 10px; }
.result-summary { font-size: 18px; color: var(--c-text-main); font-weight: bold; }
.result-summary span { color: var(--c-primary); font-family: monospace; font-size: 24px; }

.submission-form {
    background: var(--c-bg-sub);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
    border: 1px solid var(--c-border);
}

.form-group { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 14px; color: var(--c-text-sub); }
.comp-input { padding: 10px; border: 1px solid var(--c-border); border-radius: 6px; background: var(--c-bg); color: var(--c-text-main); }

.rating-selector { display: flex; gap: 5px; font-size: 20px; color: #ddd; cursor: pointer; }
.rating-selector i.active, .rating-selector i:hover, .rating-selector i:hover ~ i { color: #ffc107; } /* Hover logic handled by JS for better UX usually, simple CSS here */
.rating-selector i.active { color: #ffc107; }

.leaderboard-section { flex: 1; display: flex; flex-direction: column; min-height: 300px; }
.lb-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.lb-search { display: flex; gap: 10px; }
.lb-table-wrapper { flex: 1; overflow-y: auto; border: 1px solid var(--c-border); border-radius: 8px; }
.lb-table-wrapper table { width: 100%; border-collapse: collapse; }
.lb-table-wrapper th { position: sticky; top: 0; background: var(--c-bg-sub); z-index: 1; }

/* Dark Mode Support (Assuming global vars work, but adding specific overrides if needed) */
</style>

<script>
const TypingApp = {
    state: {
        text: "",
        startTime: null,
        timer: null,
        timeLimit: 600,
        timeLeft: 600,
        isFinished: false,
        mistakes: 0,
        history: [],
        timestampLog: [], // Anti-cheat: log keydown timestamps
        currentRating: 5
    },

    texts: {
        english: [
            `Technology is best when it brings people together. It is not just about information tools; it is about facilitating human connection. In the digital age, we have the power to reach across the globe in milliseconds, sharing ideas, cultures, and dreams. Yet, true connection requires more than just connectivity; it requires empathy and understanding.`,
            `The only way to do great work is to love what you do. If you haven't found it yet, keep looking. Don't settle. As with all matters of the heart, you'll know when you find it. And, like any great relationship, it just gets better and better as the years roll on. So keep looking until you find it. Don't settle.`,
            `In computer science, a data structure is a data organization, management, and storage format that enables efficient access and modification. More precisely, a data structure is a collection of data values, the relationships among them, and the functions or operations that can be applied to the data.`,
            `Artificial intelligence (AI) is intelligence demonstrated by machines, as opposed to the natural intelligence displayed by humans or animals. Leading AI textbooks define the field as the study of "intelligent agents": any system that perceives its environment and takes actions that maximize its chance of achieving its goals.`,
            `Clean code is code that is easy to understand and easy to change. Bad code attempts to do too much, it has muddled intent and ambiguity of purpose. Clean code is focused. Each function, each class, each module exposes a single-minded attitude that remains entirely undistracted, and unpolluted by surrounding details.`
        ],
        chinese: [
            `编程不仅是一门技术，更是一门艺术。优秀的代码就像一首优美的诗，结构严谨，逻辑清晰，读起来朗朗上口。程序员在编写代码的过程中，不仅是在与计算机对话，更是在与未来的自己和其他开发者对话。保持代码的整洁和可维护性，是对自己工作的尊重，也是对他人的负责。`,
            `人工智能正在深刻地改变着我们的生活方式。从智能家居到自动驾驶，从医疗诊断到金融分析，AI的应用场景无处不在。然而，技术的发展也带来了新的挑战，如何平衡效率与公平，如何保障数据安全与隐私，是我们必须面对的重要课题。`,
            `海纳百川，有容乃大；壁立千仞，无欲则刚。这句古训告诉我们要有宽广的胸怀和坚定的意志。在学习技术的道路上，我们也要保持开放的心态，勇于接受新知识，敢于挑战新难题。只有不断积累，才能厚积薄发，成就一番事业。`,
            `敏捷开发是一种以人为核心、迭代、循序渐进的开发方法。在敏捷开发中，软件项目的构建被切分成多个子项目，各个子项目的成果都经过测试，具备集成和可运行的特征。简言之，就是把一个大项目分为多个相互联系，但也可独立运行的小项目，并分别完成，在此过程中软件一直处于可使用状态。`,
            `生活就像一盒巧克力，你永远不知道下一颗是什么味道。面对未知的未来，我们既要有仰望星空的梦想，也要有脚踏实地的行动。每一行代码，每一个算法，都是通向梦想的阶梯。不要畏惧困难，因为每一次调试bug的过程，都是成长的机会。`
        ]
    },

    init: function() {
        this.changeMode();
        
        const input = document.getElementById('typing-input');
        input.addEventListener('input', (e) => this.handleInput(e));
        input.addEventListener('keydown', (e) => this.handleKeydown(e));
        
        input.addEventListener('compositionstart', () => { this.state.isComposing = true; });
        input.addEventListener('compositionend', (e) => { 
            this.state.isComposing = false;
            this.handleInput(e);
        });
        
        // Anti-Cheat: Disable paste and context menu
        input.addEventListener('paste', (e) => { e.preventDefault(); alert('禁止粘贴！'); });
        input.addEventListener('contextmenu', (e) => { e.preventDefault(); });
        
        // Rating Click
        document.querySelectorAll('.rating-selector i').forEach(star => {
            star.addEventListener('click', function() {
                const val = this.getAttribute('data-val');
                TypingApp.state.currentRating = val;
                document.querySelectorAll('.rating-selector i').forEach(s => {
                    s.classList.toggle('active', s.getAttribute('data-val') <= val);
                });
            });
        });
    },

    changeMode: function() {
        this.restart();
    },

    restart: function() {
        // UI Reset
        document.getElementById('typing-board').style.display = 'block';
        document.getElementById('typing-result-panel').style.display = 'none';
        document.getElementById('typing-controls-panel').style.visibility = 'visible';

        const mode = document.getElementById('typing-mode').value;
        const timeSel = document.getElementById('typing-time-limit');
        this.state.timeLimit = parseInt(timeSel.value);
        this.state.timeLeft = this.state.timeLimit;
        
        const textArray = this.texts[mode];
        this.state.text = textArray[Math.floor(Math.random() * textArray.length)];
        
        this.state.startTime = null;
        this.state.isFinished = false;
        this.state.mistakes = 0;
        this.state.isComposing = false;
        this.state.timestampLog = [];
        
        if (this.state.timer) clearInterval(this.state.timer);
        this.state.timer = null;

        const input = document.getElementById('typing-input');
        input.value = '';
        input.disabled = false;
        input.focus();

        document.getElementById('typing-wpm').innerText = '0';
        document.getElementById('typing-acc').innerText = '100%';
        this.updateTimeDisplay();
        
        this.renderText();
    },

    updateTimeDisplay: function() {
        const minutes = Math.floor(this.state.timeLeft / 60);
        const seconds = this.state.timeLeft % 60;
        document.getElementById('typing-time').innerText = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    },

    renderText: function() {
        const display = document.getElementById('typing-display');
        display.innerHTML = '';
        this.state.text.split('').forEach((char, index) => {
            const span = document.createElement('span');
            span.innerText = char;
            span.className = index === 0 ? 't-char pending current' : 't-char pending';
            if (char === ' ') span.classList.add('space');
            span.dataset.index = index;
            display.appendChild(span);
        });
    },
    
    handleKeydown: function(e) {
        if (!this.state.isFinished) {
            this.state.timestampLog.push(Date.now());
        }
    },

    handleInput: function(e) {
        if (this.state.isFinished || this.state.isComposing) return;

        if (!this.state.startTime && e.target.value.length > 0) {
            this.state.startTime = Date.now();
            this.startTimer();
        }

        const inputVal = e.target.value;
        const currentLength = inputVal.length;
        const targetText = this.state.text;

        const displayChars = document.getElementById('typing-display').children;
        let mistakes = 0;

        for (let i = 0; i < displayChars.length; i++) {
            const charSpan = displayChars[i];
            charSpan.classList.remove('current');
            
            if (i < currentLength) {
                charSpan.classList.remove('pending');
                if (inputVal[i] === targetText[i]) {
                    charSpan.className = 't-char correct';
                } else {
                    charSpan.className = 't-char incorrect';
                    mistakes++;
                }
                if (targetText[i] === ' ') charSpan.classList.add('space');

            } else if (i === currentLength) {
                charSpan.className = 't-char pending current';
                if (targetText[i] === ' ') charSpan.classList.add('space');
                
                // Auto Scroll
                const container = document.getElementById('typing-display');
                const spanTop = charSpan.offsetTop;
                const containerHeight = container.clientHeight;
                const scrollTop = container.scrollTop;
                
                if (spanTop > scrollTop + containerHeight - 50) {
                    container.scrollTo({ top: spanTop - 50, behavior: 'smooth' });
                } else if (spanTop < scrollTop) {
                    container.scrollTo({ top: spanTop - 50, behavior: 'smooth' });
                }

            } else {
                charSpan.className = 't-char pending';
                if (targetText[i] === ' ') charSpan.classList.add('space');
            }
        }

        this.state.mistakes = mistakes;
        this.calculateStats();

        if (currentLength >= targetText.length) {
            this.finish(true);
        }
    },

    startTimer: function() {
        this.state.timer = setInterval(() => {
            this.state.timeLeft--;
            this.updateTimeDisplay();
            this.calculateStats();

            if (this.state.timeLeft <= 0) {
                this.finish(false);
            }
        }, 1000);
    },

    calculateStats: function() {
        if (!this.state.startTime) return { wpm: 0, accuracy: 100 };
        
        const timeElapsed = (this.state.timeLimit - this.state.timeLeft);
        if (timeElapsed <= 0) return { wpm: 0, accuracy: 100 };
        const timeInMinutes = timeElapsed / 60;

        const inputLength = document.getElementById('typing-input').value.length;
        
        const wpm = Math.round((inputLength / 5) / timeInMinutes) || 0;
        
        const accuracy = inputLength > 0 
            ? Math.round(((inputLength - this.state.mistakes) / inputLength) * 100) 
            : 100;
        
        document.getElementById('typing-wpm').innerText = wpm;
        document.getElementById('typing-acc').innerText = accuracy + '%';
        
        return { wpm, accuracy };
    },

    finish: function(completed) {
        this.state.isFinished = true;
        clearInterval(this.state.timer);
        document.getElementById('typing-input').disabled = true;
        
        const stats = this.calculateStats();
        
        // Show Result Panel
        document.getElementById('typing-board').style.display = 'none';
        document.getElementById('typing-result-panel').style.display = 'flex';
        document.getElementById('typing-controls-panel').style.visibility = 'hidden'; // Hide controls during result
        
        document.getElementById('res-wpm').innerText = stats.wpm;
        document.getElementById('res-acc').innerText = stats.accuracy + '%';
        
        // Load Leaderboard
        this.loadLeaderboard();
    },
    
    submitScore: function() {
        const name = document.getElementById('sub-name').value.trim();
        const rating = this.state.currentRating;
        const stats = this.calculateStats();
        const mode = document.getElementById('typing-mode').value;
        
        if (!name) {
            alert('请输入您的姓名');
            return;
        }
        
        if(!confirm('确认提交您的成绩吗？')) return;
        
        const btn = document.getElementById('btn-submit-score');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 提交中...';
        
        fetch('api/submit_typing_score.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: name,
                wpm: stats.wpm,
                accuracy: stats.accuracy,
                satisfaction: rating,
                mode: mode,
                timestamp_log: this.state.timestampLog
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('提交成功！');
                this.loadLeaderboard(); // Refresh leaderboard
                // Disable form inputs
                document.getElementById('sub-name').disabled = true;
                btn.innerHTML = '<i class="fas fa-check"></i> 已提交';
            } else {
                alert('提交失败: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '提交成绩';
            }
        })
        .catch(err => {
            alert('网络错误，请稍后重试');
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = '提交成绩';
        });
    },
    
    loadLeaderboard: function() {
        const search = document.getElementById('lb-search-input').value;
        const mode = document.getElementById('typing-mode').value;
        const tbody = document.getElementById('lb-body');
        
        fetch(`api/get_typing_leaderboard.php?mode=${mode}&search=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    tbody.innerHTML = '';
                    if (res.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center">暂无数据</td></tr>';
                        return;
                    }
                    
                    res.data.forEach((row, index) => {
                        let rankClass = '';
                        if(index === 0) rankClass = 'text-gold font-bold';
                        else if(index === 1) rankClass = 'text-silver font-bold';
                        else if(index === 2) rankClass = 'text-bronze font-bold';
                        
                        tbody.innerHTML += `
                            <tr class="${rankClass}">
                                <td>#${index + 1}</td>
                                <td>${row.username}</td>
                                <td>${row.wpm}</td>
                                <td>${row.accuracy}%</td>
                                <td>${row.mode === 'english' ? '英文' : '中文'}</td>
                                <td>${new Date(row.created_at).toLocaleDateString()}</td>
                            </tr>
                        `;
                    });
                }
            });
    }
};

function openTypingMenu() {
    document.getElementById('typing-menu-modal').style.display = 'flex';
    setTimeout(() => TypingApp.init(), 50);
}

function closeTypingMenu() {
    document.getElementById('typing-menu-modal').style.display = 'none';
    if(TypingApp.state.timer) clearInterval(TypingApp.state.timer);
}

document.getElementById('typing-menu-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTypingMenu();
    }
});
</script>
