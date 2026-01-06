<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NEON BINDING: SYSTEM FAILURE</title>
    <style>
        :root {
            --bg: #0a0a0c;
            --neon-cyan: #00f3ff;
            --neon-pink: #ff00ff;
            --neon-red: #ff3333;
            --neon-lime: #ccff00;
            --glass: rgba(10, 10, 15, 0.85);
            --border-glow: 0 0 15px rgba(0, 243, 255, 0.4);
        }
        body {
            background-color: #000;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
            overflow: hidden;
            background-image: radial-gradient(circle at center, #1a1a2e 0%, #000 100%);
        }

        #isaac-popup {
            position: relative;
            width: 98vw;
            height: 95vh;
            max-width: 1800px;
            max-height: 1125px;
            aspect-ratio: 16 / 10;
            background: var(--bg);
            border: 2px solid rgba(0, 243, 255, 0.4);
            box-shadow: 0 0 60px rgba(0, 0, 0, 0.9), 0 0 20px rgba(0, 243, 255, 0.2);
            display: flex;
            overflow: hidden;
            border-radius: 8px;
        }

        #crt-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: 
                linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%),
                linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            background-size: 100% 3px, 3px 100%;
            pointer-events: none;
            z-index: 100;
            opacity: 0.6;
            animation: crt-flicker 0.1s infinite;
        }
        #crt-overlay::after {
            content: " ";
            display: block;
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: radial-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 100%),
                        linear-gradient(to bottom, rgba(18, 16, 16, 0) 0%, rgba(18, 16, 16, 0.05) 50%, rgba(18, 16, 16, 0) 100%);
            background-size: 100% 100%, 100% 4px;
            pointer-events: none;
            animation: crt-scanline 8s linear infinite;
        }

        @keyframes crt-scanline {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        @keyframes crt-flicker {
            0% { opacity: 0.55; }
            50% { opacity: 0.6; }
            100% { opacity: 0.58; }
        }

        canvas { image-rendering: pixelated; display: block; width: 100%; height: 100%; }

        #ui-layer {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            display: none;
            justify-content: space-between;
            padding: 50px;
            box-sizing: border-box;
            z-index: 10;
        }

        .stat-panel { 
            background: rgba(10, 10, 15, 0.9);
            padding: 30px;
            border: 2px solid var(--neon-cyan);
            border-left: 8px solid var(--neon-cyan);
            backdrop-filter: blur(20px);
            box-shadow: 0 0 40px rgba(0, 243, 255, 0.3), inset 0 0 20px rgba(0, 243, 255, 0.1);
            min-width: 250px;
            pointer-events: auto;
        }

        .hp-bar { 
            font-size: 42px; 
            color: var(--neon-red); 
            margin-bottom: 15px; 
            filter: drop-shadow(0 0 15px var(--neon-red)); 
            display: flex;
            align-items: center;
            letter-spacing: 5px;
        }

        .hp-heart {
            margin-right: 8px;
            text-shadow: 0 0 20px var(--neon-red), 0 0 40px var(--neon-red);
            transition: all 0.3s ease;
            display: inline-block;
        }
        .hp-heart.pulse {
            animation: heart-pulse 0.5s ease-out;
        }
        @keyframes heart-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1); }
        }
        .shield-bar { font-size: 24px; color: var(--neon-cyan); filter: drop-shadow(0 0 5px var(--neon-cyan)); }
        
        .item-grid { 
            display: grid; 
            grid-template-columns: repeat(5, 40px); 
            gap: 8px; 
            margin-top: 20px;
        }
        .item-icon { 
            width: 40px; height: 40px; 
            background: rgba(255,255,255,0.05); 
            border: 1px solid rgba(255,255,255,0.1); 
            display: flex; align-items: center; justify-content: center; 
            font-size: 12px; color: white; border-radius: 4px;
            transition: 0.3s;
        }

        .devil-choice {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 25px;
            width: 200px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-radius: 8px;
            text-align: center;
        }
        .devil-choice:hover { 
            background: rgba(255, 255, 255, 0.08); 
            transform: translateY(-10px);
            border-color: var(--active-color, var(--neon-lime));
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        #game-over, #dark-web, #item-selection {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: var(--glass);
            border: 1px solid rgba(255,255,255,0.1);
            border-top: 4px solid var(--neon-cyan);
            box-shadow: 0 20px 50px rgba(0,0,0,0.8);
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            color: white;
            z-index: 30;
            backdrop-filter: blur(15px);
            pointer-events: auto;
        }

        .start-content {
            width: 700px;
            border: 2px solid var(--neon-cyan);
            background: rgba(5, 5, 10, 0.95);
            box-shadow: 0 0 150px rgba(0, 243, 255, 0.2), inset 0 0 60px rgba(0, 243, 255, 0.05);
            padding: 60px;
            border-radius: 12px;
            animation: start-entry 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .start-content::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: 
                linear-gradient(rgba(0, 243, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 243, 255, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
            pointer-events: none;
            z-index: -1;
        }

        #start-screen {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 30;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
            transform: none; /* Reset inherited transform */
            padding: 0;
            border: none;
        }

        @keyframes start-entry {
            0% { opacity: 0; transform: translate(-50%, -45%) scale(0.95); }
            100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }

        .btn {
            background: transparent;
            color: var(--neon-cyan);
            border: 2px solid var(--neon-cyan);
            padding: 15px 45px;
            font-family: inherit;
            font-weight: 900;
            cursor: pointer;
            margin-top: 30px;
            text-transform: uppercase;
            letter-spacing: 4px;
            position: relative;
            transition: 0.4s;
            overflow: hidden;
            font-size: 18px;
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.2);
        }

        .btn:hover {
            background: var(--neon-cyan);
            color: #000;
            box-shadow: 0 0 40px var(--neon-cyan);
            transform: scale(1.05);
        }

        .btn:active {
            transform: scale(0.95);
        }
        #game-over, #dark-web, #pause-menu { display: none; width: 400px; }
        #pause-menu { width: 600px; border-top-color: var(--neon-lime); }
        #item-selection { border-top-color: var(--neon-lime); width: 80%; z-index: 40; }

        .pause-item-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
        }
        .pause-item-slot {
            width: 50px; height: 50px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; border-radius: 4px;
            cursor: help;
            position: relative;
        }
        .pause-item-slot:hover { background: rgba(255,255,255,0.1); border-color: var(--neon-cyan); }
        .pause-item-slot:hover::after {
            content: attr(data-desc);
            position: absolute;
            bottom: 100%; left: 50%;
            transform: translateX(-50%);
            background: var(--glass);
            color: white;
            padding: 10px;
            border: 1px solid var(--neon-cyan);
            border-radius: 4px;
            font-size: 12px;
            width: 200px;
            z-index: 100;
            pointer-events: none;
            backdrop-filter: blur(5px);
        }

        .glitch-text { 
            font-size: 64px;
            font-weight: 900;
            text-transform: uppercase;
            position: relative;
            text-shadow: 0.05em 0 0 rgba(255,0,0,.75), -0.025em -.05em 0 rgba(0,255,0,.75), .025em .05em 0 rgba(0,0,255,.75);
            animation: glitch 500ms infinite, neon-pulse 2s ease-in-out infinite;
            letter-spacing: 8px;
            margin-bottom: 10px;
        }

        @keyframes neon-pulse {
            0%, 100% { filter: drop-shadow(0 0 10px var(--neon-cyan)) drop-shadow(0 0 20px var(--neon-cyan)); }
            50% { filter: drop-shadow(0 0 25px var(--neon-cyan)) drop-shadow(0 0 40px var(--neon-cyan)); }
        }

        @keyframes glitch {
            0% { text-shadow: 0.05em 0 0 rgba(255,0,0,.75), -0.05em -.025em 0 rgba(0,255,0,.75), -0.025em .05em 0 rgba(0,0,255,.75); }
            14% { text-shadow: 0.05em 0 0 rgba(255,0,0,.75), -0.05em -.025em 0 rgba(0,255,0,.75), -0.025em .05em 0 rgba(0,0,255,.75); }
            15% { text-shadow: -0.05em -0.025em 0 rgba(255,0,0,.75), 0.025em 0.025em 0 rgba(0,255,0,.75), -0.05em -0.05em 0 rgba(0,0,255,.75); }
            49% { text-shadow: -0.05em -0.025em 0 rgba(255,0,0,.75), 0.025em 0.025em 0 rgba(0,255,0,.75), -0.05em -0.05em 0 rgba(0,0,255,.75); }
            50% { text-shadow: 0.025em 0.05em 0 rgba(255,0,0,.75), 0.05em 0 0 rgba(0,255,0,.75), 0 -0.05em 0 rgba(0,0,255,.75); }
            99% { text-shadow: 0.025em 0.05em 0 rgba(255,0,0,.75), 0.05em 0 0 rgba(0,255,0,.75), 0 -0.05em 0 rgba(0,0,255,.75); }
            100% { text-shadow: -0.025em 0 0 rgba(255,0,0,.75), -0.025em -0.025em 0 rgba(0,255,0,.75), -0.025em -0.05em 0 rgba(0,0,255,.75); }
        }
    </style>
</head>
<body>

