<?php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }
if (!$userObj->isAdmin()) { header("Location: ../writer/index.php"); exit; }

// Admin sees all articles
$articles = $articleObj->getArticles();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.card { margin-bottom: 15px; border-radius: 14px; }
.badge-status { margin-left: 5px; }
.card-img-top { max-height:220px; object-fit:cover; }
</style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
<h3>Admin Dashboard — Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></h3>

<h5 class="mt-4">Article Feed</h5>
<div id="article-feed">
<?php foreach ($articles as $a): ?>
<div class="card p-2">
  <?php 
  $img = !empty($a['image_path']) ? $a['image_path'] : (!empty($a['image_url']) ? $a['image_url'] : null);

  if ($img): ?>
    <img src="<?php echo htmlspecialchars($img); ?>" class="card-img-top mb-2">
  <?php endif; ?>

  <strong><?php echo htmlspecialchars($a['title']); ?>
    <?php if ($a['is_active']==0) echo '<span class="badge bg-warning text-dark badge-status">Pending</span>';
          else echo '<span class="badge bg-success badge-status">Published</span>'; ?>
    <?php if (!empty($a['is_admin']) && $a['is_admin']==1) echo '<span class="badge bg-primary badge-status">Admin</span>'; ?>
  </strong>
  <div class="small text-muted"><?php echo htmlspecialchars($a['username']); ?> • <?php echo $a['created_at']; ?></div>
  <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>

  <?php if ($a['is_active']==0): ?>
    <form method="POST" action="../core/handleForms.php" class="d-inline">
      <input type="hidden" name="article_id" value="<?php echo $a['article_id']; ?>">
      <input type="hidden" name="status" value="1">
      <button type="submit" name="updateArticleVisibility" class="btn btn-sm btn-success">Approve</button>
    </form>
    <form method="POST" action="../core/handleForms.php" class="d-inline">
      <input type="hidden" name="article_id" value="<?php echo $a['article_id']; ?>">
      <input type="hidden" name="status" value="0">
      <button type="submit" name="updateArticleVisibility" class="btn btn-sm btn-danger">Reject</button>
    </form>
  <?php endif; ?>
  <!-- Admin can delete any article -->
  <form method="POST" class="d-inline" action="../core/handleForms.php">
    <input type="hidden" name="article_id" value="<?php echo $a['article_id']; ?>">
    <button class="btn btn-sm btn-danger" name="deleteArticleBtn">Delete</button>
  </form>

</div>
<?php endforeach; ?>
</div>
</div>
</body>
</html>
