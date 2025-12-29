<?php require_once __DIR__ . '/header.php';
// Save settings
$keys = [
  'site_name',
  'contact_address',
  'contact_phone',
  'contact_email',
  'facebook_url',
  'instagram_url',
  'twitter_url',
  'whatsapp_phone'
];
$ok = false;
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  foreach ($keys as $k) {
    set_setting($k, $_POST[$k] ?? '');
  }
  $ok = true;
  $msg = 'Settings saved.';
}
// Load
$vals = [];
foreach ($keys as $k) {
  $vals[$k] = get_setting($k, '');
}
if ($vals['site_name'] === '') $vals['site_name'] = site_name();
?>
<h2 class="mb-3">Settings</h2>
<?php if ($ok) echo '<div class="alert alert-success">' . esc($msg) . '</div>'; ?>
<form method="post" class="row g-3" style="max-width:800px;">
  <?php csrf_input(); ?>
  <div class="col-md-6">
    <label class="form-label">Site Name</label>
    <input class="form-control" name="site_name" value="<?php echo esc($vals['site_name']); ?>" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">WhatsApp Phone (include country code, numbers only)</label>
    <input class="form-control" name="whatsapp_phone" value="<?php echo esc($vals['whatsapp_phone']); ?>" placeholder="8801XXXXXXXXX">
  </div>
  <div class="col-md-12">
    <label class="form-label">Contact Address</label>
    <input class="form-control" name="contact_address" value="<?php echo esc($vals['contact_address']); ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label">Contact Phone</label>
    <input class="form-control" name="contact_phone" value="<?php echo esc($vals['contact_phone']); ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label">Contact Email</label>
    <input type="email" class="form-control" name="contact_email" value="<?php echo esc($vals['contact_email']); ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Facebook URL</label>
    <input class="form-control" name="facebook_url" value="<?php echo esc($vals['facebook_url']); ?>" placeholder="https://facebook.com/yourpage">
  </div>
  <div class="col-md-4">
    <label class="form-label">Instagram URL</label>
    <input class="form-control" name="instagram_url" value="<?php echo esc($vals['instagram_url']); ?>" placeholder="https://instagram.com/yourprofile">
  </div>
  <div class="col-md-4">
    <label class="form-label">Twitter URL</label>
    <input class="form-control" name="twitter_url" value="<?php echo esc($vals['twitter_url']); ?>" placeholder="https://twitter.com/yourprofile">
  </div>
  <div class="col-12">
    <button class="btn btn-primary">Save Settings</button>
  </div>
</form>
<?php require_once __DIR__ . '/footer.php'; ?>