<?php require_once __DIR__ . '/header.php';
// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $bid = (int)($_POST['bid'] ?? 0);
  $new = trim($_POST['status'] ?? '');
  if ($bid && in_array($new, ['Pending', 'Confirmed', 'Canceled'])) {
    $res = $conn->query("SELECT * FROM bookings WHERE id=$bid LIMIT 1");
    if ($res && $res->num_rows) {
      $b = $res->fetch_assoc();
      $old = $b['status'];
      if ($new !== $old) {
        // Adjust seats if needed
        $pkgId = (int)$b['package_id'];
        $people = (int)$b['people'];
        if ($new === 'Confirmed' && $old !== 'Confirmed') {
          $conn->query("UPDATE packages SET seats_available = seats_available - $people WHERE id=$pkgId AND seats_available >= $people");
          if ($conn->affected_rows <= 0) {
            $new = 'Pending';
          }
        }
        if ($new === 'Canceled' && $old === 'Confirmed') {
          $conn->query("UPDATE packages SET seats_available = seats_available + $people WHERE id=$pkgId");
        }
        $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
        $stmt->bind_param('si', $new, $bid);
        $stmt->execute();
      }
    }
  }
}

$filter = $_GET['status'] ?? '';
$statusWhere = $filter ? (" WHERE b.status='" . $conn->real_escape_string($filter) . "'") : '';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Bookings</h2>
  <form class="d-flex" method="get">
    <select class="form-select" name="status">
      <option value="">All Status</option>
      <?php foreach (['Pending', 'Confirmed', 'Canceled'] as $s) {
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
        <th>Customer</th>
        <th>Package</th>
        <th>Dates</th>
        <th>People</th>
        <th>Total</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php $sql = "SELECT b.*, u.name uname, p.title ptitle FROM bookings b JOIN users u ON u.id=b.user_id JOIN packages p ON p.id=b.package_id" . $statusWhere . " ORDER BY b.created_at DESC";
      $res = $conn->query($sql);
      if ($res && $res->num_rows) {
        while ($b = $res->fetch_assoc()) {
          echo '<tr>';
          echo '<td>' . (int)$b['id'] . '</td>';
          echo '<td>' . esc($b['uname']) . '</td>';
          echo '<td>' . esc($b['ptitle']) . '</td>';
          echo '<td>' . esc($b['start_date']) . ' → ' . esc($b['end_date']) . '</td>';
          echo '<td>' . (int)$b['people'] . '</td>';
          echo '<td>' . price($b['total_amount']) . '</td>';
          echo '<td>' . esc($b['status']) . '</td>';
          echo '<td class="text-nowrap">';
          echo '<a class="btn btn-sm btn-outline-secondary" target="_blank" href="../invoice.php?booking_id=' . (int)$b['id'] . '">Invoice</a> ';
          echo '<form method="post" class="d-inline-block ms-1"><input type="hidden" name="csrf" value="' . esc(csrf_token()) . '"><input type="hidden" name="bid" value="' . (int)$b['id'] . '"><select class="form-select form-select-sm d-inline-block" style="width:auto;display:inline-block" name="status">';
          foreach (['Pending', 'Confirmed', 'Canceled'] as $s) {
            $sel = $b['status'] === $s ? 'selected' : '';
            echo "<option $sel>$s</option>";
          }
          echo '</select> <button class="btn btn-sm btn-primary ms-1">Update</button></form>';
          echo '</td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="8" class="text-center text-muted">No bookings.</td></tr>';
      } ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>