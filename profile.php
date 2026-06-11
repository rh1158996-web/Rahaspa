<?php
require_once 'includes/auth_guard.php';
require_once 'includes/db.php';
$pageTitle = $lang['my_account'] ?? 'My Account';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$full_name = $user['first_name'] . ' ' . $user['father_name'] . ' ' . $user['last_name'];

$stmt = $pdo->prepare("
    SELECT b.*, s.name_en, s.name_ar, br.name_en as branch_en, br.name_ar as branch_ar,
           p.status as payment_status, p.amount as payment_amount,
           r.id as review_id
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    LEFT JOIN branches br ON b.branch_id = br.id
    LEFT JOIN payments p ON b.id = p.booking_id
    LEFT JOIN reviews r ON b.id = r.booking_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>

<section class="section" style="padding-top:2rem;">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
            <h1 style="margin:0;"><?php echo $lang['my_account']; ?></h1>
            <a href="logout.php" class="btn btn-secondary"><?php echo $lang['logout']; ?></a>
        </div>

        <div style="display:grid;grid-template-columns:280px 1fr;gap:2rem;">
            <!-- Profile Sidebar -->
            <div style="background:var(--white);border-radius:15px;padding:2rem;box-shadow:var(--shadow-sm);align-self:start;">
                <div style="text-align:center;margin-bottom:1.5rem;">
                    <div style="width:80px;height:80px;background:var(--sage-green);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 1rem;">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <h3 style="margin:0;"><?php echo htmlspecialchars($full_name); ?></h3>
                    <p style="color:var(--text-light);font-size:0.85rem;"><?php echo $current_lang==='ar' ? 'عضو منذ ' : 'Member since '; ?><?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                </div>
                <hr style="border:0;border-top:1px solid #eee;margin:1rem 0;">
                <p style="margin-bottom:0.5rem;"><strong><i class="fa-regular fa-envelope"></i></strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p style="margin-bottom:0.5rem;"><strong><i class="fa-brands fa-whatsapp"></i></strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <hr style="border:0;border-top:1px solid #eee;margin:1rem 0;">
                <div style="display:flex;flex-direction:column;gap:0.5rem;font-size:0.85rem;">
                    <span><?php echo $user['is_email_verified'] ? '✅ Email Verified' : '❌ Email Not Verified'; ?></span>
                    <span><?php echo $user['is_phone_verified'] ? '✅ WhatsApp Verified' : '❌ WhatsApp Not Verified'; ?></span>
                </div>
            </div>

            <!-- Booking History -->
            <div style="background:var(--white);border-radius:15px;padding:2rem;box-shadow:var(--shadow-sm);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
                    <h3 style="margin:0;"><?php echo $lang['booking_history']; ?></h3>
                    <a href="booking.php" class="btn btn-primary" style="padding:0.5rem 1.2rem;font-size:0.9rem;"><?php echo $lang['book_now']; ?></a>
                </div>

                <?php if(count($bookings) > 0): ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;text-align:<?php echo $dir === 'rtl' ? 'right' : 'left'; ?>;">
                        <thead>
                            <tr style="background:#f9f9f9;border-bottom:2px solid #eee;">
                                <th style="padding:0.8rem;"><?php echo $current_lang==='ar' ? 'الجلسة' : 'Session'; ?></th>
                                <th style="padding:0.8rem;"><?php echo $current_lang==='ar' ? 'الفرع' : 'Branch'; ?></th>
                                <th style="padding:0.8rem;"><?php echo $current_lang==='ar' ? 'التاريخ والوقت' : 'Date & Time'; ?></th>
                                <th style="padding:0.8rem;"><?php echo $current_lang==='ar' ? 'الحالة' : 'Status'; ?></th>
                                <th style="padding:0.8rem;"><?php echo $current_lang==='ar' ? 'الدفع' : 'Payment'; ?></th>
                                <th style="padding:0.8rem;"><?php echo $current_lang==='ar' ? 'إجراء' : 'Action'; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $b):
                                $sname  = $current_lang === 'ar' ? $b['name_ar'] : $b['name_en'];
                                $bname  = $b['branch_ar'] ? ($current_lang === 'ar' ? $b['branch_ar'] : $b['branch_en']) : '-';
                                $statuses = ['Pending'=>['#856404','⏳'], 'Confirmed'=>['#155724','✅'], 'Completed'=>['#0d4c6e','🌿'], 'Rejected'=>['#721c24','❌']];
                                $sc = $statuses[$b['status']] ?? ['#666','?'];
                            ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:0.8rem;"><strong><?php echo htmlspecialchars($sname); ?></strong></td>
                                <td style="padding:0.8rem;"><?php echo htmlspecialchars($bname); ?></td>
                                <td style="padding:0.8rem;">
                                    <?php echo date('d/m/Y', strtotime($b['booking_date'])); ?><br>
                                    <small><?php echo date('h:i A', strtotime($b['booking_time'])); ?></small>
                                </td>
                                <td style="padding:0.8rem;">
                                    <span style="font-weight:600;color:<?php echo $sc[0]; ?>;"><?php echo $sc[1]; ?> <?php echo $b['status']; ?></span>
                                </td>
                                <td style="padding:0.8rem;">
                                    <?php if($b['payment_status'] === 'Completed'): ?>
                                        <span style="color:#28a745;font-weight:600;">✅ <?php echo number_format($b['payment_amount'],0); ?> <?php echo $lang['sar']; ?></span>
                                    <?php elseif($b['payment_status'] === 'Pending'): ?>
                                        <span style="color:#f39c12;">⏳ Pending</span>
                                    <?php else: ?>
                                        <span style="color:#999;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:0.8rem;">
                                    <?php if($b['status'] === 'Completed' && !$b['review_id']): ?>
                                        <a href="leave_review.php?booking_id=<?php echo $b['id']; ?>" class="btn btn-primary" style="padding:0.4rem 0.8rem;font-size:0.85rem;">
                                            ⭐ <?php echo $current_lang==='ar' ? 'تقييم' : 'Review'; ?>
                                        </a>
                                    <?php elseif($b['review_id']): ?>
                                        <span style="color:var(--sage-green);font-size:0.85rem;">✅ <?php echo $current_lang==='ar' ? 'تم التقييم' : 'Reviewed'; ?></span>
                                    <?php else: ?>
                                        <span style="color:#ccc;font-size:0.85rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div style="text-align:center;padding:3rem;color:var(--text-light);">
                        <i class="fa-solid fa-calendar-xmark" style="font-size:3rem;margin-bottom:1rem;color:#ccc;display:block;"></i>
                        <p><?php echo $current_lang==='ar' ? 'لا يوجد لديك جلسات سابقة.' : 'No sessions yet.'; ?></p>
                        <a href="booking.php" class="btn btn-primary" style="margin-top:1rem;"><?php echo $lang['book_now']; ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
