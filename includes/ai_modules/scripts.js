/**
 * AI & IDE Modules Script
 * Refactored for Robustness and Modularity
 */

// --- Global State & Configuration ---
const CONFIG = window.IDE_CONFIG || {
    paths: {
        monacoBase: 'assets/js/lib/monaco-editor/min/vs',
        pyodideIndex: 'assets/js/lib/pyodide/',
        ffmpegCore: 'assets/js/lib/ffmpeg/ffmpeg-core.js',
        tesseractScript: 'assets/js/lib/tesseract/tesseract.min.js',
        v86Wasm: 'assets/js/lib/v86/v86.wasm'
    },
    timeouts: { init: 15000 }
};

let currentAiType = '';
let lastIdeError = '';

// --- IdeManager Class (Refactored for CodeMirror 6) ---
class IdeManager {
    constructor() {
        this.view = null; // CodeMirror EditorView instance
        this.pyodide = null;
        this.isLoading = false;
        this.config = CONFIG;
        this.currentPath = null;
        this.cm = null; // CodeMirror modules container
        this.langCompartment = null; // For switching languages
    }

    // Initialize the IDE
    async init() {
        if (this.isLoading) return;
        this.isLoading = true;
        this.updateStatus('Initializing CodeMirror 6...', 'loading');

        try {
            console.log("[IdeManager] Initializing with config:", this.config);
            await this.loadCodeMirror();
            await this.loadTree();
            this.createEditor();
            
            // Try to load Pyodide in background for Python execution
            this.loadPyodideEngine().catch(e => console.warn("Pyodide background load:", e));
            
            this.updateStatus('Ready', 'ready');
            console.log("[IdeManager] Initialization complete.");
            
            // Auto-open welcome file if exists
            if (!this.currentPath) {
                 this.openFile('welcome.txt');
            }
        } catch (error) {
            console.error("[IdeManager] Init failed:", error);
            this.updateStatus(`Error: ${error.message}`, 'error');
            this.handleError(error);
        } finally {
            this.isLoading = false;
        }
    }

    // Step 1: Load CodeMirror 6 Modules
    async loadCodeMirror() {
        const baseUrl = 'https://esm.sh';
        // Using dynamic imports for ESM modules
        const [
            { basicSetup, EditorView, placeholder },
            { EditorState, Compartment },
            { keymap },
            { defaultKeymap, history, historyKeymap, indentWithTab },
            { javascript },
            { python },
            { php },
            { oneDark }
        ] = await Promise.all([
            import(`${baseUrl}/codemirror`),
            import(`${baseUrl}/@codemirror/state`),
            import(`${baseUrl}/@codemirror/view`),
            import(`${baseUrl}/@codemirror/commands`),
            import(`${baseUrl}/@codemirror/lang-javascript`),
            import(`${baseUrl}/@codemirror/lang-python`),
            import(`${baseUrl}/@codemirror/lang-php`),
            import(`${baseUrl}/@codemirror/theme-one-dark`)
        ]);

        this.cm = {
            basicSetup, EditorView, placeholder,
            EditorState, Compartment,
            keymap, defaultKeymap, history, historyKeymap, indentWithTab,
            javascript, python, php, oneDark
        };
        
        this.langCompartment = new Compartment();
    }

    // Step 2: Create Editor Instance
    createEditor() {
        if (this.view) return;
        const container = document.getElementById('editor-container');
        if (!container) throw new Error("Editor container not found in DOM.");

        const startState = this.cm.EditorState.create({
            doc: "// Welcome to Cloud IDE\n// Loading...",
            extensions: [
                this.cm.basicSetup,
                this.cm.keymap.of([
                    ...this.cm.defaultKeymap, 
                    ...this.cm.historyKeymap, 
                    this.cm.indentWithTab,
                    { key: "Mod-s", run: () => { this.saveFile(); return true; } }
                ]),
                this.cm.oneDark,
                this.cm.EditorView.lineWrapping,
                this.cm.EditorView.updateListener.of((update) => {
                    if (update.docChanged) {
                        // Document changed
                        const pos = update.state.selection.main.head;
                        const line = update.state.doc.lineAt(pos);
                        document.getElementById('editor-cursor-info').innerText = `Ln ${line.number}, Col ${pos - line.from + 1}`;
                    }
                }),
                this.langCompartment.of(this.cm.javascript()) // Default
            ]
        });

        this.view = new this.cm.EditorView({
            state: startState,
            parent: container
        });
    }

    // Step 3: Load Pyodide Engine
    async loadPyodideEngine() {
        if (this.pyodide) {
             this.enableRunButton();
             return;
        }

        // Check for Secure Context (required for some advanced features)
        const isSecure = (typeof IS_SECURE_CONTEXT !== 'undefined') ? IS_SECURE_CONTEXT : window.isSecureContext;
        
        if (!isSecure) {
            console.warn("[IdeManager] Running in non-secure context. SharedArrayBuffer is not available.");
            this.updateStatus("非安全环境 (非 HTTPS/localhost)，功能受限", "warning");
        }
        
        // Timeout handling for Pyodide loading
        const loadTimeout = setTimeout(() => {
            if (!this.pyodide) {
                console.warn("[IdeManager] Pyodide load timed out or failed.");
                this.updateStatus("Python 引擎加载超时，点击重试", "error");
                this.enableRunButton(); 
            }
        }, 30000); // Increased to 30s for slower connections

        let attempts = 0;
        while (typeof loadPyodide === 'undefined' && attempts < 200) { 
            await new Promise(r => setTimeout(r, 100));
            attempts++;
        }

        if (typeof loadPyodide === 'undefined') {
            clearTimeout(loadTimeout);
            console.warn("Pyodide bootstrap script not loaded.");
            this.updateStatus("无法加载 Python 引擎脚本 (network blocked?)", "error");
            this.enableRunButton();
            return;
        }

        // Use absolute URL if possible to ensure standard library is found
        let indexURL = this.config.paths.pyodideIndex || '/assets/js/lib/pyodide/';
        
        // Force relative path resolution without hardcoding localhost
        if (!indexURL.startsWith('http')) {
             // Ensure no double slashes if indexURL starts with /
             const cleanPath = indexURL.startsWith('/') ? indexURL.substring(1) : indexURL;
             // Construct absolute URL dynamically based on current origin
             const origin = window.location.origin;
             
             // If indexURL is root-relative (starts with /), append to origin
             if (indexURL.startsWith('/')) {
                 indexURL = `${origin}${indexURL}`;
             } else {
                 // If it's relative to current page, resolve it
                 indexURL = new URL(cleanPath, window.location.href).href;
             }
        }

        const config = {
            indexURL: indexURL,
            // Only explicitly disable things if we are in a non-secure context
            // Pyodide 0.29.0 automatically detects environment, but we can be explicit
        };
        
        // In non-secure context, ensure we don't try to use SharedArrayBuffer if we can help it
        // (Note: Pyodide main thread usually doesn't use it unless specifically asked or using certain features)
        // const isSecure = (typeof IS_SECURE_CONTEXT !== 'undefined') ? IS_SECURE_CONTEXT : window.isSecureContext; // REMOVED DUPLICATE DECLARATION
        if (!isSecure) {
            console.log("[IdeManager] Non-secure context detected. Pyodide will run in limited mode.");
        }

        console.log("Final IndexURL:", indexURL); // ADDED DEBUG LOG
        console.log("[IdeManager] Loading Pyodide from:", indexURL);
        this.updateStatus("正在下载 Python 运行时...", "loading");

        // Validate python_stdlib.zip availability before loading
        try {
            // Check if indexURL ends with slash
            const baseUrl = indexURL.endsWith('/') ? indexURL : indexURL + '/';
            const stdlibUrl = baseUrl + 'python_stdlib.zip';
            
            const checkRes = await fetch(stdlibUrl, { method: 'HEAD' });
            if (checkRes.ok) {
                 // Double check content type or size if possible, but HEAD is usually enough
                 // If the server returns 200 OK for a 404 page (bad config), we might need to GET first bytes
                 // But let's assume if it's 200, it's there. 
                 // Wait, the user said: "if content starts with <!DOCTYPE"
                 // HEAD might not return body. Let's do a Range request or just trust HEAD for existence first?
                 // No, often custom 404 pages return 200 OK. 
                 // Let's do a small GET.
            }
            
            const probeRes = await fetch(stdlibUrl, { method: 'GET', headers: { 'Range': 'bytes=0-10' } });
            if (probeRes.ok) {
                const text = await probeRes.text();
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                    throw new Error(`Standard library ZIP is an HTML page (404/Error page). URL: ${stdlibUrl}`);
                }
            } else if (probeRes.status === 404) {
                 throw new Error(`Standard library ZIP not found (404). URL: ${stdlibUrl}`);
            }
        } catch (e) {
            console.error("[IdeManager] Pre-flight check failed:", e);
            this.updateStatus(`Pyodide 资源缺失: python_stdlib.zip 无法加载 (${e.message})`, "error");
            this.enableRunButton();
            clearTimeout(loadTimeout);
            return;
        }

        // Simple Retry Logic for Server Environments
        let retryCount = 0;
        const maxRetries = 2;

