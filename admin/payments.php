<?php require_once __DIR__ . '/header.php';
// Handle refund
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'refund') {
  check_csrf();
  $pid = (int)($_POST['pid'] ?? 0);
  $res = $conn->query("SELECT py.*, b.status bstatus, b.id bid, b.package_id, b.people FROM payments py JOIN bookings b ON b.id=py.booking_id WHERE py.id=$pid LIMIT 1");
  if ($res && $res->num_rows) {
    $p = $res->fetch_assoc();
    if ($p['status'] === 'Paid') {
      $conn->query("UPDATE payments SET status='Refunded', transaction_id=CONCAT('REF',UNIX_TIMESTAMP()) WHERE id=$pid");
      if ($p['bstatus'] === 'Confirmed') {
        $conn->query("UPDATE bookings SET status='Canceled' WHERE id=" . (int)$p['bid']);
        $conn->query("UPDATE packages SET seats_available = seats_available + " . (int)$p['people'] . " WHERE id=" . (int)$p['package_id']);
      }
    }
  }
}

$filter = $_GET['status'] ?? '';
$method = $_GET['method'] ?? '';
$where = [];
if ($filter !== '') $where[] = "py.status='" . $conn->real_escape_string($filter) . "'";
if ($method !== '') $where[] = "py.method='" . $conn->real_escape_string($method) . "'";
$whereSql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Payments</h2>
  <form class="d-flex" method="get">
    <select class="form-select" name="method">
      <option value="">All Methods</option>
      <?php foreach (['SSLCommerz', 'Stripe', 'bKash', 'Offline'] as $m) {
        $sel = $method === $m ? 'selected' : '';
        echo "<option $sel>$m</option>";
      } ?>
    </select>
    <select class="form-select ms-2" name="status">
      <option value="">All Status</option>
      <?php foreach (['Pending', 'Paid', 'Failed', 'Refunded'] as $s) {
        $sel = $filter === $s ? 'selected' : '';
        echo "<option $sel>$s</option>";
      } ?>
    </select>
    <button class="btn btn-outline-secondary ms-2">Filter</button>
  </form>
</div>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Booking</th>
        <th>Customer</th>
        <th>Method</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Txn</th>
        <th>Date</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php $sql = "SELECT py.*, b.user_id, b.id bid, p.title, u.name uname FROM payments py JOIN bookings b ON b.id=py.booking_id JOIN packages p ON p.id=b.package_id JOIN users u ON u.id=b.user_id" . $whereSql . " ORDER BY py.created_at DESC";
      $res = $conn->query($sql);
      if ($res && $res->num_rows) {
        while ($row = $res->fetch_assoc()) {
          echo '<tr>';
          echo '<td>' . (int)$row['id'] . '</td>';
          echo '<td>#' . (int)$row['bid'] . ' — ' . esc($row['title']) . '</td>';
          echo '<td>' . esc($row['uname']) . '</td>';
          echo '<td>' . esc($row['method']) . '</td>';
          echo '<td>' . price($row['amount']) . '</td>';
          echo '<td>' . esc($row['status']) . '</td>';
          echo '<td>' . esc($row['transaction_id']) . '</td>';
          echo '<td>' . esc($row['created_at']) . '</td>';
          echo '<td class="text-nowrap">';
          if ($row['status'] === 'Paid') {
            echo '<form method="post" onsubmit="return confirm(\'Refund this payment?\')" class="d-inline">' . csrf_input() . '<input type="hidden" name="action" value="refund"><input type="hidden" name="pid" value="' . (int)$row['id'] . '"><button class="btn btn-sm btn-outline-danger">Refund</button></form>';
          }
          echo '</td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="9" class="text-center text-muted">No payments.</td></tr>';
      } ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>