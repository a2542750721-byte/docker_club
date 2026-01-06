<?php
// 独立活动列表页
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

    <section id="activities" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">所有活动</h2>
                <p class="section-subtitle">查看协会举办的所有活动</p>
            </div>
            <div class="unified-grid">
                <?php
                if(isset($conn) && $conn) {
                    $result = $conn->query("SELECT * FROM activities ORDER BY created_at DESC");
                    if($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()): ?>
                            <div class="card standard-card">
                                <div class="standard-img-box">
                                    <img src="<?php echo htmlspecialchars($row['cover'] ?: 'assets/images/default.jpg'); ?>" crossorigin="anonymous" loading="lazy" onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22800%22%20height%3D%22400%22%20viewBox%3D%220%200%20800%20400%22%20fill%3D%22%23f0f0f0%22%3E%3Crect%20width%3D%22800%22%20height%3D%22400%22%20%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20dominant-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20font-family%3D%22sans-serif%22%20font-size%3D%2224%22%20fill%3D%22%23aaa%22%3E%E6%9A%82%E6%97%A0%E5%B0%81%E9%9D%A2%3C%2Ftext%3E%3C%2Fsvg%3E'; this.onerror=null;">
                                </div>
                                <div class="standard-body">
                                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <p><?php echo mb_substr(strip_tags($row['content']), 0, 100); ?>...</p>
                                    <button class="btn btn-primary" onclick="safeCall('openFullArticle', <?php echo $row['id']; ?>)">查看活动详情</button>
                                </div>
                            </div>
                        <?php endwhile; 
                    } else {
                        echo '<p style="text-align:center; width:100%;">暂无活动</p>';
                    }
                } ?>
            </div>
        </div>
    </section>

<?php include __DIR__ . '/includes/modals.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>