<div id="isaac-popup">
    <div id="crt-overlay"></div>
    <canvas id="gameCanvas" width="1600" height="1000"></canvas>
    
    <div id="start-screen">
        <div class="start-content">
            <div style="position:absolute; top:-2px; left:-2px; right:-2px; height:2px; background:linear-gradient(90deg, transparent, var(--neon-cyan), transparent); animation: scan-line-h 2s linear infinite;"></div>
            <div style="position:absolute; bottom:-2px; left:-2px; right:-2px; height:2px; background:linear-gradient(90deg, transparent, var(--neon-cyan), transparent); animation: scan-line-h 2s linear infinite reverse;"></div>
            
            <h1 class="glitch-text" style="color:var(--neon-cyan); margin-bottom: 0;">赛博肉鸽</h1>
            <div style="font-family: monospace; color: var(--neon-pink); font-size: 10px; margin-bottom: 30px; letter-spacing: 5px;">CYBERPUNK ROGUELIKE v2.0.4</div>
            
            <p style="color:var(--neon-lime); margin-bottom: 25px; font-weight: bold; text-shadow: 0 0 10px var(--neon-lime);">[ 系统就绪 - 500+ 高级模组已加载 ]</p>
            
            <div style="text-align: left; font-size: 14px; margin-bottom: 30px; color: #ccc; background: rgba(0,0,0,0.3); padding: 20px; border: 1px solid rgba(0,243,255,0.1); border-radius: 4px;">
                <strong style="color:var(--neon-cyan); display: block; margin-bottom: 10px; border-bottom: 1px solid var(--neon-cyan); width: fit-content; padding-right: 20px;">操作指令集：</strong>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>- <span style="color:var(--neon-cyan)">WASD</span>: 神经链接移动</div>
                    <div>- <span style="color:var(--neon-pink)">MOUSE</span>: 自动锁定射击</div>
                    <div>- <span style="color:var(--neon-lime)">ESC</span>: 模块管理器</div>
                    <div>- <span style="color:var(--neon-red)">HP</span>: 核心稳定性</div>
                </div>
                <br>
                <small style="color:var(--neon-pink); font-style: italic;">* 核心引擎已超频: 1440p / 60FPS / 高保真渲染</small>
            </div>
            <button class="btn" onclick="game.confirmStart()">初始化神经链接</button>
        </div>
    </div>

    <style>
        @keyframes scan-line-h {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>

    <div id="ui-layer">
        <div id="hud" style="position:absolute; top:40px; left:40px; pointer-events:none;">
            <div id="hp-display" class="hp-bar"></div>
            <div id="shield-display" class="shield-bar"></div>
        </div>
    </div>

    <div id="pause-menu" class="modal" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:rgba(5, 5, 10, 0.95); backdrop-filter:blur(20px); padding:50px; border:2px solid var(--neon-cyan); box-shadow: 0 0 50px rgba(0, 243, 255, 0.2); text-align:center; color:white; z-index:50; width:800px; border-radius: 12px;">
        <h2 class="glitch-text" style="font-size: 48px; margin-bottom: 30px;">系统已暂停</h2>
        
        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 40px; text-align: left;">
            <div class="stat-panel" style="background: rgba(255,255,255,0.03); padding: 30px; border: 1px solid var(--neon-cyan); border-radius: 8px;">
                <div style="color:var(--neon-cyan); font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--neon-cyan); padding-bottom: 10px;">>>> 核心状态</div>
                <div style="font-size: 24px; margin-bottom: 15px;">分数: <span id="score-val" style="color:var(--neon-lime)">0</span></div>
                <div style="font-size: 14px; color:#888;">最高分: <span id="hi-score">0</span></div>
                
                <div style="margin-top: 40px;">
                    <button class="btn" onclick="game.ui.togglePause()" style="width: 100%; margin-bottom: 15px;">继续连接 (ESC)</button>
                    <button class="btn" onclick="game.restart()" style="width: 100%; border-color:var(--neon-red); color:var(--neon-red);">重启系统</button>
                </div>
            </div>
            
            <div style="background: rgba(255,255,255,0.03); padding: 30px; border: 1px solid var(--neon-pink); border-radius: 8px;">
                <div style="color:var(--neon-pink); font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--neon-pink); padding-bottom: 10px;">>>> 已安装模块</div>
                <div class="item-grid" id="item-list" style="grid-template-columns: repeat(6, 1fr); gap: 15px;"></div>
            </div>
        </div>
    </div>

    <div id="game-over">
        <h1 class="glitch-text" style="color:var(--neon-red)">系统已终止</h1>
        <p>运行结束。</p>
        <button class="btn" onclick="game.restart()">重启系统</button>
    </div>

    <div id="dark-web">
        <h2 style="color:var(--neon-red)">暗网访问</h2>
        <p>用最大生命值交换禁忌模块？</p>
        <div id="devil-options"></div>
        <button class="btn" style="background:#333; color:#fff" onclick="game.director.closeDevilDeal()">断开连接</button>
    </div>

    <div id="item-selection" style="display:none; position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:var(--glass); backdrop-filter:blur(15px); padding:40px; border:2px solid var(--neon-lime); text-align:center; color:white; z-index:40; width:80%;">
        <h2 style="color:var(--neon-lime)">选择一个模块</h2>
        <div id="item-options" style="display:flex; justify-content:center; gap:20px; margin-top:20px;"></div>
    </div>
</div>

<script>
/**
 * ARCHITECTURE OVERVIEW:
 * 1. Entities: Player, Enemy, Projectile, Splatter.
 * 2. ItemSystem: Database of mods that alter Player stats and Projectile flags.
 * 3. GameDirector: Manages Waves, Loot, Devil Deals, Gameloop.
 */

// --- UTILS ---
const $ = id => document.getElementById(id);
const rand = (min, max) => Math.random() * (max - min) + min;
const checkCol = (a, b) => a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
const dist = (a, b) => Math.sqrt((a.x - b.x)**2 + (a.y - b.y)**2);

// --- ITEM DATABASE (SYNERGY ENGINE 2.0) ---
const ITEMS = {};

const RARITY = {
    COMMON: { color: "#ffffff", weight: 60 },
    RARE: { color: "#00bfff", weight: 25 },
    EPIC: { color: "#a335ee", weight: 10 },
    LEGENDARY: { color: "#ffa500", weight: 4 },
    CURSED: { color: "#ff0000", weight: 1 }
};

function reg(id, name, icon, desc, rarity, apply) {
    ITEMS[id] = { 
        name, icon, desc, 
        rarity, 
        color: RARITY[rarity].color, 
        type: rarity === "CURSED" ? "forbidden" : "passive",
        apply 
    };
}

// --- 1. PROCEDURAL STAT MODULES (100 Items) ---
const STAT_TYPES = [
    { id: "dmg", name: "暴力核心", icon: "▲", desc: "提升武器基础伤害", stat: "damage", val: 1, type: "add" },
    { id: "spd", name: "机动推进", icon: "▶", desc: "提升移动速度", stat: "speedMultiplier", val: 0.05, type: "mult" },
    { id: "hp", name: "纳米装甲", icon: "❤", desc: "提升最大生命值上限", stat: "maxHp", val: 1, type: "add_hp" },
    { id: "fr", name: "超频火控", icon: "⚡", desc: "提升攻击射速", stat: "fireRate", val: -1.5, type: "add" },
    { id: "ss", name: "磁轨加速", icon: "➹", desc: "提升弹丸飞行速度", stat: "shotSpeed", val: 1, type: "add" },
    { id: "crit", name: "弱点透镜", icon: "🎯", desc: "提升暴击几率", stat: "critChance", val: 0.05, type: "add" },
    { id: "cdmg", name: "爆裂弹头", icon: "💥", desc: "提升暴击倍率", stat: "critDmg", val: 0.2, type: "add" },
    { id: "luck", name: "幸运算法", icon: "🍀", desc: "提升幸运触发几率", stat: "luck", val: 0.1, type: "add" },
    { id: "dodge", name: "虚空引擎", icon: "👻", desc: "提升完全闪避几率", stat: "dodge", val: 0.03, type: "add" },
    { id: "thorns", name: "反伤装甲", icon: "🌵", desc: "提升接触反伤", stat: "thorns", val: 1, type: "add" }
];

const ROMAN = ["I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X"];

STAT_TYPES.forEach(s => {
    ROMAN.forEach((lvl, i) => {
        let mult = i + 1;
        let r = i < 3 ? "COMMON" : (i < 6 ? "RARE" : (i < 9 ? "EPIC" : "LEGENDARY"));
        reg(`${s.id}_${i}`, `${s.name} Mk.${lvl}`, s.icon, `${s.desc} +${Math.round(s.val*mult*100)/100}`, r, (p) => {
            if(s.type === "add") p[s.stat] += s.val * mult;
            if(s.type === "mult") p[s.stat] += s.val * mult; // Logic handles multiplier
            if(s.type === "add_hp") { p.maxHp += Math.ceil(s.val * mult * 0.5); p.hp += Math.ceil(s.val * mult * 0.5); game.ui.updateHearts(); }
        });
    });
});

// --- 2. ELEMENTAL MODULES (20 Items) ---
const ELEMENTS = [
    { id: "fire", name: "燃烧", color: "#f50", desc: "攻击附加燃烧效果", flag: "burn" },
    { id: "ice", name: "冰霜", color: "#0ff", desc: "攻击附加减速效果", flag: "freeze" },
    { id: "tox", name: "腐蚀", color: "#0f0", desc: "攻击附加中毒效果", flag: "poison" },
    { id: "elec", name: "电磁", color: "#ff0", desc: "攻击附加连锁闪电", flag: "shock" }
];

ELEMENTS.forEach(e => {
    reg(`${e.id}_1`, `${e.name}组件 I`, "💧", `小幅几率${e.desc}`, "COMMON", p => p.projFlags[e.flag] = true);
    reg(`${e.id}_2`, `${e.name}组件 II`, "💧", `中幅几率${e.desc}`, "RARE", p => p.projFlags[e.flag] = true); // Simplified logic, could add chance
    reg(`${e.id}_3`, `${e.name}组件 III`, "💧", `大幅几率${e.desc}`, "EPIC", p => p.projFlags[e.flag] = true);
    reg(`${e.id}_MAX`, `${e.name}核心`, "🔥", `必定触发${e.desc}`, "LEGENDARY", p => p.projFlags[e.flag] = true);
    reg(`${e.id}_AOE`, `${e.name}新星`, "✴", `击杀触发${e.desc}爆炸`, "EPIC", p => {}); // Placeholder for future logic
});

// --- 3. UNIQUE WEAPON MODS (30 Items) ---
reg("scatter_1", "散射模块 I", "◈", "子弹数量+1, 伤害-10%", "RARE", p => { p.shotCount++; p.damage *= 0.9; });
reg("scatter_2", "散射模块 II", "◈", "子弹数量+2, 伤害-20%", "EPIC", p => { p.shotCount+=2; p.damage *= 0.8; });
reg("scatter_3", "散射模块 MAX", "◈", "子弹数量+4, 伤害-40%", "LEGENDARY", p => { p.shotCount+=4; p.damage *= 0.6; });

reg("pierce_1", "穿甲弹 I", "➢", "子弹可穿透1个敌人", "RARE", p => p.projFlags.pierce = true);
reg("pierce_2", "穿甲弹 II", "➢", "子弹可穿透所有敌人", "EPIC", p => p.projFlags.pierce = true);

reg("bounce_1", "弹射协议 I", "⤣", "子弹反弹1次", "RARE", p => p.projFlags.bounce = true);
reg("bounce_2", "弹射协议 II", "⤣", "子弹反弹3次", "EPIC", p => p.projFlags.bounce = true);

reg("homing_1", "追踪芯片", "⦿", "子弹获得追踪能力", "EPIC", p => p.projFlags.homing = true);
reg("split", "分裂弹头", "☍", "击中敌人后分裂", "EPIC", p => {}); // Logic needed in Projectile

reg("sniper", "狙击镜", "🔭", "射程+100%, 射速-50%, 伤害+100%", "EPIC", p => { p.shotSpeed *= 2; p.fireRate *= 2; p.damage *= 2; });
reg("minigun", "转管机枪", "🔫", "射速+300%, 伤害-60%, 精度降低", "LEGENDARY", p => { p.fireRate = Math.max(2, p.fireRate / 4); p.damage *= 0.4; });

reg("shotgun", "霰弹枪管", "🎉", "子弹+5, 射程降低", "EPIC", p => { p.shotCount += 5; p.shotSpeed *= 0.7; });

// --- 4. ORBITALS & SUMMONS (10 Items) ---
reg("orb_atk", "攻击无人机", "🛸", "环绕并攻击敌人", "RARE", p => game.orbitals.push(new Orbital(p, "attack")));
reg("orb_def", "护盾无人机", "🛡", "环绕并阻挡伤害", "RARE", p => game.orbitals.push(new Orbital(p, "shield")));
reg("orb_bomb", "自爆无人机", "💣", "接触敌人爆炸", "EPIC", p => game.orbitals.push(new Orbital(p, "bomb")));

// --- 5. CURSED ITEMS (10 Items) ---
reg("glass_canon", "玻璃大炮", "☠", "伤害+200%, 生命上限锁定为1", "CURSED", p => { p.damage *= 3; p.maxHp = 1; p.hp = 1; });
reg("blood_pact", "鲜血契约", "🩸", "每杀10个敌人恢复1HP, 受伤双倍", "CURSED", p => { p.lifesteal = 0.1; }); // Logic simplified
reg("heavy", "重型装甲", "🐘", "生命+5, 移速-50%", "CURSED", p => { p.maxHp += 5; p.hp += 5; p.speedMultiplier *= 0.5; });

// --- 6. LEGENDARY TRANSFORMATIONS (10 Items) ---
reg("laser", "硫磺射线", "⚡", "发射贯穿激光", "LEGENDARY", p => { p.weaponType = "laser"; p.fireRate = 40; });
reg("ring", "科技光环", "◎", "发射能量光环", "LEGENDARY", p => { p.weaponType = "ring"; p.fireRate = 35; });
reg("godhead", "神性", "👁", "子弹获得光环与追踪", "LEGENDARY", p => { p.projFlags.homing = true; p.damage += 10; });

// --- 8. ELITE WEAPON MODS (10 Items) ---
reg("railgun", "电磁轨道炮", "━", "伤害+500%, 子弹速度+300%, 射速-80%", "LEGENDARY", p => { p.damage *= 6; p.shotSpeed *= 4; p.fireRate *= 5; });
reg("plasma_nuke", "等离子核弹", "☢", "子弹击中产生巨大爆炸, 射速-50%", "LEGENDARY", p => { p.projFlags.explode = true; p.fireRate *= 2; p.damage *= 1.5; });
reg("echo_fire", "回响击发", "⫶", "所有攻击会延迟触发第二次伤害", "EPIC", p => { p.projFlags.echo = true; });
reg("nanite_swarm", "纳米虫群", "⑄", "击中敌人有几率将其转化为临时友军", "LEGENDARY", p => { p.projFlags.charm = true; });
reg("void_ammo", "虚空弹头", "⚛", "子弹无视障碍并概率直接斩杀低血量敌人", "LEGENDARY", p => { p.projFlags.pierce = true; p.projFlags.void = true; });
reg("chain_reaction", "连锁反应", "⛓", "击杀敌人时产生连锁闪电伤害周围目标", "EPIC", p => { p.projFlags.chainKill = true; });
reg("overclock_v2", "超频插件 V2", "⚡", "射速翻倍, 暴击几率+25%", "LEGENDARY", p => { p.fireRate /= 2; p.critChance += 0.25; });
reg("smart_cluster", "智能集束弹", "⚄", "子弹分裂为追踪小弹头", "LEGENDARY", p => { p.projFlags.split = true; p.projFlags.homing = true; });
reg("gravity_well", "重力井", "🕳", "子弹落点产生吸引敌人的黑洞", "EPIC", p => { p.projFlags.gravity = true; });
reg("cyber_blade", "赛博利刃", "⚔", "近距离伤害+300%, 射程大幅降低", "EPIC", p => { p.damage *= 4; p.shotSpeed *= 0.3; });

// --- 9. ADVANCED CURSED ITEMS (5 Items) ---
reg("devil_eye", "恶魔之眼", "👁", "伤害+100%, 运气大幅下降", "CURSED", p => { p.damage *= 2; p.luck -= 0.5; });
reg("soulless_core", "无魂核心", "💀", "获得10点护盾, 但生命上限锁定为1", "CURSED", p => { p.shield += 10; p.maxHp = 1; p.hp = 1; });
reg("time_leak", "时空泄露", "⌛", "移速+200%, 敌人速度+50%", "CURSED", p => { p.speedMultiplier *= 3; }); 
reg("glitch_power", "错误力量", "⍰", "所有属性随机波动 (-50% 到 +200%)", "CURSED", p => { 
    p.damage *= rand(0.5, 3); p.fireRate *= rand(0.5, 2); p.speedMultiplier *= rand(0.5, 2); 
});
reg("chaos_engine", "混沌引擎", "🌀", "攻击方向完全随机, 但伤害+500%", "CURSED", p => { p.damage *= 6; p.projFlags.randomDir = true; });

// --- 7. HYBRID MODULES (Generated 40 Items) ---
    for(let i=0; i<40; i++) {
        let s1 = STAT_TYPES[Math.floor(Math.random() * STAT_TYPES.length)];
        let s2 = STAT_TYPES[Math.floor(Math.random() * STAT_TYPES.length)];
        if(s1 !== s2) {
            reg(`hyb_${i}`, `混合模块 ${s1.id.toUpperCase()}-${s2.id.toUpperCase()}`, "⚯", `${s1.desc} & ${s2.desc}`, "RARE", p => {
                p[s1.stat] += s1.val;
                p[s2.stat] += s2.val;
            });
        }
    }

function getRandomItemKey(filterFn) {
    const pool = Object.keys(ITEMS).filter(k => filterFn ? filterFn(ITEMS[k], k) : true);
    if(pool.length === 0) return null;
    
    let totalWeight = 0;
    pool.forEach(k => {
        let r = ITEMS[k].rarity;
        totalWeight += (RARITY[r] ? RARITY[r].weight : 10);
    });
    
    let rnd = Math.random() * totalWeight;
    for(let k of pool) {
        let r = ITEMS[k].rarity;
        let w = (RARITY[r] ? RARITY[r].weight : 10);
        if(rnd < w) return k;
        rnd -= w;
    }
    return pool[0];
}

// --- 10. EXPANSION PACK (50 NEW ITEMS) ---
// [A. 属性流 - 极致攻防]
reg("exp_glass_dagger", "玻璃匕首", "🗡️", "伤害+3, 生命上限-2", "RARE", p => { p.damage += 3; p.maxHp -= 2; if(p.maxHp < 1) p.maxHp = 1; p.hp = Math.min(p.hp, p.maxHp); game.ui.updateHearts(); });
reg("exp_titan_plate", "泰坦装甲", "🛡️", "生命上限+5, 移速-30%", "EPIC", p => { p.maxHp += 5; p.hp += 5; p.speedMultiplier *= 0.7; game.ui.updateHearts(); });
reg("exp_hyper_accel", "超光速引擎", "⏩", "移速+80%, 护盾清零", "RARE", p => { p.speedMultiplier *= 1.8; p.shield = 0; game.ui.updateShields(); });
reg("exp_heavy_barrel", "重型枪管", "🧱", "伤害+2, 射速-20%", "COMMON", p => { p.damage += 2; p.fireRate *= 1.2; });
reg("exp_light_trigger", "轻量扳机", "🤏", "射速+30%, 伤害-10%", "COMMON", p => { p.fireRate *= 0.7; p.damage *= 0.9; });
reg("exp_sniper_scope", "精密瞄具", "🔭", "暴击率+20%, 射速-10%", "RARE", p => { p.critChance += 0.2; p.fireRate *= 1.1; });
reg("exp_lucky_coin", "古旧硬币", "🪙", "幸运+2, 暴击伤害+0.5", "RARE", p => { p.luck += 2; p.critDmg += 0.5; });
reg("exp_thorn_mail", "荆棘锁甲", "🌵", "反伤+3, 受伤获得短暂无敌", "RARE", p => { p.thorns += 3; });
reg("exp_vampire_fang", "吸血鬼之牙", "🧛", "吸血几率+5%, 治疗效果减半(未实现)", "EPIC", p => { p.lifesteal += 0.05; });
reg("exp_ninja_tabi", "忍者足具", "👟", "闪避+10%, 移速+10%", "EPIC", p => { p.dodge += 0.1; p.speedMultiplier += 0.1; });

// [B. 子弹变异 - 物理与形态]
reg("exp_wave_beam", "波浪光束", "〰️", "子弹获得强力击退 (通过增加伤害模拟)", "RARE", p => { p.damage *= 1.2; p.shotSize += 2; });
reg("exp_ghost_ammo", "幽灵弹药", "👻", "子弹获得虚空与穿透属性", "EPIC", p => { p.projFlags.void = true; p.projFlags.pierce = true; });
reg("exp_cluster_bomb", "集束炸弹", "💣", "爆炸+分裂 (需配合逻辑)", "LEGENDARY", p => { p.projFlags.explode = true; p.shotCount += 2; p.damage *= 0.8; });
reg("exp_tesla_coil", "特斯拉线圈", "⚡", "电击+连锁伤害", "EPIC", p => { p.projFlags.shock = true; p.projFlags.chainKill = true; });
reg("exp_frost_nova", "冰霜新星", "❄️", "冰冻+反弹", "RARE", p => { p.projFlags.freeze = true; p.projFlags.bounce = true; });
reg("exp_magma_core", "熔岩核心", "🌋", "燃烧+爆炸", "EPIC", p => { p.projFlags.burn = true; p.projFlags.explode = true; });
reg("exp_void_gaze", "虚空凝视", "👁️", "追踪+斩杀", "LEGENDARY", p => { p.projFlags.homing = true; p.projFlags.void = true; });
reg("exp_charm_shot", "魅惑射击", "💕", "魅惑+穿透", "EPIC", p => { p.projFlags.charm = true; p.projFlags.pierce = true; });
reg("exp_gravity_well", "奇点发生器", "⚫", "重力+减速", "EPIC", p => { p.projFlags.gravity = true; p.projFlags.freeze = true; });
reg("exp_echo_round", "回响弹", "🔊", "回响+暴击", "RARE", p => { p.projFlags.echo = true; p.critChance += 0.1; });

// [C. 协同效应 - 轨道与召唤]
reg("exp_orb_saw", "轨道锯片", "⚙️", "高频近战伤害", "RARE", p => { let o = new Orbital(p, "attack"); o.dist = 40; o.speed = 0.1; o.damage = 2; game.orbitals.push(o); });
reg("exp_orb_sat", "远程卫星", "📡", "远距离打击", "RARE", p => { let o = new Orbital(p, "attack"); o.dist = 120; o.damage = 8; o.color = "#0f0"; game.orbitals.push(o); });
reg("exp_orb_shield_mk2", "强化护盾仪", "🛡️", "双层护盾无人机", "EPIC", p => { game.orbitals.push(new Orbital(p, "shield")); game.orbitals.push(new Orbital(p, "shield")); });
reg("exp_orb_bomber", "自爆蜂群", "🐝", "3个自爆无人机", "LEGENDARY", p => { for(let i=0;i<3;i++) game.orbitals.push(new Orbital(p, "bomb")); });
reg("exp_orb_laser", "激光浮游炮", "🔫", "极高伤害慢速环绕", "EPIC", p => { let o = new Orbital(p, "attack"); o.damage = 20; o.speed = 0.02; o.color = "#f00"; o.size = 15; game.orbitals.push(o); });
reg("exp_orb_lazy", "懒惰守护者", "🐢", "极慢速，巨大碰撞体积", "RARE", p => { let o = new Orbital(p, "standard"); o.speed = 0.01; o.size = 30; o.damage = 10; game.orbitals.push(o); });
reg("exp_orb_neutron", "中子星", "⚛️", "极快速，小体积", "RARE", p => { let o = new Orbital(p, "attack"); o.speed = 0.2; o.size = 5; o.dist = 50; game.orbitals.push(o); });
reg("exp_summon_swarm", "纳米虫群", "🦟", "赋予魅惑攻击", "EPIC", p => { p.projFlags.charm = true; p.shotCount += 1; });
reg("exp_turret_kit", "哨塔套件", "🏗️", "射速+50%，但移速-50%", "RARE", p => { p.fireRate *= 0.5; p.speedMultiplier *= 0.5; });
reg("exp_drone_commander", "无人机指挥官", "👑", "所有轨道物伤害提升(需逻辑支持，此处仅加轨道物)", "LEGENDARY", p => { game.orbitals.push(new Orbital(p, "attack")); game.orbitals.push(new Orbital(p, "shield")); });

// [D. 机制创新 - 资源转化与特殊效果]
reg("exp_blood_rage", "鲜血狂怒", "🩸", "现有生命值减半，伤害翻倍", "CURSED", p => { p.hp = Math.ceil(p.hp / 2); p.damage *= 2; game.ui.updateHearts(); });
reg("exp_shield_converter", "能量转化", "🔋", "消耗所有护盾，每个护盾+1伤害", "RARE", p => { p.damage += p.shield; p.shield = 0; game.ui.updateShields(); });
reg("exp_life_battery", "生命电池", "🏥", "生命上限翻倍，但射速减半", "EPIC", p => { p.maxHp *= 2; p.hp = p.maxHp; p.fireRate *= 2; game.ui.updateHearts(); });
reg("exp_glass_cannon_mk2", "究极玻璃炮", "☠️", "伤害+500%，生命锁定1，无敌时间消失", "CURSED", p => { p.damage += 15; p.maxHp = 1; p.hp = 1; p.invuln = 0; game.ui.updateHearts(); });
reg("exp_gambler_dice", "命运骰子", "🎲", "随机改变所有属性 (±20%)", "RARE", p => { 
    p.damage *= rand(0.8, 1.2); p.fireRate *= rand(0.8, 1.2); p.speedMultiplier *= rand(0.8, 1.2); 
    game.ui.showMessage("命运已改写");
});
reg("exp_medkit", "急救包", "💊", "恢复所有生命值", "COMMON", p => { p.hp = p.maxHp; game.ui.updateHearts(); });
reg("exp_shield_pack", "护盾补给", "📦", "获得3个护盾", "COMMON", p => { p.shield += 3; game.ui.updateShields(); });
reg("exp_purifier", "净化者", "✨", "清除所有负面状态(未实现)，全属性+5%", "RARE", p => { p.damage *= 1.05; p.speedMultiplier *= 1.05; });
reg("exp_overclock_cpu", "CPU超频", "💻", "游戏速度加快(射速移速提升)，受伤增加", "EPIC", p => { p.fireRate *= 0.7; p.speedMultiplier *= 1.3; p.maxHp -= 1; game.ui.updateHearts(); });
reg("exp_recycler", "回收站", "♻️", "射速降低，但每击杀敌人回盾(需逻辑，此处加盾)", "RARE", p => { p.fireRate *= 1.5; p.shield += 2; game.ui.updateShields(); });

// [E. 趣味模组 - 娱乐至上]
reg("exp_confetti", "彩带炮", "🎉", "子弹数+5，伤害-80%，随机方向", "EPIC", p => { p.shotCount += 5; p.damage *= 0.2; p.projFlags.randomDir = true; });
reg("exp_one_punch", "一击男", "👊", "伤害+1000%，射速-90% (极慢)", "LEGENDARY", p => { p.damage *= 10; p.fireRate = 120; });
reg("exp_matrix", "黑客帝国", "🕶️", "闪避+30%，移速-20%", "EPIC", p => { p.dodge += 0.3; p.speedMultiplier *= 0.8; });
reg("exp_snail", "蜗牛壳", "🐌", "移速-50%，防御(血量)+10", "RARE", p => { p.speedMultiplier *= 0.5; p.maxHp += 10; p.hp += 10; game.ui.updateHearts(); });
reg("exp_machine_spirit", "机魂", "🤖", "变为激光武器，射速极大提升", "LEGENDARY", p => { p.weaponType = "laser"; p.fireRate = 5; p.damage *= 0.5; });
reg("exp_shotgun_king", "霰弹国王", "👑", "子弹数+8，射程极短", "LEGENDARY", p => { p.shotCount += 8; p.shotSpeed *= 0.4; });
reg("exp_bouncy_castle", "充气城堡", "🏰", "反弹+3，子弹变大", "RARE", p => { p.projFlags.bounce = true; p.shotSize += 5; });
reg("exp_drill", "超级钻头", "🔩", "穿透+虚空", "EPIC", p => { p.projFlags.pierce = true; p.projFlags.void = true; });
reg("exp_size_up", "巨化蘑菇", "🍄", "生命+2，判定体积变大(未实现视觉)，伤害+2", "RARE", p => { p.maxHp += 2; p.hp += 2; p.damage += 2; game.ui.updateHearts(); });
reg("exp_size_down", "缩小药丸", "💊", "生命-1，闪避+15%，移速+10%", "RARE", p => { p.maxHp -= 1; if(p.maxHp<1) p.maxHp=1; p.hp = Math.min(p.hp, p.maxHp); p.dodge += 0.15; p.speedMultiplier += 0.1; game.ui.updateHearts(); });

// --- CLASSES ---

class Splatter {
    constructor(x, y, color) {
        this.x = x; this.y = y;
        this.size = rand(2, 6);
        this.color = color;
        this.alpha = 0.8;
        this.vx = rand(-2, 2);
        this.vy = rand(-2, 2);
    }
    update() {
        this.x += this.vx;
        this.y += this.vy;
        this.alpha -= 0.02;
        this.vx *= 0.9;
        this.vy *= 0.9;
    }
    draw(ctx) {
        if(this.alpha <= 0) return;
        ctx.globalAlpha = this.alpha;
        ctx.fillStyle = this.color;
        ctx.fillRect(this.x, this.y, this.size, this.size);
        ctx.globalAlpha = 1;
    }
}

class DamageNumber {
    constructor(x, y, amt, crit) {
        this.x = x + rand(-10, 10);
        this.y = y - 10;
        this.amt = amt;
        this.crit = crit;
        this.life = 40;
        this.vy = -1.2;
        this.vx = rand(-0.4, 0.4);
    }
    update() {
        this.x += this.vx;
        this.y += this.vy;
        this.vy *= 0.96;
        this.life--;
    }
    draw(ctx) {
        ctx.save();
        ctx.globalAlpha = Math.min(1, this.life / 20);
        ctx.fillStyle = this.crit ? "#ff0" : "#fff";
        ctx.font = (this.crit ? "bold 22px" : "16px") + " 'Segoe UI', sans-serif";
        ctx.shadowBlur = this.crit ? 8 : 4;
        ctx.shadowColor = this.crit ? "rgba(255,255,0,0.8)" : "rgba(255,255,255,0.5)";
        ctx.textAlign = "center";
        ctx.fillText(this.amt, this.x, this.y);
        ctx.restore();
    }
}

class Orbital {
    constructor(owner, type="standard") {
        this.owner = owner;
        this.type = type;
        this.angle = rand(0, Math.PI*2);
        this.dist = 60 + rand(0, 20);
        this.speed = 0.05 * (rand(0, 1) > 0.5 ? 1 : -1);
        this.size = 10;
        this.damage = 2;
        this.color = "#fff";
        
        if(type === "attack") { this.color = "#f00"; this.damage = 5; }
        if(type === "shield") { this.color = "#0ff"; this.dist = 40; }
        if(type === "bomb") { this.color = "#f90"; this.damage = 20; }
    }
    update() {
        this.angle += this.speed;
        this.x = this.owner.x + this.owner.w/2 + Math.cos(this.angle) * this.dist - this.size/2;
        this.y = this.owner.y + this.owner.h/2 + Math.sin(this.angle) * this.dist - this.size/2;
        
        // Orbital contact damage
        game.enemies.forEach(e => {
            if (dist(this, e) < 20) {
                e.takeDamage(this.damage);
                if(this.type === "bomb") {
                    game.createExplosion(this.x, this.y, 100, 50);
                    this.dead = true;
                }
            }
        });
        
        // Block projectiles for shield type
        if(this.type === "shield") {
             // Logic could be added here to block enemy bullets
        }
    }
    draw(ctx) {
        ctx.fillStyle = this.color;
        ctx.shadowBlur = 10;
        ctx.shadowColor = this.color;
        ctx.beginPath(); ctx.arc(this.x + this.size/2, this.y + this.size/2, this.size, 0, Math.PI*2); ctx.fill();
        ctx.shadowBlur = 0;
    }
}

class Projectile {
    constructor(owner, x, y, vx, vy, stats, flags) {
        this.owner = owner;
        this.x = x; this.y = y;
        this.vx = vx; this.vy = vy;
        this.w = stats.size || 10; this.h = stats.size || 10;
        this.damage = stats.damage;
        this.flags = flags; // {bounce, homing, pierce, burn, freeze, shock, poison, crit, explode}
        this.life = 100;
        this.color = stats.color || "#0ff";
        this.hitList = []; 
        
        if (this.flags.crit) {
            this.damage *= 2; // Critical Hit logic pre-calculated or applied here
            this.w *= 1.5; this.h *= 1.5;
            this.color = "#ff0";
        }
    }

    update() {
        // --- 1. 追踪逻辑 (Homing) ---
        if (this.flags.homing) {
            let closest = null;
            let minDist = 300;
            game.enemies.forEach(e => {
                let d = dist(this, e);
                if (d < minDist) { minDist = d; closest = e; }
            });
            if (closest) {
                let angle = Math.atan2(closest.y - this.y, closest.x - this.x);
                this.vx += Math.cos(angle) * 0.5;
                this.vy += Math.sin(angle) * 0.5;
                let spd = Math.sqrt(this.vx**2 + this.vy**2);
                if (spd > 8) { this.vx *= 0.9; this.vy *= 0.9; }
            }
        }

        // --- 2. 移动 ---
        this.x += this.vx;
        this.y += this.vy;
        this.life--;

        // --- 3. 敌人碰撞检测 ---
        for (let i = 0; i < game.enemies.length; i++) {
            let e = game.enemies[i];
            if (checkCol(this, e)) {
                if (this.flags.pierce) {
                    if (!this.hitList.includes(e)) {
                        this.hitEnemy(e);
                        this.hitList.push(e);
                    }
                } else {
                    this.hitEnemy(e);
                    this.life = 0; 
                    break; 
                }
            }
        }

        // --- 4. 墙壁碰撞 ---
        if (this.x < 0 || this.x > game.width) {
            if (this.flags.bounce) this.vx *= -1;
            else this.life = 0;
        }
        if (this.y < 0 || this.y > game.height) {
            if (this.flags.bounce) this.vy *= -1;
            else this.life = 0;
        }
    }
    
    hitEnemy(e) {
        // Apply Damage
        e.takeDamage(this.damage, this.flags.crit);
        
        // Apply Status Effects
        if(this.flags.burn) e.applyStatus('burn', 60);
        if(this.flags.freeze) e.applyStatus('freeze', 60);
        if(this.flags.poison) e.applyStatus('poison', 120);
        if(this.flags.shock) {
            e.applyStatus('shock', 30);
            if(game.createLightning) game.createLightning(e.x, e.y, 3);
        }
        
        // Special Effects
        if(this.flags.explode) {
            if(game.createExplosion) game.createExplosion(this.x, this.y, 100, this.damage * 0.8);
        }
        if(this.flags.vampire && Math.random() < 0.05) {
             this.owner.heal(1);
        }
        
        // --- 扩展效果 ---
        if(this.flags.echo) {
            setTimeout(() => { if(e && e.hp > 0) e.takeDamage(this.damage * 0.5); }, 400);
        }
        if(this.flags.void && Math.random() < 0.1) {
            e.takeDamage(e.hp, true); // 斩杀
        }
        
        // --- 补充新效果 ---
        if(this.flags.charm && Math.random() < 0.2) {
            e.applyStatus('charm', 120);
        }
        if(this.flags.gravity) {
            e.applyStatus('gravity', 30);
        }
        
        game.addSplatter(this.x, this.y, "#ffaa00");
    }

    draw(ctx) {
        ctx.fillStyle = this.color;
        ctx.shadowBlur = 5;
        ctx.shadowColor = this.color;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.w, 0, Math.PI*2);
        ctx.fill();
        ctx.shadowBlur = 0;
    }
}
class Laser {
    constructor(owner, angle) {
        this.owner = owner;
        this.angle = angle;
        this.life = 15;
        this.maxLife = 15;
    }
    update() { this.life--; }
    draw(ctx) {
        ctx.save();
        ctx.translate(this.owner.x + 10, this.owner.y + 10);
        ctx.rotate(this.angle);
        
        // Draw Laser
        let width = (this.life / this.maxLife) * 30;
        ctx.fillStyle = `rgba(255, 0, 0, ${this.life/15})`;
        ctx.fillRect(0, -width/2, 1000, width);
        
        ctx.fillStyle = "#fff";
        ctx.fillRect(0, -width/4, 1000, width/2);

        // Collision Check (Raycast simplified)
        let p1 = {x: this.owner.x, y: this.owner.y};
        let p2 = {x: this.owner.x + Math.cos(this.angle)*1000, y: this.owner.y + Math.sin(this.angle)*1000};
        
        if (this.life % 5 === 0) { // Tick damage
            game.enemies.forEach(e => {
                let num = Math.abs((p2.y - p1.y)*e.x - (p2.x - p1.x)*e.y + p2.x*p1.y - p2.y*p1.x);
                let den = Math.sqrt((p2.y - p1.y)**2 + (p2.x - p1.x)**2);
                let distToLine = num / den;
                
                if (distToLine < 30) {
                    e.takeDamage(this.owner.damage);
                    game.addSplatter(e.x, e.y, "#f00");
                }
            });
        }

        ctx.restore();
    }
}