        const attemptLoad = async () => {
            try {
                this.pyodide = await loadPyodide(config);
                
                // Set up Standard I/O handlers
                this.pyodide.setStdout({
                    batched: (text) => {
                        const consoleEl = document.getElementById('ide-console');
                        if (consoleEl) {
                            consoleEl.innerText += text + '\n';
                            consoleEl.scrollTop = consoleEl.scrollHeight;
                        }
                    }
                });

                this.pyodide.setStderr({
                    batched: (text) => {
                        const consoleEl = document.getElementById('ide-console');
                        if (consoleEl) {
                            consoleEl.innerText += 'Error: ' + text + '\n';
                            consoleEl.style.color = '#ff4757';
                            consoleEl.scrollTop = consoleEl.scrollHeight;
                        }
                    }
                });

                this.pyodide.setStdin({
                    stdin: () => {
                        const result = window.prompt("Python Input:");
                        if (result === null) return null; // EOF
                        return result + "\n"; // Append newline for input()
                    }
                });

                // Critical: Ensure standard library is preloaded if using local paths
                // to avoid ModuleNotFoundError: No module named 'encodings'
                this.updateStatus("正在初始化标准库...", "loading");
                
                clearTimeout(loadTimeout);
                this.enableRunButton();
                this.updateStatus("Python 引擎就绪", "success");
                console.log("[IdeManager] Pyodide loaded successfully.");
            } catch (e) {
                if (retryCount < maxRetries) {
                    retryCount++;
                    console.warn(`[IdeManager] Load attempt ${retryCount} failed. Retrying...`, e);
                    this.updateStatus(`加载失败，正在重试 (${retryCount}/${maxRetries})...`, "loading");
                    setTimeout(attemptLoad, 2000);
                } else {
                    clearTimeout(loadTimeout);
                    console.error("Pyodide Load Error after retries:", e);
                    this.updateStatus("Python 引擎加载失败: " + e.message, "error");
                    this.enableRunButton(); 
                }
            }
        };

        await attemptLoad();
    }

    // File Operations
    async loadTree() {
        try {
            const res = await fetch('api/ide_api.php?action=list');
            const text = await res.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                console.error("[IdeManager] API Response is not valid JSON:", text);
                // Fallback: try to extract message from common HTML patterns (e.g., PHP errors)
                let errorMsg = "无法解析文件列表";
                if (text.includes("Fatal error") || text.includes("Database Connection Failed")) {
                    errorMsg = text.split('\n')[0].replace(/<[^>]*>?/gm, ''); // Simple strip tags
                }
                this.updateStatus(`解析错误: ${errorMsg.substring(0, 50)}`, "error");
                return;
            }
            
            if(json.success) {
                this.renderTree(json.data);
            } else {
                console.error("[IdeManager] API Error:", json.message);
                this.updateStatus(`加载失败: ${json.message}`, "error");
            }
        } catch(e) { 
            console.error("[IdeManager] Load tree failed", e); 
            this.updateStatus("网络连接失败", "error");
        }
    }

    renderTree(files) {
        const tree = document.getElementById('file-tree');
        if (!tree) return;
        tree.innerHTML = '';
        
        if (files.length === 0) {
            tree.innerHTML = '<div style="padding:10px; color:#666;">Empty workspace</div>';
            return;
        }

        files.forEach(file => {
            const div = document.createElement('div');
            div.className = 'file-tree-item';
            div.innerHTML = `<i class="${file.icon}"></i> ${file.text}`;
            div.onclick = () => {
                if(file.type === 'file') this.openFile(file.id);
            };
            if(this.currentPath === file.id) div.classList.add('active');
            tree.appendChild(div);
        });
    }

    async openFile(path) {
        if(this.currentPath === path) return;
        this.updateStatus(`Loading ${path}...`, 'loading');
        
        try {
            const res = await fetch(`api/ide_api.php?action=read&path=${encodeURIComponent(path)}`);
            const text = await res.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                console.error("[IdeManager] API Response is not valid JSON:", text);
                let errorMsg = "无法解析文件内容";
                if (text.includes("Fatal error") || text.includes("Database Connection Failed")) {
                    errorMsg = text.split('\n')[0].replace(/<[^>]*>?/gm, '');
                }
                this.updateStatus(`解析错误: ${errorMsg.substring(0, 50)}`, "error");
                return;
            }

            if(json.success) {
                this.currentPath = path;
                const fileInfoEl = document.getElementById('current-file-path');
                if (fileInfoEl) fileInfoEl.innerText = path;
                
                // Update content
                const content = json.data.content;
                this.view.dispatch({
                    changes: {from: 0, to: this.view.state.doc.length, insert: content}
                });
                
                // Update Language
                let langExt = this.cm.javascript(); // Default
                if(path.endsWith('.py')) langExt = this.cm.python();
                else if(path.endsWith('.php')) langExt = this.cm.php();
                
                this.view.dispatch({
                    effects: this.langCompartment.reconfigure(langExt)
                });
                
                // Refresh tree highlight
                this.renderTreeHighlight(); 
                this.updateStatus("Ready", "ready");
            } else {
                this.updateStatus(`Error: ${json.message}`, "error");
            }
        } catch(e) {
            console.error(e);
            this.updateStatus("无法读取文件", "error");
        }
    }

    renderTreeHighlight() {
        const items = document.querySelectorAll('.file-tree-item');
        items.forEach(item => {
            if (item.innerText.includes(this.currentPath.split('/').pop())) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    async saveFile() {
        if(!this.currentPath) return;
        const content = this.view.state.doc.toString();
        this.updateStatus("Saving...", "loading");
        
        try {
            const res = await fetch('api/ide_api.php?action=save', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ path: this.currentPath, content: content })
            });
            const text = await res.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                console.error("[IdeManager] API Response is not valid JSON:", text);
                let errorMsg = "响应格式错误";
                if (text.includes("Fatal error") || text.includes("Database Connection Failed")) {
                    errorMsg = text.split('\n')[0].replace(/<[^>]*>?/gm, '');
                }
                this.updateStatus(`保存失败: ${errorMsg.substring(0, 50)}`, "error");
                return;
            }
            if(json.success) {
                this.updateStatus(`Saved ${this.currentPath}`, 'success');
                setTimeout(() => this.updateStatus('Ready', 'ready'), 2000);
            } else {
                this.updateStatus(`Save failed: ${json.message}`, "error");
            }
        } catch(e) {
            console.error(e);
            this.updateStatus("保存失败: 网络错误", "error");
        }
    }

    async createFile() {
        const name = prompt("Enter file name (e.g., test.py):");
        if(!name) return;
        
        try {
            const res = await fetch('api/ide_api.php?action=create', {
                method: 'POST',
                body: JSON.stringify({ path: name, type: 'file' })
            });
            const text = await res.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                console.error("API Response is not valid JSON:", text);
                let errorMsg = "响应格式错误";
                if (text.includes("Fatal error") || text.includes("Database Connection Failed")) {
                    errorMsg = text.split('\n')[0].replace(/<[^>]*>?/gm, '');
                }
                alert("创建失败: " + errorMsg.substring(0, 100));
                return;
            }
            if(json.success) {
                this.loadTree();
                this.openFile(name);
            } else {
                alert("Create failed: " + json.message);
            }
        } catch(e) {
            alert("Error: " + e.message);
        }
    }
    
    async createFolder() {
        const name = prompt("Enter folder name:");
        if(!name) return;
        try {
            const res = await fetch('api/ide_api.php?action=create', {
                method: 'POST',
                body: JSON.stringify({ path: name, type: 'directory' })
            });
            const text = await res.text();
            let json;
            try {
                json = JSON.parse(text);
            } catch (e) {
                console.error("API Response is not valid JSON:", text);
                let errorMsg = "响应格式错误";
                if (text.includes("Fatal error") || text.includes("Database Connection Failed")) {
                    errorMsg = text.split('\n')[0].replace(/<[^>]*>?/gm, '');
                }
                alert("创建失败: " + errorMsg.substring(0, 100));
                return;
            }
            if(json.success) {
                this.loadTree();
            } else {
                alert("Create failed: " + json.message);
            }
        } catch(e) {
             alert("Error: " + e.message);
        }
    }

    async refreshTree() {
        await this.loadTree();
    }

    // Run Code
    async run() {
        const consoleEl = document.getElementById('ide-console');
        if (!consoleEl) return;

        // If Python file, run with Pyodide
        if (this.currentPath && this.currentPath.endsWith('.py')) {
            if (!this.pyodide) {
                consoleEl.innerText = "Error: Python engine is not ready.";
                return;
            }

            // Reset console for new run
            consoleEl.innerText = "";
            consoleEl.style.color = "#fff";
            
            const code = this.view.state.doc.toString();

            try {
                // Execute code directly - stdout/stderr are handled by setStdout/setStderr
                await this.pyodide.runPythonAsync(code);
                
                // Final check if no output was produced
                if (consoleEl.innerText === "") {
                    consoleEl.innerText = "Program finished (No Output)";
                }

                window.lastIdeOutput = consoleEl.innerText.trim();
                lastIdeError = "";

                // --- Auto-Save for Competition Mode ---
                // Check if CompApp exists and we are in Quiz Mode
                if (typeof CompApp !== 'undefined' && CompApp.state) {
                    const quizView = document.getElementById('view-quiz');
                    const practiceView = document.getElementById('view-practice');
                    
                    // Quiz Mode
                    if (quizView && quizView.classList.contains('active') && CompApp.state.quizData) {
                        const currentQ = CompApp.state.quizData[CompApp.state.quizIdx];
                        if (currentQ && currentQ.type === 'practical') {
                            CompApp.saveQuizAnswer(currentQ.id, window.lastIdeOutput);
                            this.updateStatus("Result synced to Quiz Question " + (CompApp.state.quizIdx + 1), "success");
                        }
                    } 
                    // Practice Mode
                    else if (practiceView && practiceView.classList.contains('active')) {
                        const display = document.getElementById('prac-answer-display');
                        if (display) {
                            display.innerText = window.lastIdeOutput;
                            display.classList.remove('empty');
                            this.updateStatus("Result synced to Practice Question", "success");
                        }
                    }
                }
            } catch (err) {
                // The error might already be partially printed via stderr, 
                // but we also show the traceback here.
                const errorMsg = `\nTraceback (most recent call last):\n${err}`;
                consoleEl.innerText += errorMsg;
                consoleEl.style.color = '#ff4757';
                lastIdeError = err.toString();
            }
        } else {
            consoleEl.innerText = "Run is only supported for Python files (.py) in this version.";
        }
    }

    // UI Helpers
    updateStatus(msg, type) {
        const consoleEl = document.getElementById('ide-console');
        if (consoleEl) {
            consoleEl.innerText = msg;
            consoleEl.style.color = (type === 'error') ? '#ff4757' : '#fff';
        }
    }

    enableRunButton() {
        const btn = document.getElementById('run-btn');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play"></i> 运行';
            btn.onclick = () => this.run();
        }
    }

    handleError(error) {
        const btn = document.getElementById('run-btn');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-redo"></i> 重试加载';
            btn.onclick = () => this.init();
        }
    }
}

