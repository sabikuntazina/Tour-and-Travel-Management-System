<?php require_once __DIR__ . '/header.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $title = trim($_POST['title'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $duration = (int)($_POST['duration_days'] ?? 1);
  $type = trim($_POST['type'] ?? '');
  $overview = trim($_POST['overview'] ?? '');
  $includes = trim($_POST['includes'] ?? '');
  $excludes = trim($_POST['excludes'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $seats = (int)($_POST['seats_total'] ?? 0);
  $featured = isset($_POST['featured']) ? 1 : 0;
  $status = $_POST['status'] ?? 'active';
  if (!$title || !$location || !$type || $price <= 0) {
    $err = 'Please fill all required fields.';
  } else {
    $slug = slugify($title);
    $i = 1;
    while (true) {
      $sl = $conn->real_escape_string($slug);
      $du = $conn->query("SELECT id FROM packages WHERE slug='$sl' LIMIT 1");
      if ($du && $du->num_rows) {
        $slug = preg_match('/-\d+$/', $slug) ? preg_replace('/-\d+$/', '-' . (++$i), $slug) : ($slug . '-' . (++$i));
      } else break;
    }
    $stmt = $conn->prepare("INSERT INTO packages(title,slug,location,duration_days,type,overview,includes,excludes,price,seats_total,seats_available,featured,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $seats_avail = $seats;
    $stmt->bind_param('sssissssdiiis', $title, $slug, $location, $duration, $type, $overview, $includes, $excludes, $price, $seats, $seats_avail, $featured, $status);
    $stmt->execute();
    $id = $stmt->insert_id;
    header('Location: package_edit.php?id=' . $id . '&created=1');
    exit;
  }
} ?>
<h2 class="mb-3">New Package</h2>
<?php if ($err) echo '<div class="alert alert-danger">' . esc($err) . '</div>'; ?>
<form method="post" class="row g-3">
  <?php csrf_input(); ?>
  <div class="col-md-6"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
  <div class="col-md-3"><label class="form-label">Location</label><input class="form-control" name="location" required></div>
  <div class="col-md-3"><label class="form-label">Type</label><input class="form-control" name="type" placeholder="Adventure / Relax" required></div>
  <div class="col-md-3"><label class="form-label">Duration (days)</label><input type="number" class="form-control" name="duration_days" value="1" min="1" required></div>
  <div class="col-md-3"><label class="form-label">Price</label><input type="number" step="0.01" class="form-control" name="price" required></div>
  <div class="col-md-3"><label class="form-label">Seats Total</label><input type="number" class="form-control" name="seats_total" value="0"></div>
  <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status">
      <option>active</option>
      <option>inactive</option>
    </select></div>
  <div class="col-12"><label class="form-label">Overview</label><textarea class="form-control" name="overview" rows="3"></textarea></div>
  <div class="col-md-6"><label class="form-label">Includes</label><textarea class="form-control" name="includes" rows="4"></textarea></div>
  <div class="col-md-6"><label class="form-label">Excludes</label><textarea class="form-control" name="excludes" rows="4"></textarea></div>
  <div class="col-12 form-check"><input class="form-check-input" type="checkbox" name="featured" id="featured"><label for="featured" class="form-check-label">Featured</label></div>
  <div class="col-12"><button class="btn btn-primary">Create</button> <a class="btn btn-secondary" href="packages.php">Cancel</a></div>
</form>
<?php require_once __DIR__ . '/footer.php'; ?>