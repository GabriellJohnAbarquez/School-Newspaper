<?php
// PATH: ./writer/shared_articles.php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }
if ($userObj->isAdmin()) { header("Location: ../admin/index.php"); exit; }
$shared = $articleObj->getSharedArticles($_SESSION['user_id']);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Shared Articles</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <div class="container mt-4">
    <h4>Articles Shared With You</h4>
    <?php if (empty($shared)) echo "<div>No shared articles yet.</div>"; ?>
    <?php foreach ($shared as $a): ?>
      <div class="card mt-3">
        <?php if (!empty($a['image_path'])): ?>
          <img src="<?php echo htmlspecialchars($a['image_path']); ?>" class="card-img-top" style="max-height:220px;object-fit:cover;">
        <?php endif; ?>
        <div class="card-body">
          <h5><?php echo htmlspecialchars($a['title']); ?></h5>
          <div class="small text-muted">Author: <?php echo htmlspecialchars($a['username']); ?> | <?php echo $a['created_at']; ?></div>
          <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>

          <button class="btn btn-secondary toggleEdit mt-2">Edit</button>
          <div class="editForm mt-3 d-none">
            <form action="../core/handleForms.php" method="POST" enctype="multipart/form-data">
              <input type="text" name="title" class="form-control mb-1" value="<?php echo htmlspecialchars($a['title']); ?>">
              <textarea name="description" class="form-control mb-1"><?php echo htmlspecialchars($a['content']); ?></textarea>
              <input type="file" name="image" class="form-control mb-1" accept="image/*">
              <input type="text" name="image_url" class="form-control mb-1" placeholder="Or paste image URL (optional)">
              <input type="hidden" name="article_id" value="<?php echo $a['article_id']; ?>">
              <button class="btn btn-primary btn-sm" name="editArticleBtn">Save</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<script>
$('.toggleEdit').on('click', function(){ $(this).closest('.card').find('.editForm').toggleClass('d-none'); });
</script>
</body>
</html>
