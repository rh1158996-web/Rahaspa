<?php
require_once 'includes/db.php';
$pageTitle = 'Verify Account';
include 'includes/header.php';

// Must come from registration
if (!isset($_SESSION['verify_user_id'])) {
    header("Location: register.php");
    exit;
}

$user_id = $_SESSION['verify_user_id'];

// Fetch the user's current data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_unset();
    header("Location: register.php");
    exit;
}

// If already fully verified, just log them in
if ($user['is_email_verified'] && $user['is_phone_verified']) {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    unset($_SESSION['verify_user_id']);
    header("Location: profile.php");
    exit;
}

$step        = isset($_GET['step']) ? $_GET['step'] : 'email';
$error       = '';
$success_msg = '';

// -------------------------------------------------------
// STEP 1 – Email OTP verification
// -------------------------------------------------------
if ($step === 'email' && !$user['is_email_verified']) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email_otp'])) {
        $entered = trim($_POST['email_otp']);
        if ($entered === $user['email_token']) {          // email_token stores the 6-digit email OTP
            $pdo->prepare("UPDATE users SET is_email_verified = 1 WHERE id = ?")
                ->execute([$user_id]);
            header("Location: verify.php?step=phone");
            exit;
        } else {
            $error = "Incorrect code. Please check and try again.";
        }
    }

    // Resend / regenerate Email OTP
    if (isset($_GET['resend']) && $_GET['resend'] === 'email') {
        $new_otp = rand(100000, 999999);
        $pdo->prepare("UPDATE users SET email_token = ? WHERE id = ?")
            ->execute([$new_otp, $user_id]);
        $pdo->prepare("SELECT * FROM users WHERE id = ?")->execute([$user_id]);
        $user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $user->execute([$user_id]);
        $user = $user->fetch();
        $success_msg = "A new verification code has been generated.";
    }
}

// -------------------------------------------------------
// STEP 2 – WhatsApp / Phone OTP verification
// -------------------------------------------------------
elseif ($step === 'phone' && $user['is_email_verified'] && !$user['is_phone_verified']) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_otp'])) {
        $entered = trim($_POST['phone_otp']);
        if ($entered === $user['otp_code']) {
            $pdo->prepare("UPDATE users SET is_phone_verified = 1 WHERE id = ?")
                ->execute([$user_id]);

            // Both verified → create session
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            unset($_SESSION['verify_user_id']);

            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'profile.php';
            unset($_SESSION['redirect_after_login']);
            header("Location: " . $redirect);
            exit;
        } else {
            $error = "Incorrect code. Please check and try again.";
        }
    }

    if (isset($_GET['resend']) && $_GET['resend'] === 'phone') {
        $new_otp = rand(100000, 999999);
        $pdo->prepare("UPDATE users SET otp_code = ? WHERE id = ?")
            ->execute([$new_otp, $user_id]);
        $user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $user->execute([$user_id]);
        $user = $user->fetch();
        $success_msg = "A new OTP has been generated.";
    }
}

// Edge case: email verified but accessing email step
elseif ($step === 'email' && $user['is_email_verified']) {
    header("Location: verify.php?step=phone");
    exit;
}
?>

