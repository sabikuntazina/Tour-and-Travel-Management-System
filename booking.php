<?php require_once __DIR__ . '/includes/auth.php';
require_login();
$pid = (int)($_GET['package_id'] ?? 0);
$pkg = null;
if ($pid) {
  $res = $conn->query("SELECT * FROM packages WHERE id=$pid LIMIT 1");
  $pkg = $res && $res->num_rows ? $res->fetch_assoc() : null;
}
if (!$pkg) {
  redirect('packages.php');
}
include __DIR__ . '/includes/header.php'; ?>
<div class="container py-4" style="max-width:720px;">
  <h1 class="mb-3">Book: <?php echo esc($pkg['title']); ?></h1>
  <?php if (isset($_GET['err']) && $_GET['err'] === 'no_seats') echo '<div class="alert alert-danger">Not enough seats available for your requested number of people.</div>'; ?>
  <div class="card">
    <div class="card-body">
      <form method="post" action="booking_submit.php" class="vstack gap-3">
        <?php csrf_input(); ?>
        <input type="hidden" name="package_id" value="<?php echo (int)$pkg['id']; ?>">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Start Date</label><input type="date" class="form-control" name="start_date" required></div>
          <div class="col-md-6"><label class="form-label">People</label><input type="number" class="form-control" name="people" min="1" value="1" required></div>
        </div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Contact Phone</label><input class="form-control" name="phone" placeholder="Phone" required></div>
          <div class="col-md-6"><label class="form-label">Total Amount</label><input class="form-control" id="total" value="<?php echo number_format($pkg['price'], 2); ?>" disabled></div>
        </div>
        <button class="btn btn-primary">Proceed to Payment</button>
      </form>
    </div>
  </div>
  <script>
    const price = <?php echo (float)$pkg['price']; ?>;
    const ppl = document.querySelector('input[name=people]');
    const total = document.getElementById('total');
    ppl.addEventListener('input', () => {
      const v = Math.max(1, parseInt(ppl.value || '1'));
      total.value = (v * price).toFixed(2);
    });
  </script>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>