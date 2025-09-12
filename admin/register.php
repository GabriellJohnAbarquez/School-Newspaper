<?php
// PATH: ./admin/register.php
require_once __DIR__ . '/../classloader.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h4>Admin Register</h4>
    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert <?php echo ($_SESSION['status']=='200') ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['status']); ?>
      </div>
    <?php endif; ?>
    <form action="../core/handleForms.php" method="POST">
      <input type="hidden" name="role" value="1">
      <input type="text" name="username" placeholder="Username" class="form-control mb-2" required>
      <input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
      <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" class="form-control mb-2" required>
      <button class="btn btn-primary" name="insertNewUserBtn">Register as Admin</button>
    </form>
  </div>
</body>
</html>
