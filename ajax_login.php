<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'error' => 'Email and password required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if (!$user['is_email_verified'] || !$user['is_phone_verified']) {
            // Unverified: Gen new OTPs
            $email_otp = rand(100000, 999999);
            $phone_otp = rand(100000, 999999);
            $pdo->prepare("UPDATE users SET email_token=?, otp_code=? WHERE id=?")->execute([$email_otp, $phone_otp, $user['id']]);
            
            $_SESSION['verify_user_id'] = $user['id'];
            
            echo json_encode([
                'success' => true,
                'verified' => false,
                'email_otp' => $email_otp,
                'phone_otp' => $phone_otp,
                'is_email_verified' => $user['is_email_verified'],
                'is_phone_verified' => $user['is_phone_verified']
            ]);
            exit;
        }

        // Fully verified
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];

        $redirect = $_SESSION['redirect_after_login'] ?? 'profile.php';
        unset($_SESSION['redirect_after_login']);

        echo json_encode(['success' => true, 'verified' => true, 'redirect' => $redirect]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
