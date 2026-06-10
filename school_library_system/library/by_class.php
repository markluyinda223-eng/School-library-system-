<?php
/**
 * by_class.php — Browse Books Organised by Class
 */
require_once __DIR__ . '/includes/library_functions.php';
require_once __DIR__ . '/includes/book_card.php';

$xml        = loadLibraryXML();
$categories = getCategories($xml);
$classes    = getClasses($xml);
$allBooks   = getAllBooks($xml);

$selectedClass = $_GET['class'] ?? '';
$selectedLevel = $_GET['level'] ?? 'all'; // 'primary', 'secondary', 'all'

$pageTitle  = 'Books by Class';
$activePage = 'byclass';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrap">

  <h1 class="page-title">🏫 Books by Class</h1>
  <p class="page-subtitle">Find books recommended for each school class — Primary 1 through Senior 6.</p>

  <!-- Level Tabs -->
  <div class="level-tabs">
    <a href="by_class.php?level=all"       class="level-tab <?= $selectedLevel==='all'       ? 'active':'' ?>">All Levels</a>
    <a href="by_class.php?level=primary"   class="level-tab <?= $selectedLevel==='primary'   ? 'active':'' ?>">Primary Only</a>
    <a href="by_class.php?level=secondary" class="level-tab <?= $selectedLevel==='secondary' ? 'active':'' ?>">Secondary Only</a>
  </div>

  <?php if ($selectedClass === ''):
    // ---- SHOW ALL CLASS CARDS ----
  ?>
    <?php
    $showLevels = ['primary', 'secondary'];
    if ($selectedLevel !== 'all') $showLevels = [$selectedLevel];

    foreach ($showLevels as $level):
      $levelLabel = ucfirst($level);
    ?>
      <div class="section-header">
        <div class="section-bar" style="background:<?= $level==='primary' ? '#1565c0' : '#6a1a8a' ?>"></div>
        <h2><?= $levelLabel ?> Level</h2>
      </div>
      <div class="class-grid">
        <?php foreach ($classes as $cid => $cls):
          if ($cls['level'] !== $level) continue;
          $books = getBooksByClass($allBooks, $cid);
          $bcount = count($books);
        ?>
          <a href="by_class.php?class=<?= urlencode($cid) ?>&level=<?= $level ?>" class="class-card">
            <span class="class-card-level level-<?= $level ?>"><?= $levelLabel ?></span>
            <div class="class-card-name"><?= htmlspecialchars($cls['name']) ?></div>
            <div class="class-card-info">👶 Age: <?= $cls['age_range'] ?> years</div>
            <div class="class-book-count">📚 <?= $bcount ?> book<?= $bcount !== 1 ? 's' : '' ?> available</div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

  <?php else:
    // ---- SHOW BOOKS FOR A SPECIFIC CLASS ----
    $cls = $classes[$selectedClass] ?? null;
    if (!$cls): ?>
      <p class="error">Class not found.</p>
    <?php else:
      $booksForClass = array_values(getBooksByClass($allBooks, $selectedClass));
      $bcount        = count($booksForClass);
    ?>
      <!-- Back link -->
      <p style="margin-bottom:18px">
        <a href="by_class.php?level=<?= $selectedLevel ?>" style="color:var(--primary);font-weight:600;text-decoration:none">
          ← Back to all classes
        </a>
      </p>

      <div class="section-header">
        <div class="section-bar" style="background:<?= $cls['level']==='primary' ? '#1565c0' : '#6a1a8a' ?>"></div>
        <h2><?= htmlspecialchars($cls['name']) ?></h2>
        <span class="section-count"><?= $bcount ?> book<?= $bcount!==1?'s':'' ?></span>
        <span class="class-card-level level-<?= $cls['level'] ?>" style="margin-left:4px">
          <?= ucfirst($cls['level']) ?>
        </span>
      </div>
      <p style="font-size:.85rem;color:var(--text-light);margin-bottom:20px">
        Recommended age: <?= $cls['age_range'] ?> years
      </p>

      <?php if ($bcount > 0): ?>
        <!-- Group by category for this class -->
        <?php
        $byCategory = [];
        foreach ($booksForClass as $book) {
            $byCategory[$book['category_ref']][] = $book;
        }
        ?>
        <?php foreach ($byCategory as $cid => $catBooks):
          $cat = $categories[$cid] ?? ['name'=>'Unknown','color'=>'#888'];
        ?>
          <div class="section-header" style="margin-top:20px">
            <div class="section-bar" style="background:<?= $cat['color'] ?>"></div>
            <h2 style="font-size:1rem"><?= htmlspecialchars($cat['name']) ?></h2>
            <span class="section-count" style="background:<?= $cat['color'] ?>"><?= count($catBooks) ?></span>
          </div>
          <div class="books-grid">
            <?php foreach ($catBooks as $book):
              renderBookCard($book, $categories, $classes);
            endforeach; ?>
          </div>
        <?php endforeach; ?>

      <?php else: ?>
        <div class="no-results">
          <div class="no-icon">📭</div>
          <h3>No books assigned to this class yet</h3>
          <p>Check back later or browse the <a href="catalog.php">full catalog</a>.</p>
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
