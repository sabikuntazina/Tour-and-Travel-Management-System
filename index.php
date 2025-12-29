<?php require_once __DIR__ . '/includes/auth.php';
include __DIR__ . '/includes/header.php'; ?>
<section class="hero-wrap position-relative">
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="images/sajek-1.png" class="d-block w-100 hero-img" alt="Sadek Valley">
      </div>
      <div class="carousel-item">
        <img src="images/cox-bazar-1.jpg" class="d-block w-100 hero-img" alt="Ocean Waves">
      </div>
      <div class="carousel-item">
        <img src="images/rangamati3.jpg" class="d-block w-100 hero-img" alt="City Skyline">
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>
  <div class="hero-overlay"></div>
  <div class="container hero-content text-white">
    <div class="row">
      <div class="col-lg-8" data-aos="fade-up">
        <h1 class="display-5 fw-bold">Explore the world with <?php echo esc(site_name()); ?></h1>
        <p class="lead">Handpicked tours, seamless booking, and unforgettable memories.</p>
        <form class="bg-white p-3 rounded shadow-sm row g-2" method="get" action="packages.php">
          <div class="col-md-4"><input type="text" class="form-control" name="dest" placeholder="Destination"></div>
          <div class="col-md-3"><input type="date" class="form-control" name="date"></div>
          <div class="col-md-3"><input type="number" class="form-control" name="budget" placeholder="Max Budget"></div>
          <div class="col-md-2 d-grid"><button class="btn btn-primary">Search</button></div>
        </form>
      </div>
    </div>
  </div>
</section>


<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="section-title">Popular Packages</h2>
      <a href="packages.php" class="btn btn-outline-primary btn-sm">View All</a>
    </div>
    <div class="row g-4">
      <?php
      $res = $conn->query("SELECT p.*, (SELECT image_url FROM package_images WHERE package_id=p.id LIMIT 1) img FROM packages p WHERE featured=1 AND status='active' ORDER BY created_at DESC LIMIT 6");
      while ($row = $res && $res->num_rows ? $res->fetch_assoc() : null) {
        if (!$row) break;
        echo '<div class="col-md-4" data-aos="fade-up">';
        echo '  <div class="card card-package h-100">';
        $img = image_src($row['img'] ?? '');
        echo '    <img src="' . esc($img) . '" class="card-img-top" alt="' . esc($row['title']) . '">';
        echo '    <div class="card-body">';
        echo '      <h5 class="card-title">' . esc($row['title']) . '</h5>';
        echo '      <p class="text-muted small"><i class="fa-solid fa-location-dot me-1"></i> ' . esc($row['location']) . ' • ' . (int)$row['duration_days'] . ' days</p>';
        echo '      <p class="fw-bold">' . price($row['price']) . '</p>';
        echo '      <a href="package.php?id=' . (int)$row['id'] . '" class="btn btn-primary">View Details</a>';
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
      }
      ?>
    </div>
  </div>
</section>



<section class="py-5">
  <div class="container">
    <h2 class="section-title mb-4">What our customers say</h2>
    <div class="row g-4">
      <?php
      $rev = $conn->query("SELECT r.*, u.name uname, p.title ptitle FROM reviews r JOIN users u ON u.id=r.user_id JOIN packages p ON p.id=r.package_id WHERE r.status='Approved' ORDER BY r.created_at DESC LIMIT 6");
      if ($rev && $rev->num_rows) {
        while ($r = $rev->fetch_assoc()) {
          echo '<div class="col-md-4" data-aos="fade-up">';
          echo '  <div class="card h-100"><div class="card-body">';
          echo '    <div class="rating mb-2">' . str_repeat('<i class=\'fa-solid fa-star\'></i>', (int)$r['rating']) . '</div>';
          echo '    <p class="mb-2">' . esc($r['comment']) . '</p>';
          echo '    <small class="text-muted">— ' . esc($r['uname']) . ' on ' . esc($r['ptitle']) . '</small>';
          echo '  </div></div>';
          echo '</div>';
        }
      } else {
        // Fallback: show the most recent reviews regardless of status
        $rev2 = $conn->query("SELECT r.*, u.name uname, p.title ptitle FROM reviews r JOIN users u ON u.id=r.user_id JOIN packages p ON p.id=r.package_id ORDER BY r.created_at DESC LIMIT 6");
        if ($rev2 && $rev2->num_rows) {
          while ($r = $rev2->fetch_assoc()) {
            echo '<div class="col-md-4" data-aos="fade-up">';
            echo '  <div class="card h-100"><div class="card-body">';
            echo '    <div class="rating mb-2">' . str_repeat('<i class=\'fa-solid fa-star\'></i>', (int)$r['rating']) . '</div>';
            echo '    <p class="mb-2">' . esc($r['comment']) . '</p>';
            echo '    <small class="text-muted">— ' . esc($r['uname']) . ' on ' . esc($r['ptitle']) . '</small>';
            echo '  </div></div>';
            echo '</div>';
          }
        } else {
          echo '<div class="col-12"><div class="alert alert-info">No reviews yet. Be the first to review after your trip!</div></div>';
        }
      }
      ?>
    </div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-md-6" data-aos="fade-right">
        <h2 class="section-title">Get in touch</h2>
        <p>Have questions or custom requests? Send us a message.</p>
        <form method="post" action="send_message.php" class="row g-2">
          <?php csrf_input(); ?>
          <div class="col-md-6"><input class="form-control" name="name" placeholder="Your name" required></div>
          <div class="col-md-6"><input type="email" class="form-control" name="email" placeholder="Your email" required></div>
          <div class="col-12"><input class="form-control" name="subject" placeholder="Subject" required></div>
          <div class="col-12"><textarea class="form-control" name="message" rows="3" placeholder="Message" required></textarea></div>
          <div class="col-12 d-grid d-md-block"><button class="btn btn-primary">Send Message</button></div>
        </form>
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <img class="img-fluid rounded" src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=1400&auto=format&fit=crop" alt="contact">
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>