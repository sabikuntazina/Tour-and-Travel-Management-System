<?php require_once __DIR__ . '/header.php'; ?>
<h2 class="mb-4">Dashboard</h2>
<div class="row g-3">
  <div class="col-md-3">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">Total Bookings</div>
        <div class="h3 mb-0"><?php $r = $conn->query("SELECT COUNT(*) c FROM bookings");
                              echo (int)($r ? $r->fetch_assoc()['c'] : 0); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">Total Earnings</div>
        <div class="h3 mb-0"><?php $r = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status='Paid'");
                              echo price($r ? $r->fetch_assoc()['s'] : 0); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">Pending Payments</div>
        <div class="h3 mb-0"><?php $r = $conn->query("SELECT COUNT(*) c FROM payments WHERE status='Pending'");
                              echo (int)($r ? $r->fetch_assoc()['c'] : 0); ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card">
      <div class="card-body">
        <div class="text-muted small">Active Packages</div>
        <div class="h3 mb-0"><?php $r = $conn->query("SELECT COUNT(*) c FROM packages WHERE status='active'");
                              echo (int)($r ? $r->fetch_assoc()['c'] : 0); ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title">Upcoming Tours</h5>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>#</th>
                <th>Package</th>
                <th>Start</th>
                <th>People</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php $res = $conn->query("SELECT b.*, p.title FROM bookings b JOIN packages p ON p.id=b.package_id WHERE b.start_date>=CURDATE() ORDER BY b.start_date ASC LIMIT 8");
              if ($res && $res->num_rows) {
                while ($b = $res->fetch_assoc()) {
                  echo '<tr><td>' . (int)$b['id'] . '</td><td>' . esc($b['title']) . '</td><td>' . esc($b['start_date']) . '</td><td>' . (int)$b['people'] . '</td><td>' . esc($b['status']) . '</td></tr>';
                }
              } else {
                echo '<tr><td colspan=5 class="text-muted">No upcoming tours.</td></tr>';
              } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title">Latest Messages</h5>
        <div class="list-group">
          <?php $res = $conn->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 6");
          if ($res && $res->num_rows) {
            while ($m = $res->fetch_assoc()) {
              echo '<a class="list-group-item list-group-item-action" href="messages.php?id=' . (int)$m['id'] . '"><div class="d-flex w-100 justify-content-between"><h6 class="mb-1">' . esc($m['subject']) . '</h6><small>' . esc($m['created_at']) . '</small></div><small class="text-muted">' . esc($m['email']) . '</small></a>';
            }
          } else {
            echo '<div class="text-muted">No messages.</div>';
          } ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>