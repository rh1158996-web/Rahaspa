<?php
require_once 'includes/init.php';
$pageTitle = isset($lang['contact']) ? $lang['contact'] : 'Contact Us';
include 'includes/header.php';

$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success_msg = $current_lang === 'ar' ? "شكرًا لتواصلك معنا! لقد تم إرسال رسالتك بنجاح. سنرد عليك قريبًا." : "Thank you for reaching out! Your message has been sent successfully. We will get back to you soon.";
}
?>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="text-center" style="margin-bottom: 3rem;">
            <h1 class="section-title"><?php echo $current_lang === 'ar' ? 'تواصل معنا' : 'Get in Touch'; ?></h1>
            <p><?php echo $current_lang === 'ar' ? 'هل لديك أسئلة أو تحتاج إلى مساعدة؟ اتصل بنا وسنكون سعداء بمساعدتك.' : 'Have questions or need assistance? Contact us and we\'ll be happy to help.'; ?></p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 4rem;">
            <div>
                <h3 style="margin-bottom: 1.5rem; color: var(--sage-green-dark);"><?php echo $current_lang === 'ar' ? 'معلومات الاتصال' : 'Contact Information'; ?></h3>
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-start;">
                    <div style="background-color: var(--light-beige); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: var(--sage-green);">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.2rem;"><?php echo $current_lang === 'ar' ? 'العنوان' : 'Address'; ?></h4>
                        <p><?php echo $current_lang === 'ar' ? ($settings['address_ar'] ?? 'المملكة العربية السعودية') : ($settings['address_en'] ?? 'Saudi Arabia'); ?></p>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-start;">
                    <div style="background-color: var(--light-beige); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: var(--sage-green);">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.2rem;"><?php echo $current_lang === 'ar' ? 'الهاتف' : 'Phone'; ?></h4>
                        <p><?php echo htmlspecialchars($settings['contact_phone'] ?? '+966 50 000 0000'); ?></p>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-start;">
                    <div style="background-color: var(--light-beige); width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: var(--sage-green);">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.2rem;"><?php echo $current_lang === 'ar' ? 'البريد الإلكتروني' : 'Email'; ?></h4>
                        <p><?php echo htmlspecialchars($settings['contact_email'] ?? 'info@rahaspa.com'); ?></p>
                    </div>
                </div>

            </div>

            <div class="form-container" style="margin: 0; padding: 2rem;">
                <h3 style="margin-bottom: 1.5rem;"><?php echo $current_lang === 'ar' ? 'أرسل رسالة' : 'Send a Message'; ?></h3>
                
                <?php if($success_msg): ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-check-circle"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label"><?php echo $current_lang === 'ar' ? 'الاسم' : 'Name'; ?></label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label"><?php echo $current_lang === 'ar' ? 'البريد الإلكتروني' : 'Email'; ?></label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="subject" class="form-label"><?php echo $current_lang === 'ar' ? 'الموضوع' : 'Subject'; ?></label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="message" class="form-label"><?php echo $current_lang === 'ar' ? 'الرسالة' : 'Message'; ?></label>
                        <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><?php echo $current_lang === 'ar' ? 'إرسال الرسالة' : 'Send Message'; ?></button>
                </form>
            </div>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
