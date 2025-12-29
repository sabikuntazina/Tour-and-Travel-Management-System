<?php require_once __DIR__ . '/includes/auth.php';
require_login();
$bid = (int)($_GET['booking_id'] ?? 0);
$status = $_GET['status'] ?? 'failed';
$method = $_GET['method'] ?? 'Unknown';
$res = $conn->query("SELECT * FROM bookings WHERE id=$bid AND user_id=" . (int)$_SESSION['user_id'] . ' LIMIT 1');
if (!$res || !$res->num_rows) redirect('dashboard.php');
$booking = $res->fetch_assoc();
$pay = $conn->query("SELECT id FROM payments WHERE booking_id=$bid ORDER BY id DESC LIMIT 1");
$pid = $pay && $pay->num_rows ? $pay->fetch_assoc()['id'] : 0;
if ($method === 'Offline') {
  $conn->query("UPDATE payments SET method='Offline', status='Pending' WHERE id=$pid");
  $conn->query("UPDATE bookings SET status='Pending' WHERE id=$bid");
  redirect('invoice.php?booking_id=' . $bid);
}
if ($status === 'success') {
  $txn = 'TXN' . time() . rand(100, 999);
  $conn->query("UPDATE payments SET method='" . $conn->real_escape_string($method) . "', status='Paid', transaction_id='" . $txn . "' WHERE id=$pid");
  // Try to deduct seats atomically; if not enough, keep booking pending
  $pkgId = (int)$booking['package_id'];
  $people = (int)$booking['people'];
  $conn->query("UPDATE packages SET seats_available = seats_available - $people WHERE id=$pkgId AND seats_available >= $people");
  if ($conn->affected_rows > 0) {
    $conn->query("UPDATE bookings SET status='Confirmed' WHERE id=$bid");
  } else {
    $conn->query("UPDATE bookings SET status='Pending' WHERE id=$bid");
  }
} else {
  $conn->query("UPDATE payments SET method='" . $conn->real_escape_string($method) . "', status='Failed' WHERE id=$pid");
  $conn->query("UPDATE bookings SET status='Pending' WHERE id=$bid");
}
redirect('invoice.php?booking_id=' . $bid);
