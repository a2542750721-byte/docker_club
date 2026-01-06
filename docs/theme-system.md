# 主题系统文档

## 概述
本项目实现了完整的深色/浅色主题切换系统，支持自动检测系统偏好和用户手动切换，并将选择持久化存储。

## 主题变量系统

### 基础颜色变量
- `--bg-primary`: 主背景色
- `--bg-secondary`: 次级背景色
- `--text-primary`: 主要文本色
- `--text-secondary`: 次要文本色
- `--border-color`: 边框颜色
- `--primary-bg`: 主要背景色
- `--secondary-bg`: 次级背景色
- `--primary-text`: 主要文本色
- `--secondary-text`: 次要文本色
- `--hover-bg`: 悬停背景色
- `--active-bg`: 激活背景色
- `--divider-color`: 分割线颜色

### 主题颜色变量
- `--main-color-primary`: 主色调 (克莱因蓝 #002FA7)
- `--main-color-secondary`: 辅助色
- `--main-color-light`: 亮色
- `--main-color-dark`: 深色
- `--klein-blue`: 克莱因蓝色
- `--klein-blue-light`: 克莱因亮蓝色
- `--klein-blue-dark`: 克莱因深蓝色

### 状态颜色变量
- `--success`: 成功状态颜色
- `--danger`: 危险状态颜色
- `--warning`: 警告状态颜色
- `--info`: 信息状态颜色

## 主题切换机制

### 自动检测
1. 优先检查 localStorage 中的用户选择
2. 如果没有用户选择，则检查系统主题偏好 (`prefers-color-scheme`)
3. 如果两者都没有，则使用默认浅色主题

### 持久化存储
- 用户选择的主题会保存在 localStorage 中
- 键名: `theme`
- 可能的值: `dark-mode` 或 `light-mode`

### 过渡动画
- 所有颜色相关的 CSS 属性都应用了 `0.3s` 的平滑过渡效果
- 避免了主题切换时的闪烁和布局抖动

## 组件适配

所有 UI 组件都已适配主题系统：
- 按钮、卡片、导航栏、表单元素等
- 模态框、弹窗等交互组件
- 文本、背景、边框颜色

## 使用方法

### 在 CSS 中使用主题变量
```css
.my-component {
  background-color: var(--bg-primary);
  color: var(--text-primary);
  border-color: var(--border-color);
}
```

### JavaScript 主题切换
```javascript
// 切换主题
document.documentElement.classList.toggle('dark-mode');

// 检查当前主题
const isDarkMode = document.documentElement.classList.contains('dark-mode');

// 设置特定主题
document.documentElement.classList.add('dark-mode'); // 深色
document.documentElement.classList.remove('dark-mode'); // 浅色
```

## 最佳实践

1. **始终使用 CSS 变量**：避免使用硬编码颜色值
2. **组件级适配**：确保所有新组件都支持主题变量
3. **测试不同场景**：在不同系统主题下测试应用
4. **性能优化**：利用 CSS 变量的性能优势