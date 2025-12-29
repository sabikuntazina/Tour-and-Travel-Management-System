<?php require_once __DIR__ . '/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Packages</h2>
  <a class="btn btn-primary" href="package_new.php"><i class="fa-solid fa-plus me-1"></i>New Package</a>
</div>
<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Title</th>
        <th>Location</th>
        <th>Duration</th>
        <th>Price</th>
        <th>Seats</th>
        <th>Status</th>
        <th>Featured</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php $res = $conn->query("SELECT * FROM packages ORDER BY created_at DESC");
      if ($res && $res->num_rows) {
        while ($p = $res->fetch_assoc()) {
          echo '<tr>';
          echo '<td>' . (int)$p['id'] . '</td>';
          echo '<td>' . esc($p['title']) . '</td>';
          echo '<td>' . esc($p['location']) . '</td>';
          echo '<td>' . (int)$p['duration_days'] . 'd</td>';
          echo '<td>' . price($p['price']) . '</td>';
          echo '<td>' . (int)$p['seats_available'] . '/' . (int)$p['seats_total'] . '</td>';
          echo '<td>' . esc($p['status']) . '</td>';
          echo '<td>' . ((int)$p['featured'] ? 'Yes' : 'No') . '</td>';
          echo '<td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="package_edit.php?id=' . (int)$p['id'] . '">Edit</a> <a class="btn btn-sm btn-outline-danger" href="package_delete.php?id=' . (int)$p['id'] . '" onclick="return confirm(\'Delete package?\')">Delete</a></td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="9" class="text-center text-muted">No packages.</td></tr>';
      } ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>