// Singleton Instance
window.ideManager = new IdeManager();

// --- Toolbox Manager (New) ---
window.ToolboxManager = {
    openMenu() {
        const modal = document.getElementById('toolbox-menu-modal');
        if (modal) {
            modal.style.display = 'flex';
            const mainMenu = document.getElementById('main-menu');
            const subMenu = document.getElementById('ai-sub-menu');
            if(mainMenu) mainMenu.style.display = 'block';
            if(subMenu) subMenu.style.display = 'none';
        }
    },
    closeMenu() {
        const modal = document.getElementById('toolbox-menu-modal');
        if (modal) modal.style.display = 'none';
    },
    showSubMenu() {
        document.getElementById('main-menu').style.display = 'none';
        document.getElementById('ai-sub-menu').style.display = 'block';
    },
    backToMainMenu() {
        document.getElementById('ai-sub-menu').style.display = 'none';
        document.getElementById('main-menu').style.display = 'block';
    }
};

// --- AiChatManager (Refactored) ---
window.AiChatManager = {
    storageKey: 'geek_ai_chats',
    sessions: [],
    currentId: null,
    history: [], // Current session messages

    init() {
        try {
            const stored = localStorage.getItem(this.storageKey);
            if (stored) this.sessions = JSON.parse(stored);
        } catch(e) {
            console.error("Failed to load chat history:", e);
            this.sessions = [];
        }
        
        if (this.sessions.length === 0) this.newChat(false); 
        else {
            if (!this.currentId) this.currentId = this.sessions[0].id;
            this.loadChat(this.currentId);
        }
    },

    save() {
        if (!this.currentId) return;
        const sessionIndex = this.sessions.findIndex(s => s.id === this.currentId);
        if (sessionIndex !== -1) {
            const session = this.sessions[sessionIndex];
            session.messages = this.history;
            session.updatedAt = Date.now();
            
            if ((session.title === "新对话" || !session.title) && this.history.length > 0) {
                const firstUserMsg = this.history.find(m => m.role === 'user');
                if (firstUserMsg) {
                    let title = firstUserMsg.content.substring(0, 20);
                    if (firstUserMsg.content.length > 20) title += "...";
                    session.title = title.replace(/\n/g, ' ');
                }
            }
            this.sessions.splice(sessionIndex, 1);
            this.sessions.unshift(session);
            this.persist();
            this.renderHistoryList();
        }
    },

    persist() {
        try { localStorage.setItem(this.storageKey, JSON.stringify(this.sessions)); }
        catch(e) { console.error("Failed to save chat history:", e); }
    },

    newChat(refreshUI = true) {
        const id = Date.now().toString();
        const newSession = {
            id: id,
            title: "新对话",
            createdAt: Date.now(),
            updatedAt: Date.now(),
            messages: []
        };
        this.sessions.unshift(newSession);
        this.currentId = id;
        this.history = [];
        this.persist();
        
        if (refreshUI) {
            this.clearUI();
            this.renderHistoryList();
            const out = document.getElementById('ai-output');
            if(out) out.innerText = "等待 AI 生成结果...";
        }
    },

    loadChat(id) {
        const session = this.sessions.find(s => s.id === id);
        if (session) {
            this.currentId = id;
            this.history = session.messages || [];
            this.clearUI();
            const out = document.getElementById('ai-output');
            
            if (this.history.length === 0) out.innerText = "等待 AI 生成结果...";
            else {
                 out.innerHTML = "";
                 this.history.forEach(msg => AiLabManager.appendMessage(out, msg.role, msg.content));
            }
            this.renderHistoryList();
            if(window.innerWidth < 768) document.getElementById('ai-history-panel').style.display = 'none';
        }
    },

    deleteChat(id, e) {
        if (e) e.stopPropagation();
        if (!confirm("确定要删除这条对话记录吗？")) return;
        this.sessions = this.sessions.filter(s => s.id !== id);
        this.persist();
        if (this.currentId === id) {
            if (this.sessions.length > 0) this.loadChat(this.sessions[0].id);
            else this.newChat();
        } else {
            this.renderHistoryList();
        }
    },

    clearUI() {
        const out = document.getElementById('ai-output');
        if(out) out.innerHTML = "";
        const prompt = document.getElementById('ai-prompt');
        if (prompt) prompt.value = "";
    },

    toggleHistoryPanel() {
        const panel = document.getElementById('ai-history-panel');
        if (panel.style.display === 'none') {
            panel.style.display = 'flex';
            this.renderHistoryList();
        } else {
            panel.style.display = 'none';
        }
    },

    renderHistoryList(sessions = null) {
        const list = document.getElementById('history-list');
        if(!list) return;
        list.innerHTML = "";
        const source = sessions || this.sessions;
        
        if (source.length === 0) {
            list.innerHTML = "<div style='padding:20px; text-align:center; color:var(--text-secondary); font-size:12px;'>暂无记录</div>";
            return;
        }
        
        source.forEach(session => {
            const el = document.createElement('div');
            el.className = `history-item ${session.id === this.currentId ? 'active' : ''}`;
            el.onclick = () => this.loadChat(session.id);
            const time = new Date(session.updatedAt).toLocaleDateString();
            el.innerHTML = `
                <div class="history-title">${session.title || '无标题'}</div>
                <div class="history-meta">
                    <span>${time}</span>
                    <i class="fas fa-trash history-delete-btn" onclick="AiChatManager.deleteChat('${session.id}', event)"></i>
                </div>
            `;
            list.appendChild(el);
        });
    },
    
    filterHistory(query) {
        if (!query) return this.renderHistoryList();
        const filtered = this.sessions.filter(s => (s.title || "").toLowerCase().includes(query.toLowerCase()));
        this.renderHistoryList(filtered);
    },

    exportChat() {
        if (!this.history || this.history.length === 0) return alert("当前没有对话内容可导出");
        let content = "# 对话记录\n\n";
        this.history.forEach(msg => {
            const role = msg.role === 'user' ? "User" : "AI";
            content += `### ${role}\n${msg.content}\n\n`;
        });
        const blob = new Blob([content], { type: 'text/markdown' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `chat_export_${Date.now()}.md`;
        a.click();
        URL.revokeObjectURL(url);
    }
};

// --- AiTaskManager (Refactored Monitor) ---
window.AiTaskManager = {
    startTime: 0,
    timerInterval: null,
    
    init(taskId) {
        this.reset();
        this.startTime = Date.now();
        const monitor = document.getElementById('ai-monitor');
        monitor.classList.add('active');
        monitor.classList.remove('collapsed');
        const icon = document.getElementById('monitor-toggle-icon');
        if(icon) icon.className = 'fas fa-chevron-down';
        document.getElementById('monitor-task-id').innerText = `ID: ${taskId || 'PENDING'}`;
        
        this.timerInterval = setInterval(() => {
            const diff = ((Date.now() - this.startTime) / 1000).toFixed(1);
            document.getElementById('metric-time').innerText = diff + 's';
        }, 100);

        this.setStep('req', 'active');
        this.log('Monitor initialized. Task submitted.', 'info');
    },

    reset() {
        if(this.timerInterval) clearInterval(this.timerInterval);
        document.getElementById('monitor-line').style.width = '0%';
        document.querySelectorAll('.step').forEach(el => el.className = 'step');
        document.getElementById('metric-status').innerText = 'IDLE';
        document.getElementById('metric-time').innerText = '0.0s';
        document.getElementById('metric-progress').style.width = '0%';
        document.getElementById('monitor-log-list').innerHTML = '';
    },

    setStep(stepName, state) {
        const step = document.getElementById(`step-${stepName}`);
        if(step) {
            if (state === 'completed') {
                step.className = 'step completed';
                if (stepName === 'req') document.getElementById('monitor-line').style.width = '50%';
                if (stepName === 'proc') document.getElementById('monitor-line').style.width = '100%';
            } else if (state === 'active') {
                step.className = 'step active';
                if (stepName === 'proc') document.getElementById('monitor-line').style.width = '50%';
            } else if (state === 'error') step.className = 'step error';
        }
    },

    updateStatus(status, percent) {
        document.getElementById('metric-status').innerText = status;
        if(percent !== undefined) document.getElementById('metric-progress').style.width = percent + '%';
    },

    log(msg, type='info') {
        const list = document.getElementById('monitor-log-list');
        const entry = document.createElement('div');
        entry.className = 'log-entry';
        const time = new Date().toLocaleTimeString();
        entry.innerHTML = `<span class="log-time">[${time}]</span><span class="log-msg log-type-${type}">${msg}</span>`;
        list.appendChild(entry);
        list.scrollTop = list.scrollHeight;
    },

    finish(success) {
        if(this.timerInterval) clearInterval(this.timerInterval);
        if(success) {
            this.setStep('proc', 'completed');
            this.setStep('res', 'completed');
            this.updateStatus('COMPLETED', 100);
            this.log('Task finished successfully.', 'success');
        } else {
            this.setStep('res', 'error');
            this.updateStatus('FAILED');
            this.log('Task failed.', 'error');
        }
    },
    
    toggleMonitor() {
        const monitor = document.getElementById('ai-monitor');
        const icon = document.getElementById('monitor-toggle-icon');
        if (monitor.classList.contains('collapsed')) {
            monitor.classList.remove('collapsed');
            icon.className = 'fas fa-chevron-down';
        } else {
            monitor.classList.add('collapsed');
            icon.className = 'fas fa-chevron-up';
        }
    }
};

// --- AiLabManager (New Controller) ---
window.AiLabManager = {
    openWindow(type) {
        currentAiType = type;
        ToolboxManager.closeMenu();
        
        const modal = document.getElementById('ai-task-modal');
        const urlBox = document.getElementById('vid-url-box');
        const title = document.getElementById('ai-task-title');
        const promptArea = document.getElementById('ai-prompt');
        const output = document.getElementById('ai-output');
        const toolbar = document.getElementById('ai-text-toolbar');

        if (!modal) return;
        modal.style.display = 'flex';
        
        if (toolbar) toolbar.style.display = (type === 'text') ? 'flex' : 'none';

        if (type !== 'text') {
            output.innerText = "等待 AI 生成结果...";
            AiChatManager.history = []; 
            if (document.getElementById('ai-history-panel')) document.getElementById('ai-history-panel').style.display = 'none';
        } else {
            AiChatManager.init();
        }
        
        document.getElementById('ai-status-panel').style.display = 'none';
        
        if (urlBox) {
            urlBox.style.display = (type === 'video' || type === 'image') ? 'block' : 'none';
            const urlInput = document.getElementById('ai-url');
            if (type === 'video') urlInput.placeholder = "请输入图片 URL (用于图生视频)";
            else if (type === 'image') urlInput.placeholder = "请输入参考图 URL (可选)";
        }
        
        if (type === 'debug') {
            title.innerHTML = '<i class="fas fa-bug"></i> AI 代码诊断官';
            const code = ideManager.editor ? ideManager.editor.getValue() : "暂未打开 IDE";
            promptArea.value = "【待分析代码】：\n" + code + "\n\n【错误日志】：\n" + (lastIdeError || "无报错日志");
        } else {
            const icons = { 'text': 'comment-dots', 'image': 'paint-brush', 'video': 'video' };
            title.innerHTML = `<i class="fas fa-${icons[type] || 'robot'}"></i> AI 创作助手`;
            promptArea.value = "";
        }
    },

    closeWindow(force = false) {
        const modal = document.getElementById('ai-task-modal');
        if (!modal || modal.style.display === 'none') return;

        const btn = document.getElementById('ai-btn');
        if (!force && btn && btn.disabled) {
            if (!confirm("当前有任务正在进行中，关闭窗口将导致进度丢失。确定要关闭吗？")) return;
        }

        const container = modal.querySelector('.ai-modal-container');
        container.classList.add('closing');
        
        setTimeout(() => {
            modal.style.display = 'none';
            container.classList.remove('closing');
            
            if (AiTaskManager.timerInterval) {
                clearInterval(AiTaskManager.timerInterval);
                AiTaskManager.timerInterval = null;
            }
            
            if (currentAiType !== 'text') {
                 const out = document.getElementById('ai-output');
                 if(out) out.innerText = "等待 AI 生成结果...";
            }
            
            if (btn) btn.disabled = false;
            const statusPanel = document.getElementById('ai-status-panel');
            if (statusPanel) statusPanel.style.display = 'none';
        }, 300);
    },

    async runTask() {
        const promptInput = document.getElementById('ai-prompt');
        const prompt = promptInput.value;
        const urlInput = document.getElementById('ai-url');
        const url = urlInput ? urlInput.value : '';
        const out = document.getElementById('ai-output');
        const btn = document.getElementById('ai-btn');
        const statusPanel = document.getElementById('ai-status-panel');

        if (!prompt) return alert("请输入需要处理的内容");

        btn.disabled = true;
        if (statusPanel) statusPanel.style.display = 'block';
        
        AiTaskManager.init();

        if (currentAiType === 'text') {
            AiChatManager.history.push({ role: "user", content: prompt });
            this.appendMessage(out, "user", prompt);
            promptInput.value = ""; 
        }

        const updateProgress = (percent, text) => {
            const fill = document.getElementById('ai-progress-fill');
            const txt = document.getElementById('ai-status-text');
            const pct = document.getElementById('ai-status-percent');
            if (fill) fill.style.width = percent + '%';
            if (txt) txt.innerText = text;
            if (pct) pct.innerText = percent + '%';
        };

        try {
            updateProgress(20, "握手连接：正在触达云端服务器...");
            AiTaskManager.log("Sending request to API Gateway...", "info");
            
            const payload = { 
                type: (currentAiType === 'debug' ? 'text' : currentAiType), 
                prompt: prompt, 
                img_url: url 
            };

            if (currentAiType === 'text') {
                const maxContext = 20; 
                const contextHistory = AiChatManager.history.length > maxContext 
                    ? AiChatManager.history.slice(AiChatManager.history.length - maxContext) 
                    : AiChatManager.history;
                payload.history = contextHistory;
            }

            const response = await fetch('api/ai_gateway.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            updateProgress(60, "逻辑处理：AI 正在深度思考...");
            AiTaskManager.setStep('req', 'completed');
            AiTaskManager.setStep('proc', 'active');
            AiTaskManager.updateStatus('PROCESSING', 20);

            if (!response.ok) throw new Error("服务器响应异常: " + response.status);

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            updateProgress(100, "处理完毕！正在解析结果...");
            AiTaskManager.log("Response received from API.", "info");
            
            let content = "";
            console.log("AI Response Data:", data);

            if (data.output && data.output.choices && data.output.choices[0]) {
                content = data.output.choices[0].message.content;
                AiTaskManager.finish(true);
            } else if (data.choices && data.choices[0]) {
                content = data.choices[0].message.content;
                AiTaskManager.finish(true);
            } else if (data.output && data.output.task_id) {
                const taskId = data.output.task_id;
                document.getElementById('monitor-task-id').innerText = `ID: ${taskId}`;
                AiTaskManager.log(`Async task created: ${taskId}`, "info");
                
                let statusMsg = "任务已提交，正在生成中... (ID: " + taskId + ")";
                if (currentAiType === 'video') statusMsg += "\n(视频生成可能需要数分钟，请耐心等待)";
                else if (currentAiType === 'image') statusMsg += "\n(图片生成中...)";
                
                if (currentAiType === 'text') {
                    AiChatManager.history.push({ role: "assistant", content: statusMsg });
                    this.appendMessage(out, "assistant", statusMsg);
                } else {
                    this.typeText(out, statusMsg);
                }
                
                this.pollTask(taskId, out, btn, statusPanel);
                return; 
            } else if (data.output && data.output.results) {
                 if (data.output.results[0].url) {
                     content = `![Generated Image](${data.output.results[0].url})`;
                     AiTaskManager.finish(true);
                 } else {
                     content = JSON.stringify(data.output, null, 2);
                     AiTaskManager.finish(true);
                 }
            } else {
                content = "【未识别的响应格式】\n" + JSON.stringify(data, null, 2);
                AiTaskManager.finish(true);
            }

            if (currentAiType === 'text') {
                AiChatManager.history.push({ role: "assistant", content: content });
                this.appendMessage(out, "assistant", content);
                AiChatManager.save();
            } else {
                this.typeText(out, content);
            }

        } catch (e) {
            updateProgress(0, "请求中断");
            AiTaskManager.log(`Error: ${e.message}`, "error");
            AiTaskManager.setStep('req', 'error');
            AiTaskManager.updateStatus('ERROR');
            
            if (currentAiType === 'text') this.appendMessage(out, "system", `[系统错误]: ${e.message}`);
            else out.innerHTML = `<span style="color:#ff4757;">[系统错误]: ${e.message}</span>`;
        } finally {
            btn.disabled = false;
            setTimeout(() => { if (statusPanel) statusPanel.style.display = 'none'; }, 5000);
        }
    },

    appendMessage(container, role, text) {
        if (container.innerText === "等待 AI 生成结果...") container.innerHTML = "";
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-message ${role}-message`;
        msgDiv.style.marginBottom = "15px";
        msgDiv.style.padding = "10px";
        msgDiv.style.borderRadius = "8px";
        
        if (role === 'user') {
            msgDiv.style.background = "rgba(0, 47, 167, 0.1)";
            msgDiv.style.borderLeft = "4px solid var(--main-color-primary)";
            msgDiv.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <strong>You:</strong>
                    <i class="fas fa-pen" style="cursor:pointer;font-size:12px;color:var(--text-secondary);" title="引用并编辑" onclick="document.getElementById('ai-prompt').value = this.parentElement.nextElementSibling.innerText; document.getElementById('ai-prompt').focus();"></i>
                </div>
                <div style="margin-top:5px; white-space: pre-wrap;">${text}</div>`;
        } else if (role === 'assistant') {
            msgDiv.style.background = "var(--bg-secondary)";
            msgDiv.style.borderLeft = "4px solid #27ae60";
            msgDiv.innerHTML = `<strong>AI:</strong> <div class="ai-content" style="margin-top:5px; white-space: pre-wrap;"></div>`;
            const contentDiv = msgDiv.querySelector('.ai-content');
            this.typeText(contentDiv, text);
        } else {
            msgDiv.style.color = "#ff4757";
            msgDiv.innerText = text;
        }
        container.appendChild(msgDiv);
        container.scrollTop = container.scrollHeight;
    },

    typeText(container, text) {
        if (!container) return;
        container.innerHTML = "";
        const safeText = String(text || "");
        
        if (safeText.startsWith('![') && safeText.includes('](')) {
            const src = safeText.match(/\((.*?)\)/)[1];
            container.innerHTML = `<img src="${src}" style="max-width:100%; border-radius:8px;">`;
            return;
        }

        let i = 0;
        const timer = setInterval(() => {
            if (i < safeText.length) {
                container.innerText += safeText.charAt(i++);
                container.scrollTop = container.scrollHeight;
            } else {
                clearInterval(timer);
            }
        }, 10);
    },

    async pollTask(taskId, out, btn, statusPanel) {
        let attempts = 0;
        const maxAttempts = 120; 
        AiTaskManager.updateStatus('POLLING', 30);
        
        const interval = setInterval(async () => {
            attempts++;
            if (attempts > maxAttempts) {
                clearInterval(interval);
                out.innerHTML += "\n[系统提示]: 任务等待超时，请稍后重试。";
                AiTaskManager.log("Polling timeout.", "warning");
                AiTaskManager.finish(false);
                btn.disabled = false;
                if (statusPanel) statusPanel.style.display = 'none';
                return;
            }
            
            try {
                const response = await fetch('api/ai_gateway.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ type: 'fetch_task', task_id: taskId })
                });
                
                const data = await response.json();
                const status = data.output ? data.output.task_status : 'UNKNOWN';
                AiTaskManager.updateStatus(status);
                
                if (status === 'SUCCEEDED') {
                    clearInterval(interval);
                    AiTaskManager.finish(true);
                    
                    let resultUrl = "";
                    if (data.output.results && data.output.results[0] && data.output.results[0].url) {
                        resultUrl = data.output.results[0].url;
                        AiTaskManager.log("Image URL received.", "success");
                    } else if (data.output.video_url) {
                        resultUrl = data.output.video_url;
                        AiTaskManager.log("Video URL received.", "success");
                    }
                    
                    if (resultUrl) {
                        let mediaHtml = "";
                        if (currentAiType === 'video') mediaHtml = `<video src="${resultUrl}" controls style="max-width:100%; border-radius:8px;"></video>`;
                        else mediaHtml = `<img src="${resultUrl}" style="max-width:100%; border-radius:8px;">`;
                        
                        if (currentAiType === 'text') {
                             AiChatManager.history.push({ role: "assistant", content: "生成完成！" });
                             this.appendMessage(out, "assistant", mediaHtml);
                        } else {
                            out.innerHTML = mediaHtml;
                        }
                    } else {
                        out.innerText = "任务成功，但无法解析结果 URL。";
                        AiTaskManager.log("No URL found in response.", "warning");
                    }
                    
                    btn.disabled = false;
                    if (statusPanel) statusPanel.style.display = 'none';
                    
                } else if (status === 'FAILED') {
                    clearInterval(interval);
                    const errMsg = data.output.message || "未知错误";
                    out.innerText = "任务失败: " + errMsg;
                    AiTaskManager.log(`Task failed: ${errMsg}`, "error");
                    AiTaskManager.finish(false);
                    btn.disabled = false;
                    if (statusPanel) statusPanel.style.display = 'none';
                } else {
                    const progress = document.getElementById('ai-status-text');
                    if (progress) progress.innerText = `正在生成中... (${status})`;
                    let simProgress = 30 + (attempts * 1);
                    if(simProgress > 95) simProgress = 95;
                    AiTaskManager.updateStatus(status, simProgress);
                }
            } catch (e) {
                console.error("Polling error:", e);
                AiTaskManager.log(`Polling network error: ${e.message}`, "warning");
            }
        }, 2000);
    }
};

// --- Converter Logic ---
window.ConverterManager = {
    open() {
        ToolboxManager.closeMenu();
        const modal = document.getElementById('converter-modal');
        if (modal) modal.style.display = 'flex';
    },
    close() {
        document.getElementById('converter-modal').style.display = 'none';
    },
    updateUI(mode) {
        document.getElementById('conv-upload-wrapper').style.display = mode === 'file' ? 'block' : 'none';
        document.getElementById('conv-input-wrapper').style.display = mode === 'text' ? 'block' : 'none';
        document.getElementById('converter-result').style.display = 'none';
    },
    processText() {
        const type = document.getElementById('conv-cat-text').value;
        const input = document.getElementById('conv-input-text').value;
        const output = document.getElementById('convert-output');
        
        if(!input) return;
        
        try {
            const handler = this.handlers[type] || ((s) => s);
            output.value = handler(input);
            document.getElementById('converter-result').style.display = 'block';
            document.getElementById('result-info').innerText = "转换完成";
        } catch(e) {
            alert("格式有误，处理失败: " + e.message);
        }
    },
    copyResult() {
        const out = document.getElementById('convert-output');
        out.select();
        document.execCommand('copy');
        alert("已复制到剪贴板");
    },
    handlers: {
        'json-prettify': (s) => JSON.stringify(JSON.parse(s), null, 4),
        'json2yaml': (s) => {
            try { return jsyaml.dump(JSON.parse(s)); } 
            catch(e) { return JSON.stringify(jsyaml.load(s), null, 4); }
        },
        'md2html': (s) => marked.parse(s),
        'unix-time': (s) => {
            if (!isNaN(s) && s.length > 5) {
                const d = new Date(parseInt(s) * (s.length === 10 ? 1000 : 1));
                return d.toLocaleString();
            }
            return Math.floor(new Date(s).getTime() / 1000).toString();
        },
        'case-convert': (s) => {
            if (s.includes('_')) return s.replace(/_([a-z])/g, m => m[1].toUpperCase());
            return s.replace(/([A-Z])/g, "_$1").toLowerCase().replace(/^_/, "");
        },
        'word-count': (s) => {
            return `字符数: ${s.length}\n汉字: ${(s.match(/[\u4e00-\u9fa5]/g) || []).length}\n行数: ${s.split('\n').length}`;
        },
        'color-convert': (s) => {
            if(s.startsWith('#')) {
                const r = parseInt(s.slice(1,3), 16), g = parseInt(s.slice(3,5), 16), b = parseInt(s.slice(5,7), 16);
                return `rgb(${r}, ${g}, ${b})`;
            }
            return "请尝试输入 #002FA7";
        },
        'url-codec': (s) => { try { return decodeURIComponent(s); } catch { return encodeURIComponent(s); } },
        'base64-text': (s) => { try { return atob(s); } catch { return btoa(s); } }
    }
};

// File Input Event Listener
document.getElementById('file-input')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const type = document.getElementById('conv-cat-file').value;
    if(!file) return;

    const reader = new FileReader();
    const resultArea = document.getElementById('converter-result');
    const output = document.getElementById('convert-output');
    const downloadBtn = document.getElementById('download-btn');

    if (type.startsWith('img')) {
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                
                if(type === 'img-gray') ctx.filter = 'grayscale(100%)';
                ctx.drawImage(img, 0, 0);
                
                const format = type === 'img-format' ? 'image/jpeg' : 'image/png';
                const dataUrl = canvas.toDataURL(format);
                
                output.value = dataUrl.substring(0, 100) + "... (Base64 data truncated)";
                resultArea.style.display = 'block';
                
                downloadBtn.style.display = 'flex';
                downloadBtn.onclick = () => {
                    const a = document.createElement('a');
                    a.href = dataUrl;
                    a.download = "converted_" + file.name.replace(/\.[^/.]+$/, "") + (type === 'img-format' ? '.jpg' : '.png');
                    a.click();
                };
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    } 
    else if (type === 'docx2pdf') {
        alert("DOCX 预览/转换功能需要后端支持，目前仅作演示。请在弹出的打印窗口中选择「另存为 PDF」");
        window.print(); 
    }
});

