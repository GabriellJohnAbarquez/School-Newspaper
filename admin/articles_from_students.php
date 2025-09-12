<?php
// PATH: ./admin/articles_from_students.php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }
if (!$userObj->isAdmin()) { header("Location: ../writer/index.php"); exit; }
$articles = $articleObj->getArticles();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Pending Articles</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <div class="container mt-4">
    <h4>All Articles (Admin View)</h4>
    <?php foreach ($articles as $article): ?>
      <div class="card mt-3">
        <?php if (!empty($article['image_path'])): ?>
          <img src="<?php echo htmlspecialchars($article['image_path']); ?>" class="card-img-top" style="max-height:220px;object-fit:cover;">
        <?php endif; ?>
        <div class="card-body">
          <h5><?php echo htmlspecialchars($article['title']); ?></h5>
          <div class="small text-muted"><?php echo htmlspecialchars($article['username']); ?> — <?php echo $article['created_at']; ?></div>
          <p><?php echo nl2br(htmlspecialchars($article['content'])); ?></p>

          <button class="btn btn-danger deleteBtn" data-id="<?php echo $article['article_id']; ?>">Delete (notify author)</button>

          <form class="d-inline ms-2" action="../core/handleForms.php" method="POST">
            <input type="hidden" name="article_id" value="<?php echo $article['article_id']; ?>">
            <select name="status" class="form-select d-inline-block" style="width:auto">
              <option value="">Visibility</option>
              <option value="0">Pending</option>
              <option value="1">Active</option>
            </select>
            <button type="submit" name="updateArticleVisibility" value="1" class="btn btn-sm btn-outline-secondary ms-1">Set</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<script>
$('.deleteBtn').on('click', function(){
  if (!confirm('Delete article and notify author?')) return;
  var id = $(this).data('id');
  $.post('../core/handleForms.php', { deleteArticleBtn: 1, article_id: id }, function(data){
    if (data == 1) location.reload(); else alert('Failed');
  });
});
</script>
</body>
</html>
