<?php
// PATH: ./admin/notifications.php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn() || !$userObj->isAdmin()) { header("Location: login.php"); exit; }
$notes = $articleObj->getNotifications($_SESSION['user_id']);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Notifications</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <div class="container mt-4">
    <h4>Notifications</h4>
    <?php if (empty($notes)) echo "<div>No notifications.</div>"; ?>
    <?php foreach ($notes as $n): ?>
      <div class="border p-3 mt-2 <?php echo $n['is_read'] ? '' : 'bg-light'; ?>">
        <div><?php echo htmlspecialchars($n['message']); ?></div>
        <div class="small text-muted"><?php echo $n['created_at']; ?></div>
        <?php if (!$n['is_read']): ?>
          <button class="btn btn-sm btn-primary markReadBtn mt-2" data-id="<?php echo $n['notification_id']; ?>">Mark as read</button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

<script>
$('.markReadBtn').on('click', function(){
  var nid = $(this).data('id');
  $.post('../core/handleForms.php', { markNotificationRead: 1, notification_id: nid }, function(data){
    if (data == 1) location.reload(); else alert('Failed');
  });
});
</script>
</body>
</html>
