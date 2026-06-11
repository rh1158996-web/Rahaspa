<?php
require_once 'includes/init.php';
$pageTitle = isset($lang['login']) ? $lang['login'] : 'Login';
include 'includes/header.php';

if(isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}
?>

<style>
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
.modal-content { background: var(--white); padding: 2.5rem; border-radius: 15px; width: 90%; max-width: 450px; box-shadow: var(--shadow-lg); text-align: center; transform: translateY(20px); opacity: 0; transition: all 0.3s ease; }
.modal-overlay.active { display: flex; }
.modal-overlay.active .modal-content { transform: translateY(0); opacity: 1; }
.mock-message-box { background: #f8f9fa; border: 2px dashed #ccc; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem; color: #555; text-align: left; }
.mock-message-box strong { font-size: 1.5rem; letter-spacing: 4px; display: block; text-align: center; margin-top: 0.5rem; color: #333; }
.otp-input { text-align: center; font-size: 2rem; letter-spacing: 8px; font-weight: 700; }
</style>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="form-container" style="max-width: 480px;">
            <div class="text-center" style="margin-bottom: 2rem;">
                <div style="font-size: 3rem; color: var(--sage-green); margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <h1 style="color: var(--dark-gray);"><?php echo isset($lang['welcome_back']) ? $lang['welcome_back'] : 'Welcome Back'; ?></h1>
                <p style="color: var(--text-light);">Sign in to manage your sessions</p>
            </div>

            <div id="form-error" class="alert alert-error" style="display: none;"></div>

            <form id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email" class="form-label"><?php echo isset($lang['email']) ? $lang['email'] : 'Email'; ?></label>
                    <input type="email" id="email" name="email" class="form-control" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="password" class="form-label"><?php echo isset($lang['password']) ? $lang['password'] : 'Password'; ?></label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                </div>
                <button type="submit" id="btn-submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.05rem;">
                    <?php echo isset($lang['login_btn']) ? $lang['login_btn'] : 'Login'; ?>
                </button>
            </form>

            <div style="margin-top: 2rem; text-align: center; border-top: 1px solid #eee; padding-top: 1.5rem;">
                <p>Don't have an account? <a href="register.php" style="color: var(--sage-green-dark); font-weight: 600;"><?php echo isset($lang['register']) ? $lang['register'] : 'Register'; ?></a></p>
            </div>
        </div>
    </div>
</section>

<!-- MODALS FOR OTP -->
<div class="modal-overlay" id="emailModal">
    <div class="modal-content">
        <i class="fa-solid fa-envelope-open-text" style="font-size:3rem; color:var(--sage-green); margin-bottom:1rem;"></i>
        <h2>Verify Email</h2>
        <div class="mock-message-box" style="border-color:var(--sage-green);">
            <small>📧 Simulated Email Inbox (Dev Mode)</small>
            <br>Your email code is: <strong id="mock-email-otp">------</strong>
        </div>
        <div id="email-error" class="alert alert-error" style="display:none; padding: 0.5rem;"></div>
        <input type="text" id="input_email_otp" class="form-control otp-input" maxlength="6" placeholder="000000">
        <button id="btn-verify-email" class="btn btn-primary" style="width:100%; margin-top:1rem;">Verify Email</button>
    </div>
</div>

<div class="modal-overlay" id="phoneModal">
    <div class="modal-content">
        <i class="fa-brands fa-whatsapp" style="font-size:3rem; color:#25D366; margin-bottom:1rem;"></i>
        <h2>Verify WhatsApp</h2>
        <div class="mock-message-box" style="border-color:#25D366; background:#e8f5e9;">
            <small style="color:#25D366;"><i class="fa-brands fa-whatsapp"></i> Simulated WhatsApp (Dev Mode)</small>
            <br>Your WhatsApp code is: <strong id="mock-phone-otp">------</strong>
        </div>
        <div id="phone-error" class="alert alert-error" style="display:none; padding: 0.5rem;"></div>
        <input type="text" id="input_phone_otp" class="form-control otp-input" maxlength="6" placeholder="000000">
        <button id="btn-verify-phone" class="btn btn-primary" style="width:100%; margin-top:1rem; background:#25D366; border-color:#25D366;">Verify WhatsApp</button>
    </div>
</div>

<script>
let needsEmail = false;
let needsPhone = false;

document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit');
    const errBox = document.getElementById('form-error');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    errBox.style.display = 'none';

    const fd = new FormData();
    fd.append('email', document.getElementById('email').value);
    fd.append('password', document.getElementById('password').value);

    try {
        const res = await fetch('ajax_login.php', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.success) {
            if (data.verified) {
                window.location.href = data.redirect;
            } else {
                needsEmail = !data.is_email_verified;
                needsPhone = !data.is_phone_verified;
                document.getElementById('mock-email-otp').innerText = data.email_otp;
                document.getElementById('mock-phone-otp').innerText = data.phone_otp;
                
                if (needsEmail) document.getElementById('emailModal').classList.add('active');
                else if (needsPhone) document.getElementById('phoneModal').classList.add('active');
            }
        } else {
            errBox.innerText = data.error; errBox.style.display = 'block';
            btn.disabled = false; btn.innerText = 'Login';
        }
    } catch (err) {
        errBox.innerText = 'Network error.'; errBox.style.display = 'block';
        btn.disabled = false; btn.innerText = 'Login';
    }
});

// Verify Handlers
async function verifyCode(type, code, errBox, btn) {
    if(code.length !== 6) return false;
    btn.disabled = true;
    const fd = new FormData(); fd.append('type', type); fd.append('code', code);
    const res = await fetch('ajax_verify.php', { method: 'POST', body: fd });
    const data = await res.json();
    if(data.success) return data;
    errBox.innerText = data.error; errBox.style.display = 'block'; btn.disabled = false;
    return false;
}

document.getElementById('btn-verify-email').addEventListener('click', async function() {
    const data = await verifyCode('email', document.getElementById('input_email_otp').value, document.getElementById('email-error'), this);
    if(data) {
        document.getElementById('emailModal').classList.remove('active');
        if(needsPhone) document.getElementById('phoneModal').classList.add('active');
        else window.location.href = 'profile.php';
    }
});

document.getElementById('btn-verify-phone').addEventListener('click', async function() {
    const data = await verifyCode('phone', document.getElementById('input_phone_otp').value, document.getElementById('phone-error'), this);
    if(data) window.location.href = data.redirect;
});
</script>

<?php include 'includes/footer.php'; ?>
