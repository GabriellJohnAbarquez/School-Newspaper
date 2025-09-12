<?php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }
if ($userObj->isAdmin()) { header("Location: ../admin/index.php"); exit; }

// Fetch all articles for writer feed (own + shared + others)
$articles = $articleObj->getFeedForUser($_SESSION['user_id']);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Writer Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<style>
.card { margin-bottom: 15px; border-radius: 14px; }
.badge-status { margin-left: 5px; }
.card-img-top { max-height:220px; object-fit:cover; border-radius: 14px 14px 0 0; }
</style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
<h4>Writer Dashboard — Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></h4>

<div class="row mb-4">
  <div class="col-md-6">
    <form action="../core/handleForms.php" method="POST" enctype="multipart/form-data">
      <input type="text" name="title" class="form-control mb-2" placeholder="Title" required>
      <textarea name="description" class="form-control mb-2" placeholder="Write your article..." required></textarea>
      <input type="file" name="image" class="form-control mb-2" accept="image/*">
      <input type="text" name="image_url" class="form-control mb-2" placeholder="Or paste image URL (optional)">
      <button class="btn btn-primary" name="insertArticleBtn">Submit Article</button>
    </form>
  </div>
</div>

<h5 class="mt-4">Article Feed</h5>
<div class="row">
<?php foreach ($articles as $a): ?>
<div class="col-md-4">
  <div class="card">
    <?php 
    $img = !empty($a['image_path']) ? $a['image_path'] : (!empty($a['image_url']) ? $a['image_url'] : null);

    if ($img): ?>
      <img src="<?php echo htmlspecialchars($img); ?>" class="card-img-top mb-2">
    <?php endif; ?>
    <div class="card-body">
      <h6>
        <?php echo htmlspecialchars($a['title']); ?>
        <?php
          if ($a['relation'] === 'own' && $a['is_active']==0) echo '<span class="badge bg-warning text-dark badge-status">Pending</span>';
          elseif ($a['relation'] === 'own' && $a['is_active']==1) echo '<span class="badge bg-success badge-status">Published</span>';
          elseif ($a['relation'] === 'shared') echo '<span class="badge bg-info text-dark badge-status">Shared</span>';
          else echo '<span class="badge bg-secondary text-dark badge-status">Other</span>';
          if (!empty($a['is_admin']) && $a['is_admin']==1) echo '<span class="badge bg-primary badge-status">Admin</span>';
        ?>
      </h6>
      <div class="small text-muted"><?php echo htmlspecialchars($a['username']); ?> • <?php echo $a['created_at']; ?></div>
      <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>

      <?php
      $canEdit = $a['relation']==='own' || $a['relation']==='shared';
      if ($canEdit):
          if ($a['relation']==='own'): ?>
            <a href="edit_article.php?id=<?php echo $a['article_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
            <form method="POST" action="../core/handleForms.php" class="d-inline">
              <input type="hidden" name="article_id" value="<?php echo $a['article_id']; ?>">
              <button class="btn btn-sm btn-danger" name="deleteArticleBtn" onclick="return confirm('Are you sure you want to delete this article?');">Delete</button>
            </form>
          <?php else: ?>
            <!-- Shared articles: optional Edit if allowed -->
            <a href="edit_article.php?id=<?php echo $a['article_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
          <?php endif;
      else: ?>
        <form class="d-inline" action="../core/handleForms.php" method="POST">
          <input type="hidden" name="article_id" value="<?php echo $a['article_id']; ?>">
          <input type="text" name="message" class="form-control mb-1" placeholder="(Optional) Why request edit?" />
          <button class="btn btn-outline-primary btn-sm" name="requestEditBtn">Request Edit</button>
        </form>
      <?php endif; ?>

    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
</div>
</body>
</html>
