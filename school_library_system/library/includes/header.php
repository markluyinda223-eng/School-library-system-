<?php
/**
 * header.php — Reusable page header & navigation
 * Usage: include __DIR__ . '/header.php';
 * Set $pageTitle before including.
 */
$pageTitle  = $pageTitle  ?? 'Library';
$activePage = $activePage ?? 'home';
$rootPath   = $rootPath   ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle) ?> — Greenfield School Library</title>
  <link rel="stylesheet" href="<?= $rootPath ?>assets/css/style.css"/>
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <div class="header-logo">📚</div>
    <div class="header-text">
      <h1>Greenfield School Library</h1>
      <p>Kampala, Uganda &nbsp;|&nbsp; Knowledge is Power</p>
    </div>
  </div>
  <nav class="main-nav">
    <ul>
      <li><a href="<?= $rootPath ?>index.php"      class="<?= $activePage==='home'       ? 'active':'' ?>">🏠 Home</a></li>
      <li><a href="<?= $rootPath ?>catalog.php"    class="<?= $activePage==='catalog'    ? 'active':'' ?>">📖 Full Catalog</a></li>
      <li><a href="<?= $rootPath ?>by_class.php"   class="<?= $activePage==='byclass'    ? 'active':'' ?>">🏫 By Class</a></li>
      <li><a href="<?= $rootPath ?>by_category.php"class="<?= $activePage==='bycat'      ? 'active':'' ?>">🗂️ By Category</a></li>
      <li><a href="<?= $rootPath ?>borrowing.php"  class="<?= $activePage==='borrowing'  ? 'active':'' ?>">📋 Borrowing</a></li>
    </ul>
  </nav>
</header>
