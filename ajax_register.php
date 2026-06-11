<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$first_name     = trim($_POST['first_name'] ?? '');
$father_name    = trim($_POST['father_name'] ?? '');
$last_name      = trim($_POST['last_name'] ?? '');
$email          = strtolower(trim($_POST['email'] ?? ''));
$phone          = trim($_POST['phone'] ?? '');
$password       = $_POST['password'] ?? '';
$confirm_pass   = $_POST['confirm_password'] ?? '';

if (empty($first_name) || empty($father_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
    exit;
}

if ($password !== $confirm_pass) {
    echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'This email is already registered.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $email_otp = rand(100000, 999999);
    $phone_otp = rand(100000, 999999);

    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, father_name, last_name, email, phone, password_hash, email_token, otp_code)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$first_name, $father_name, $last_name, $email, $phone, $hash, $email_otp, $phone_otp]);
    $user_id = $pdo->lastInsertId();

    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['verify_user_id'] = $user_id;

    echo json_encode([
        'success' => true,
        'user_id' => $user_id,
        // Passing OTP back for local simulated UI modal
        'email_otp' => $email_otp,
        'phone_otp' => $phone_otp
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
