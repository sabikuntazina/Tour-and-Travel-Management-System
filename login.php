<?php require_once __DIR__ . '/includes/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $emailEsc = $conn->real_escape_string($email);
  $res = $conn->query("SELECT * FROM users WHERE email='$emailEsc' LIMIT 1");
  if ($res && $res->num_rows) {
    $u = $res->fetch_assoc();
    if (password_verify($pass, $u['password'])) {
      if (!(int)$u['is_active']) {
        $err = 'Account deactivated.';
      } else {
        $_SESSION['user_id'] = $u['id'];
        redirect('dashboard.php');
      }
    } else {
      $err = 'Invalid credentials.';
    }
  } else {
    $err = 'Invalid credentials.';
  }
}
include __DIR__ . '/includes/header.php'; ?>
<div class="container py-5" style="max-width:520px;">
  <h1 class="mb-3">Login</h1>
  <?php if ($err) echo '<div class="alert alert-danger">' . esc($err) . '</div>'; ?>
  <form method="post" class="vstack gap-3">
    <?php csrf_input(); ?>
    <input type="email" class="form-control" name="email" placeholder="Email" required>
    <input type="password" class="form-control" name="password" placeholder="Password" required>
    <button class="btn btn-primary">Login</button>
    <div class="small">New here? <a href="register.php">Create an account</a></div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>