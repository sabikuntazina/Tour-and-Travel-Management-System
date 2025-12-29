<?php require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - <?php echo esc(site_name()); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <style>
    .admin-sidebar {
      min-height: 100vh
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/navbar.php'; ?>
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-2 p-3 bg-light admin-sidebar">
        <div class="list-group">
          <a class="list-group-item list-group-item-action" href="index.php"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</a>
          <a class="list-group-item list-group-item-action" href="packages.php"><i class="fa-solid fa-suitcase-rolling me-2"></i>Packages</a>
          <a class="list-group-item list-group-item-action" href="bookings.php"><i class="fa-solid fa-calendar-check me-2"></i>Bookings</a>
          <a class="list-group-item list-group-item-action" href="payments.php"><i class="fa-solid fa-credit-card me-2"></i>Payments</a>
          <a class="list-group-item list-group-item-action" href="users.php"><i class="fa-regular fa-user me-2"></i>Users</a>
          <a class="list-group-item list-group-item-action" href="reviews.php"><i class="fa-regular fa-star me-2"></i>Reviews</a>
          <a class="list-group-item list-group-item-action" href="messages.php"><i class="fa-regular fa-envelope me-2"></i>Messages</a>
          <a class="list-group-item list-group-item-action" href="settings.php"><i class="fa-solid fa-gear me-2"></i>Settings</a>
        </div>
      </aside>
      <main class="col-md-10 p-4">