// --- WebTorrent Manager (New) ---
window.WebTorrentManager = {
    client: null,
    torrents: [],
    updateInterval: null,

    init() {
        if (this.client) return;
        
        if (typeof WebTorrent === 'undefined') {
            console.error("WebTorrent library not loaded.");
            alert("WebTorrent 组件加载失败，请检查网络或本地依赖。");
            return;
        }

        this.client = new WebTorrent();
        console.log("[WebTorrent] Client initialized.");
        
        this.client.on('error', (err) => {
            console.error('[WebTorrent] Error:', err);
            alert("WebTorrent 错误: " + err.message);
        });

        // Drag & Drop Support
        const dropZone = document.getElementById('wt-drop-zone');
        if (dropZone) {
            dropZone.ondragover = (e) => { e.preventDefault(); dropZone.classList.add('active'); };
            dropZone.ondragleave = (e) => { e.preventDefault(); dropZone.classList.remove('active'); };
            dropZone.ondrop = (e) => {
                e.preventDefault();
                dropZone.classList.remove('active');
                this.handleFiles(e.dataTransfer.files);
            };
        }
        
        const fileInput = document.getElementById('wt-file-input');
        if (fileInput) {
            fileInput.onchange = (e) => this.handleFiles(e.target.files);
        }

        this.startStatsUpdater();
    },

    handleFiles(files) {
        if (!files || files.length === 0) return;
        Array.from(files).forEach(file => {
            console.log("[WebTorrent] Seeding file:", file.name);
            this.client.seed(file, (torrent) => this.onTorrentAdded(torrent));
        });
    },

    addTorrentFromInput() {
        const input = document.getElementById('wt-magnet-input');
        const id = input.value.trim();
        if (!id) return alert("请输入 Magnet 链接或 InfoHash");
        
        console.log("[WebTorrent] Adding torrent:", id);
        this.client.add(id, (torrent) => this.onTorrentAdded(torrent));
        input.value = "";
    },

    onTorrentAdded(torrent) {
        console.log("[WebTorrent] Torrent added:", torrent.infoHash);
        this.torrents.push(torrent);
        this.renderTorrentList();
        
        torrent.on('done', () => {
            console.log('[WebTorrent] Torrent done:', torrent.name);
            this.renderTorrentList();
        });
        
        torrent.on('download', () => {
            // Optional: Throttle updates to avoid UI lag
        });
    },

    renderTorrentList() {
        const list = document.getElementById('wt-torrent-list');
        if (!list) return;
        
        if (this.torrents.length === 0) {
            list.innerHTML = `
                <div style="text-align: center; color: var(--text-secondary); margin-top: 50px;">
                    <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>暂无传输任务</p>
                </div>`;
            return;
        }

        list.innerHTML = "";
        this.torrents.forEach(torrent => {
            const el = document.createElement('div');
            el.className = 'wt-item';
            el.style.cssText = "background: var(--bg-primary); padding: 10px; border-radius: 6px; margin-bottom: 10px;";
            
            const progress = (torrent.progress * 100).toFixed(1);
            const downloaded = this.formatBytes(torrent.downloaded);
            const speed = this.formatBytes(torrent.downloadSpeed) + '/s';
            const peers = torrent.numPeers;
            
            el.innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom: 5px;">
                    <span style="font-weight:bold; max-width: 70%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        ${torrent.name || '获取元数据中...'}
                    </span>
                    <span style="color:var(--text-secondary); font-size: 11px;">
                        ${this.formatBytes(torrent.length)}
                    </span>
                </div>
                <div class="ai-progress-bar" style="height: 6px; margin-bottom: 5px; background: #333; border-radius: 3px; overflow:hidden;">
                    <div class="ai-progress-fill" style="width: ${progress}%; background: var(--main-color-primary); height: 100%;"></div>
                </div>
                <div style="display:flex; justify-content:space-between; font-size: 11px; color:var(--text-secondary);">
                    <span>
                        ${progress === '100.0' ? '<i class="fas fa-check-circle" style="color:#2ecc71"></i> 完成' : `<i class="fas fa-spinner fa-spin"></i> ${progress}%`} 
                        | ↓ ${speed} | ${peers} 节点
                    </span>
                    <button onclick="WebTorrentManager.removeTorrent('${torrent.infoHash}')" style="border:none; background:none; color: #e74c3c; cursor: pointer;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                ${progress === '100.0' ? `<div style="margin-top:5px; text-align:right;">
                    <button onclick="WebTorrentManager.saveFile('${torrent.infoHash}')" style="font-size:11px; padding: 2px 8px; cursor:pointer;">保存文件</button>
                </div>` : ''}
            `;
            list.appendChild(el);
        });
    },
    
    removeTorrent(infoHash) {
        const t = this.torrents.find(t => t.infoHash === infoHash);
        if (t) {
            t.destroy();
            this.torrents = this.torrents.filter(t => t.infoHash !== infoHash);
            this.renderTorrentList();
        }
    },
    
    saveFile(infoHash) {
        const torrent = this.torrents.find(t => t.infoHash === infoHash);
        if (torrent) {
            torrent.files.forEach(file => {
                file.getBlobURL((err, url) => {
                    if (err) return alert(err.message);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = file.name;
                    a.click();
                });
            });
        }
    },

    startStatsUpdater() {
        if (this.updateInterval) clearInterval(this.updateInterval);
        this.updateInterval = setInterval(() => {
            if (!this.client) return;
            
            // Update global stats
            document.getElementById('wt-down-speed').innerText = this.formatBytes(this.client.downloadSpeed) + '/s';
            document.getElementById('wt-up-speed').innerText = this.formatBytes(this.client.uploadSpeed) + '/s';
            document.getElementById('wt-ratio').innerText = this.client.ratio.toFixed(2);
            
            // Calculate total peers
            let totalPeers = 0;
            this.torrents.forEach(t => totalPeers += t.numPeers);
            document.getElementById('wt-num-peers').innerText = totalPeers;
            
            // Re-render list to update progress bars
            if (this.torrents.length > 0) this.renderTorrentList();
            
        }, 1000);
    },

    formatBytes(bytes, decimals = 2) {
        if (!+bytes) return '0 B';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
    }
};

