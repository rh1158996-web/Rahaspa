<?php
require_once 'includes/init.php';
$pageTitle = isset($lang['about']) ? $lang['about'] : 'About Us';
include 'includes/header.php';
?>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div class="text-center" style="margin-bottom: 3rem;">
            <h1 class="section-title"><?php echo $current_lang === 'ar' ? 'عن رها سبا' : 'About Raha Spa'; ?></h1>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 4rem; align-items: center;">
            <div>
                <img src="https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Spa Interior" style="border-radius: 15px; box-shadow: var(--shadow-lg);">
            </div>
            <div>
                <h2 style="font-size: 2rem; color: var(--sage-green-dark);"><?php echo $current_lang === 'ar' ? 'مهمتنا' : 'Our Mission'; ?></h2>
                <p><?php echo $current_lang === 'ar' ? 'في رها سبا، مهمتنا هي توفير واحة من الهدوء في عالم مليء بالضغوط. نحن نؤمن بأن العلاج بالتدليك ليس مجرد رفاهية، بل هو عنصر حيوي لنمط حياة صحي. هدفنا هو مساعدتك في تحقيق الاسترخاء العميق، وتخفيف الألم، وتحسين صحتك العامة.' : 'At Raha Spa, our mission is to provide an oasis of calm in a chaotic world. We believe that regular massage therapy is not merely a luxury, but a vital component of a healthy lifestyle. Our goal is to help you achieve profound relaxation, relieve chronic pain, and improve your overall well-being.'; ?></p>
                
                <h2 style="font-size: 2rem; color: var(--sage-green-dark); margin-top: 2rem;"><?php echo $current_lang === 'ar' ? 'رؤيتنا' : 'Our Vision'; ?></h2>
                <p><?php echo $current_lang === 'ar' ? 'أن نكون الوجهة الأولى للعافية المعروفة بخدمتنا الاستثنائية، ومعالجينا ذوي المهارات العالية، والبيئة الهادئة بعمق والتي تتجاوز توقعات عملائنا باستمرار.' : 'To be the premier wellness destination known for our exceptional service, highly skilled therapists, and a deeply tranquil environment that consistently exceeds our clients\' expectations.'; ?></p>
                
                <h2 style="font-size: 2rem; color: var(--sage-green-dark); margin-top: 2rem;"><?php echo $current_lang === 'ar' ? 'فريقنا' : 'The Team'; ?></h2>
                <p><?php echo $current_lang === 'ar' ? 'يتكون فريقنا من معالجين معتمدين وذوي خبرة شغوفين بالشفاء. يخضع كل معالج لتدريب مستمر ليقدم لك تقنيات التدليك الأكثر فعالية وابتكارًا المتاحة.' : 'Our team consists of certified, experienced massage therapists who are passionate about healing. Each therapist undergoes continuous training to bring you the most effective and innovative massage techniques available.'; ?></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
