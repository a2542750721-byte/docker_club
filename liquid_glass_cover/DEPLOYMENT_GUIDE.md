# 液态玻璃特效 (Liquid Glass Effect) 部署指南

本文档详细说明了如何将“液态玻璃特效”项目部署到生产环境或本地测试环境。

## 1. 部署前准备

### 1.1 系统环境要求
虽然本项目核心是纯前端代码 (HTML/CSS/JS)，但为了本地开发和预览，建议具备以下环境：
*   **操作系统**: Windows, macOS, 或 Linux
*   **Web 服务器**: 任何静态 Web 服务器 (如 Nginx, Apache, 或 Python SimpleHTTPServer)
*   **Node.js** (可选): 如果使用 `http-server` 进行本地预览，建议版本 >= 14.0.0

### 1.2 项目文件清单
确保你的部署目录包含以下核心文件：
*   `liquid-glass.js` - 核心逻辑脚本
*   `simple-demo.html` - 演示页面 (或你的 `index.html`)
*   `DEPLOYMENT_GUIDE.md` - 本文档

### 1.3 依赖说明
本项目为原生 JavaScript 实现，**无任何第三方运行时依赖**。不需要 `npm install` 即可运行。

---

## 2. 部署步骤

### 2.1 方案 A：静态文件部署 (推荐)
适用于将特效集成到现有的静态网站或 CMS 中。

1.  **上传文件**：
    将 `liquid-glass.js` 上传到你网站的静态资源目录（例如 `/assets/js/`）。

2.  **引入脚本**：
    在你的 HTML 文件底部（`</body>` 标签之前）添加引用：
    ```html
    <script src="/path/to/assets/js/liquid-glass.js"></script>
    ```

3.  **添加触发器**：
    在需要触发特效的按钮上添加点击事件：
    ```html
    <button onclick="toggleLiquidGlass()">开启特效</button>
    ```

### 2.2 方案 B：本地开发预览
如果你想在本地机器上运行演示：

1.  **打开终端**，进入项目根目录：
    ```bash
    cd /path/to/liquid_glass_test
    ```

2.  **启动静态服务器**：
    *   **Python 3**:
        ```bash
        python -m http.server 8000
        ```
    *   **Node.js (http-server)**:
        ```bash
        npx http-server . -p 8000
        ```

3.  **访问地址**：
    打开浏览器访问 `http://localhost:8000/simple-demo.html`

---

## 3. 验证部署

### 3.1 验证步骤
1.  打开部署好的网页。
2.  打开浏览器开发者工具 (F12) -> Console (控制台)。
3.  点击触发按钮。
4.  观察页面是否出现一个半透明的圆形玻璃球。
5.  **拖拽测试**：尝试用鼠标按住玻璃球并拖动，检查是否有折射效果。
6.  再次点击按钮，检查玻璃球是否完全消失。

### 3.2 常见问题排查

| 现象 | 可能原因 | 解决方案 |
| :--- | :--- | :--- |
| **点击按钮无反应** | JS 文件路径错误 | 检查 `<script src="...">` 路径是否正确，确保 Console 无 404 错误。 |
| **报错 `toggleLiquidGlass is not defined`** | JS 未加载或加载顺序错误 | 确保 JS 文件已成功加载，且在按钮点击前已执行完毕。 |
| **玻璃球看不见** | 背景太单一 | 玻璃特效依赖背景折射。请确保页面背景有图案或文字，纯白背景下效果不明显。 |
| **拖拽卡顿** | 性能问题 | 该特效使用大量 SVG 滤镜计算。在低端移动设备上可能会有性能压力。 |

---

## 4. 维护说明

### 4.1 日志解读
脚本会在控制台输出简单的状态日志，用于辅助调试：
*   `Liquid Glass effect created!` - 特效已成功初始化。
*   `Liquid Glass destroyed` - 特效已销毁。

### 4.2 日常维护
*   **文件位置**: 核心逻辑全部位于 `liquid-glass.js`。
*   **参数调整**: 如需修改玻璃球大小或折射强度，请直接编辑 `liquid-glass.js` 中的 `Shader` 类配置。

### 4.3 升级与回滚
*   **升级**: 直接替换 `liquid-glass.js` 文件即可，无需重启服务器。
*   **回滚**: 保留旧版本文件的备份，如遇问题，覆盖回滚即可。

---

**注意**: 本项目使用了较为先进的 SVG 滤镜技术，建议在 Chrome, Edge, Firefox, Safari 的最新版本上运行。IE 浏览器可能不支持。