// Special Projectile: Tech X Ring
class TechRing extends Projectile {
    constructor(owner, x, y, vx, vy, stats) {
        super(owner, x, y, vx, vy, stats, {});
        this.maxRadius = 100;
        this.radius = 10;
        this.growth = 2;
        this.life = 80;
    }
    update() {
        this.x += this.vx; this.y += this.vy;
        if(this.radius < this.maxRadius) this.radius += this.growth;
        this.life--;
        // Collision ring
        game.enemies.forEach(e => {
            let d = dist({x:this.x, y:this.y}, e);
            if (Math.abs(d - this.radius) < 20 && this.life % 5 === 0) {
                e.takeDamage(this.damage);
            }
        });
    }
    draw(ctx) {
        ctx.strokeStyle = "#0ff";
        ctx.lineWidth = 3;
        ctx.beginPath(); ctx.arc(this.x, this.y, this.radius, 0, Math.PI*2); ctx.stroke();
    }
}

class Enemy {
    constructor(x, y, wave) {
        this.x = x; this.y = y;
        this.w = 40; this.h = 40; 
        // 难度梯度上升：血量随波次呈非线性增长
        this.hp = 10 + Math.pow(wave, 1.4) * 3;
        this.maxHp = this.hp;
        // 难度梯度上升：速度随波次增加
        this.baseSpeed = (rand(1.0, 2.2) + (wave * 0.08)) * 0.45;
        this.speed = this.baseSpeed;
        this.color = "#f33";
        this.vx = 0; this.vy = 0;
        
        // Status Effects
        this.statuses = { burn:0, freeze:0, poison:0, shock:0, charm:0, gravity:0 };
    }

