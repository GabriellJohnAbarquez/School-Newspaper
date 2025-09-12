<?php
// PATH: ./writer/register.php
require_once __DIR__ . '/../classloader.php';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Writer Register</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include __DIR__ . '/../includes/navbar.php'; ?>

  <div class="container mt-4">
    <h4>Writer Register</h4>
    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert <?php echo ($_SESSION['status']=='200') ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['status']); ?>
      </div>
    <?php endif; ?>
    <form action="../core/handleForms.php" method="POST">
      <input type="hidden" name="role" value="0">
      <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
      <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
      <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
      <input type="password" name="confirm_password" class="form-control mb-2" placeholder="Confirm password" required>
      <button class="btn btn-primary" name="insertNewUserBtn">Register</button>
    </form>
  </div>
</body>
</html>
