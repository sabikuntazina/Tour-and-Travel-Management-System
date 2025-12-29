<?php require_once __DIR__ . '/header.php';
$id = (int)($_GET['id'] ?? 0);
$res = $conn->query("SELECT * FROM packages WHERE id=$id LIMIT 1");
if (!$res || !$res->num_rows) {
  echo '<div class="alert alert-danger">Package not found.</div>';
  require_once __DIR__ . '/footer.php';
  exit;
}
$pkg = $res->fetch_assoc();
$err = '';
// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $action = $_POST['action'] ?? 'update';
  if ($action === 'update') {
    $title = trim($_POST['title'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $duration = (int)($_POST['duration_days'] ?? 1);
    $type = trim($_POST['type'] ?? '');
    $overview = trim($_POST['overview'] ?? '');
    $includes = trim($_POST['includes'] ?? '');
    $excludes = trim($_POST['excludes'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $seats_total = (int)($_POST['seats_total'] ?? 0);
    $seats_available = (int)($_POST['seats_available'] ?? 0);
    $featured = isset($_POST['featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';
    if (!$title || !$location || !$type || $price <= 0) {
      $err = 'Please fill all required fields.';
    } else {
      $stmt = $conn->prepare("UPDATE packages SET title=?, location=?, duration_days=?, type=?, overview=?, includes=?, excludes=?, price=?, seats_total=?, seats_available=?, featured=?, status=? WHERE id=?");
      // types: s,s,i,s,s,s,s,d,i,i,i,s,i (13 total)
      $stmt->bind_param('ssisssssdiiisi', $title, $location, $duration, $type, $overview, $includes, $excludes, $price, $seats_total, $seats_available, $featured, $status, $id);
      $stmt->execute();
      $pkg = array_merge($pkg, compact('title', 'location', 'duration', 'type', 'overview', 'includes', 'excludes', 'price', 'seats_total', 'seats_available'));
      $pkg['featured'] = $featured;
      $pkg['status'] = $status;
    }
  }
  if ($action === 'add_itinerary') {
    $day = (int)($_POST['day_number'] ?? 1);
    $t = trim($_POST['it_title'] ?? '');
    $d = trim($_POST['it_desc'] ?? '');
    if ($t !== '') {
      $stmt = $conn->prepare("INSERT INTO itineraries(package_id,day_number,title,description) VALUES(?,?,?,?)");
      $stmt->bind_param('iiss', $id, $day, $t, $d);
      $stmt->execute();
    }
  }
  if ($action === 'delete_itinerary') {
    $iid = (int)($_POST['iid'] ?? 0);
    $conn->query("DELETE FROM itineraries WHERE id=$iid AND package_id=$id");
  }
  if ($action === 'add_image') {
    // Flexible image intake: local upload OR remote URL. Always save under /uploads/packages and store local relative path.
    $finalPath = '';
    $msgErr = '';
    $dir = __DIR__ . '/../uploads/packages/';
    if (!is_dir($dir)) {
      @mkdir($dir, 0775, true);
    }
    $webBase = 'uploads/packages/';
    $maxSize = 8 * 1024 * 1024; // 8MB
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    $hasFile = isset($_FILES['image_file']) && is_array($_FILES['image_file']) && (int)($_FILES['image_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
    $url = trim($_POST['image_url'] ?? '');

    if ($hasFile) {
      $tmp = $_FILES['image_file']['tmp_name'];
      $size = (int)($_FILES['image_file']['size'] ?? 0);
      if ($size <= 0 || $size > $maxSize) {
        $msgErr = 'File too large or empty (max 8MB).';
      } else {
        $info = @getimagesize($tmp);
        $mime = $info['mime'] ?? '';
        if (!$info || !isset($allowed[$mime])) {
          $msgErr = 'Unsupported image format.';
        } else {
          $ext = $allowed[$mime];
          $name = 'pkg_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
          $dest = $dir . $name;
          if (move_uploaded_file($tmp, $dest)) {
            $finalPath = $webBase . $name;
          } else {
            $msgErr = 'Failed to save uploaded file.';
          }
        }
      }
    } elseif ($url !== '') {
      if (!preg_match('~^https?://~i', $url)) {
        $msgErr = 'Invalid image URL.';
      } else {
        $tmp = $dir . 'tmp_' . bin2hex(random_bytes(6));
        $ok = false;
        $mime = '';
        $dlErr = '';
        if (function_exists('curl_init')) {
          $fp = @fopen($tmp, 'wb');
          if ($fp) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 4, CURLOPT_FILE => $fp, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_USERAGENT => 'Mozilla/5.0 TravelNext Bot', CURLOPT_NOPROGRESS => false, CURLOPT_PROGRESSFUNCTION => function ($res, $dltotal, $dlnow) use ($maxSize) {
              return ($dlnow > $maxSize) ? 1 : 0;
            }]);
            $ok = curl_exec($ch);
            if (!$ok) {
              $dlErr = curl_error($ch) ?: 'Download failed.';
            }
            $ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            if (is_string($ctype)) {
              $mime = strtolower(trim(explode(';', $ctype)[0]));
            }
            curl_close($ch);
            fclose($fp);
          } else {
            $dlErr = 'Cannot write temp file.';
          }
        }
        if (!$ok) {
          $in = @fopen($url, 'rb');
          $out = @fopen($tmp, 'wb');
          if ($in && $out) {
            $read = 0;
            while (!feof($in)) {
              $buf = fread($in, 8192);
              if ($buf === false) break;
              $read += strlen($buf);
              if ($read > $maxSize) {
                $dlErr = 'File exceeds size limit.';
                $ok = false;
                break;
              }
              fwrite($out, $buf);
              $ok = true;
            }
            fclose($in);
            fclose($out);
          }
        }
        if (!$ok) {
          if (is_file($tmp)) @unlink($tmp);
          $msgErr = $dlErr ?: 'Could not download image.';
        } else {
          $info = @getimagesize($tmp);
          $mime = $info['mime'] ?? $mime;
          if (!$info || !isset($allowed[$mime])) {
            @unlink($tmp);
            $msgErr = 'Downloaded file is not a supported image.';
          } else {
            $ext = $allowed[$mime];
            $name = 'pkg_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $dir . $name;
            if (@rename($tmp, $dest)) {
              $finalPath = $webBase . $name;
            } else {
              @unlink($tmp);
              $msgErr = 'Failed to save downloaded image.';
            }
          }
        }
      }
    } else {
      $msgErr = 'Please select a file or enter an image URL.';
    }

    if ($finalPath !== '') {
      $stmt = $conn->prepare("INSERT INTO package_images(package_id,image_url) VALUES(?,?)");
      $stmt->bind_param('is', $id, $finalPath);
      $stmt->execute();
    } else {
      $err = $msgErr;
    }
  }
  if ($action === 'delete_image') {
    $imgid = (int)($_POST['imgid'] ?? 0);
    $conn->query("DELETE FROM package_images WHERE id=$imgid AND package_id=$id");
  }
}
$imgs = $conn->query("SELECT * FROM package_images WHERE package_id=$id ORDER BY id DESC");
$itins = $conn->query("SELECT * FROM itineraries WHERE package_id=$id ORDER BY day_number ASC, id ASC");
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Edit Package</h2>
  <a href="packages.php" class="btn btn-secondary">Back</a>
</div>
<?php if (isset($_GET['created'])) echo '<div class="alert alert-success">Package created successfully. You can now add details and photos.</div>'; ?>
<?php if ($err) echo '<div class="alert alert-danger">' . esc($err) . '</div>'; ?>
<form method="post" class="row g-3">
  <?php csrf_input(); ?>
  <input type="hidden" name="action" value="update">
  <div class="col-md-6"><label class="form-label">Title</label><input class="form-control" name="title" value="<?php echo esc($pkg['title']); ?>" required></div>
  <div class="col-md-3"><label class="form-label">Location</label><input class="form-control" name="location" value="<?php echo esc($pkg['location']); ?>" required></div>
  <div class="col-md-3"><label class="form-label">Type</label><input class="form-control" name="type" value="<?php echo esc($pkg['type']); ?>" required></div>
  <div class="col-md-3"><label class="form-label">Duration (days)</label><input type="number" class="form-control" name="duration_days" value="<?php echo (int)$pkg['duration_days']; ?>" min="1" required></div>
  <div class="col-md-3"><label class="form-label">Price</label><input type="number" step="0.01" class="form-control" name="price" value="<?php echo esc($pkg['price']); ?>" required></div>
  <div class="col-md-3"><label class="form-label">Seats Total</label><input type="number" class="form-control" name="seats_total" value="<?php echo (int)$pkg['seats_total']; ?>"></div>
  <div class="col-md-3"><label class="form-label">Seats Available</label><input type="number" class="form-control" name="seats_available" value="<?php echo (int)$pkg['seats_available']; ?>"></div>
  <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status">
      <option value="active" <?php echo $pkg['status'] === 'active' ? 'selected' : ''; ?>>active</option>
      <option value="inactive" <?php echo $pkg['status'] !== 'active' ? 'selected' : ''; ?>>inactive</option>
    </select></div>
  <div class="col-12"><label class="form-label">Overview</label><textarea class="form-control" name="overview" rows="4"><?php echo esc($pkg['overview']); ?></textarea></div>
  <div class="col-md-6"><label class="form-label">Includes</label><textarea class="form-control" name="includes" rows="5"><?php echo esc($pkg['includes']); ?></textarea></div>
  <div class="col-md-6"><label class="form-label">Excludes</label><textarea class="form-control" name="excludes" rows="5"><?php echo esc($pkg['excludes']); ?></textarea></div>
  <div class="col-12 form-check"><input class="form-check-input" type="checkbox" name="featured" id="featured" <?php echo (int)$pkg['featured'] ? 'checked' : ''; ?>><label for="featured" class="form-check-label">Featured</label></div>
  <div class="col-12"><button class="btn btn-primary">Save Changes</button></div>
</form>
<hr>
<div class="row g-4">
  <div class="col-lg-6">
    <h4>Itinerary</h4>
    <form method="post" class="row g-2 mb-3">
      <?php csrf_input(); ?><input type="hidden" name="action" value="add_itinerary">
      <div class="col-3"><input type="number" class="form-control" name="day_number" value="1" min="1"></div>
      <div class="col-9"><input class="form-control" name="it_title" placeholder="Title" required></div>
      <div class="col-12"><textarea class="form-control" name="it_desc" rows="2" placeholder="Description"></textarea></div>
      <div class="col-12"><button class="btn btn-outline-primary btn-sm">Add Day</button></div>
    </form>
    <ul class="list-group">
      <?php if ($itins && $itins->num_rows) {
        while ($it = $itins->fetch_assoc()) {
          echo '<li class="list-group-item d-flex justify-content-between align-items-start"><div><strong>Day ' . (int)$it['day_number'] . ': ' . esc($it['title']) . '</strong><br><span class="small">' . nl2br(esc($it['description'])) . '</span></div><form method="post" onsubmit="return confirm(\'Delete day?\')"><input type="hidden" name="action" value="delete_itinerary"><?php csrf_input(); ?><input type="hidden" name="iid" value="' . (int)$it['id'] . '"><button class="btn btn-sm btn-outline-danger ms-2">Delete</button></form></li>';
        }
      } else {
        echo '<li class="list-group-item">No items.</li>';
      } ?>
    </ul>
  </div>
  <div class="col-lg-6">
    <h4>Photos</h4>
    <form method="post" enctype="multipart/form-data" class="row g-2 mb-3">
      <?php csrf_input(); ?><input type="hidden" name="action" value="add_image">
      <div class="col-8"><input class="form-control" name="image_url" placeholder="Image URL (any website)"></div>
      <div class="col-4"><input type="file" class="form-control" name="image_file" accept="image/*"></div>
      <div class="col-12"><button class="btn btn-outline-primary btn-sm">Add Photo</button></div>
    </form>
    <div class="row g-2">
      <?php if ($imgs && $imgs->num_rows) {
        while ($im = $imgs->fetch_assoc()) {
          $src = $im['image_url'];
          if (strpos($src, 'http') !== 0) {
            $src = '../' . $src;
          }
          echo '<div class="col-6"><div class="position-relative"><img src="' . esc($src) . '" class="img-fluid rounded" style="height:140px;object-fit:cover"><form method="post" class="position-absolute" style="top:6px;right:6px"><input type="hidden" name="action" value="delete_image">';
          csrf_input();
          echo '<input type="hidden" name="imgid" value="' . (int)$im['id'] . '"><button class="btn btn-sm btn-danger">&times;</button></form></div></div>';
        }
      } else {
        echo '<div class="col-12 text-muted">No photos.</div>';
      } ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>