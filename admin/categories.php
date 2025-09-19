<?php
// PATH: ./admin/categories.php
require_once __DIR__ . '/../classloader.php';
if (!$userObj->isAdmin()) { header("Location: ../writer/index.php"); exit; }

$cats = $articleObj->getCategories();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Categories</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<div class="container mt-4">
  <h4>Manage Categories</h4>
  
  <form method="POST" action="../core/handleForms.php" class="form-inline mb-3">
    <input type="text" name="category_name" class="form-control mr-2" placeholder="Category name" required>
    <button class="btn btn-primary" name="addCategoryBtn">Add</button>
  </form>
  
  <ul class="list-group">
    <?php foreach ($cats as $c): ?>
      <li class="list-group-item d-flex justify-content-between">
        <?php echo htmlspecialchars($c['name']); ?>
        <form method="POST" action="../core/handleForms.php" class="mb-0">
          <input type="hidden" name="category_id" value="<?php echo $c['category_id']; ?>">
          <button class="btn btn-sm btn-danger" name="deleteCategoryBtn">Delete</button>
        </form>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
</body>
</html>
