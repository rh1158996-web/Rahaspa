<?php
require_once 'includes/init.php';
$pageTitle = isset($lang['register']) ? $lang['register'] : 'Register';
include 'includes/header.php';

if(isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}
?>

<style>
/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}
.modal-content {
    background: var(--white);
    padding: 2.5rem;
    border-radius: 15px;
    width: 90%;
    max-width: 450px;
    box-shadow: var(--shadow-lg);
    text-align: center;
    position: relative;
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s ease;
}
.modal-overlay.active {
    display: flex;
}
.modal-overlay.active .modal-content {
    transform: translateY(0);
    opacity: 1;
}
.mock-message-box {
    background: #f8f9fa;
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    color: #555;
    text-align: left;
}
.mock-message-box strong { font-size: 1.5rem; letter-spacing: 4px; display: block; text-align: center; margin-top: 0.5rem; color: #333; }
.otp-input {
    text-align: center;
    font-size: 2rem;
    letter-spacing: 8px;
    font-weight: 700;
}
</style>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="form-container" style="max-width: 620px;">
            <div class="text-center" style="margin-bottom: 2rem;">
                <div style="font-size: 3rem; color: var(--sage-green); margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h1 style="color: var(--dark-gray);"><?php echo isset($lang) ? $lang['create_account'] : 'Create Account'; ?></h1>
                <p>Join <?php echo isset($site_name) ? htmlspecialchars($site_name) : 'Raha Spa'; ?> to manage your sessions online.</p>
            </div>

            <div id="form-error" class="alert alert-error" style="display: none;"></div>

            <form id="registerForm" novalidate>
                <p style="font-weight: 600; color: var(--dark-gray); margin-bottom: 0.5rem;">Full Name</p>
                <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1rem;">
                    <div>
                        <label class="form-label"><?php echo isset($lang['first_name']) ? $lang['first_name'] : 'First Name'; ?> *</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label"><?php echo isset($lang['father_name']) ? $lang['father_name'] : 'Father Name'; ?> *</label>
                        <input type="text" name="father_name" id="father_name" class="form-control" required>
                    </div>
                    <div>
                        <label class="form-label"><?php echo isset($lang['last_name']) ? $lang['last_name'] : 'Last Name'; ?> *</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" required>
                    </div>
                </div>

                <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-top: 0.5rem;">
                    <div>
                        <label class="form-label"><?php echo isset($lang['email']) ? $lang['email'] : 'Email Address'; ?> *</label>
                        <input type="email" name="email" id="email" class="form-control" required autocomplete="email">
                    </div>
                    <div>
                        <label class="form-label"><?php echo isset($lang['phone']) ? $lang['phone'] : 'WhatsApp Number'; ?> *</label>
                        <input type="tel" name="phone" id="phone" class="form-control" required placeholder="+966 5X XXX XXXX">
                    </div>
                </div>

                <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem; margin-top: 0.5rem;">
                    <div>
                        <label class="form-label"><?php echo isset($lang['password']) ? $lang['password'] : 'Password'; ?> *</label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="6" autocomplete="new-password">
                    </div>
                    <div>
                        <label class="form-label"><?php echo isset($lang['confirm_password']) ? $lang['confirm_password'] : 'Confirm Password'; ?> *</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.05rem; margin-top: 1rem;">
                    <i class="fa-solid fa-arrow-right"></i> Create Account & Verify
                </button>
            </form>

            <div style="margin-top: 2rem; text-align: center; border-top: 1px solid #eee; padding-top: 1.5rem;">
                <p>Already have an account? <a href="login.php" style="color: var(--sage-green-dark); font-weight: 600;">Login</a></p>
            </div>
        </div>
    </div>
</section>

<!-- MODALS FOR OTP -->
<!-- Email OTP Modal -->
<div class="modal-overlay" id="emailModal">
    <div class="modal-content">
        <i class="fa-solid fa-envelope-open-text" style="font-size:3rem; color:var(--sage-green); margin-bottom:1rem;"></i>
        <h2>Verify Email</h2>
        <p>Enter the code sent to your email.</p>
        
        <div class="mock-message-box" style="border-color:var(--sage-green);">
            <small>📧 Simulated Email Inbox (Dev Mode)</small>
            <br>Your email code is:
            <strong id="mock-email-otp">------</strong>
        </div>

        <div id="email-error" class="alert alert-error" style="display:none; padding: 0.5rem;"></div>
        <input type="text" id="input_email_otp" class="form-control otp-input" maxlength="6" placeholder="000000">
        <button id="btn-verify-email" class="btn btn-primary" style="width:100%; margin-top:1rem;">Verify Email</button>
    </div>
</div>

<!-- WhatsApp OTP Modal -->
<div class="modal-overlay" id="phoneModal">
    <div class="modal-content">
        <i class="fa-brands fa-whatsapp" style="font-size:3rem; color:#25D366; margin-bottom:1rem;"></i>
        <h2>Verify WhatsApp</h2>
        <p>Enter the code sent to your WhatsApp.</p>
        
        <div class="mock-message-box" style="border-color:#25D366; background:#e8f5e9;">
            <small style="color:#25D366;"><i class="fa-brands fa-whatsapp"></i> Simulated WhatsApp (Dev Mode)</small>
            <br>Your WhatsApp code is:
            <strong id="mock-phone-otp">------</strong>
        </div>

        <div id="phone-error" class="alert alert-error" style="display:none; padding: 0.5rem;"></div>
        <input type="text" id="input_phone_otp" class="form-control otp-input" maxlength="6" placeholder="000000">
        <button id="btn-verify-phone" class="btn btn-primary" style="width:100%; margin-top:1rem; background:#25D366; border-color:#25D366;">Verify WhatsApp</button>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit');
    const errBox = document.getElementById('form-error');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    errBox.style.display = 'none';

    const formData = new FormData();
    ['first_name','father_name','last_name','email','phone','password','confirm_password'].forEach(id => {
        formData.append(id, document.getElementById(id).value);
    });

    try {
        const res = await fetch('ajax_register.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            // Show Email Modal and populate mock OTP
            document.getElementById('mock-email-otp').innerText = data.email_otp;
            document.getElementById('mock-phone-otp').innerText = data.phone_otp;
            document.getElementById('emailModal').classList.add('active');
        } else {
            errBox.innerText = data.error;
            errBox.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Create Account & Verify';
        }
    } catch (err) {
        errBox.innerText = 'Network error. Please try again.';
        errBox.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i> Create Account & Verify';
    }
});

