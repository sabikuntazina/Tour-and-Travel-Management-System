<?php require_once __DIR__ . '/includes/auth.php';
require_login();
$u = current_user();
include __DIR__ . '/includes/header.php'; ?>
<div class="container py-4">
  <h1 class="mb-3">My Dashboard</h1>
  <ul class="nav nav-tabs" id="dashTabs" role="tablist">
    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#bookings" type="button">My Bookings</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#payments" type="button">Payment History</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#notifications" type="button">Notifications</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#wishlist" type="button">Wishlist</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile" type="button">Profile Settings</button></li>
  </ul>
  <div class="tab-content pt-3">
    <div class="tab-pane fade show active" id="bookings">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Package</th>
              <th>Dates</th>
              <th>People</th>
              <th>Total</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php $res = $conn->query("SELECT b.*, p.title FROM bookings b JOIN packages p ON p.id=b.package_id WHERE b.user_id=" . (int)$u['id'] . " ORDER BY b.created_at DESC");
            if ($res && $res->num_rows) {
              while ($b = $res->fetch_assoc()) {
                echo '<tr><td>' . (int)$b['id'] . '</td><td>' . esc($b['title']) . '</td><td>' . esc($b['start_date']) . ' → ' . esc($b['end_date']) . '</td><td>' . (int)$b['people'] . '</td><td>' . price($b['total_amount']) . '</td><td>' . esc($b['status']) . '</td><td><a class="btn btn-sm btn-outline-primary" href="invoice.php?booking_id=' . (int)$b['id'] . '">Invoice</a></td></tr>';
              }
            } else {
              echo '<tr><td colspan="7" class="text-center text-muted">No bookings yet.</td></tr>';
            } ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="tab-pane fade" id="payments">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Booking</th>
              <th>Method</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php $res = $conn->query("SELECT py.*, b.package_id, p.title FROM payments py JOIN bookings b ON b.id=py.booking_id JOIN packages p ON p.id=b.package_id WHERE b.user_id=" . (int)$u['id'] . " ORDER BY py.created_at DESC");
            if ($res && $res->num_rows) {
              while ($p = $res->fetch_assoc()) {
                echo '<tr><td>' . (int)$p['id'] . '</td><td>' . esc($p['title']) . '</td><td>' . esc($p['method']) . '</td><td>' . price($p['amount']) . '</td><td>' . esc($p['status']) . '</td><td>' . esc($p['created_at']) . '</td></tr>';
              }
            } else {
              echo '<tr><td colspan="6" class="text-center text-muted">No payments yet.</td></tr>';
            } ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="tab-pane fade" id="notifications">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">My Notifications</h5>
        <a class="btn btn-sm btn-outline-secondary" href="notifications_mark.php?all=1">Mark all as read</a>
      </div>
      <div class="list-group">
        <?php $res = $conn->query("SELECT * FROM notifications WHERE user_id=" . (int)$u['id'] . " ORDER BY created_at DESC");
        if ($res && $res->num_rows) {
          while ($n = $res->fetch_assoc()) {
            echo '<div class="list-group-item d-flex justify-content-between align-items-start ' . ((int)$n['is_read'] ? '' : 'bg-light') . '">';
            echo '<div><div class="fw-semibold">' . esc($n['title']) . '</div><div class="small">' . esc($n['message']) . '</div><div class="text-muted small">' . esc($n['created_at']) . '</div></div>';
            echo '<div class="ms-2">';
            if (!(int)$n['is_read']) echo '<a class="btn btn-sm btn-outline-primary" href="notifications_mark.php?id=' . (int)$n['id'] . '">Mark read</a>';
            echo '</div></div>';
          }
        } else {
          echo '<div class="text-muted">No notifications yet.</div>';
        } ?>
      </div>
    </div>
    <div class="tab-pane fade" id="wishlist">
      <div class="row g-4">
        <?php $res = $conn->query("SELECT w.*, p.id pid, p.title, p.location, p.duration_days, p.price, (SELECT image_url FROM package_images WHERE package_id=p.id LIMIT 1) img FROM wishlist w JOIN packages p ON p.id=w.package_id WHERE w.user_id=" . (int)$u['id']);
        if ($res && $res->num_rows) {
          while ($row = $res->fetch_assoc()) {
            $img = image_src($row['img'] ?? '');
            echo '<div class="col-md-4"><div class="card h-100">';
            echo '<img src="' . esc($img) . '" class="card-img-top" style="height:180px;object-fit:cover">';
            echo '<div class="card-body"><h6 class="card-title">' . esc($row['title']) . '</h6><div class="small text-muted"><i class=\'fa-solid fa-location-dot me-1\'></i> ' . esc($row['location']) . ' • ' . (int)$row['duration_days'] . ' days</div><div class="fw-bold mt-2">' . price($row['price']) . '</div><div class="d-grid gap-2 mt-2"><a class="btn btn-outline-primary" href="package.php?id=' . (int)$row['pid'] . '">View</a><a class="btn btn-outline-danger" href="wishlist_toggle.php?package_id=' . (int)$row['pid'] . '">Remove</a></div></div></div></div>';
          }
        } else {
          echo '<div class="col-12"><div class="alert alert-info">Your wishlist is empty.</div></div>';
        } ?>
      </div>
    </div>
    <div class="tab-pane fade" id="profile">
      <div class="row">
        <div class="col-md-6">
          <form method="post" action="profile_update.php" class="vstack gap-2">
            <?php csrf_input(); ?>
            <label class="form-label">Full Name</label>
            <input class="form-control" name="name" value="<?php echo esc($u['name']); ?>" required>
            <label class="form-label mt-2">Email</label>
            <input type="email" class="form-control" name="email" value="<?php echo esc($u['email']); ?>" required>
            <label class="form-label mt-2">New Password (optional)</label>
            <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current">
            <button class="btn btn-primary mt-2">Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>