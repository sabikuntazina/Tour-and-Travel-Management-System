<?php require_once __DIR__ . '/header.php';
// Actions approve/reject/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $rid = (int)($_POST['rid'] ?? 0);
  $act = $_POST['action'] ?? '';
  if ($rid && in_array($act, ['approve', 'reject', 'delete'])) {
    if ($act === 'delete') $conn->query("DELETE FROM reviews WHERE id=$rid");
    if ($act === 'approve') $conn->query("UPDATE reviews SET status='Approved' WHERE id=$rid");
    if ($act === 'reject') $conn->query("UPDATE reviews SET status='Rejected' WHERE id=$rid");
  }
}
$filter = $_GET['status'] ?? '';
$where = $filter ? (" WHERE r.status='" . $conn->real_escape_string($filter) . "'") : '';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Reviews</h2>
  <form class="d-flex" method="get">
    <select class="form-select" name="status">
      <option value="">All Status</option>
      <?php foreach (['Pending', 'Approved', 'Rejected'] as $s) {
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
        <th>Package</th>
        <th>User</th>
        <th>Rating</th>
        <th>Comment</th>
        <th>Status</th>
        <th>Date</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php $sql = "SELECT r.*, p.title ptitle, u.name uname FROM reviews r JOIN packages p ON p.id=r.package_id JOIN users u ON u.id=r.user_id" . $where . " ORDER BY r.created_at DESC";
      $res = $conn->query($sql);
      if ($res && $res->num_rows) {
        while ($row = $res->fetch_assoc()) {
          echo '<tr>';
          echo '<td>' . (int)$row['id'] . '</td>';
          echo '<td>' . esc($row['ptitle']) . '</td>';
          echo '<td>' . esc($row['uname']) . '</td>';
          echo '<td>' . (int)$row['rating'] . ' ★</td>';
          echo '<td>' . esc($row['comment']) . '</td>';
          echo '<td>' . esc($row['status']) . '</td>';
          echo '<td>' . esc($row['created_at']) . '</td>';
          echo '<td class="text-nowrap">';
          echo '<form method="post" class="d-inline">' . csrf_input() . '<input type="hidden" name="rid" value="' . (int)$row['id'] . '"><input type="hidden" name="action" value="approve"><button class="btn btn-sm btn-outline-success"' . ($row['status'] === 'Approved' ? ' disabled' : '') . '>Approve</button></form> ';
          echo '<form method="post" class="d-inline ms-1">' . csrf_input() . '<input type="hidden" name="rid" value="' . (int)$row['id'] . '"><input type="hidden" name="action" value="reject"><button class="btn btn-sm btn-outline-warning"' . ($row['status'] === 'Rejected' ? ' disabled' : '') . '>Reject</button></form> ';
          echo '<form method="post" class="d-inline ms-1" onsubmit="return confirm(\'Delete review?\')">' . csrf_input() . '<input type="hidden" name="rid" value="' . (int)$row['id'] . '"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger">Delete</button></form>';
          echo '</td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="8" class="text-center text-muted">No reviews.</td></tr>';
      } ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>