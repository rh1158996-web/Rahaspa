<?php
require_once 'includes/init.php';
$pageTitle = isset($lang['reviews']) ? $lang['reviews'] : 'التقييمات';
include 'includes/header.php';

// Fetch approved reviews with user name joined
$stmt = $pdo->query("
    SELECT r.*, 
           CONCAT(u.first_name, ' ', u.last_name) as customer_name
    FROM reviews r
    JOIN bookings b ON r.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();
?>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="text-center" style="margin-bottom: 3rem;">
            <h1 class="section-title"><?php echo $current_lang === 'ar' ? 'آراء عملائنا' : 'What Our Clients Say'; ?></h1>
            <p><?php echo $current_lang === 'ar' ? 'اقرأ عن تجارب عملائنا الكرام.' : 'Read about the experiences of our valued customers.'; ?></p>
        </div>

        <?php if(count($reviews) > 0): ?>
            <div class="services-grid">
                <?php foreach($reviews as $review): ?>
                    <div class="service-card" style="text-align:center;">
                        <div style="color:#f39c12;font-size:1.5rem;margin-bottom:1rem;">
                            <?php for($i=1;$i<=5;$i++) echo $i<=$review['rating'] ? '★' : '☆'; ?>
                        </div>
                        <p style="font-style:italic;color:var(--text-light);margin-bottom:1rem;">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                        <div style="font-weight:600;color:var(--sage-green-dark);">— <?php echo htmlspecialchars($review['customer_name']); ?></div>
                        <small style="color:#ccc;"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center" style="padding:4rem;">
                <i class="fa-solid fa-star" style="font-size:3rem;color:#ddd;display:block;margin-bottom:1rem;"></i>
                <p style="color:var(--text-light);"><?php echo $current_lang === 'ar' ? 'لا توجد تقييمات بعد. كن أول من يترك تقييمًا!' : 'No reviews available yet. Be the first to leave one!'; ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
