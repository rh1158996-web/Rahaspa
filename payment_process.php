<?php
require_once 'includes/init.php';
require_once 'includes/auth_guard.php';
require_once 'includes/config.php';

if (!isset($_GET['booking_id'])) {
    header("Location: profile.php");
    exit;
}

$booking_id = (int)$_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// Get booking details
$stmt = $pdo->prepare("SELECT b.*, s.name_en, s.name_ar, s.price FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.id = ? AND b.user_id = ? AND b.status = 'Pending'");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header("Location: profile.php");
    exit;
}

$price_sar = (int)$booking['price'];
$sname = $current_lang === 'ar' ? $booking['name_ar'] : $booking['name_en'];

// Handle payment selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'];

    if ($method === 'cash') {
        // Confirm booking with Cash
        $pdo->prepare("UPDATE bookings SET status = 'Confirmed' WHERE id = ?")->execute([$booking_id]);
        $pdo->prepare("INSERT INTO payments (booking_id, stripe_session_id, amount, currency, status) VALUES (?, 'CASH', ?, 'SAR', 'Pending')")->execute([$booking_id, $price_sar]);
        header("Location: payment_success.php?method=cash");
        exit;
    } elseif ($method === 'card') {
        // Stripe Checkout integration via cURL
        $url = 'https://api.stripe.com/v1/checkout/sessions';
        $data = [
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'sar',
                        'product_data' => ['name' => 'Session: ' . $sname],
                        'unit_amount' => $price_sar * 100, // Halalas
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => 'http://localhost/serenity/payment_success.php?session_id={CHECKOUT_SESSION_ID}&booking_id=' . $booking_id,
            'cancel_url' => 'http://localhost/serenity/payment_cancel.php?booking_id=' . $booking_id,
            'client_reference_id' => $booking_id,
            'customer_email' => $_SESSION['user_email']
        ];

        $post_fields = http_build_query($data);
        $post_fields = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $post_fields); // Fix Stripe array formatting

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($http_code == 200 && isset($result['url'])) {
            // Save initial payment record
            $pdo->prepare("INSERT INTO payments (booking_id, stripe_session_id, amount, currency, status) VALUES (?, ?, ?, 'SAR', 'Pending')")
                ->execute([$booking_id, $result['id'], $price_sar]);
            
            header("Location: " . $result['url']);
            exit;
        } else {
            $error = "Payment gateway error. Please verify your Stripe API keys in includes/config.php.";
        }
    }
}

$pageTitle = $lang['proceed_payment'] ?? 'Payment';
include 'includes/header.php';
?>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="form-container" style="max-width: 500px; text-align: center;">
            <i class="fa-solid fa-credit-card" style="font-size: 3rem; color: var(--sage-green); margin-bottom: 1rem;"></i>
            <h2><?php echo $current_lang === 'ar' ? 'طريقة الدفع' : 'Payment Method'; ?></h2>
            <p><strong><?php echo htmlspecialchars($sname); ?></strong></p>
            <h3 style="color: var(--sage-green-dark); margin: 1rem 0 2rem; font-size: 2.5rem; font-family: var(--font-heading);">
                <?php echo $price_sar; ?> <?php echo $lang['sar']; ?>
            </h3>

            <?php if(isset($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <button type="submit" name="payment_method" value="card" class="btn btn-primary" style="width: 100%; padding: 1rem; margin-bottom: 1rem; font-size: 1.1rem;">
                    <i class="fa-solid fa-credit-card"></i> <?php echo $current_lang === 'ar' ? 'الدفع بالبطاقة (Stripe)' : 'Pay with Card (Stripe)'; ?>
                </button>
                <button type="submit" name="payment_method" value="cash" class="btn btn-secondary" style="width: 100%; padding: 1rem; font-size: 1.1rem; border-color: #999; color: #555;">
                    <i class="fa-solid fa-money-bill-wave"></i> <?php echo $current_lang === 'ar' ? 'الدفع نقداً في المركز' : 'Pay Cash at Spa'; ?>
                </button>
            </form>
            
            <p style="margin-top: 2rem; font-size: 0.85rem; color: #999;">
                <?php echo $current_lang === 'ar' ? 'الرجاء إكمال الدفع خلال 15 دقيقة وإلا سيتم إلغاء الحجز.' : 'Please complete payment within 15 minutes or the slot will be released.'; ?>
            </p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
