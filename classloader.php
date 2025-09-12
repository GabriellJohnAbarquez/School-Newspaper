<?php
// PATH: ./classloader.php
// Single classloader (root). All pages should require this using absolute path.

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Article.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// instantiate (these are used globally by pages)
$databaseObj = new Database();
$userObj = new User();
$articleObj = new Article();
?>
