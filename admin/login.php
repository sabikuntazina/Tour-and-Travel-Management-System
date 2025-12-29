<?php require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';
if (isset($_SESSION['admin_id'])) {
  header('Location: index.php');
  exit;
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $uEsc = $conn->real_escape_string($username);
  $res = $conn->query("SELECT * FROM admins WHERE username='$uEsc' LIMIT 1");
  if ($res && $res->num_rows) {
    $admin = $res->fetch_assoc();
    if ($password === $admin['password']) {
      $_SESSION['admin_id'] = $admin['id'];
      $_SESSION['admin_name'] = $admin['name'];
      header('Location: index.php');
      exit;
    }
  }
  $err = 'Invalid credentials';
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login - TravelNext</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
  <div class="container" style="max-width:420px;">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-3">Admin Login</h3>
        <?php if ($err) echo '<div class="alert alert-danger">' . esc($err) . '</div>'; ?>
        <form method="post" class="vstack gap-3">
          <input class="form-control" name="username" placeholder="Username" required>
          <input type="password" class="form-control" name="password" placeholder="Password" required>
          <button class="btn btn-primary">Login</button>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>