    update(player) {
        // Handle Statuses
        if(this.statuses.burn > 0) {
            if(this.statuses.burn % 20 === 0) this.takeDamage(1, false, true);
            this.statuses.burn--;
            game.addSplatter(this.x + rand(0,this.w), this.y + rand(0,this.h), "#f90");
        }
        if(this.statuses.poison > 0) {
            if(this.statuses.poison % 30 === 0) this.takeDamage(1, false, true);
            this.statuses.poison--;
            game.addSplatter(this.x + rand(0,this.w), this.y + rand(0,this.h), "#0f0");
        }
        
        // Freeze logic
        if(this.statuses.freeze > 0) {
            this.speed = this.baseSpeed * 0.5;
            this.statuses.freeze--;
            this.color = "#0ff";
        } else if (this.statuses.charm > 0) {
            this.speed = 0;
            this.statuses.charm--;
            this.color = "#f0f";
        } else {
            this.speed = this.baseSpeed;
            this.color = "#f33";
        }
        
        // Shock logic (stun micro-interrupts)
        if(this.statuses.shock > 0) {
             if(Math.random() < 0.3) this.speed = 0;
             this.statuses.shock--;
        }

        let angle = Math.atan2(player.y - this.y, player.x - this.x);
        
        // Gravity effect
        if(this.statuses.gravity > 0) {
            this.statuses.gravity--;
            // Pull towards player or specific point? Let's say pull towards current position strongly
            this.vx *= 0.5; this.vy *= 0.5;
        } else {
            this.vx = Math.cos(angle) * this.speed;
            this.vy = Math.sin(angle) * this.speed;
        }

        this.x += this.vx;
        this.y += this.vy;

        // Collision with Player
        if (checkCol(this, player)) {
            player.takeDamage(1);
            // Knockback
            this.x -= this.vx * 20;
            this.y -= this.vy * 20;
            
            // Thorns
            if(player.thorns > 0) {
                this.takeDamage(player.thorns);
            }
        }
    }
    
