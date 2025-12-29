<?php require_once __DIR__ . '/includes/auth.php';
include __DIR__ . '/includes/header.php'; ?>
<div class="container py-5">
  <div class="row g-4 align-items-center">
    <div class="col-md-6">
      <h1 class="mb-3">Contact Us</h1>
      <p>We're here to help with your travel plans. Send us a message and we'll get back soon.</p>
      <ul class="list-unstyled">
        <li><i class="fa-solid fa-location-dot me-2"></i> 123 Travel St, Dhaka</li>
        <li><i class="fa-solid fa-phone me-2"></i> +880 1700 000000</li>
        <li><i class="fa-regular fa-envelope me-2"></i> support@travelnext.example</li>
      </ul>
      <?php if (isset($_GET['sent'])) echo '<div class="alert alert-success mt-3">Message sent. We will reply soon.</div>'; ?>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <form method="post" action="send_message.php" class="vstack gap-3">
            <?php csrf_input(); ?>
            <input class="form-control" name="name" placeholder="Your name" required>
            <input type="email" class="form-control" name="email" placeholder="Your email" required>
            <input class="form-control" name="phone" placeholder="Phone (optional)">
            <input class="form-control" name="subject" placeholder="Subject" required>
            <textarea class="form-control" name="message" rows="4" placeholder="Message" required></textarea>
            <button class="btn btn-primary">Send Message</button>
          </form>
        </div>
      </div>
    </div>
    <?php $me = current_user();
    if ($me) {
      $emailEsc = $conn->real_escape_string($me['email']);
      $msgs = $conn->query("SELECT subject,message,status,admin_reply,replied_at,created_at FROM messages WHERE email='$emailEsc' ORDER BY created_at DESC LIMIT 5");
      if ($msgs && $msgs->num_rows) { ?>
        <div class="mt-3">
          <h5>Your recent messages</h5>
          <?php while ($m = $msgs->fetch_assoc()) { ?>
            <div class="card mb-2">
              <div class="card-body">
                <div class="small text-muted"><?php echo esc($m['created_at']); ?> • Status: <?php echo esc($m['status']); ?></div>
                <div class="fw-semibold"><?php echo esc($m['subject']); ?></div>
                <div class="mb-2"><?php echo nl2br(esc($m['message'])); ?></div>
                <?php if (($m['admin_reply'] ?? '') !== '') { ?>
                  <div class="border-top pt-2">
                    <div class="small text-muted mb-1">Admin reply <?php echo esc($m['replied_at']); ?></div>
                    <?php echo nl2br(esc($m['admin_reply'])); ?>
                  </div>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        </div>
    <?php }
    } ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>