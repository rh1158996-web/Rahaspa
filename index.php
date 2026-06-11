<?php
require_once 'includes/init.php';
$pageTitle = isset($lang['home']) ? $lang['home'] : 'Home';
include 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM services LIMIT 3");
$services = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM branches ORDER BY id ASC");
$branches = $stmt->fetchAll();
?>

<!-- HERO -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1><?php echo $current_lang === 'ar' ? 'مرحباً بك في راحة سبا' : 'Welcome to Raha Spa'; ?></h1>
            <p><?php echo $current_lang === 'ar' ? 'ملاذك الأمثل للاسترخاء والتجديد. اهرب من ضغوط الحياة اليومية واستمتع بجلسات التدليك الاحترافية.' : 'Your ultimate sanctuary for relaxation and rejuvenation. Escape daily stress with our premium therapy sessions.'; ?></p>
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <a href="booking.php" class="btn btn-primary"><?php echo $lang['book_now']; ?></a>
                <a href="services.php" class="btn btn-secondary" style="color:#fff;border-color:#fff;"><?php echo $lang['services']; ?></a>
            </div>
        </div>
    </div>
</section>

<!-- FEATURED SERVICES -->
<section class="section" style="background:var(--light-beige);">
    <div class="container">
        <div class="text-center" style="margin-bottom:3rem;">
            <h2 class="section-title"><?php echo $current_lang === 'ar' ? 'جلساتنا المميزة' : 'Our Featured Sessions'; ?></h2>
        </div>
        <div class="services-grid">
            <?php foreach($services as $s): $sname = $current_lang === 'ar' ? $s['name_ar'] : $s['name_en']; $sdesc = $current_lang === 'ar' ? $s['description_ar'] : $s['description_en']; ?>
            <div class="service-card">
                <div class="service-icon">
                    <?php if(!empty($s['image_url']) && file_exists(__DIR__ . '/' . $s['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($s['image_url']); ?>" alt="<?php echo htmlspecialchars($sname); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <i class="fa-solid fa-spa"></i>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($sname); ?></h3>
                <p><?php echo htmlspecialchars(mb_substr($sdesc, 0, 90)) . '...'; ?></p>
                <div class="service-price"><?php echo number_format($s['price'], 0); ?> <?php echo $lang['sar']; ?></div>
                <a href="booking.php?service_id=<?php echo $s['id']; ?>" class="btn btn-secondary"><?php echo $lang['book_now']; ?></a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center" style="margin-top:2.5rem;">
            <a href="services.php" class="btn btn-primary"><?php echo $current_lang === 'ar' ? 'عرض جميع الجلسات' : 'View All Sessions'; ?></a>
        </div>
    </div>
</section>

<!-- BRANCHES -->
<?php if(count($branches) > 0): ?>
<section class="section">
    <div class="container">
        <div class="text-center" style="margin-bottom:3rem;">
            <h2 class="section-title"><?php echo $current_lang === 'ar' ? 'فروعنا' : 'Our Branches'; ?></h2>
        </div>
        <div class="services-grid">
            <?php foreach($branches as $b): $bname = $current_lang === 'ar' ? $b['name_ar'] : $b['name_en']; $baddr = $current_lang === 'ar' ? $b['address_ar'] : $b['address_en']; ?>
            <div class="service-card">
                <div class="service-icon"><i class="fa-solid fa-location-dot"></i></div>
                <h3><?php echo htmlspecialchars($bname); ?></h3>
                <p><?php echo htmlspecialchars($baddr); ?></p>
                <a href="booking.php?branch_id=<?php echo $b['id']; ?>" class="btn btn-primary"><?php echo $lang['book_now']; ?></a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
