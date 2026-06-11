<?php
// webhook.php - Stripe Webhook Handler
require_once 'includes/db.php';
require_once 'includes/config.php';

// If you want to verify the webhook signature, you would define the endpoint secret here
// define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

$payload = @file_get_contents('php://input');
$event = null;

try {
    $event = json_decode($payload, true);
} catch(\UnexpectedValueException $e) {
    http_response_code(400);
    exit();
}

// Handle the checkout.session.completed event
if ($event['type'] == 'checkout.session.completed') {
    $session = $event['data']['object'];
    $session_id = $session['id'];

    // Update payment record
    $stmt = $pdo->prepare("UPDATE payments SET status = 'Completed' WHERE stripe_session_id = ?");
    $stmt->execute([$session_id]);

    // Update associated booking record to confirmed
    $stmt = $pdo->prepare("
        UPDATE bookings b
        JOIN payments p ON b.id = p.booking_id
        SET b.status = 'Confirmed'
        WHERE p.stripe_session_id = ?
    ");
    $stmt->execute([$session_id]);
}

http_response_code(200);
?>
