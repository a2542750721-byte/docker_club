<?php
// 独立活动列表页
require_once __DIR__ . '/includes/functions.php';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

    <section id="activities" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">所有活动</h2>
                <p class="section-subtitle">查看协会举办的所有活动</p>
            </div>
            <div class="activities-grid" id="activities-grid">
                <!-- 活动列表将通过JavaScript动态加载 -->
            </div>
        </div>
    </section>

<?php include __DIR__ . '/includes/modals.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>