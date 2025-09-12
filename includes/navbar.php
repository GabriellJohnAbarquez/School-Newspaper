<?php
// PATH: ./includes/navbar.php
// expects $userObj and $articleObj from classloader

// Notifications count
$notifCount = 0;
if (isset($_SESSION['user_id'])) {
    $notes = $articleObj->getNotifications($_SESSION['user_id']);
    if ($notes) {
        foreach ($notes as $n) {
            if ($n['is_read'] == 0) $notifCount++;
        }
    }
}

// Logout path (absolute path works everywhere)
$logoutPath = '/School_Newspaper/core/handleForms.php?logoutUserBtn=1';
?>
<nav class="navbar navbar-expand-lg navbar-dark p-3" style="background-color: #008080;">
  <div class="container">
    <a class="navbar-brand" href="/School_Newspaper/index.php">School Gazette</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navmenu">
      <ul class="navbar-nav ms-auto">
        <?php if (!isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="/School_Newspaper/writer/login.php">Writer Login</a></li>
          <li class="nav-item"><a class="nav-link" href="/School_Newspaper/admin/login.php">Admin Login</a></li>
        <?php else: ?>
          <?php if ($userObj->isAdmin()): ?>
            <li class="nav-item"><a class="nav-link" href="/School_Newspaper/admin/index.php">Admin Dashboard</a></li>
            <li class="nav-item">
              <a class="nav-link" href="/School_Newspaper/admin/notifications.php">
                Notifications <?php if ($notifCount) echo "<span class='badge bg-light text-dark'>{$notifCount}</span>"; ?>
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/School_Newspaper/writer/index.php">Writer Dashboard</a></li>
            <li class="nav-item">
              <a class="nav-link" href="/School_Newspaper/writer/notifications.php">
                Notifications <?php if ($notifCount) echo "<span class='badge bg-light text-dark'>{$notifCount}</span>"; ?>
              </a>
            </li>
            <li class="nav-item"><a class="nav-link" href="/School_Newspaper/writer/shared_articles.php">Shared Articles</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo $logoutPath; ?>">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
