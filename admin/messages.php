<?php require_once __DIR__ . '/header.php';
// Reply action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reply') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  $reply = trim($_POST['admin_reply'] ?? '');
  if ($id && $reply !== '') {
    $stmt = $conn->prepare("UPDATE messages SET admin_reply=?, status='Replied', replied_at=NOW() WHERE id=?");
    $stmt->bind_param('si', $reply, $id);
    $stmt->execute();
    // Notify the user by email (best-effort)
    $info = $conn->prepare("SELECT name,email,subject,message FROM messages WHERE id=? LIMIT 1");
    $info->bind_param('i', $id);
    $info->execute();
    $res = $info->get_result();
    if ($res && ($row = $res->fetch_assoc()) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
      $to = $row['email'];
      $sub = 'Re: ' . ($row['subject'] ?? 'Your enquiry');
      $from = (function_exists('get_setting') && get_setting('support_email')) ? get_setting('support_email') : ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
      $body = "Hello " . ($row['name'] ?: 'there') . ",\n\n" .
        "Thanks for contacting " . site_name() . ".\n" .
        "Your message: \n" . ($row['message'] ?? '') . "\n\n" .
        "Our reply: \n" . $reply . "\n\n" .
        "Regards,\n" . site_name();
      $hdrs = "From: " . site_name() . " <" . $from . ">\r\n" .
        "Reply-To: " . $from . "\r\n" .
        "MIME-Version: 1.0\r\n" .
        "Content-Type: text/plain; charset=UTF-8\r\n";
      @mail($to, $sub, $body, $hdrs);
    }
  }
}
$status = $_GET['status'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$where = $status ? (" WHERE status='" . $conn->real_escape_string($status) . "'") : '';
$list = $conn->query("SELECT * FROM messages $where ORDER BY created_at DESC LIMIT 100");
$view = null;
if ($id) {
  $r = $conn->query("SELECT * FROM messages WHERE id=$id");
  $view = $r && $r->num_rows ? $r->fetch_assoc() : null;
}
?>
<div class="row g-3">
  <div class="col-lg-4">
    <form class="d-flex mb-2" method="get">
      <select class="form-select" name="status">
        <option value="">All</option>
        <?php foreach (['New', 'Replied'] as $s) {
          $sel = $status === $s ? 'selected' : '';
          echo "<option $sel>$s</option>";
        } ?>
      </select>
      <button class="btn btn-outline-secondary ms-2">Filter</button>
    </form>
    <div class="list-group">
      <?php if ($list && $list->num_rows) {
        while ($m = $list->fetch_assoc()) {
          echo '<a class="list-group-item list-group-item-action ' . ($id == $m['id'] ? 'active' : '') . '" href="?id=' . (int)$m['id'] . '">';
          echo '<div class="d-flex w-100 justify-content-between"><h6 class="mb-1">' . esc($m['subject']) . '</h6><small>' . esc($m['created_at']) . '</small></div>';
          echo '<div class="small">' . esc($m['email']) . ' • ' . esc($m['status']) . '</div>';
          echo '</a>';
        }
      } else {
        echo '<div class="text-muted">No messages.</div>';
      } ?>
    </div>
  </div>
  <div class="col-lg-8">
    <?php if ($view): ?>
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="mb-1"><?php echo esc($view['subject']); ?></h5>
          <div class="small text-muted mb-2">From: <?php echo esc($view['name']); ?> (<?php echo esc($view['email']); ?>) • <?php echo esc($view['created_at']); ?></div>
          <p><?php echo nl2br(esc($view['message'])); ?></p>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h6>Reply</h6>
          <form method="post" class="vstack gap-2">
            <?php csrf_input(); ?><input type="hidden" name="action" value="reply"><input type="hidden" name="id" value="<?php echo (int)$view['id']; ?>">
            <select class="form-select" id="templates" onchange="document.getElementById('reply').value=this.value?this.value:'';">
              <option value="">Select quick template</option>
              <option>Thank you for contacting us. We will get back shortly.</option>
              <option>We have received your enquiry and will confirm availability soon.</option>
              <option>Could you please share the preferred dates and number of travelers?</option>
            </select>
            <textarea id="reply" class="form-control" name="admin_reply" rows="4" placeholder="Type your reply..." required><?php echo esc($view['admin_reply']); ?></textarea>
            <button class="btn btn-primary">Send Reply</button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info">Select a message to view and reply.</div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>