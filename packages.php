<?php require_once __DIR__ . '/includes/auth.php';
include __DIR__ . '/includes/header.php'; ?>
<div class="container py-4">
  <h1 class="mb-3">Tour Packages</h1>
  <form class="row g-2 mb-3" method="get">
    <div class="col-md-3"><input type="text" class="form-control" name="dest" value="<?php echo esc($_GET['dest'] ?? ''); ?>" placeholder="Destination or Title"></div>
    <div class="col-md-3"><input type="number" class="form-control" name="budget" value="<?php echo esc($_GET['budget'] ?? ''); ?>" placeholder="Max Budget"></div>
    <div class="col-md-3">
      <select class="form-select" name="type">
        <option value="">All Types</option>
        <?php $types = ['Adventure', 'Relax', 'Experience', 'Cultural', 'Family'];
        foreach ($types as $t) {
          $sel = (($_GET['type'] ?? '') == $t) ? 'selected' : '';
          echo '<option ' . $sel . '>' . esc($t) . '</option>';
        } ?>
      </select>
    </div>
    <div class="col-md-3 d-grid"><button class="btn btn-primary">Filter</button></div>
  </form>
  <div class="row g-4">
    <?php
    $where = ["status='active'"];
    $dest = trim($_GET['dest'] ?? '');
    if ($dest !== '') {
      $d = $conn->real_escape_string($dest);
      $where[] = "(title LIKE '%$d%' OR location LIKE '%$d%')";
    }
    $budget = (float)($_GET['budget'] ?? 0);
    if ($budget > 0) {
      $where[] = "price <= $budget";
    }
    $type = trim($_GET['type'] ?? '');
    if ($type !== '') {
      $t = $conn->real_escape_string($type);
      $where[] = "type='$t'";
    }
    $sql = "SELECT p.*, (SELECT image_url FROM package_images WHERE package_id=p.id LIMIT 1) img FROM packages p WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
    $res = $conn->query($sql);
    if ($res && $res->num_rows) {
      while ($row = $res->fetch_assoc()) {
        echo '<div class="col-md-4">';
        echo '  <div class="card card-package h-100">';
        $img = image_src($row['img'] ?? '');
        echo '    <img src="' . esc($img) . '" class="card-img-top" alt="' . esc($row['title']) . '">';
        echo '    <div class="card-body">';
        echo '      <h5 class="card-title">' . esc($row['title']) . '</h5>';
        echo '      <p class="text-muted small"><i class="fa-solid fa-location-dot me-1"></i> ' . esc($row['location']) . ' • ' . (int)$row['duration_days'] . ' days</p>';
        echo '      <p class="fw-bold">' . price($row['price']) . '</p>';
        echo '      <a href="package.php?id=' . (int)$row['id'] . '" class="btn btn-outline-primary">Details</a> ';
        echo '      <a href="booking.php?package_id=' . (int)$row['id'] . '" class="btn btn-primary">Book Now</a>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
      }
    } else {
      echo '<div class="col-12"><div class="alert alert-info">No packages found.</div></div>';
    }
    ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>