    applyStatus(type, duration) {
        this.statuses[type] = duration;
    }

    takeDamage(amt, crit=false, isDoT=false) {
        if(crit) {
            amt *= game.player.critDmg || 2;
            game.ui.showDamage(this.x, this.y, Math.floor(amt), true);
        } else if (!isDoT && Math.random() < 0.3) {
             game.ui.showDamage(this.x, this.y, Math.floor(amt), false);
        }
        
        this.hp -= amt;
        game.shake = crit ? 5 : 2;
        this.flash = 2;
        if (this.hp <= 0) {
            game.killEnemy(this);
        }
    }

    draw(ctx) {
        if (this.flash > 0) {
            ctx.fillStyle = "#fff";
            this.flash--;
        } else {
            ctx.fillStyle = this.color;
        }
        ctx.fillRect(this.x, this.y, this.w, this.h);
        
        // Status indicators
        if(this.statuses.poison > 0) {
             ctx.fillStyle = "#0f0"; ctx.fillRect(this.x, this.y-5, this.w, 3);
        }
        
        // Draw eyes
        ctx.fillStyle = "#000";
        ctx.fillRect(this.x+4, this.y+4, 4, 4);
        ctx.fillRect(this.x+16, this.y+4, 4, 4);
    }
}

class Player {
    constructor() {
        this.x = 720; this.y = 450; 
        this.w = 32; this.h = 32;
        this.vx = 0; this.vy = 0;
        this.friction = 0.85;
        
        // --- 移动参数优化 ---
        this.baseAccel = 0.42; 
        this.speedMultiplier = 1.0; 
        this.accel = this.baseAccel * this.speedMultiplier;
        
        // Stats
        this.maxHp = 3;
        this.hp = 3;
        this.shield = 0;
        this.fireRate = 20; 
        this.damage = 3;
        this.shotSpeed = 7;
        this.shotSize = 4;
        this.invuln = 0;
        
        // Advanced Stats
        this.critChance = 0;
        this.critDmg = 2.0;
        this.dodge = 0; // % chance
        this.luck = 1.0;
        this.thorns = 0;
        this.lifesteal = 0; // chance

        // Inventory / Mods
        this.items = [];
        this.shotCount = 1;
        this.projFlags = { bounce: false, homing: false, pierce: false, burn:false, freeze:false, poison:false, shock:false, explode:false, vampire:false };
        this.weaponType = "standard"; 
        
        this.cooldown = 0;
    }
    
