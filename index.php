<?php
require_once __DIR__ . '/classloader.php';

// Determine feed articles
$feedArticles = [];

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // 1. User's own articles
    $ownArticles = $articleObj->getArticlesByUserID($user_id);

    // 2. Shared articles
    $sharedArticles = $articleObj->getSharedArticles($user_id);

    // 3. Other authors' published articles
    $otherArticles = $articleObj->getActiveArticles();
    $otherArticles = array_filter($otherArticles, fn($a) => $a['author_id'] != $user_id);

    // Combine all and sort by date
    $feedArticles = array_merge($ownArticles, $sharedArticles, $otherArticles);
    usort($feedArticles, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
} else {
    $feedArticles = $articleObj->getActiveArticles();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>School Gazette</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Comic Neue', cursive; background: #f5f9ff; }
.card { border-radius: 14px; }
.site-title { color:#ff6f61; font-weight:700; }
.hero-icon { height:120px; object-fit:contain; }
.small-note { font-size:0.9rem; color:#666; }
.card-img-top { max-height:320px; object-fit:cover; }
</style>
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
  <?php include __DIR__ . '/includes/navbar.php'; ?>
<?php endif; ?>

<div class="container mt-5 mb-5">
<h1 class="text-center site-title">📰 School Gazette</h1>
<p class="text-center small-note">A kid-friendly yet professional place for school news. Choose your role below.</p>

<?php if (!isset($_SESSION['user_id'])): ?>
<div class="row mt-4">
  <div class="col-md-6">
    <div class="card shadow p-3 text-center">
      <img src="https://cdn-icons-png.flaticon.com/512/201/201565.png" class="hero-icon mx-auto d-block" alt="">
      <h3>Writer ✏️</h3>
      <p class="small-note">Create and request edits. Browse other writers' posts.</p>
      <a href="writer/login.php" class="btn btn-primary me-2">Writer Login</a>
      <a href="writer/register.php" class="btn btn-outline-primary">Writer Register</a>
    </div>
  </div>
  <div class="col-md-6">
  <div class="card shadow p-3 text-center">
    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="hero-icon mx-auto d-block" alt="">
    <h3>Admin 👨‍🏫</h3>
    <p class="small-note">Approve/reject posts, moderate content, manage all writers.</p>
    <a href="admin/login.php" class="btn btn-success me-2">Admin Login</a>
    <a href="admin/register.php" class="btn btn-outline-success">Admin Register</a>
  </div>
</div>

<?php endif; ?>

<h4 class="mt-5">Latest Articles</h4>
<div class="row">
<?php foreach ($feedArticles as $a): ?>
<div class="col-md-4">
  <div class="card p-2 mb-3">
     <?php 
     $img = !empty($a['image_path']) ? $a['image_path'] : (!empty($a['image_url']) ? $a['image_url'] : null);

     if ($img): ?>
       <img src="<?php echo htmlspecialchars($img); ?>" class="card-img-top mb-2">
     <?php endif; ?>
    <strong><?php echo htmlspecialchars($a['title']); ?></strong>
    <div class="small text-muted"><?php echo htmlspecialchars($a['username']); ?> • <?php echo $a['created_at']; ?></div>
    <?php if (!empty($a['is_admin']) && $a['is_admin']==1) echo '<span class="badge bg-primary mt-1">Admin</span>'; ?>
    <p><?php echo nl2br(htmlspecialchars(substr($a['content'],0,150))); ?>...</p>
    <a href="article_view.php?id=<?php echo $a['article_id']; ?>" class="btn btn-sm btn-outline-primary">Read More</a>
  </div>
</div>
<?php endforeach; ?>
</div>
</div>
</body>
</html>
