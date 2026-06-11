<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

if (!isset($_SESSION['verify_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Session expired. Please register again.']);
    exit;
}

$user_id = $_SESSION['verify_user_id'];
$type = $_POST['type'] ?? ''; // 'email' or 'phone'
$code = trim($_POST['code'] ?? '');

if (!$code || !$type) {
    echo json_encode(['success' => false, 'error' => 'Missing data.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found.']);
        exit;
    }

    if ($type === 'email') {
        if ($code === $user['email_token']) {
            $pdo->prepare("UPDATE users SET is_email_verified = 1 WHERE id = ?")->execute([$user_id]);
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid Email OTP.']);
            exit;
        }
    } elseif ($type === 'phone') {
        if ($code === $user['otp_code']) {
            $pdo->prepare("UPDATE users SET is_phone_verified = 1 WHERE id = ?")->execute([$user_id]);
            
            // Both verified, log them in fully
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            unset($_SESSION['verify_user_id']);

            echo json_encode(['success' => true, 'redirect' => 'profile.php']);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid WhatsApp OTP.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Unknown verification type.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