<section class="section" style="padding-top: 2rem; min-height: 60vh;">
    <div class="container">
        <div class="form-container" style="max-width: 520px;">

            <!-- Progress Indicator -->
            <div style="display: flex; justify-content: center; gap: 1rem; margin-bottom: 2rem;">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $user['is_email_verified'] ? 'var(--sage-green)' : ($step === 'email' ? 'var(--dark-gray)' : '#ccc'); ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        <?php echo $user['is_email_verified'] ? '<i class="fa-solid fa-check"></i>' : '1'; ?>
                    </div>
                    <small style="font-weight: 600; color: <?php echo $step === 'email' ? 'var(--dark-gray)' : '#aaa'; ?>">Email</small>
                </div>
                <div style="flex: 1; height: 2px; background: <?php echo $user['is_email_verified'] ? 'var(--sage-green)' : '#ddd'; ?>; margin-top: 20px;"></div>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.4rem;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: <?php echo $user['is_phone_verified'] ? 'var(--sage-green)' : ($step === 'phone' ? 'var(--dark-gray)' : '#ccc'); ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        <?php echo $user['is_phone_verified'] ? '<i class="fa-solid fa-check"></i>' : '2'; ?>
                    </div>
                    <small style="font-weight: 600; color: <?php echo $step === 'phone' ? 'var(--dark-gray)' : '#aaa'; ?>">WhatsApp</small>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success_msg): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>

            <?php if ($step === 'email' && !$user['is_email_verified']): ?>
                <!-- ===== EMAIL VERIFICATION STEP ===== -->
                <div class="text-center" style="margin-bottom: 2rem;">
                    <div style="font-size: 3rem; color: var(--sage-green); margin-bottom: 1rem;">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <h2>Verify Your Email</h2>
                    <p>A verification code has been sent to <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
                </div>

                <!-- SIMULATED EMAIL PREVIEW BOX -->
                <div style="background: linear-gradient(135deg, var(--light-beige), var(--cream-white)); border: 2px dashed var(--sage-green); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                    <p style="font-size: 0.8rem; color: var(--text-light); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">📧 Simulated Email (Dev Mode)</p>
                    <p style="margin: 0; font-size: 0.9rem;">To: <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
                    <p style="margin: 0; font-size: 0.9rem;">Subject: Your Raha Spa Verification Code</p>
                    <hr style="margin: 0.8rem 0; border-color: #ddd;">
                    <p style="margin: 0 0 0.5rem;">Dear <?php echo htmlspecialchars($user['first_name']); ?>,</p>
                    <p style="margin: 0 0 1rem;">Your email verification code is:</p>
                    <div style="background: var(--dark-gray); color: white; text-align: center; font-size: 2.5rem; font-weight: 700; letter-spacing: 8px; padding: 1rem; border-radius: 8px;">
                        <?php echo $user['email_token']; ?>
                    </div>
                    <p style="margin: 1rem 0 0; font-size: 0.85rem; color: var(--text-light);">This code expires in 30 minutes.</p>
                </div>

                <form action="verify.php?step=email" method="POST">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Enter Email Verification Code</label>
                        <input type="text" name="email_otp" class="form-control"
                               maxlength="6" placeholder="000000"
                               style="text-align: center; font-size: 2rem; font-weight: 700; letter-spacing: 8px;"
                               required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">Verify Email &rarr;</button>
                </form>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="verify.php?step=email&resend=email" style="color: var(--sage-green-dark);">Resend code</a>
                </div>

            <?php elseif ($step === 'phone' && $user['is_email_verified'] && !$user['is_phone_verified']): ?>
                <!-- ===== PHONE / WHATSAPP VERIFICATION STEP ===== -->
                <div class="text-center" style="margin-bottom: 2rem;">
                    <div style="font-size: 3rem; color: #25D366; margin-bottom: 1rem;">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                    <h2>Verify WhatsApp Number</h2>
                    <p>A verification code has been sent to <strong><?php echo htmlspecialchars($user['phone']); ?></strong></p>
                </div>

                <!-- SIMULATED WHATSAPP MESSAGE BOX -->
                <div style="background: linear-gradient(135deg, #e8f5e9, #f1f8f2); border: 2px dashed #25D366; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
                    <p style="font-size: 0.8rem; color: var(--text-light); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;"><i class="fa-brands fa-whatsapp" style="color:#25D366;"></i> Simulated WhatsApp Message (Dev Mode)</p>
                    <p style="margin: 0; font-size: 0.9rem;">To: <strong><?php echo htmlspecialchars($user['phone']); ?></strong></p>
                    <hr style="margin: 0.8rem 0; border-color: #ddd;">
                    <div style="background: white; border-radius: 12px; padding: 1rem; border-bottom-left-radius: 0; border: 1px solid #e0e0e0;">
                        <p style="margin: 0 0 0.8rem;">🌿 *Raha Spa* - Verification</p>
                        <p style="margin: 0 0 0.8rem;">Your WhatsApp OTP code is:</p>
                        <div style="background: #25D366; color: white; text-align: center; font-size: 2.5rem; font-weight: 700; letter-spacing: 8px; padding: 1rem; border-radius: 8px;">
                            <?php echo $user['otp_code']; ?>
                        </div>
                        <p style="margin: 0.8rem 0 0; font-size: 0.85rem; color: #888;">Valid for 10 minutes. Do not share.</p>
                    </div>
                </div>

                <form action="verify.php?step=phone" method="POST">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Enter WhatsApp OTP Code</label>
                        <input type="text" name="phone_otp" class="form-control"
                               maxlength="6" placeholder="000000"
                               style="text-align: center; font-size: 2rem; font-weight: 700; letter-spacing: 8px;"
                               required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; background: #25D366; border-color: #25D366;">
                        <i class="fa-brands fa-whatsapp"></i> Verify WhatsApp &rarr;
                    </button>
                </form>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="verify.php?step=phone&resend=phone" style="color: #25D366;">Resend OTP</a>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 2rem; font-size: 0.9rem;">
                <a href="logout.php" style="color: #999;">Cancel &amp; Start Over</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
