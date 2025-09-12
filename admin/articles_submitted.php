<?php
// PATH: ./admin/articles_submitted.php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }
if (!$userObj->isAdmin()) { header("Location: ../writer/index.php"); exit; }
$articles = $articleObj->getArticlesByUserID($_SESSION['user_id']);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Submit</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <div class="container mt-4">
    <h4>Your Admin Messages</h4>
    <form action="../core/handleForms.php" method="POST" enctype="multipart/form-data">
      <input type="text" name="title" class="form-control mb-2" placeholder="Title">
      <textarea name="description" class="form-control mb-2" placeholder="Message"></textarea>
      <input type="file" name="image" class="form-control mb-2" accept="image/*">
      <button class="btn btn-primary" name="insertAdminArticleBtn">Publish as Admin</button>
    </form>

    <h5 class="mt-4">Your Admin posts</h5>
    <?php foreach ($articles as $a): ?>
      <div class="card mt-2 p-2">
        <h6><?php echo htmlspecialchars($a['title']); ?></h6>
        <small><?php echo $a['created_at']; ?></small>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
