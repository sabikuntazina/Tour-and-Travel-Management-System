<?php $aname = $_SESSION['admin_name'] ?? 'Admin'; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php"><i class="fa-solid fa-plane-departure text-primary"></i> <?php echo esc(site_name()); ?> Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><span class="nav-link">Hello, <?php echo esc($aname); ?></span></li>
        <li class="nav-item"><a class="nav-link" target="_blank" href="../index.php">View Site</a></li>
        <li class="nav-item"><a class="btn btn-outline-light ms-2" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>