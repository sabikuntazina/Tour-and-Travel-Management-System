<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

function current_user()
{
  global $conn;
  $id = $_SESSION['user_id'] ?? 0;
  if (!$id) return null;
  $res = $conn->query("SELECT id,name,email,is_active FROM users WHERE id=" . (int)$id . " LIMIT 1");
  return $res && $res->num_rows ? $res->fetch_assoc() : null;
}
function require_login()
{
  if (!($_SESSION['user_id'] ?? null)) redirect('login.php');
}
function is_admin()
{
  return isset($_SESSION['admin_id']);
}
function require_admin()
{
  if (!is_admin()) {
    $inAdmin = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') !== false;
    header('Location: ' . ($inAdmin ? 'login.php' : 'admin/login.php'));
    exit;
  }
}