    heal(amt) {
        if(this.hp < this.maxHp) {
            this.hp = Math.min(this.hp + amt, this.maxHp);
            game.ui.updateHearts();
            game.ui.showMessage("HP RESTORED");
        }
    }

    addItem(itemId) {
        const item = ITEMS[itemId];
        if (!item) return;
        this.items.push(item);
        item.apply(this);
        game.ui.addItemIcon(itemId);
        game.ui.showMessage(`已获得模块: ${item.name}`);
    }

    update() {
        // ... (rest is same, but updated accel logic)
        this.accel = this.baseAccel * this.speedMultiplier;

        if (game.keys['w']) this.vy -= this.accel;
        if (game.keys['s']) this.vy += this.accel;
        if (game.keys['a']) this.vx -= this.accel;
        if (game.keys['d']) this.vx += this.accel;

        this.x += this.vx;
        this.y += this.vy;
        this.vx *= this.friction;
        this.vy *= this.friction;

        this.x = Math.max(0, Math.min(game.width - this.w, this.x));
        this.y = Math.max(0, Math.min(game.height - this.h, this.y));

        if (this.cooldown > 0) this.cooldown--;
        if (this.invuln > 0) this.invuln--;

        if (this.cooldown <= 0) {
            this.shoot();
            this.cooldown = this.fireRate;
        }
    }

    shoot() {
        let angle = Math.atan2(game.mouseY - this.y, game.mouseX - this.x);
        
        // 混沌引擎效果
        if(this.projFlags.randomDir) {
            angle = Math.random() * Math.PI * 2;
        }

        let totalShots = this.shotCount;
        let spread = 0.2; 
        let startAngle = angle - (spread * (totalShots - 1)) / 2;

        for (let i = 0; i < totalShots; i++) {
            let currentAngle = startAngle + i * spread;
            
            // Check Crit
            let isCrit = Math.random() < this.critChance;
            let currentFlags = { ...this.projFlags, crit: isCrit };

            if (this.weaponType === "laser") {
                game.projectiles.push(new Laser(this, currentAngle));
            } else {
                let vx = Math.cos(currentAngle) * this.shotSpeed + (this.vx * 0.2);
                let vy = Math.sin(currentAngle) * this.shotSpeed + (this.vy * 0.2);
                
                if (this.weaponType === "ring") {
                    game.projectiles.push(new TechRing(this, this.x + 10, this.y + 10, vx*0.5, vy*0.5, {damage: this.damage}));
                } else {
                    game.projectiles.push(new Projectile(
                        this, this.x + 10, this.y + 10, vx, vy, 
                        { damage: this.damage, size: this.shotSize, color: this.items.length > 0 ? this.items[this.items.length-1].color : "#0ff" }, 
                        currentFlags
                    ));
                }
            }
        }
    }

