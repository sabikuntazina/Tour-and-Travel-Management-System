<?php require_once __DIR__ . '/includes/auth.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
check_csrf();
$pid = (int)($_POST['package_id'] ?? 0);
$start = $_POST['start_date'] ?? '';
$people = max(1, (int)($_POST['people'] ?? 1));
$res = $conn->query("SELECT * FROM packages WHERE id=$pid AND status='active' LIMIT 1");
if (!$res || !$res->num_rows) redirect('packages.php');
$pkg = $res->fetch_assoc();
if ($people > (int)$pkg['seats_available']) {
    redirect('booking.php?package_id=' . $pid . '&err=no_seats');
}
$start_dt = date_create($start);
if (!$start_dt) redirect('booking.php?package_id=' . $pid);
$end_dt = clone $start_dt;
$end_dt->modify('+' . (int)$pkg['duration_days'] . ' day');
$total = (float)$pkg['price'] * $people;
$stmt = $conn->prepare("INSERT INTO bookings(user_id,package_id,start_date,end_date,people,total_amount,status) VALUES(?,?,?,?,?,?,'Pending')");
$uid = (int)$_SESSION['user_id'];
$sd = $start_dt->format('Y-m-d');
$ed = $end_dt->format('Y-m-d');
$stmt->bind_param('iissid', $uid, $pid, $sd, $ed, $people, $total);
$stmt->execute();
$bid = $stmt->insert_id;
$conn->query("INSERT INTO payments(booking_id,method,amount,status) VALUES($bid,'Unselected',$total,'Pending')");
// Notify user
$title = $conn->real_escape_string('Booking Created');
$msg = $conn->real_escape_string('Your booking #' . $bid . ' for ' . $pkg['title'] . ' has been created.');
$conn->query("INSERT INTO notifications(user_id,title,message) VALUES($uid,'$title','$msg')");
redirect('payment.php?booking_id=' . $bid);
