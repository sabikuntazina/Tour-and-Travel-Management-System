<footer class="bg-dark text-light mt-5 pt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <h5 class="fw-bold">About <?php echo esc(site_name()); ?></h5>
        <p>Your trusted travel partner for curated experiences worldwide.</p>
      </div>
      <div class="col-md-4">
        <h5 class="fw-bold">Contact</h5>
        <ul class="list-unstyled small">
          <li><i class="fa-solid fa-location-dot me-2"></i> <?php echo esc(get_setting('contact_address', '123 Travel St, Dhaka')); ?></li>
          <li><i class="fa-solid fa-phone me-2"></i> <?php echo esc(get_setting('contact_phone', '+880 1700 000000')); ?></li>
          <li><i class="fa-regular fa-envelope me-2"></i> <?php echo esc(get_setting('contact_email', 'support@travelnext.example')); ?></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h5 class="fw-bold">Follow</h5>
        <?php $fb = get_setting('facebook_url', '#');
        $ig = get_setting('instagram_url', '#');
        $tw = get_setting('twitter_url', '#'); ?>
        <a href="<?php echo esc($fb ?: '#'); ?>" class="text-light me-3" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="<?php echo esc($ig ?: '#'); ?>" class="text-light me-3" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="<?php echo esc($tw ?: '#'); ?>" class="text-light me-3" target="_blank"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
    <div class="text-center py-3 mt-4 border-top border-secondary">
      <small>© <?php echo date('Y'); ?> <?php echo esc(site_name()); ?>. All rights reserved.</small>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="assets/js/main.js"></script>
</body>

</html>