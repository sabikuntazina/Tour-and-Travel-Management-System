<?php require_once __DIR__ . '/header.php';
// Toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $uid = (int)($_POST['uid'] ?? 0);
  $act = ($_POST['action'] ?? '') === 'activate' ? 1 : 0;
  if ($uid) {
    $conn->query("UPDATE users SET is_active=$act WHERE id=$uid");
  }
}
// Precompute CSRF value to embed explicitly in echoed forms
$csrf_val = esc(csrf_token());
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Users</h2>
</div>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Bookings</th>
        <th>Messages</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php $sql = "SELECT u.*, (SELECT COUNT(*) FROM bookings b WHERE b.user_id=u.id) bcnt, (SELECT COUNT(*) FROM messages m WHERE m.email=u.email) mcnt FROM users u ORDER BY u.created_at DESC";
      $res = $conn->query($sql);
      if ($res && $res->num_rows) {
        while ($u = $res->fetch_assoc()) {
          echo '<tr>';
          echo '<td>' . (int)$u['id'] . '</td>';
          echo '<td>' . esc($u['name']) . '</td>';
          echo '<td>' . esc($u['email']) . '</td>';
          echo '<td>' . esc($u['phone']) . '</td>';
          echo '<td>' . (int)$u['bcnt'] . '</td>';
          echo '<td>' . (int)$u['mcnt'] . '</td>';
          echo '<td>' . ((int)$u['is_active'] ? 'Active' : 'Inactive') . '</td>';
          echo '<td class="text-nowrap">';
          echo '<form method="post" class="d-inline"><input type="hidden" name="csrf" value="' . $csrf_val . '"><input type="hidden" name="uid" value="' . (int)$u['id'] . '">';
          if ((int)$u['is_active']) {
            echo '<input type="hidden" name="action" value="deactivate"><button class="btn btn-sm btn-outline-warning">Deactivate</button>';
          } else {
            echo '<input type="hidden" name="action" value="activate"><button class="btn btn-sm btn-outline-success">Activate</button>';
          }
          echo '</form>';
          echo '</td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="8" class="text-center text-muted">No users found.</td></tr>';
      } ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>