    takeDamage(amt) {
        if (this.invuln > 0) return;
        
        // Dodge check
        if(Math.random() < this.dodge) {
            game.ui.showMessage("DODGED!");
            return;
        }
        
        if (this.shield > 0) {
            this.shield--;
            game.ui.updateShields();
        } else {
            this.hp -= amt;
            game.ui.updateHearts();
            
            // Add pulse effect to remaining hearts
            const hearts = document.querySelectorAll('.hp-heart');
            hearts.forEach((h, i) => {
                if (i < game.player.hp) {
                    h.classList.remove('pulse');
                    void h.offsetWidth; // trigger reflow
                    h.classList.add('pulse');
                }
            });
        }

        this.invuln = 60;
        game.shake = 15;
        game.addSplatter(this.x, this.y, "#0ff"); 

        if (this.hp <= 0) game.gameOver();
    }
    
    // ... draw method remains similar
    draw(ctx) {
        if (this.invuln > 0 && Math.floor(Date.now() / 50) % 2 === 0) return;
        ctx.fillStyle = "#fff";
        ctx.fillRect(this.x, this.y, this.w, this.h);
        
        if (this.weaponType === "laser") {
            ctx.fillStyle = "#000"; ctx.fillRect(this.x + 5, this.y + 5, 10, 10); 
            ctx.fillStyle = "#f00"; ctx.fillRect(this.x + 8, this.y + 8, 4, 4); 
        } else if (this.weaponType === "ring") {
            ctx.strokeStyle = "#0ff"; ctx.strokeRect(this.x-2, this.y-2, this.w+4, this.h+4);
        }

        if (this.projFlags.homing) {
            ctx.fillStyle = "#f0f"; ctx.fillRect(this.x + 12, this.y + 4, 6, 6); 
        } else {
            ctx.fillStyle = "#000"; ctx.fillRect(this.x + 12, this.y + 4, 4, 4);
        }
    }
}

class Pickup {
    constructor(x, y, type) {
        this.x = x; this.y = y; this.w = 16; this.h = 16;
        this.type = type; // heart, shield, battery, item
        this.bob = 0;
        
        if (type === 'item') {
            // Weighted Random item from pool
            this.itemId = getRandomItemKey(item => item.type !== 'forbidden');
            if(!this.itemId) this.itemId = "dmg_0";
            this.color = ITEMS[this.itemId].color;
        }
    }
    update() {
        this.bob += 0.1;
        if (checkCol(this, game.player)) {
            // Collect
            if (this.type === 'heart') {
                if(game.player.hp < game.player.maxHp) { game.player.hp++; game.ui.updateHearts(); }
            } else if (this.type === 'shield') {
                game.player.shield++; game.ui.updateShields();
            } else if (this.type === 'item') {
                game.player.addItem(this.itemId);
                // No message needed here as addItem handles it? No, addItem handles it.
            }
            game.pickups = game.pickups.filter(p => p !== this);
        }
    }
    draw(ctx) {
        let yOff = Math.sin(this.bob) * 3;
        if (this.type === 'heart') {
            ctx.fillStyle = "#f33"; ctx.font = "24px Arial"; ctx.fillText("♥", this.x, this.y + 24 + yOff);
        } else if (this.type === 'shield') {
            ctx.fillStyle = "#0ff"; ctx.font = "24px Arial"; ctx.fillText("🛡", this.x, this.y + 24 + yOff);
        } else if (this.type === 'item') {
            ctx.fillStyle = "#111"; ctx.fillRect(this.x, this.y+yOff, 30, 30);
            ctx.strokeStyle = this.color; ctx.lineWidth = 3; ctx.strokeRect(this.x, this.y+yOff, 30, 30);
            ctx.fillStyle = "#fff"; ctx.font = "18px Arial"; ctx.fillText("?", this.x+10, this.y+22+yOff);
        }
    }
}

