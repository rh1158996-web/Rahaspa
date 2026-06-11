<?php
require_once 'includes/init.php';
require_once 'includes/auth_guard.php';
$pageTitle = isset($lang['book_now']) ? $lang['book_now'] : 'Book Appointment';
include 'includes/header.php';

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $branch_id  = (int)$_POST['branch_id'];
    $service_id = (int)$_POST['service_id'];
    $date       = $_POST['booking_date'];
    $time       = $_POST['booking_time'];
    $notes      = trim($_POST['notes'] ?? '');

    if (!$branch_id || !$service_id || !$date || !$time) {
        $error_msg = "Please complete all fields.";
    } else {
        // Double-booking prevention – UNIQUE KEY enforces it, but check here for friendly error
        $chk = $pdo->prepare("SELECT id FROM bookings WHERE branch_id=? AND booking_date=? AND booking_time=? AND status != 'Rejected'");
        $chk->execute([$branch_id, $date, $time]);
        if ($chk->fetch()) {
            $error_msg = $current_lang === 'ar' ? 'هذا الوقت محجوز بالفعل. الرجاء اختيار وقت آخر.' : 'This time slot is already taken. Please choose another time.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, branch_id, service_id, booking_date, booking_time, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$user_id, $branch_id, $service_id, $date, $time, $notes]);
            $booking_id = $pdo->lastInsertId();
            header("Location: payment_process.php?booking_id=" . $booking_id);
            exit;
        }
    }
}

$branches = $pdo->query("SELECT * FROM branches ORDER BY id")->fetchAll();
$services = $pdo->query("SELECT * FROM services ORDER BY price")->fetchAll();

$preselect_service = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$preselect_branch  = isset($_GET['branch_id'])  ? (int)$_GET['branch_id']  : 0;
?>

<section class="section" style="padding-top:2rem;">
    <div class="container">
        <div class="text-center" style="margin-bottom:3rem;">
            <h1 class="section-title"><?php echo $current_lang==='ar' ? 'حجز موعد' : 'Book an Appointment'; ?></h1>
            <p><?php echo $current_lang==='ar' ? 'اختر الجلسة والوقت المناسب، ثم أكمل الدفع لتأكيد موعدك.' : 'Select your session and preferred time, then complete payment to confirm your appointment.'; ?></p>
        </div>

        <div class="form-container">
            <?php if($error_msg): ?>
                <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form id="bookingForm" action="booking.php" method="POST">
                <!-- Booking for -->
                <div class="form-group">
                    <label class="form-label"><?php echo $current_lang==='ar' ? 'الحجز باسم' : 'Booking for'; ?></label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name'] . ' (' . $_SESSION['user_email'] . ')'); ?>" disabled style="background:#eee;">
                </div>

                <!-- Branch -->
                <div class="form-group">
                    <label for="branch_id" class="form-label"><?php echo $lang['select_branch']; ?> *</label>
                    <select id="branch_id" name="branch_id" class="form-control" required onchange="resetSlots()">
                        <option value="">-- <?php echo $lang['select_branch']; ?> --</option>
                        <?php foreach($branches as $b): $bname = $current_lang==='ar' ? $b['name_ar'] : $b['name_en']; ?>
                            <option value="<?php echo $b['id']; ?>" <?php echo ($preselect_branch==$b['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bname); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Service -->
                <div class="form-group">
                    <label for="service_id" class="form-label"><?php echo $lang['select_service']; ?> *</label>
                    <select id="service_id" name="service_id" class="form-control" required>
                        <option value="">-- <?php echo $lang['select_service']; ?> --</option>
                        <?php foreach($services as $s): $sname = $current_lang==='ar' ? $s['name_ar'] : $s['name_en']; ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($preselect_service==$s['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sname); ?> — <?php echo $s['duration_minutes']; ?> <?php echo $lang['mins']; ?> (<?php echo number_format($s['price'],0); ?> <?php echo $lang['sar']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date -->
                <div class="form-group">
                    <label for="booking_date" class="form-label"><?php echo $lang['select_date']; ?> *</label>
                    <input type="date" id="booking_date" name="booking_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required onchange="loadSlots()">
                </div>

                <!-- Time Slots (Dynamic AJAX) -->
                <div class="form-group" id="slots_section" style="display:none;">
                    <label class="form-label"><?php echo $lang['select_time']; ?> *</label>
                    <div id="slots_container" style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:0.5rem;"></div>
                    <input type="hidden" id="booking_time" name="booking_time" required>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label for="notes" class="form-label"><?php echo $current_lang==='ar' ? 'ملاحظات إضافية (اختياري)' : 'Additional Notes (Optional)'; ?></label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="<?php echo $current_lang==='ar' ? 'أي طلبات خاصة...' : 'Any special requests...'; ?>"></textarea>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary" style="width:100%;padding:1rem;font-size:1.1rem;" disabled>
                    <i class="fa-solid fa-credit-card"></i> <?php echo $lang['proceed_payment']; ?>
                </button>
            </form>
        </div>
    </div>
</section>

<script>
function resetSlots() {
    document.getElementById('slots_section').style.display = 'none';
    document.getElementById('slots_container').innerHTML = '';
    document.getElementById('booking_time').value = '';
    document.getElementById('submitBtn').disabled = true;
    const dateInput = document.getElementById('booking_date');
    if (dateInput.value) loadSlots();
}

function loadSlots() {
    const branch_id    = document.getElementById('branch_id').value;
    const booking_date = document.getElementById('booking_date').value;
    if (!branch_id || !booking_date) return;

    document.getElementById('slots_container').innerHTML = '<p style="color:#999;"><i class="fa-solid fa-spinner fa-spin"></i> Loading slots...</p>';
    document.getElementById('slots_section').style.display = 'block';
    document.getElementById('booking_time').value = '';
    document.getElementById('submitBtn').disabled = true;

    fetch('get_slots.php?branch_id=' + branch_id + '&date=' + booking_date)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('slots_container');
            if (!data.slots || data.slots.length === 0) {
                container.innerHTML = '<p style="color:#dc3545;"><?php echo $lang["no_slots"]; ?></p>';
                return;
            }
            container.innerHTML = '';
            data.slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = slot.display;
                btn.dataset.value = slot.value;
                btn.className = 'slot-btn';
                btn.style.cssText = slot.available
                    ? 'padding:0.6rem 1.2rem;border:2px solid var(--sage-green);background:#fff;color:var(--dark-gray);border-radius:8px;cursor:pointer;font-weight:500;transition:all .2s;font-size:0.95rem;'
                    : 'padding:0.6rem 1.2rem;border:2px solid #ddd;background:#f5f5f5;color:#aaa;border-radius:8px;cursor:not-allowed;font-size:0.95rem;text-decoration:line-through;';
                if (slot.available) {
                    btn.addEventListener('click', () => selectSlot(btn, slot.value));
                }
                container.appendChild(btn);
            });
        })
        .catch(() => {
            document.getElementById('slots_container').innerHTML = '<p style="color:#dc3545;">Error loading slots. Please try again.</p>';
        });
}

function selectSlot(btn, value) {
    document.querySelectorAll('.slot-btn').forEach(b => {
        b.style.background = '#fff';
        b.style.color = 'var(--dark-gray)';
    });
    btn.style.background = 'var(--sage-green)';
    btn.style.color = '#fff';
    document.getElementById('booking_time').value = value;
    document.getElementById('submitBtn').disabled = false;
}
</script>

<?php include 'includes/footer.php'; ?>
