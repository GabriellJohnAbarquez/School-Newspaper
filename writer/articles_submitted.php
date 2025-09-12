<?php
// PATH: ./writer/articles_submitted.php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }
if ($userObj->isAdmin()) { header("Location: ../admin/index.php"); exit; }
$articles = $articleObj->getArticlesByUserID($_SESSION['user_id']);
$requests = $articleObj->getEditRequestsForAuthor($_SESSION['user_id']);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Your Articles</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <div class="container mt-4">
    <h4>Your Articles</h4>

    <div class="card mb-3">
      <div class="card-body">
        <h5>Pending Edit Requests on your articles</h5>
        <?php if (empty($requests)) echo "<div>No pending requests.</div>"; ?>
        <?php foreach ($requests as $r): ?>
          <div class="border p-2 mt-2">
            <strong>From:</strong> <?php echo htmlspecialchars($r['requester_name']); ?> —
            <strong>Article:</strong> <?php echo htmlspecialchars($r['title']); ?>
            <div class="mt-2">
              <button class="btn btn-success respondReqBtn" data-id="<?php echo $r['request_id']; ?>" data-action="accept">Accept</button>
              <button class="btn btn-danger respondReqBtn" data-id="<?php echo $r['request_id']; ?>" data-action="reject">Reject</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php foreach ($articles as $a): ?>
      <div class="card mt-2">
        <?php if (!empty($a['image_path'])): ?>
          <img src="<?php echo htmlspecialchars($a['image_path']); ?>" class="card-img-top" style="max-height:220px;object-fit:cover;">
        <?php endif; ?>
        <div class="card-body">
          <h6><?php echo htmlspecialchars($a['title']); ?></h6>
          <small class="text-muted"><?php echo $a['created_at']; ?></small>
          <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>

          <form class="d-inline deleteForm">
            <input type="hidden" name="article_id" value="<?php echo $a['article_id']; ?>">
            <button class="btn btn-danger btn-sm">Delete</button>
          </form>

          <button class="btn btn-sm btn-secondary ms-2 toggleEdit">Edit</button>

          <div class="editForm mt-2 d-none">
            <form action="../core/handleForms.php" method="POST" enctype="multipart/form-data">
              <input type="text" name="title" class="form-control mb-1" value="<?php echo htmlspecialchars($a['title']); ?>">
              <textarea name="description" class="form-control mb-1"><?php echo htmlspecialchars($a['content']); ?></textarea>
              <input type="file" name="image" class="form-control mb-1" accept="image/*">
              <input type="hidden" name="article_id" value="<?php echo $a['article_id']; ?>">
              <button class="btn btn-primary btn-sm" name="editArticleBtn">Save</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<script>
$('.deleteForm').on('submit', function(e){
  e.preventDefault();
  if (!confirm('Delete this article?')) return;
  var id = $(this).find('input[name="article_id"]').val();
  $.post('../core/handleForms.php', { deleteArticleBtn:1, article_id:id }, function(data){
    if (data==1) location.reload(); else alert('Failed');
  });
});
$('.toggleEdit').on('click', function(){ $(this).closest('.card').find('.editForm').toggleClass('d-none'); });
$('.respondReqBtn').on('click', function(){
  var req = $(this).data('id'), action = $(this).data('action');
  $.post('../core/handleForms.php', { respondEditRequest:1, request_id:req, action:action }, function(data){
    if (data==1) location.reload(); else alert('Failed');
  });
});
</script>
</body>
</html>
