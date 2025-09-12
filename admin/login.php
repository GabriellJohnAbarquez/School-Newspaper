<?php
// PATH: ./admin/login.php
require_once __DIR__ . '/../classloader.php';
if ($userObj->isLoggedIn() && $userObj->isAdmin()) { header("Location: index.php"); exit; }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h4>Admin Login</h4>
    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert <?php echo ($_SESSION['status']=='200') ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['status']); ?>
      </div>
    <?php endif; ?>
    <form action="../core/handleForms.php" method="POST">
      <input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
      <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
      <button class="btn btn-success" name="loginUserBtn">Login</button>
    </form>
    <p class="mt-3">Don't have an account? <a href="register.php">Register as admin</a></p>
  </div>
</body>
</html>
