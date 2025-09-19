<?php
// PATH: ./classloader.php
// Single classloader (root). All pages should require this using absolute path.

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Article.php';
require_once __DIR__ . '/classes/Category.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// instantiate DB first
$databaseObj = new Database();
$pdo = $databaseObj->getConnection(); // ✅ now $pdo exists

// pass $pdo into your other classes if needed
$userObj = new User($pdo);
$articleObj = new Article($pdo);
$categoryObj = new Category($pdo);