// Verify Email
document.getElementById('btn-verify-email').addEventListener('click', async function() {
    const code = document.getElementById('input_email_otp').value;
    const errBox = document.getElementById('email-error');
    if(code.length !== 6) return;
    
    this.disabled = true;
    const fd = new FormData(); fd.append('type', 'email'); fd.append('code', code);
    const res = await fetch('ajax_verify.php', { method: 'POST', body: fd });
    const data = await res.json();
    
    if (data.success) {
        document.getElementById('emailModal').classList.remove('active');
        document.getElementById('phoneModal').classList.add('active');
    } else {
        errBox.innerText = data.error;
        errBox.style.display = 'block';
        this.disabled = false;
    }
});

// Verify Phone
document.getElementById('btn-verify-phone').addEventListener('click', async function() {
    const code = document.getElementById('input_phone_otp').value;
    const errBox = document.getElementById('phone-error');
    if(code.length !== 6) return;
    
    this.disabled = true;
    const fd = new FormData(); fd.append('type', 'phone'); fd.append('code', code);
    const res = await fetch('ajax_verify.php', { method: 'POST', body: fd });
    const data = await res.json();
    
    if (data.success) {
        window.location.href = data.redirect;
    } else {
        errBox.innerText = data.error;
        errBox.style.display = 'block';
        this.disabled = false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
