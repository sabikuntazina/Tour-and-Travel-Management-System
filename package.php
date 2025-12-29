<?php require_once __DIR__ . '/includes/auth.php';
include __DIR__ . '/includes/header.php'; ?>
<?php $id = (int)($_GET['id'] ?? 0);
$pkg = null;
if ($id) {
  $res = $conn->query("SELECT * FROM packages WHERE id=$id AND status='active' LIMIT 1");
  $pkg = $res && $res->num_rows ? $res->fetch_assoc() : null;
}
if (!$pkg) {
  echo '<div class="container py-5"><div class="alert alert-danger">Package not found.</div></div>';
  include __DIR__ . '/includes/footer.php';
  exit;
}
$imgs = $conn->query("SELECT * FROM package_images WHERE package_id=" . $pkg['id']);
$itins = $conn->query("SELECT * FROM itineraries WHERE package_id=" . $pkg['id'] . " ORDER BY day_number ASC");
?>
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-8">
      <div id="gallery" class="mb-3">
        <div class="row g-2">
          <?php if ($imgs && $imgs->num_rows) {
            while ($im = $imgs->fetch_assoc()) {
              $src = image_src($im['image_url']);
              echo '<div class="col-6"><img class="img-fluid rounded" src="' . esc($src) . '" alt="img"></div>';
            }
          } else {
            echo '<img class="img-fluid rounded" src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?q=80&w=1400&auto=format&fit=crop" alt="">';
          } ?>
        </div>
      </div>
      <h1 class="mb-1"><?php echo esc($pkg['title']); ?></h1>
      <p class="text-muted"><i class="fa-solid fa-location-dot me-1"></i> <?php echo esc($pkg['location']); ?> • <?php echo (int)$pkg['duration_days']; ?> days • <?php echo esc($pkg['type']); ?></p>
      <h5>Overview</h5>
      <p><?php echo nl2br(esc($pkg['overview'])); ?></p>
      <div class="row g-3">
        <div class="col-md-6">
          <h6>What's Included</h6>
          <div class="small bg-light p-2 rounded"><?php echo nl2br(esc($pkg['includes'])); ?></div>
        </div>
        <div class="col-md-6">
          <h6>What's Excluded</h6>
          <div class="small bg-light p-2 rounded"><?php echo nl2br(esc($pkg['excludes'])); ?></div>
        </div>
      </div>
      <h5 class="mt-4">Itinerary</h5>
      <ol class="list-group list-group-numbered">
        <?php if ($itins && $itins->num_rows) {
          while ($it = $itins->fetch_assoc()) {
            echo '<li class="list-group-item"><strong>Day ' . (int)$it['day_number'] . ': ' . esc($it['title']) . '</strong><br><span class="small">' . nl2br(esc($it['description'])) . '</span></li>';
          }
        } ?>
      </ol>

      <h5 class="mt-4">Customer Reviews</h5>
      <div class="vstack gap-3">
        <?php $rev = $conn->query("SELECT r.*, u.name uname FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.status='Approved' AND r.package_id=" . $pkg['id'] . " ORDER BY r.created_at DESC");
        if ($rev && $rev->num_rows) {
          while ($r = $rev->fetch_assoc()) {
            echo '<div class="border rounded p-2"><div class="rating">' . str_repeat('<i class=\'fa-solid fa-star\'></i>', (int)$r['rating']) . '</div><div>' . esc($r['comment']) . '</div><small class=\"text-muted\">— ' . esc($r['uname']) . '</small></div>';
          }
        } else {
          echo '<div class="alert alert-info">No reviews yet.</div>';
        }
        ?>
      </div>
      <?php if ($user): ?>
        <form class="mt-3" method="post" action="review_submit.php">
          <input type="hidden" name="package_id" value="<?php echo (int)$pkg['id']; ?>">
          <?php csrf_input(); ?>
          <div class="row g-2 align-items-center">
            <div class="col-md-2"><select class="form-select" name="rating"><?php for ($i = 5; $i >= 1; $i--) echo '<option value="' . $i . '">' . $i . ' ★</option>'; ?></select></div>
            <div class="col-md-8"><input class="form-control" name="comment" placeholder="Write a review" required></div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-primary">Submit</button></div>
          </div>
        </form>
      <?php endif; ?>
    </div>
    <div class="col-lg-4">
      <div class="border rounded p-3 sticky-top" style="top:90px">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="mb-0"><?php echo price($pkg['price']); ?></h4><small><?php echo (int)$pkg['seats_available']; ?> seats left</small>
        </div>
        <div class="d-grid mt-3"><a class="btn btn-primary" href="booking.php?package_id=<?php echo (int)$pkg['id']; ?>">Book Now</a></div>
        <?php if ($user): ?>
          <div class="d-grid mt-2"><a class="btn btn-outline-secondary" href="wishlist_toggle.php?package_id=<?php echo (int)$pkg['id']; ?>">Add to Wishlist</a></div>
        <?php endif; ?>
      </div>
      <div class="mt-4">
        <h5>Related Packages</h5>
        <div class="vstack gap-3">
          <?php $rel = $conn->query("SELECT id,title,(SELECT image_url FROM package_images WHERE package_id=id LIMIT 1) img FROM packages WHERE status='active' AND location='" . $conn->real_escape_string($pkg['location']) . "' AND id<>" . $pkg['id'] . " LIMIT 4");
          if ($rel && $rel->num_rows) {
            while ($p = $rel->fetch_assoc()) {
              $rimg = image_src($p['img'] ?? '');
              echo '<a class="d-flex align-items-center text-decoration-none" href="package.php?id=' . (int)$p['id'] . '"><img src="' . esc($rimg) . '" class="rounded me-2" width="70" height="50" style="object-fit:cover"><span class="text-dark">' . esc($p['title']) . '</span></a>';
            }
          } else {
            echo '<div class="text-muted small">No related packages.</div>';
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>