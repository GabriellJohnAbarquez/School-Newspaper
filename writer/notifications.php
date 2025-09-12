<?php
// PATH: ./writer/notifications.php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }

// Notifications for the user
$notes = $articleObj->getNotifications($_SESSION['user_id']);

// Fetch pending edit requests for the user's articles
$editRequests = $articleObj->getEditRequestsForAuthor($_SESSION['user_id']);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Notifications</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <h4>Notifications</h4>

    <?php if (empty($notes) && empty($editRequests)) echo "<div>No notifications.</div>"; ?>

    <?php foreach ($notes as $n): ?>
    <div class="border p-3 mt-2 <?php echo $n['is_read'] ? '' : 'bg-light'; ?>">
        <div><?php echo htmlspecialchars($n['message']); ?></div>
        <div class="small text-muted"><?php echo $n['created_at']; ?></div>
        <?php if (!$n['is_read']): ?>
        <button class="btn btn-sm btn-primary markReadBtn mt-2" data-id="<?php echo $n['notification_id']; ?>">Mark as read</button>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php if (!empty($editRequests)): ?>
    <h5 class="mt-4">Pending Edit Requests</h5>
    <?php foreach ($editRequests as $req): ?>
    <div class="border p-3 mt-2 bg-light">
        <div>
            <strong><?php echo htmlspecialchars($req['requester_name']); ?></strong> requested edit access to
            "<strong><?php echo htmlspecialchars($req['title']); ?></strong>"
        </div>
        <div class="small text-muted"><?php echo $req['created_at']; ?></div>
        <form method="POST" class="mt-2 d-inline">
            <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
            <button type="submit" name="action" value="accept" class="btn btn-sm btn-success" formaction="../core/handleForms.php" formmethod="post">Accept</button>
            <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" formaction="../core/handleForms.php" formmethod="post">Reject</button>
            <input type="hidden" name="respondEditRequest" value="1">
        </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
$('.markReadBtn').on('click', function(){
    var nid = $(this).data('id');
    $.post('../core/handleForms.php', { markNotificationRead: 1, notification_id: nid }, function(){
        location.reload();
    });
});
</script>
</body>
</html>
