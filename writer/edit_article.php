<?php
// PATH: ./writer/edit_article.php
require_once __DIR__ . '/../classloader.php';
$categoryObj = new Category($pdo);
$categories = $categoryObj->getAllCategories();

if (!$userObj->isLoggedIn()) { header("Location: login.php"); exit; }
$article_id = $_GET['id'] ?? null;
if (!$article_id) { header("Location: index.php"); exit; }

// Fetch the article
$article = $articleObj->getArticles($article_id);
if (!$article) { die("Article not found."); }

// Permission check: author OR shared OR admin
if (!($article['author_id'] == $_SESSION['user_id'] || $articleObj->userCanEdit($article_id, $_SESSION['user_id']) || $userObj->isAdmin())) {
    die("You are not allowed to edit this article.");
}

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['description'] ?? '');
    $image_path = null;

    // Handle image upload or URL
    if (!empty($_POST['image_url'])) {
        $image_path = trim($_POST['image_url']);
    } elseif (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $u = $_FILES['image'];
        $allowed = ['image/png','image/jpeg','image/jpg','image/gif','image/webp'];
        if (in_array($u['type'], $allowed)) {
            $uploadsDir = __DIR__ . '/../uploads';
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);
            $ext = pathinfo($u['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_') . '.' . $ext;
            $target = $uploadsDir . '/' . $filename;
            if (move_uploaded_file($u['tmp_name'], $target)) {
                $image_path = 'uploads/' . $filename;
            }
        }
    }

    // Update article
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $articleObj->updateArticle($article_id, $title, $content, $image_path, $category_id);
    $_SESSION['message'] = "Article updated successfully.";
    $_SESSION['status'] = '200';
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Edit Article</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
<h4>Edit Article</h4>
<?php if (isset($_SESSION['message'])): ?>
  <div class="alert <?php echo ($_SESSION['status']=='200') ? 'alert-success' : 'alert-danger'; ?>">
    <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['status']); ?>
  </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <div class="form-group">
    <label>Title</label>
    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($article['title']); ?>" required>
  </div>
  <div class="form-group">
    <label>Content</label>
    <textarea name="description" class="form-control" rows="6" required><?php echo htmlspecialchars($article['content']); ?></textarea>
  </div>
  <div class="form-group">
    <label>Current Image</label><br>
    <?php if (!empty($article['image_path'])): ?>
      <img src="<?php echo htmlspecialchars($article['image_path']); ?>" style="max-width:200px; max-height:150px; object-fit:cover;">
    <?php else: ?>
      <span>No image</span>
    <?php endif; ?>
  </div>
  <div class="form-group">
    <label>Change Image</label>
    <input type="file" name="image" class="form-control mb-2" accept="image/*">
    <input type="text" name="image_url" class="form-control" placeholder="Or paste image URL (optional)">
  </div>
  <div class="form-group">
    <label>Category</label>
    <select name="category_id" class="form-control" required>
      <option value="">-- Select Category --</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo $cat['category_id']; ?>"
          <?php echo ($article['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($cat['name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-primary">Update Article</button>
  <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>
</div>
</body>
</html>