/**
 * V86Manager - x86 Emulator Logic
 */
window.V86Manager = {
    emulator: null,
    isStarted: false,
    
    init() {
        // Initialization if needed
    },

    updateOSInfo() {
        const select = document.getElementById('v86-os-select');
        const upload = document.getElementById('v86-custom-upload');
        if (select && upload) {
            upload.style.display = (select.value === 'custom') ? 'block' : 'none';
        }
    },

    async start() {
        if (this.emulator) {
            this.emulator.destroy();
        }

        const os = document.getElementById('v86-os-select').value;
        const btn = document.getElementById('v86-start-btn');
        const loading = document.getElementById('v86-loading');
        
        btn.disabled = true;
        loading.style.display = 'flex';

        // Check if v86 library is loaded, if not try to load it dynamically
        let V86Class = window.V86Starter || window.V86;
        if (!V86Class) {
            console.log("[V86Manager] V86 library not found in window, attempting to load from:", CONFIG.paths.v86Lib);
            try {
                await this.loadScript(CONFIG.paths.v86Lib);
                V86Class = window.V86Starter || window.V86;
                if (V86Class) {
                    console.log("[V86Manager] libv86.js loaded successfully. Class found:", window.V86Starter ? "V86Starter" : "V86");
                }
            } catch (e) {
                console.error("[V86Manager] Failed to load libv86.js:", e);
                alert("无法加载 v86 核心库，请检查网络连接或本地文件。");
                btn.disabled = false;
                loading.style.display = 'none';
                return;
            }
        }

        if (!V86Class) {
            console.error("[V86Manager] Script loaded but V86Starter/V86 not found in window.");
            alert("v86 库已加载但未正确初始化，请尝试刷新页面。");
            btn.disabled = false;
            loading.style.display = 'none';
            return;
        }

        // Base settings - using local if available, else remote
        const screenContainer = document.getElementById("v86-screen");
        
        if (!screenContainer) {
             console.error("[V86Manager] screen_container (#v86-screen) not found!");
             alert("初始化失败: 找不到屏幕容器元素。");
             btn.disabled = false;
             loading.style.display = 'none';
             return;
        }

        let settings = {
            wasm_path: CONFIG.paths.v86Wasm,
            memory_size: 128 * 1024 * 1024,
            vga_memory_size: 8 * 1024 * 1024,
            screen_container: screenContainer,
            bios: { url: CONFIG.paths.v86Bios },
            vga_bios: { url: CONFIG.paths.v86VgaBios },
            autostart: true,
            disable_keyboard: false // Enable keyboard
        };

        // Configure OS images
        if (os === 'linux') {
            // Use buildroot from copy.sh main domain
            settings.bzimage = { url: "https://copy.sh/v86/images/buildroot-bzimage.bin" };
            settings.initrd = { url: "https://copy.sh/v86/images/rootfs.cpio.gz" };
            settings.cmdline = "rw root=/dev/ram0 console=ttyS0 quiet";
        } else if (os === 'kolibrios') {
            settings.fda = { url: "https://copy.sh/v86/images/kolibri.img" };
        } else if (os === 'freedos') {
            settings.fda = { url: "https://copy.sh/v86/images/freedos722.img" };
        } else if (os === 'custom') {
            const file = document.getElementById('v86-file-input').files[0];
            if (!file) {
                alert("请先选择一个镜像文件！");
                btn.disabled = false;
                loading.style.display = 'none';
                return;
            }
            settings.fda = { buffer: await file.arrayBuffer() };
        }

        // Pre-flight check for critical resources
        const checkResource = async (url) => {
            try {
                const response = await fetch(url, { method: 'GET' }); // Use GET to check content type
                console.log(`[V86Manager] Resource check: ${url}`);
                console.log(` - Status: ${response.status} (${response.statusText})`);
                console.log(` - Content-Type: ${response.headers.get('Content-Type')}`);
                console.log(` - Content-Length: ${response.headers.get('Content-Length')}`);
                
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                
                // If it's the WASM file, check if it's actually WASM
                if (url.endsWith('.wasm') && response.headers.get('Content-Type') !== 'application/wasm') {
                    console.warn(`[V86Manager] Warning: ${url} has incorrect MIME type: ${response.headers.get('Content-Type')}. Should be application/wasm`);
                }
                
                return true;
            } catch (e) {
                console.error(`[V86Manager] Resource check FAILED: ${url}`, e);
                return false;
            }
        };

        if (CONFIG.flags.isV86Local) {
            await checkResource(CONFIG.paths.v86Wasm);
            await checkResource(CONFIG.paths.v86Bios);
        } else {
            // Check remote resources if not local
            if (os === 'linux') {
                await checkResource("https://copy.sh/v86/images/buildroot-bzimage.bin");
                await checkResource("https://copy.sh/v86/images/rootfs.cpio.gz");
            }
        }

        try {
            console.log("[V86Manager] Initializing emulator with settings:", settings);
            this.emulator = new V86Class(settings);
            
            // Listen for internal errors from the emulator
            this.emulator.add_listener("emulator-ready", () => {
                console.log("[V86Manager] Emulator is ready.");
                loading.style.display = 'none';
            });

            this.emulator.add_listener("download-error", (e) => {
                console.error("[V86Manager] Download error:", e);
                alert("资源下载失败，请检查网络连接。详情请查看控制台。");
                btn.disabled = false;
                loading.style.display = 'none';
            });

            this.isStarted = true;
            
            // Add a safety timeout to hide loading if it takes too long
            setTimeout(() => {
                if (loading.style.display !== 'none') {
                    console.log("[V86Manager] Auto-hiding loading screen after timeout.");
                    loading.style.display = 'none';
                }
            }, 10000);

            console.log("[V86Manager] Emulator instance created.");
        } catch (err) {
            console.error("[V86Manager] Initialization sync error:", err);
            alert("虚拟机初始化失败: " + err.message);
            btn.disabled = false;
            loading.style.display = 'none';
        }
    },

    restart() {
        if (this.emulator) {
            this.emulator.restart();
        }
    },

    loadScript(url) {
        return new Promise((resolve, reject) => {
            // Handle AMD/RequireJS and CommonJS conflict (like Monaco's loader)
            const oldDefine = window.define;
            const oldModule = window.module;
            const oldExports = window.exports;

            if (oldDefine && oldDefine.amd) {
                window.define = undefined;
                console.log("[V86Manager] Temporarily disabled AMD define.");
            }
            if (oldModule) {
                window.module = undefined;
                console.log("[V86Manager] Temporarily disabled module exports.");
            }
            if (oldExports) {
                window.exports = undefined;
            }

            const script = document.createElement('script');
            script.src = url;
            script.onload = () => {
                // Restore
                if (oldDefine) window.define = oldDefine;
                if (oldModule) window.module = oldModule;
                if (oldExports) window.exports = oldExports;
                resolve();
            };
            script.onerror = (err) => {
                // Restore
                if (oldDefine) window.define = oldDefine;
                if (oldModule) window.module = oldModule;
                if (oldExports) window.exports = oldExports;
                reject(err);
            };
            document.head.appendChild(script);
        });
    }
};

