<?php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }
if (!$userObj->isAdmin()) { header("Location: ../writer/index.php"); exit; }

// Admin sees all articles
$articles = $articleObj->getArticles();
$categories = $articleObj->getCategories();
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

<!-- Category Management -->
<div class="mt-4 mb-4">
  <h5>Manage Categories</h5>
  <form class="form-inline mb-3" method="POST" action="../core/handleForms.php">
    <input type="text" name="category_name" class="form-control mr-2" placeholder="New Category" required>
    <button type="submit" name="addCategoryBtn" class="btn btn-primary">Add</button>
  </form>
  <ul class="list-group">
    <?php foreach ($categories as $c): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <?php echo htmlspecialchars($c['name']); ?>
        <form method="POST" action="../core/handleForms.php" class="m-0">
          <input type="hidden" name="category_id" value="<?php echo $c['category_id']; ?>">
          <button class="btn btn-sm btn-danger" name="deleteCategoryBtn" onclick="return confirm('Delete this category?');">Delete</button>
        </form>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

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
  <?php if (!empty($a['category_name'])): ?>
    <div><span class="badge badge-secondary">Category: <?php echo htmlspecialchars($a['category_name']); ?></span></div>
  <?php endif; ?>
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
