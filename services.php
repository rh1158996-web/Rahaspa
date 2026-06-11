<?php
require_once 'includes/init.php';
$pageTitle = isset($lang['services']) ? $lang['services'] : 'Services';
include 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM services ORDER BY price ASC");
$services = $stmt->fetchAll();
?>

<section class="section" style="padding-top:2rem;">
    <div class="container">
        <div class="text-center" style="margin-bottom:3rem;">
            <h1 class="section-title"><?php echo $current_lang === 'ar' ? 'جلساتنا العلاجية' : 'Our Therapy Sessions'; ?></h1>
            <p><?php echo $current_lang === 'ar' ? 'اكتشف مجموعتنا من جلسات التدليك الاحترافية.' : 'Discover our range of professional massage therapy sessions.'; ?></p>
        </div>
        <div class="services-grid">
            <?php foreach($services as $s):
                $sname = $current_lang === 'ar' ? $s['name_ar'] : $s['name_en'];
                $sdesc = $current_lang === 'ar' ? $s['description_ar'] : $s['description_en'];
            ?>
            <div class="service-card">
                <div class="service-icon">
                    <?php if(!empty($s['image_url']) && file_exists(__DIR__ . '/' . $s['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($s['image_url']); ?>" alt="<?php echo htmlspecialchars($sname); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <i class="fa-solid fa-hands-bubbles"></i>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($sname); ?></h3>
                <p><i class="fa-regular fa-clock"></i> <?php echo $s['duration_minutes']; ?> <?php echo $lang['mins']; ?></p>
                <p><?php echo htmlspecialchars($sdesc); ?></p>
                <div class="service-price"><?php echo number_format($s['price'], 0); ?> <?php echo $lang['sar']; ?></div>
                <a href="booking.php?service_id=<?php echo $s['id']; ?>" class="btn btn-primary"><?php echo $lang['book_now']; ?></a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