// --- Legacy Global Bridge (Keep for HTML Onclick Compatibility) ---
let editor, pyodide; // Globals

// IDE
function openOnlineIDE() {
    ToolboxManager.closeMenu();
    document.getElementById('ide-modal').style.display = 'flex';
    ideManager.init().then(() => {
        // editor = ideManager.editor; // No longer used directly
        pyodide = ideManager.pyodide;
    });
}
function closeIDE() { document.getElementById('ide-modal').style.display = 'none'; }
function runCode() { ideManager.run(); }
function initIDE() { return ideManager.init(); }

// Toolbox
function openToolboxMenu() { ToolboxManager.openMenu(); }
function closeToolboxMenu() { ToolboxManager.closeMenu(); }
function showAiSubMenu() { ToolboxManager.showSubMenu(); }
function backToMainMenu() { ToolboxManager.backToMainMenu(); }

// AI Lab
const ChatManager = { // Forward compatibility
    newChat: () => AiChatManager.newChat(),
    toggleHistory: () => AiChatManager.toggleHistoryPanel(),
    exportChat: () => AiChatManager.exportChat(),
    filterHistory: (q) => AiChatManager.filterHistory(q),
    deleteChat: (id, e) => AiChatManager.deleteChat(id, e)
};
const TaskMonitor = { // Forward compatibility
    toggleMonitor: () => AiTaskManager.toggleMonitor()
};
function openAiWindow(type) { AiLabManager.openWindow(type); }
function closeAiWindow() { AiLabManager.closeWindow(); }
function runAiTask() { AiLabManager.runTask(); }
function toggleMonitor() { AiTaskManager.toggleMonitor(); }

