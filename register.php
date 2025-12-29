<?php
require_once __DIR__ . '/includes/auth.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $pass = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';
  if (!$name || !$email || !$phone || !$address || !$pass || !$confirm) {
    $err = 'All fields are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'Invalid email address.';
  } elseif (strlen($pass) < 6) {
    $err = 'Password must be at least 6 characters.';
  } elseif ($pass !== $confirm) {
    $err = 'Passwords do not match.';
  } else {
    $emailEsc = $conn->real_escape_string($email);
    $exists = $conn->query("SELECT id FROM users WHERE email='$emailEsc' LIMIT 1");
    if ($exists && $exists->num_rows) {
      $err = 'Email already registered.';
    } else {
      // Add address column only if it doesn't already exist
      $colChk = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
      if (!$colChk || !$colChk->num_rows) {
        @$conn->query("ALTER TABLE users ADD COLUMN address VARCHAR(255) NULL");
      }
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users(name,email,password,phone,address) VALUES(?,?,?,?,?)");
      $stmt->bind_param('sssss', $name, $email, $hash, $phone, $address);
      $stmt->execute();
      $_SESSION['user_id'] = $stmt->insert_id;
      redirect('dashboard.php');
    }
  }
}
include __DIR__ . '/includes/header.php'; ?>
<div class="auth-wrap">
  <div class="auth-card">
    <h1 class="auth-title">Create Account</h1>
    <?php if ($err) echo '<div class="auth-alert">' . esc($err) . '</div>'; ?>
    <form method="post" class="auth-form" novalidate>
      <?php csrf_input(); ?>
      <input class="auth-input" name="name" placeholder="Full Name" required>
      <input type="email" class="auth-input" name="email" placeholder="Email" required>
      <input class="auth-input" name="phone" placeholder="Phone" required>
      <input class="auth-input" name="address" placeholder="Address" required>
      <input type="password" class="auth-input" name="password" placeholder="Password" minlength="6" required>
      <input type="password" class="auth-input" name="confirm_password" placeholder="Confirm Password" minlength="6" required>
      <button class="auth-btn" type="submit">Register</button>
      <div class="auth-note">Already have an account? <a href="login.php">Login</a></div>
    </form>
  </div>
  <style>
    .auth-wrap {
      min-height: calc(100vh - 120px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px
    }

    .auth-card {
      width: 100%;
      max-width: 560px;
      background: #fff;
      border: 1px solid rgba(0, 0, 0, .06);
      border-radius: 16px;
      box-shadow: 0 18px 40px rgba(0, 0, 0, .08), 0 2px 8px rgba(0, 0, 0, .06);
      padding: 24px
    }

    .auth-title {
      margin: 0 0 12px 0;
      font-weight: 700
    }

    .auth-alert {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
      border-radius: 10px;
      padding: 10px 12px;
      margin-bottom: 12px
    }

    .auth-form {
      display: flex;
      flex-direction: column;
      gap: 12px
    }

    .auth-input {
      border: 1px solid #e6e8eb;
      border-radius: 12px;
      padding: 12px 14px;
      font-size: 14px;
      outline: none
    }

    .auth-input:focus {
      border-color: #0d6efd;
      box-shadow: 0 0 0 3px rgba(13, 110, 253, .12)
    }

    .auth-btn {
      margin-top: 6px;
      background: linear-gradient(45deg, #0d6efd, #6610f2);
      color: #fff;
      border: 0;
      border-radius: 14px;
      padding: 12px 16px;
      font-weight: 700;
      cursor: pointer;
      width: 100%
    }

    .auth-btn:hover {
      filter: brightness(1.03)
    }

    .auth-note {
      font-size: 13px;
      color: #555;
      margin-top: 8px
    }

    .auth-note a {
      color: #0d6efd;
      text-decoration: none
    }

    .auth-note a:hover {
      text-decoration: underline
    }
  </style>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>