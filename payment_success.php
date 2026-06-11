<?php
require_once 'includes/init.php';
require_once 'includes/auth_guard.php';
require_once 'includes/config.php';

$payment_successful = false;
$user_id = $_SESSION['user_id'];

if (isset($_GET['method']) && $_GET['method'] === 'cash') {
    $payment_successful = true;
} elseif (isset($_GET['session_id']) && isset($_GET['booking_id'])) {
    $session_id = $_GET['session_id'];
    $booking_id = (int)$_GET['booking_id'];
    
    // Verify Stripe session status
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/checkout/sessions/" . $session_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $response = json_decode($result, true);

    if ($http_code == 200 && isset($response['payment_status']) && $response['payment_status'] === 'paid') {
        $payment_successful = true;
        
        // Update payment record
        $stmt = $pdo->prepare("UPDATE payments SET status = 'Completed' WHERE stripe_session_id = ?");
        $stmt->execute([$session_id]);

        // Update booking record
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Confirmed' WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
    }
} else {
    header("Location: profile.php");
    exit;
}

$pageTitle = $current_lang === 'ar' ? 'نجاح الدفع' : 'Payment Success';
include 'includes/header.php';
?>

<section class="section" style="padding-top: 5rem; min-height: 60vh;">
    <div class="container text-center">
        <?php if($payment_successful): ?>
            <div style="font-size: 5rem; color: #28a745; margin-bottom: 1rem;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h1 class="section-title"><?php echo $current_lang === 'ar' ? 'تم تأكيد موعدك!' : 'Appointment Confirmed!'; ?></h1>
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">
                <?php echo $current_lang === 'ar' ? 'لقد تم تأكيد حجزك بنجاح. ننتظر زيارتك في رها سبا.' : 'Your booking is now confirmed. We look forward to seeing you at Raha Spa.'; ?>
            </p>
            <a href="profile.php" class="btn btn-primary"><?php echo $lang['my_account']; ?></a>
        <?php else: ?>
            <div style="font-size: 5rem; color: #dc3545; margin-bottom: 1rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h1 class="section-title"><?php echo $current_lang === 'ar' ? 'حدث خطأ' : 'Verification Issue'; ?></h1>
            <p><?php echo $current_lang === 'ar' ? 'لم نتمكن من التحقق من الدفع.' : 'We could not automatically verify your payment.'; ?></p>
            <a href="profile.php" class="btn btn-primary"><?php echo $lang['my_account']; ?></a>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