// Converter
function openConverterWindow() { ConverterManager.open(); }
function closeConverter() { ConverterManager.close(); }
function updateConvUI(m) { ConverterManager.updateUI(m); }
function processTextConvert() { ConverterManager.processText(); }
function copyConvertResult() { ConverterManager.copyResult(); }

// WebTorrent (New Bridge)
function openWebTorrentWindow() { 
     ToolboxManager.closeMenu(); 
     document.getElementById('webtorrent-modal').style.display = 'flex'; 
     setTimeout(() => WebTorrentManager.init(), 100); 
 }
 function closeWebTorrentWindow() { document.getElementById('webtorrent-modal').style.display = 'none'; }
 
 // CyberChef (New Bridge)
 function openCyberChefWindow() {
     ToolboxManager.closeMenu();
     const modal = document.getElementById('cyberchef-modal');
     const frame = document.getElementById('cyberchef-frame');
     const loading = document.getElementById('cyberchef-loading');
     
     modal.style.display = 'flex';
     if (frame.src === 'about:blank') {
         loading.style.display = 'flex';
         // Use a reliable mirror or the official one
         frame.src = 'https://gchq.github.io/CyberChef/';
     }
 }
 function closeCyberChefWindow() { document.getElementById('cyberchef-modal').style.display = 'none'; }
 function refreshCyberChef() {
     const frame = document.getElementById('cyberchef-frame');
     const loading = document.getElementById('cyberchef-loading');
     loading.style.display = 'flex';
     frame.src = frame.src;
 }

 // --- FFmpeg Manager (New) ---
 window.FfmpegManager = {
     ffmpeg: null,
     inputFile: null,
     outputData: null,
     outputName: '',
 
     async init() {
         if (this.ffmpeg) return;
         
         if (typeof FFmpeg === 'undefined') {
             this.log("Error: FFmpeg.js not loaded.", "error");
             return;
         }
 
         this.log("Initializing FFmpeg.wasm...");
         const { createFFmpeg } = FFmpeg;
         this.ffmpeg = createFFmpeg({ 
            log: true,
            corePath: CONFIG.paths.ffmpegCore,
            logger: ({ message }) => this.log(message)
        });
 
         try {
             await this.ffmpeg.load();
             this.log("FFmpeg.wasm loaded successfully.", "success");
             document.getElementById('ffmpeg-status').innerText = "状态: 就绪";
             
             // Bind file input
             const fileInput = document.getElementById('ffmpeg-file-input');
             fileInput.onchange = (e) => this.handleFile(e.target.files[0]);
             
             // Bind drag & drop
             const dropZone = document.getElementById('ffmpeg-drop-zone');
             dropZone.ondragover = (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--main-color-primary)'; };
             dropZone.ondragleave = (e) => { e.preventDefault(); dropZone.style.borderColor = ''; };
             dropZone.ondrop = (e) => {
                 e.preventDefault();
                 dropZone.style.borderColor = '';
                 this.handleFile(e.dataTransfer.files[0]);
             };
         } catch (err) {
             this.log("Failed to load FFmpeg: " + err.message, "error");
             console.error(err);
         }
     },
 
     handleFile(file) {
         if (!file) return;
         this.inputFile = file;
         document.getElementById('ffmpeg-file-name').innerText = `已选择: ${file.name} (${this.formatBytes(file.size)})`;
         this.log(`File selected: ${file.name}`);
     },
 
     applyPreset() {
         const preset = document.getElementById('ffmpeg-preset').value;
         if (preset) {
             document.getElementById('ffmpeg-command').value = preset;
         }
     },
 
     async run() {
         if (!this.ffmpeg || !this.ffmpeg.isLoaded()) {
             alert("FFmpeg 尚未加载完成，请稍候...");
             return;
         }
         if (!this.inputFile) {
             alert("请先选择输入文件");
             return;
         }
 
         const commandStr = document.getElementById('ffmpeg-command').value.trim();
         if (!commandStr) return alert("请输入命令");
 
         const args = commandStr.split(' ');
         const runBtn = document.getElementById('ffmpeg-run-btn');
         const status = document.getElementById('ffmpeg-status');
         const resultPanel = document.getElementById('ffmpeg-result');
 
         try {
             runBtn.disabled = true;
             runBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 处理中...';
             status.innerText = "状态: 正在处理";
             resultPanel.style.display = 'none';
 
             // Write file to MEMFS
             const { fetchFile } = FFmpeg;
             this.ffmpeg.FS('writeFile', 'input', await fetchFile(this.inputFile));
 
             this.log(`Starting execution: ffmpeg ${commandStr}`);
             
             // Execute
             await this.ffmpeg.run(...args);
 
             // Find output file name from args
             this.outputName = args[args.length - 1];
             const data = this.ffmpeg.FS('readFile', this.outputName);
             this.outputData = data;
 
             // Show Result
             resultPanel.style.display = 'block';
             const video = document.getElementById('ffmpeg-output-video');
             const info = document.getElementById('ffmpeg-output-info');
             
             const url = URL.createObjectURL(new Blob([data.buffer], { type: this.getMimeType(this.outputName) }));
             
             if (this.outputName.match(/\.(mp4|webm|ogg|mov)$/i)) {
                 video.src = url;
                 video.style.display = 'block';
             } else {
                 video.style.display = 'none';
             }
 
             info.innerHTML = `输出文件: <strong>${this.outputName}</strong> (${this.formatBytes(data.length)})`;
             this.log("Processing finished.", "success");
             status.innerText = "状态: 完成";
         } catch (err) {
             this.log("Error during processing: " + err.message, "error");
             status.innerText = "状态: 出错";
         } finally {
             runBtn.disabled = false;
             runBtn.innerHTML = '<i class="fas fa-play"></i> 开始处理 (本地 Wasm 加速)';
         }
     },
 
     download() {
         if (!this.outputData) return;
         const blob = new Blob([this.outputData.buffer], { type: this.getMimeType(this.outputName) });
         const url = URL.createObjectURL(blob);
         const a = document.createElement('a');
         a.href = url;
         a.download = this.outputName;
         a.click();
     },
 
     log(msg, type = 'info') {
         const logs = document.getElementById('ffmpeg-logs');
         if (!logs) return;
         const div = document.createElement('div');
         if (type === 'error') div.style.color = '#ff4757';
         if (type === 'success') div.style.color = '#2ecc71';
         div.innerText = `[${new Date().toLocaleTimeString()}] ${msg}`;
         logs.appendChild(div);
         logs.scrollTop = logs.scrollHeight;
     },
 
     formatBytes(bytes) {
         if (bytes === 0) return '0 B';
         const k = 1024;
         const sizes = ['B', 'KB', 'MB', 'GB'];
         const i = Math.floor(Math.log(bytes) / Math.log(k));
         return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
     },
 
     getMimeType(name) {
         if (name.endsWith('.mp4')) return 'video/mp4';
         if (name.endsWith('.mp3')) return 'audio/mpeg';
         if (name.endsWith('.gif')) return 'image/gif';
         if (name.endsWith('.png')) return 'image/png';
         return 'application/octet-stream';
     }
 };
 
 // FFmpeg (New Bridge)
 function openFfmpegWindow() {
     ToolboxManager.closeMenu();
     document.getElementById('ffmpeg-modal').style.display = 'flex';
     FfmpegManager.init();
 }
 function closeFfmpegWindow() {
    document.getElementById('ffmpeg-modal').style.display = 'none';
}

