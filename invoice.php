<?php require_once __DIR__ . '/includes/auth.php';
require_login();
$bid = (int)($_GET['booking_id'] ?? 0);
$booking = null;
if ($bid) {
  $sql = "SELECT b.*, u.name uname, u.email uemail, p.title, p.location FROM bookings b JOIN users u ON u.id=b.user_id JOIN packages p ON p.id=b.package_id WHERE b.id=$bid AND b.user_id=" . (int)$_SESSION['user_id'] . ' LIMIT 1';
  $res = $conn->query($sql);
  $booking = $res && $res->num_rows ? $res->fetch_assoc() : null;
}
if (!$booking) {
  redirect('dashboard.php');
}
$pay = $conn->query("SELECT * FROM payments WHERE booking_id=$bid ORDER BY id DESC LIMIT 1");
$payment = $pay && $pay->num_rows ? $pay->fetch_assoc() : null;
include __DIR__ . '/includes/header.php'; ?>
<div class="container py-4" style="max-width:900px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Invoice</h1>
    <div>
      <button class="btn btn-outline-secondary me-2" onclick="window.print()">Print</button>
      <button class="btn btn-primary" onclick="downloadInvoice()">Download PDF</button>
    </div>
  </div>
  <div id="invoice" class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <h3 class="mb-1"><?php echo esc(SITE_NAME); ?></h3>
          <div class="small text-muted">support@travelnext.example</div>
        </div>
        <div class="col-md-6 text-md-end">
          <div><strong>Invoice #</strong> <?php echo (int)$booking['id']; ?></div>
          <div><strong>Date</strong> <?php echo esc(date('Y-m-d')); ?></div>
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-md-6">
          <h5>Bill To</h5>
          <div><?php echo esc($booking['uname']); ?></div>
          <div class="small text-muted"><?php echo esc($booking['uemail']); ?></div>
        </div>
        <div class="col-md-6 text-md-end">
          <h5>Booking</h5>
          <div><?php echo esc($booking['title']); ?> (<?php echo esc($booking['location']); ?>)</div>
          <div class="small text-muted">From <?php echo esc($booking['start_date']); ?> to <?php echo esc($booking['end_date']); ?> • <?php echo (int)$booking['people']; ?> people</div>
        </div>
      </div>
      <table class="table mt-3">
        <thead>
          <tr>
            <th>Description</th>
            <th class="text-end">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><?php echo esc($booking['title']); ?></td>
            <td class="text-end"><?php echo price($booking['total_amount']); ?></td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th>Total</th>
            <th class="text-end"><?php echo price($booking['total_amount']); ?></th>
          </tr>
        </tfoot>
      </table>
      <div class="mt-3">
        <strong>Payment Method:</strong> <?php echo esc($payment['method'] ?? 'Unselected'); ?>
        <span class="ms-3"><strong>Status:</strong> <?php echo esc($payment['status'] ?? 'Pending'); ?></span>
        <?php if (!empty($payment['transaction_id'])) echo '<span class="ms-3"><strong>Txn:</strong> ' . esc($payment['transaction_id']) . '</span>'; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>