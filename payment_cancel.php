<?php
require_once 'includes/init.php';
require_once 'includes/auth_guard.php';

if(!isset($_GET['booking_id'])) {
    header("Location: profile.php");
    exit;
}

$booking_id = (int)$_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// We can just leave the booking as 'Pending' or mark it 'Rejected' 
// Let's mark the payment as 'Failed' if it exists
$stmt = $pdo->prepare("UPDATE payments SET status = 'Failed' WHERE booking_id = ? AND status = 'Pending'");
$stmt->execute([$booking_id]);

$pageTitle = $current_lang === 'ar' ? 'تم إلغاء الدفع' : 'Payment Cancelled';
include 'includes/header.php';
?>

<section class="section" style="padding-top: 5rem; min-height: 60vh;">
    <div class="container text-center">
        <div style="font-size: 5rem; color: #f39c12; margin-bottom: 1rem;">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <h1 class="section-title"><?php echo $current_lang === 'ar' ? 'تم إلغاء عملية الدفع' : 'Payment Cancelled'; ?></h1>
        <p style="font-size: 1.2rem; margin-bottom: 2rem;">
            <?php echo $current_lang === 'ar' ? 'لقد قمت بإلغاء عملية الدفع. حجزك المبدئي محفوظ ولكن يجب الدفع لتأكيده.' : 'You have cancelled the payment process. Your appointment remains unpaid and unconfirmed.'; ?>
        </p>
        <div style="display:flex; justify-content:center; gap:1rem;">
            <a href="payment_process.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-primary"><?php echo $current_lang === 'ar' ? 'إعادة المحاولة' : 'Try Again'; ?></a>
            <a href="profile.php" class="btn btn-secondary"><?php echo $lang['my_account']; ?></a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
