<?php
require_once 'includes/init.php';
$pageTitle = isset($lang['offers']) ? $lang['offers'] : 'Offers';
include 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM offers ORDER BY created_at DESC");
$offers = $stmt->fetchAll();
?>

<section class="section" style="padding-top:2rem;">
    <div class="container">
        <div class="text-center" style="margin-bottom:3rem;">
            <h1 class="section-title"><?php echo $current_lang === 'ar' ? 'العروض والتخفيضات' : 'Special Offers & Promotions'; ?></h1>
        </div>

        <?php if(count($offers) > 0): ?>
        <div class="services-grid">
            <?php foreach($offers as $o):
                $otitle = $current_lang === 'ar' ? $o['title_ar'] : $o['title_en'];
                $odesc  = $current_lang === 'ar' ? $o['description_ar'] : $o['description_en'];
            ?>
            <div class="service-card" style="<?php echo $o['discount_percentage'] >= 20 ? 'border:2px solid var(--sage-green);' : ''; ?>">
                <?php if(!empty($o['image_url']) && file_exists(__DIR__ . '/' . $o['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($o['image_url']); ?>" alt="<?php echo htmlspecialchars($otitle); ?>" style="width:100%;height:180px;object-fit:cover;border-radius:10px;margin-bottom:1rem;">
                <?php endif; ?>
                <div class="service-icon" style="background:var(--sage-green);color:#fff;">
                    <i class="fa-solid fa-tag"></i>
                </div>
                <h3><?php echo htmlspecialchars($otitle); ?></h3>
                <p><?php echo htmlspecialchars($odesc); ?></p>
                <div class="service-price" style="font-size:2rem;"><?php echo $o['discount_percentage']; ?>% <?php echo $current_lang === 'ar' ? 'خصم' : 'OFF'; ?></div>
                <?php if($o['valid_until']): ?>
                    <p style="font-size:0.85rem;color:var(--text-light);">
                        <i class="fa-regular fa-calendar"></i>
                        <?php echo $current_lang === 'ar' ? 'صالح حتى: ' : 'Valid until: '; ?>
                        <?php echo date('d M Y', strtotime($o['valid_until'])); ?>
                    </p>
                <?php endif; ?>
                <a href="booking.php" class="btn btn-primary"><?php echo $lang['book_now']; ?></a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="text-center" style="padding:4rem;color:var(--text-light);">
                <i class="fa-solid fa-tag" style="font-size:3rem;margin-bottom:1rem;color:#ccc;display:block;"></i>
                <p><?php echo $current_lang === 'ar' ? 'لا توجد عروض متاحة حالياً.' : 'No offers available at the moment. Check back soon!'; ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
