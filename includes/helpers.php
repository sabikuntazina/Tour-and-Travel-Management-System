<?php
function esc($v)
{
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function redirect($url)
{
  header('Location: ' . $url);
  exit;
}
function price($n)
{
  return '$' . number_format((float)$n, 2);
}
function csrf_token()
{
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}
function csrf_input()
{
  echo '<input type="hidden" name="csrf" value="' . esc(csrf_token()) . '">';
}
function check_csrf()
{
  if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
    http_response_code(403);
    exit('Invalid CSRF token');
  }
}
function active_menu($p)
{
  $cur = basename($_SERVER['PHP_SELF']);
  return $cur === $p ? 'active' : '';
}
function slugify($text)
{
  $text = preg_replace('~[\p{Pd}\s]+~u', '-', trim($text));
  $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
  $text = preg_replace('~[^-\w]+~', '', strtolower($text));
  $text = trim($text, '-');
  return $text ?: ('pkg-' . substr(md5($text . microtime()), 0, 8));
}
function get_setting($key, $default = null)
{
  global $conn;
  $k = $conn->real_escape_string($key);
  $res = $conn->query("SELECT svalue FROM settings WHERE skey='$k' LIMIT 1");
  if ($res && $res->num_rows) {
    $row = $res->fetch_assoc();
    return $row['svalue'];
  }
  return $default;
}
function set_setting($key, $value)
{
  global $conn;
  $k = $conn->real_escape_string($key);
  $v = $conn->real_escape_string($value);
  $conn->query("INSERT INTO settings(skey,svalue) VALUES('$k','$v') ON DUPLICATE KEY UPDATE svalue='$v'");
}
function site_name()
{
  return get_setting('site_name', defined('SITE_NAME') ? SITE_NAME : 'TravelNext');
}
function image_src($u)
{
  $u = trim((string)$u);
  if ($u === '') return 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1200&auto=format&fit=crop';
  if (strpos($u, '../uploads/') === 0) {
    $u = substr($u, 3);
  }
  if (strpos($u, './') === 0) {
    $u = substr($u, 2);
  }
  if (strpos($u, 'http') !== 0) {
    return $u;
  }
  if (preg_match('~unsplash\.com/photos/([A-Za-z0-9_-]+)~', $u, $m)) {
    return 'https://source.unsplash.com/' . $m[1] . '/1200x800';
  }
  return $u;
}

function unsplash_destination_image($location, $w = 800, $h = 600)
{
  $loc = strtolower(trim((string)$location));
  $map = [
    'cox' => "cox%27s%20bazar,beach,bangladesh",
    'bandarban' => "bandarban,hills,bangladesh",
    'sylhet' => "sylhet,tea%20garden,bangladesh",
    'rangamati' => "rangamati,lake,bangladesh",
    'khagrachari' => "khagrachari,hill%20tracts,bangladesh",
    'sajek' => "sajek%20valley,hills,bangladesh",
    'saint martin' => "saint%20martin%20island,sea,bangladesh",
  ];
  foreach ($map as $k => $q) {
    if (strpos($loc, $k) !== false) {
      $query = $q;
      break;
    }
  }
  if (empty($query)) {
    $query = rawurlencode($location) . ',travel';
  }
  return "https://source.unsplash.com/featured/{$w}x{$h}/?{$query}";
}
