<?php $user = current_user(); ?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-plane-departure text-primary"></i> <?php echo esc(site_name()); ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample" aria-controls="navbarsExample" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsExample">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?php echo active_menu('index.php'); ?>" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?php echo active_menu('packages.php'); ?>" href="packages.php">Packages</a></li>
        <li class="nav-item"><a class="nav-link <?php echo active_menu('contact.php'); ?>" href="contact.php">Contact</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fa-regular fa-user"></i> <?php echo esc($user['name']); ?></a></li>
          <li class="nav-item"><a class="btn btn-outline-danger ms-2" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-primary ms-2" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>