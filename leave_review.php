<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db.php';
$pageTitle = 'Leave a Review';
include 'includes/header.php';

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$user_id    = $_SESSION['user_id'];

// Verify this booking belongs to the user, is Completed, and has no review yet
$stmt = $pdo->prepare("
    SELECT b.*, s.name_en, s.name_ar, r.id as review_id
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    LEFT JOIN reviews r ON b.id = r.booking_id
    WHERE b.id = ? AND b.user_id = ? AND b.status = 'Completed'
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    echo '<section class="section"><div class="container text-center"><h2>Invalid or Unauthorized</h2><a href="profile.php" class="btn btn-primary">Back to Profile</a></div></section>';
    include 'includes/footer.php';
    exit;
}

if ($booking['review_id']) {
    echo '<section class="section"><div class="container text-center"><h2>' . ($current_lang==='ar' ? 'لقد قمت بالتقييم بالفعل' : 'You have already reviewed this session') . '</h2><a href="profile.php" class="btn btn-primary">Back</a></div></section>';
    include 'includes/footer.php';
    exit;
}

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating  = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    if ($rating >= 1 && $rating <= 5) {
        $pdo->prepare("INSERT INTO reviews (booking_id, rating, comment) VALUES (?, ?, ?)")
            ->execute([$booking_id, $rating, $comment]);
        $success = true;
    }
}

$sname = $current_lang === 'ar' ? $booking['name_ar'] : $booking['name_en'];
?>

<section class="section" style="padding-top:2rem;">
    <div class="container">
        <div class="form-container" style="max-width:520px;">
            <div class="text-center" style="margin-bottom:2rem;">
                <div style="font-size:3rem;color:#f39c12;margin-bottom:0.5rem;">⭐</div>
                <h2><?php echo $current_lang==='ar' ? 'قيّم جلستك' : 'Rate Your Session'; ?></h2>
                <p><strong><?php echo htmlspecialchars($sname); ?></strong> — <?php echo date('d/m/Y', strtotime($booking['booking_date'])); ?></p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success" style="text-align:center;">
                    <i class="fa-solid fa-circle-check"></i>
                    <?php echo $current_lang==='ar' ? 'شكراً على تقييمك! سيظهر بعد مراجعته.' : 'Thank you for your review! It will appear after approval.'; ?>
                </div>
                <a href="profile.php" class="btn btn-primary" style="width:100%;margin-top:1rem;">Back to Profile</a>
            <?php else: ?>
            <form method="POST">
                <div class="form-group" style="text-align:center;">
                    <label class="form-label"><?php echo $current_lang==='ar' ? 'تقييمك (1-5)' : 'Your Rating (1-5)'; ?></label>
                    <div id="star_selector" style="display:flex;justify-content:center;gap:1rem;font-size:2.5rem;cursor:pointer;margin:1rem 0;">
                        <?php for($i=1;$i<=5;$i++): ?>
                            <span data-val="<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)" style="color:#ddd;transition:color 0.2s;">★</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="rating_input" required>
                </div>
                <div class="form-group">
                    <label for="comment" class="form-label"><?php echo $current_lang==='ar' ? 'تعليقك (اختياري)' : 'Your Comment (Optional)'; ?></label>
                    <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="<?php echo $current_lang==='ar' ? 'شاركنا تجربتك...' : 'Share your experience...'; ?>"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;padding:1rem;"><?php echo $current_lang==='ar' ? 'إرسال التقييم' : 'Submit Review'; ?></button>
            </form>
            <script>
            function setRating(val) {
                document.getElementById('rating_input').value = val;
                document.querySelectorAll('#star_selector span').forEach((s, i) => {
                    s.style.color = i < val ? '#f39c12' : '#ddd';
                });
            }
            </script>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