// --- GAME DIRECTOR ---
const game = {
    canvas: $('gameCanvas'),
    ctx: $('gameCanvas').getContext('2d'),
    width: 1600, height: 1000, // Updated size for higher resolution
    keys: {},
    mouseX: 0, mouseY: 0,
    
    player: null,
    enemies: [],
    projectiles: [],
    splatters: [],
    damageNumbers: [],
    lightningLines: [],
    pickups: [],
    orbitals: [],
    
    shake: 0,
    wave: 1,
    enemiesToSpawn: 0,
    spawnTimer: 0,
    score: 0,
    state: 'init', // Start with init state
    paused: false,

    ui: {
        updateHearts: () => {
            const container = $('hp-display');
            const oldHp = container.querySelectorAll('.hp-heart').length;
            const newHp = game.player.maxHp;
            
            let html = '';
            for(let i=0; i<newHp; i++) {
                const active = i < game.player.hp;
                const isNew = i >= oldHp;
                html += `<span class="hp-heart ${isNew ? 'pulse' : ''}" style="opacity:${active ? 1 : 0.2}">❤</span>`;
            }
            container.innerHTML = html;
        },
        updateShields: () => {
            $('shield-display').innerHTML = game.player.shield > 0 ? '⬢'.repeat(game.player.shield) : '';
        },
        showDamage: (x, y, amt, crit) => {
            game.damageNumbers.push(new DamageNumber(x, y, amt, crit));
        },
        addItemIcon: (id) => {
            const item = ITEMS[id];
            const grid = $('item-list');
            const icon = document.createElement('div');
            icon.className = 'item-icon';
            icon.style.borderColor = item.color;
            icon.style.color = item.color;
            icon.innerHTML = item.icon || '⊡';
            icon.title = `${item.name}: ${item.desc}`;
            grid.appendChild(icon);
        },
        togglePause: () => {
            if (game.state !== 'playing' && !game.paused) return;
            
            game.paused = !game.paused;
            const menu = $('pause-menu');
            if (game.paused) {
                menu.style.display = 'block';
                game.ui.updatePauseItems();
            } else {
                menu.style.display = 'none';
            }
        },
        updatePauseItems: () => {
            const grid = $('pause-items');
            grid.innerHTML = '';
            game.player.items.forEach(item => {
                const slot = document.createElement('div');
                slot.className = 'pause-item-slot';
                slot.style.color = item.color;
                slot.style.borderColor = item.color;
                slot.innerHTML = item.icon || '⊡';
                slot.setAttribute('data-desc', `${item.name}: ${item.desc}`);
                grid.appendChild(slot);
            });
        },
        showMessage: (msg) => {
            // 将英文提示映射为中文
            const translations = {
                "ACQUIRED:": "已获得模块:",
                "DARK WEB ACCESS": "暗网访问",
                "Exchange MAX HP for Forbidden Mods?": "用最大生命值交换禁忌模块？"
            };
            let translatedMsg = msg;
            for(let key in translations) {
                if(msg.includes(key)) translatedMsg = msg.replace(key, translations[key]);
            }
            console.log(translatedMsg);
        }
    },

    director: {
        waveActive: false,
        checkWave: () => {
            if (game.enemies.length === 0 && game.enemiesToSpawn === 0 && game.director.waveActive) {
                // Wave Clear
                game.director.waveActive = false;
                
                // 停止游戏循环或进入暂停状态以选择道具
                game.director.presentItemSelection();
            }
        },
        presentItemSelection: () => {
            const selectionEl = $('item-selection');
            const optionsEl = $('item-options');
            optionsEl.innerHTML = '';
            
            // 获取玩家当前已有的道具ID列表
            const ownedIds = game.player.items.map(item => {
                return Object.keys(ITEMS).find(key => ITEMS[key] === item);
            }).filter(x => x);

            // 随机选3个不重复且玩家未拥有的道具 (Weighted)
            let selected = [];
            let attempts = 0;
            while(selected.length < 3 && attempts < 50) {
                attempts++;
                let k = getRandomItemKey((item, key) => 
                    item.type !== 'forbidden' && !ownedIds.includes(key) && !selected.includes(key)
                );
                if(k) selected.push(k);
            }
            
            if (selected.length === 0) {
                // 如果没有道具可供选择，直接跳过
                game.director.nextWave();
                return;
            }

            selected.forEach(key => {
                const item = ITEMS[key];
                let div = document.createElement('div');
                div.className = 'devil-choice';
                div.style.setProperty('--active-color', item.color);
                div.style.borderColor = item.color;
                div.style.boxShadow = `0 0 10px ${item.color}40`; // Soft glow
                div.innerHTML = `
                    <div style="font-size: 40px; margin-bottom: 10px; color:${item.color}">${item.icon || '⊡'}</div>
                    <div style="font-size: 10px; color:${item.color}; letter-spacing:2px; margin-bottom:5px; opacity:0.8">${item.rarity}</div>
                    <h4 style="color:${item.color}; margin: 5px 0;">${item.name}</h4>
                    <p style="font-size:12px; color:#ccc; line-height:1.4">${item.desc}</p>
                `;
                div.onclick = () => {
                    game.player.addItem(key);
                    selectionEl.style.display = 'none';
                    
                    // 额外掉落补给 (视觉反馈)
                    game.pickups.push(new Pickup(game.width/2 - 40, game.height/2, Math.random()>0.5 ? 'heart' : 'shield'));
                    
                    // 检查是否开启暗网交易或直接下一波
                    if (Math.random() < 0.2) { // 降低暗网触发率
                        setTimeout(() => game.director.openDevilDeal(), 800);
                    } else {
                        setTimeout(() => game.director.nextWave(), 1200);
                    }
                };
                optionsEl.appendChild(div);
            });
            
            selectionEl.style.display = 'block';
        },
        nextWave: () => {
            game.wave++;
            // 怪物数量梯度上升：每波增加更多怪物
            game.enemiesToSpawn = 4 + Math.floor(game.wave * 2.2);
            game.director.waveActive = true;
        },
        openDevilDeal: () => {
            game.state = 'devil';
            const opts = $('devil-options');
            opts.innerHTML = '';
            
            // Pick 2 weighted forbidden items
            let forbidden = [];
            let attempts = 0;
            const ownedIds = game.player.items.map(item => Object.keys(ITEMS).find(k => ITEMS[k] === item)).filter(x=>x);

            while(forbidden.length < 2 && attempts < 50) {
                attempts++;
                let k = getRandomItemKey((item, key) => 
                    item.type === 'forbidden' && !forbidden.includes(key) && !ownedIds.includes(key)
                );
                if(k) forbidden.push(k);
            }
            
            forbidden.forEach(key => {
                const item = ITEMS[key];
                let d = document.createElement('div');
                d.className = 'devil-choice';
                d.style.setProperty('--active-color', item.color);
                d.style.borderColor = item.color;
                d.style.boxShadow = `0 0 15px ${item.color}60`;
                d.innerHTML = `
                    <div style="font-size: 32px; margin-bottom: 5px; color:${item.color}">${item.icon || '☣'}</div>
                    <div style="font-size: 10px; color:${item.color}; letter-spacing:2px; margin-bottom:5px; opacity:0.8">FORBIDDEN</div>
                    <h4 style="color:${item.color}; margin: 5px 0;">${item.name}</h4>
                    <p style="font-size:11px; color:#aaa">${item.desc}</p>
                    <div style="color:var(--neon-red); font-size:10px; margin-top:5px;">代价: -1 MAX HP</div>
                `;
                d.onclick = () => {
                    if (game.player.maxHp > 1) {
                        game.player.maxHp--;
                        game.player.hp = Math.min(game.player.hp, game.player.maxHp);
                        game.player.addItem(key);
                        game.ui.updateHearts();
                        game.director.closeDevilDeal();
                    } else {
                        alert("生命值不足以进行交易。");
                    }
                };
                opts.appendChild(d);
            });
            
            $('dark-web').style.display = 'block';
        },
        closeDevilDeal: () => {
            $('dark-web').style.display = 'none';
            game.state = 'playing';
            game.director.nextWave();
        }
    },

    confirmStart: () => {
        if (confirm("准备好进入赛博空间了吗？\n\n系统初始化可能需要几秒钟。")) {
            $('start-screen').style.display = 'none';
            $('ui-layer').style.display = 'flex';
            game.start();
        }
    },

    start: () => {
        game.state = 'playing';
        game.player = new Player();
        game.enemies = [];
        game.projectiles = [];
        game.pickups = [];
        game.orbitals = [];
        game.splatters = [];
        game.score = 0;
        game.wave = 0;
        game.enemiesToSpawn = 0;
        $('item-list').innerHTML = '';
        $('game-over').style.display = 'none';
        $('hi-score').innerText = localStorage.getItem('isaac_hi') || 0;
        
        game.ui.updateHearts();
        game.ui.updateShields();
        game.director.nextWave();
        
        requestAnimationFrame(game.loop);
    },

    restart: () => {
        game.state = 'playing';
        game.start();
    },

    gameOver: () => {
        game.state = 'gameover';
        $('game-over').style.display = 'block';
        let hi = localStorage.getItem('isaac_hi') || 0;
        if (game.score > hi) {
            localStorage.setItem('isaac_hi', game.score);
            $('hi-score').innerText = game.score;
        }
    },

    addSplatter: (x, y, color) => {
        if (game.splatters.length > 200) game.splatters.shift();
        game.splatters.push(new Splatter(x, y, color));
    },

    killEnemy: (e) => {
        game.enemies = game.enemies.filter(en => en !== e);
        game.addSplatter(e.x, e.y, "#3f3"); // Alien blood
        for(let i=0; i<3; i++) game.addSplatter(e.x + rand(-10, 10), e.y + rand(-10, 10), "#0f0");
        
        game.score += 100;
        $('score-val').innerText = game.score;
        
        if (game.player.lifesteal > 0 && Math.random() < game.player.lifesteal) {
             game.player.heal(1);
        }

        // Chain Reaction (chainKill flag)
        if(game.player.projFlags.chainKill) {
            game.createLightning(e.x, e.y, 2);
            game.createExplosion(e.x, e.y, 80, 5);
        }
        
        game.director.checkWave();
    },

    createExplosion: (x, y, radius, damage) => {
        // Visual
        for(let i=0; i<15; i++) {
            game.addSplatter(x + rand(-radius/2, radius/2), y + rand(-radius/2, radius/2), "#f50");
        }
        
        // Damage
        game.enemies.forEach(e => {
            if(dist({x:x, y:y}, e) < radius) {
                e.takeDamage(damage, false, true);
            }
        });
    },
    
    createLightning: (x, y, chains) => {
        if(chains <= 0) return;
        let closest = null;
        let minDist = 250;
        game.enemies.forEach(e => {
            let d = dist({x:x, y:y}, e);
            if(d < minDist && d > 5) { minDist = d; closest = e; }
        });
        
        if(closest) {
            game.lightningLines.push({x1: x, y1: y, x2: closest.x + closest.w/2, y2: closest.y + closest.h/2, life: 5});
            closest.takeDamage(3);
            closest.applyStatus('shock', 30);
            if(chains > 1) game.createLightning(closest.x, closest.y, chains - 1);
        }
    },

    draw: () => {
        // --- DRAW ---
        game.ctx.clearRect(0, 0, game.width, game.height);
        
        // Shake
        game.ctx.save();
        if (game.shake > 0) {
            let dx = rand(-game.shake, game.shake);
            let dy = rand(-game.shake, game.shake);
            game.ctx.translate(dx, dy);
            game.shake *= 0.9;
            if (game.shake < 0.5) game.shake = 0;
        }

        // Floor / Splatters
        game.splatters.forEach(s => s.draw(game.ctx));

        game.pickups.forEach(p => p.draw(game.ctx));
        game.enemies.forEach(e => e.draw(game.ctx));
        game.projectiles.forEach(p => p.draw(game.ctx));
        game.player.draw(game.ctx);
        game.orbitals.forEach(o => o.draw(game.ctx));
        
        // Lightning Lines
        game.lightningLines.forEach(l => {
            game.ctx.strokeStyle = "#ff0";
            game.ctx.lineWidth = 3;
            game.ctx.beginPath();
            game.ctx.moveTo(l.x1, l.y1);
            game.ctx.lineTo(l.x2, l.y2);
            game.ctx.stroke();
        });
        
        // Damage Numbers (draw on top)
        game.damageNumbers.forEach(d => d.draw(game.ctx));

        game.ctx.restore();
    },

    loop: () => {
        if (game.state === 'init') return; 

        if (game.state !== 'playing' || game.paused) {
            if (game.state === 'gameover') return;
            // Even when paused, we might want to draw once to keep visual or just return
            if (game.paused) {
                game.draw(); // Keep rendering while paused
                requestAnimationFrame(game.loop);
                return;
            }
        }

        // --- UPDATE ---
        if (game.state === 'playing' && !game.paused) {
            game.player.update();
            game.orbitals.forEach(o => o.update());
            game.pickups.forEach(p => p.update());
            
            // Spawn Enemy Logic
            if (game.enemiesToSpawn > 0) {
                game.spawnTimer++;
                if (game.spawnTimer > 30) {
                    // Spawn away from player
                    let ex, ey;
                    do {
                        ex = rand(50, game.width-50);
                        ey = rand(50, game.height-50);
                    } while(dist({x:ex, y:ey}, game.player) < 300); // Increased distance for larger window
                    
                    game.enemies.push(new Enemy(ex, ey, game.wave));
                    game.enemiesToSpawn--;
                    game.spawnTimer = 0;
                }
            }

            game.enemies.forEach(e => e.update(game.player));
            
            // Projectiles update & cleanup
            game.projectiles.forEach((p, i) => {
                p.update();
                if (p.life <= 0) game.projectiles.splice(i, 1);
            });

            // Damage Numbers update
            game.damageNumbers.forEach((d, i) => {
                d.update();
                if (d.life <= 0) game.damageNumbers.splice(i, 1);
            });

            // Lightning update
            game.lightningLines.forEach((l, i) => {
                l.life--;
                if(l.life <= 0) game.lightningLines.splice(i, 1);
            });
        }

        game.draw();

        if (game.state !== 'gameover') requestAnimationFrame(game.loop);
    }
};

// --- INPUTS ---
window.addEventListener('keydown', e => {
    game.keys[e.key] = true;
    if (e.key === 'Escape') {
        game.ui.togglePause();
    }
});
window.addEventListener('keyup', e => game.keys[e.key] = false);
window.addEventListener('mousemove', e => {
    const rect = game.canvas.getBoundingClientRect();
    // Support scaling
    const scaleX = game.canvas.width / rect.width;
    const scaleY = game.canvas.height / rect.height;
    game.mouseX = (e.clientX - rect.left) * scaleX;
    game.mouseY = (e.clientY - rect.top) * scaleY;
});

// Boot - Don't auto-start
// game.start();
// The game will be started by the button in #start-screen calling game.confirmStart()

</script>
</body>
</html>
