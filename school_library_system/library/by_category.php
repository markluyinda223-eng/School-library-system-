<?php
/**
 * by_category.php — Browse Books Organised by Category
 */
require_once __DIR__ . '/includes/library_functions.php';
require_once __DIR__ . '/includes/book_card.php';

$xml        = loadLibraryXML();
$categories = getCategories($xml);
$classes    = getClasses($xml);
$allBooks   = getAllBooks($xml);

$selectedCat = $_GET['cat'] ?? '';

$pageTitle  = 'Books by Category';
$activePage = 'bycat';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrap">

  <h1 class="page-title">🗂️ Books by Category</h1>
  <p class="page-subtitle">Browse the library collection organised by subject category.</p>

  <?php if ($selectedCat === ''):
    // ---- SHOW CATEGORY OVERVIEW ----
  ?>

    <div class="class-grid">
      <?php foreach ($categories as $cid => $cat):
        $catBooks = getBooksByCategory($allBooks, $cid);
        $count = count($catBooks);
        $availCount = count(array_filter($catBooks, fn($b) => $b['copies_avail'] > 0));
      ?>
        <a href="by_category.php?cat=<?= urlencode($cid) ?>" class="class-card"
           style="border-top:4px solid <?= $cat['color'] ?>">
          <div class="class-card-name"><?= htmlspecialchars($cat['name']) ?></div>
          <div class="class-book-count">📘 <?= $count ?> title<?= $count!==1?'s':'' ?></div>
          <div class="class-card-info" style="margin-top:4px">✅ <?= $availCount ?> with copies available</div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- All books grouped under their category headers -->
    <?php foreach ($categories as $cid => $cat):
      $catBooks = array_values(getBooksByCategory($allBooks, $cid));
      if (count($catBooks) === 0) continue;
    ?>
      <div class="section-header">
        <div class="section-bar" style="background:<?= $cat['color'] ?>"></div>
        <h2><?= htmlspecialchars($cat['name']) ?></h2>
        <span class="section-count" style="background:<?= $cat['color'] ?>"><?= count($catBooks) ?></span>
      </div>
      <div class="books-grid">
        <?php foreach ($catBooks as $book):
          renderBookCard($book, $categories, $classes);
        endforeach; ?>
      </div>
    <?php endforeach; ?>

  <?php else:
    // ---- SHOW SINGLE CATEGORY ----
    $cat = $categories[$selectedCat] ?? null;
    if (!$cat): ?>
      <p class="error">Category not found.</p>
    <?php else:
      $catBooks = array_values(getBooksByCategory($allBooks, $selectedCat));
      $count    = count($catBooks);
    ?>
      <p style="margin-bottom:18px">
        <a href="by_category.php" style="color:var(--primary);font-weight:600;text-decoration:none">
          ← Back to all categories
        </a>
      </p>

      <div class="section-header">
        <div class="section-bar" style="background:<?= $cat['color'] ?>"></div>
        <h2><?= htmlspecialchars($cat['name']) ?></h2>
        <span class="section-count" style="background:<?= $cat['color'] ?>"><?= $count ?> book<?= $count!==1?'s':'' ?></span>
      </div>

      <?php if ($count > 0): ?>
        <div class="books-grid">
          <?php foreach ($catBooks as $book):
            renderBookCard($book, $categories, $classes);
          endforeach; ?>
        </div>
      <?php else: ?>
        <div class="no-results">
          <div class="no-icon">📭</div>
          <h3>No books in this category yet</h3>
          <p><a href="catalog.php">Browse all books</a></p>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  <?php endif; ?>

</div><!-- /page-wrap -->

<footer class="site-footer">
  <strong>Greenfield School Library</strong> — Kampala, Uganda &nbsp;|&nbsp; <?= date('Y') ?>
</footer>
</body>
</html>
