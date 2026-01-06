<?php
$current_script = basename($_SERVER['SCRIPT_NAME']);
if ($current_script === 'index.php') {
    include_once __DIR__ . '/background_manager.php';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>创享网络信息协会 - 官方网站</title>
    <meta name="description" content="创想网络信息协会官方网站，专注计算机技术交流与学习">
    <meta name="keywords" content="创想网络,信息协会,计算机,技术交流,编程,学习">
    <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/tailwind.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/responsive.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='40' fill='%23002FA7'/><text x='50' y='60' text-anchor='middle' fill='white' font-size='40' font-family='Arial'>C</text></svg>">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script>
        // Apply theme before page renders to prevent FOUC
        (function() {
            // Check for saved theme in localStorage
            const savedTheme = localStorage.getItem('theme');
            const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
            
            // Apply theme: prioritize user selection, fallback to system preference
            if (savedTheme) {
                if (savedTheme === 'dark-mode') {
                    document.documentElement.classList.add('dark-mode');
                }
            } else if (prefersDarkScheme.matches) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="#" id="logo-text" style="text-decoration: none; cursor: pointer;">
                    <h2>创享网络信息协会</h2>
                </a>
            </div>
            <ul class="nav-menu" id="nav-menu">
                <li class="nav-item">
                    <a href="#home" class="nav-link active">首页</a>
                </li>
                <li class="nav-item">
                    <a href="#activities" class="nav-link">活动</a>
                </li>
                <li class="nav-item">
                    <a href="#resources" class="nav-link">资源</a>
                </li>
            </ul>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <button id="theme-toggle" class="theme-toggle" aria-label="切换主题">
                    <i class="fas fa-sun sun"></i>
                    <i class="fas fa-moon moon"></i>
                </button>
    
                <button id="bg-style-toggle" class="bg-style-btn" title="切换背景风格">
                    <i class="fas fa-braille"></i>
                </button>

                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </div>
    </nav>

    <section id="home" class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="gradient-text">创享网络信息协会</span>
                </h1>
                <p class="hero-subtitle">
                    探索计算机世界的无限可能，与志同道合的伙伴一起成长
                </p>
                <div class="hero-buttons">
                    <a href="#activities" class="btn btn-primary">
                        <i class="fas fa-calendar-alt"></i> 查看活动
                    </a>
                    <a href="#resources" class="btn btn-secondary">
                        <i class="fas fa-download"></i> 获取资源
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-card-container gap-6"> 
                    <!-- Card 1: Technical Sharing -->
                    <div class="group flex-1 min-w-[280px]" data-aos="fade-up" data-aos-delay="100">
                        <div class="h-full p-8 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-500/50 dark:hover:border-blue-400/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xl mb-6 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                                <i class="fas fa-code"></i>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3">
                                技术分享
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6">
                                定期举办技术分享会，交流最新技术动态，探索前沿科技。
                            </p>
                            
                            <a href="#activities" class="inline-flex items-center text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                了解更多 <i class="fas fa-arrow-right ml-2 text-xs transition-transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 2: Team Collaboration -->
                    <div class="group flex-1 min-w-[280px]" data-aos="fade-up" data-aos-delay="200">
                        <div class="h-full p-8 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-purple-500/50 dark:hover:border-purple-400/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-xl mb-6 transition-colors group-hover:bg-purple-600 group-hover:text-white">
                                <i class="fas fa-users"></i>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3">
                                团队协作
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6">
                                组建跨学科项目团队，共同攻克难关，完成有趣且富有挑战的项目。
                            </p>
                            
                            <a href="#activities" class="inline-flex items-center text-sm font-medium text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
                                加入团队 <i class="fas fa-arrow-right ml-2 text-xs transition-transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Card 3: Competition Training -->
                    <div class="group flex-1 min-w-[280px]" data-aos="fade-up" data-aos-delay="300">
                        <div class="h-full p-8 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-amber-500/50 dark:hover:border-amber-400/50 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                            <div class="w-12 h-12 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl mb-6 transition-colors group-hover:bg-amber-600 group-hover:text-white">
                                <i class="fas fa-trophy"></i>
                            </div>
                            
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-3">
                                竞赛培训
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6">
                                系统化参与ACM、蓝桥杯等编程竞赛，全方位提升算法思维与实战能力。
                            </p>
                            
                            <a href="#activities" class="inline-flex items-center text-sm font-medium text-gray-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                                查看战绩 <i class="fas fa-arrow-right ml-2 text-xs transition-transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<script src="assets/js/liquid-glass.js"></script>
<script>
    // 初始化液态玻璃效果
    document.querySelectorAll('.liquid-glass-trigger').forEach(el => {
        new LiquidGlassElement(el);
    });
</script>
        </div>      
</div>
        </div>
        <div class="hero-bg">
            <div class="bg-circle circle-1"></div>
            <div class="bg-circle circle-2"></div>
            <div class="bg-circle circle-3"></div>
        </div>
    </section>