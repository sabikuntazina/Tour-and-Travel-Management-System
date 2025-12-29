<?php require_once __DIR__ . '/includes/auth.php';
require_login();
$bid = (int)($_GET['booking_id'] ?? 0);
$booking = null;
if ($bid) {
  $res = $conn->query("SELECT b.*, p.title FROM bookings b JOIN packages p ON p.id=b.package_id WHERE b.id=$bid AND b.user_id=" . (int)$_SESSION['user_id'] . ' LIMIT 1');
  $booking = $res && $res->num_rows ? $res->fetch_assoc() : null;
}
if (!$booking) {
  redirect('dashboard.php');
}
include __DIR__ . '/includes/header.php'; ?>
<div class="container py-4" style="max-width:720px;">
  <h1 class="mb-3">Payment</h1>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="mb-1"><?php echo esc($booking['title']); ?></h5>
      <div class="small text-muted">From <?php echo esc($booking['start_date']); ?> to <?php echo esc($booking['end_date']); ?> • <?php echo (int)$booking['people']; ?> people</div>
      <div class="fw-bold mt-2">Amount: <?php echo price($booking['total_amount']); ?></div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5>Choose Payment Method</h5>
      <div class="row g-2">
        <?php $methods = ['SSLCommerz', 'Stripe', 'bKash', 'Offline'];
        foreach ($methods as $m) {
          $q = http_build_query(['booking_id' => $bid, 'status' => 'success', 'method' => $m]);
          echo '<div class="col-6 col-md-3 d-grid"><a class="btn btn-outline-primary" href="payment_callback.php?' . $q . '">' . $m . '</a></div>';
        } ?>
      </div>
      <div class="small text-muted mt-2">Online payments are simulated for demo. Offline will mark payment as Pending.</div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>