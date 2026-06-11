<?php
/**
 * get_slots.php
 * AJAX endpoint – returns available time slots for a given branch + date.
 * Excludes blocked days, respects working hours, removes already-booked slots.
 */
header('Content-Type: application/json');
require_once 'includes/db.php';

$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;
$date      = isset($_GET['date'])      ? $_GET['date']            : '';

if (!$branch_id || !$date) {
    echo json_encode(['slots' => []]);
    exit;
}

// 1. Check if date is blocked
$blocked = $pdo->prepare("SELECT id FROM blocked_days WHERE branch_id = ? AND blocked_date = ?");
$blocked->execute([$branch_id, $date]);
if ($blocked->fetch()) {
    echo json_encode(['slots' => [], 'blocked' => true, 'message' => 'This day is unavailable.']);
    exit;
}

// 2. Get working hours for this branch on this day of week (0=Sun … 6=Sat)
$day_of_week = (int)date('w', strtotime($date));
$wh = $pdo->prepare("SELECT * FROM working_hours WHERE branch_id = ? AND day_of_week = ?");
$wh->execute([$branch_id, $day_of_week]);
$hours = $wh->fetch();

if (!$hours) {
    echo json_encode(['slots' => [], 'message' => 'Closed on this day.']);
    exit;
}

// 3. Generate 60-minute slots between start and end time
$start    = strtotime($hours['start_time']);
$end      = strtotime($hours['end_time']);
$interval = 60 * 60; // 60 minutes per slot
$slots_raw = [];
for ($t = $start; $t < $end; $t += $interval) {
    $slots_raw[] = date('H:i:s', $t);
}

// 4. Get already-booked slots for this branch + date
// We consider a slot taken if Confirmed/Completed, OR if Pending and created less than 15 minutes ago
$taken = $pdo->prepare("
    SELECT booking_time 
    FROM bookings 
    WHERE branch_id = ? AND booking_date = ? 
    AND (
        status IN ('Confirmed', 'Completed')
        OR (status = 'Pending' AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE))
    )
");
$taken->execute([$branch_id, $date]);
$taken_times = array_column($taken->fetchAll(), 'booking_time');

// 5. Build response
$slots = [];
$now = time();
foreach ($slots_raw as $slot_time) {
    // Don't show past slots for today
    $slot_ts = strtotime($date . ' ' . $slot_time);
    if ($slot_ts < $now) continue;

    $available = !in_array($slot_time, $taken_times);
    $slots[] = [
        'value'     => $slot_time,
        'display'   => date('h:i A', strtotime($slot_time)),
        'available' => $available,
    ];
}

echo json_encode(['slots' => $slots]);
?>