// v86 (New Bridge)
function openV86Window() {
    ToolboxManager.closeMenu();
    document.getElementById('v86-modal').style.display = 'flex';
    V86Manager.init();
}
function closeV86Window() {
    document.getElementById('v86-modal').style.display = 'none';
    if (V86Manager.emulator) {
        V86Manager.emulator.destroy();
        V86Manager.emulator = null;
    }
}

/**
 * OcrManager - Tesseract.js OCR Logic
 */
window.OcrManager = {
    worker: null,
    isInitialized: false,
    selectedFile: null,

    init() {
        if (this.isInitialized) return;
        
        const dropZone = document.getElementById('ocr-drop-zone');
        const fileInput = document.getElementById('ocr-file-input');

        if (dropZone) {
            dropZone.ondragover = (e) => { e.preventDefault(); dropZone.classList.add('active'); };
            dropZone.ondragleave = (e) => { e.preventDefault(); dropZone.classList.remove('active'); };
            dropZone.ondrop = (e) => {
                e.preventDefault();
                dropZone.classList.remove('active');
                if (e.dataTransfer.files.length > 0) this.handleFile(e.dataTransfer.files[0]);
            };
        }

        if (fileInput) {
            fileInput.onchange = (e) => {
                if (e.target.files.length > 0) this.handleFile(e.target.files[0]);
            };
        }

        this.isInitialized = true;
        console.log("[OcrManager] UI events bound.");
    },

    handleFile(file) {
        if (!file.type.startsWith('image/')) {
            alert("请选择有效的图片文件！");
            return;
        }
        this.selectedFile = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('ocr-preview');
            const prompt = document.getElementById('ocr-upload-prompt');
            preview.src = e.target.result;
            preview.style.display = 'block';
            prompt.style.display = 'none';
        };
        reader.readAsDataURL(file);
    },

    async recognize() {
        if (!this.selectedFile) {
            alert("请先上传图片！");
            return;
        }

        const lang = document.getElementById('ocr-lang').value;
        const btn = document.querySelector('#ocr-modal .ai-submit-btn');
        const progressContainer = document.getElementById('ocr-progress-container');
        const progressFill = document.getElementById('ocr-progress-fill');
        const statusText = document.getElementById('ocr-status-text');
        const percentText = document.getElementById('ocr-percent');
        const resultArea = document.getElementById('ocr-result-text');

        btn.disabled = true;
        progressContainer.style.display = 'block';
        resultArea.value = "正在处理中，请稍候...";

        try {
            statusText.innerText = "正在识别文字...";
            
            // Ensure Tesseract is available
            if (typeof Tesseract === 'undefined') {
                throw new Error("Tesseract.js 库未能在页面中正确加载。");
            }
            
            // Tesseract.recognize is simpler for one-off tasks
            const result = await Tesseract.recognize(
                this.selectedFile,
                lang,
                {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            const p = Math.floor(m.progress * 100);
                            progressFill.style.width = p + '%';
                            percentText.innerText = p + '%';
                            statusText.innerText = "识别进度: " + m.status;
                        } else {
                            statusText.innerText = "状态: " + m.status;
                        }
                    }
                }
            );

            resultArea.value = result.data.text;
            statusText.innerText = "识别完成！";
            progressFill.style.width = '100%';
            percentText.innerText = '100%';

        } catch (err) {
            console.error("[OcrManager] Error:", err);
            resultArea.value = "识别出错: " + err.message;
            statusText.innerText = "错误";
        } finally {
            btn.disabled = false;
        }
    },

    copyResult() {
        const text = document.getElementById('ocr-result-text').value;
        if (!text) return;
        navigator.clipboard.writeText(text).then(() => {
            alert("已复制到剪贴板！");
        });
    },

    loadScript(url) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = url;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }
};

// OCR Bridge
function openOcrWindow() {
    ToolboxManager.closeMenu();
    document.getElementById('ocr-modal').style.display = 'flex';
    OcrManager.init();
}
function closeOcrWindow() {
    document.getElementById('ocr-modal').style.display = 'none';
}

/**
 * RogueGameManager - Cyber Rogue Game Logic
 */
window.RogueGameManager = {
    init() {
        const frame = document.getElementById('rogue-game-frame');
        const loading = document.getElementById('rogue-game-loading');
        const loadingStatus = document.getElementById('loading-status');
        
        // Detect root path
        const isRoot = window.location.pathname.endsWith('/') || window.location.pathname.endsWith('index.php');
        const gamePath = isRoot ? 'includes/easy_rogue_game.php' : '../includes/easy_rogue_game.php';
        
        console.log("[RogueGameManager] Initializing from:", window.location.pathname, "Target:", gamePath);
        
        if (frame.src === 'about:blank' || !frame.src.includes('easy_rogue_game.php')) {
            if (loading) loading.style.display = 'flex';
            if (loadingStatus) loadingStatus.innerText = 'CORE_INIT: OK | CONNECTING_GAME_ENGINE...';
            
            // Set source with timestamp to prevent caching
            frame.src = gamePath + '?t=' + new Date().getTime();
        } else {
             // If already loaded, just focus
             setTimeout(() => {
                try {
                    frame.contentWindow.focus();
                } catch(e) {}
             }, 100);
        }
        
        // Error handling for iframe load
        frame.onerror = (e) => {
            console.error("[RogueGameManager] Iframe load error:", e);
            if (loadingStatus) loadingStatus.innerText = 'ERROR: CONNECTION_FAILED | CHECK_PATH';
            if (loadingStatus) loadingStatus.style.color = '#ff3333';
        };

        // Focus the iframe so keyboard controls work immediately
        frame.onload = () => {
            console.log("[RogueGameManager] Iframe loaded successfully");
            if (loading) loading.style.display = 'none';
            try {
                frame.contentWindow.focus();
                // Try to force focus again after a delay
                setTimeout(() => frame.contentWindow.focus(), 500);
            } catch(e) {
                console.warn("[RogueGameManager] Could not focus iframe:", e);
            }
        };
    },
    
    restart() {
        const frame = document.getElementById('rogue-game-frame');
        if (frame && frame.contentWindow && frame.contentWindow.game) {
            frame.contentWindow.game.restart();
            frame.contentWindow.focus();
        } else {
             // Reload iframe if game object not found
             frame.src = frame.src;
        }
    }
};

// Rogue Game Bridge
function openRogueGameWindow() {
    ToolboxManager.closeMenu();
    document.getElementById('rogue-game-modal').style.display = 'flex';
    RogueGameManager.init();
}
function closeRogueGameWindow() {
    document.getElementById('rogue-game-modal').style.display = 'none';
    // Reset source to stop game loop/audio when closed to save resources
    document.getElementById('rogue-game-frame').src = 'about:blank';
}
