<?php
/**
 * index.php — Library Home Dashboard
 */
require_once __DIR__ . '/includes/library_functions.php';
require_once __DIR__ . '/includes/book_card.php';

$xml        = loadLibraryXML();
$categories = getCategories($xml);
$classes    = getClasses($xml);
$allBooks   = getAllBooks($xml);
$records    = getBorrowingRecords($xml);
$stats      = getStats($allBooks, $records);

$pageTitle  = 'Home';
$activePage = 'home';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrap">

  <h1 class="page-title">📚 Library Dashboard</h1>
  <p class="page-subtitle">Welcome to the Greenfield School Library Management System. Browse books by class, category, or search the full catalog.</p>

  <!-- ===== STATS ===== -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-num"><?= $stats['totalBooks'] ?></div>
      <div class="stat-lbl">Book Titles</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $stats['totalCopies'] ?></div>
      <div class="stat-lbl">Total Copies</div>
    </div>
    <div class="stat-card accent">
      <div class="stat-num"><?= $stats['availCopies'] ?></div>
      <div class="stat-lbl">Available Now</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $stats['borrowed'] ?></div>
      <div class="stat-lbl">Currently Borrowed</div>
    </div>
    <div class="stat-card danger">
      <div class="stat-num"><?= $stats['overdue'] ?></div>
      <div class="stat-lbl">Overdue</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= count($categories) ?></div>
      <div class="stat-lbl">Categories</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= count($classes) ?></div>
      <div class="stat-lbl">School Classes</div>
    </div>
  </div>

  <!-- ===== CATEGORIES OVERVIEW ===== -->
  <div class="section-header">
    <div class="section-bar" style="background:var(--accent)"></div>
    <h2>Browse by Category</h2>
  </div>

  <div class="class-grid" style="margin-bottom:36px">
    <?php foreach ($categories as $cid => $cat):
      $count = count(getBooksByCategory($allBooks, $cid));
    ?>
      <a href="by_category.php?cat=<?= urlencode($cid) ?>" class="class-card" style="border-top:4px solid <?= $cat['color'] ?>">
        <div class="class-card-name"><?= htmlspecialchars($cat['name']) ?></div>
        <div class="class-book-count">📘 <?= $count ?> book<?= $count !== 1 ? 's' : '' ?></div>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- ===== CLASSES OVERVIEW ===== -->
  <div class="section-header">
    <div class="section-bar" style="background:var(--primary)"></div>
    <h2>Browse by Class</h2>
  </div>

  <div style="margin-bottom:12px">
    <strong style="font-size:.85rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.5px">Primary Level</strong>
  </div>
  <div class="class-grid">
    <?php foreach ($classes as $cid => $cls):
      if ($cls['level'] !== 'primary') continue;
      $count = count(getBooksByClass($allBooks, $cid));
    ?>
      <a href="by_class.php?class=<?= urlencode($cid) ?>" class="class-card">
        <span class="class-card-level level-primary">Primary</span>
        <div class="class-card-name"><?= htmlspecialchars($cls['name']) ?></div>
        <div class="class-card-info">Age: <?= $cls['age_range'] ?> years</div>
        <div class="class-book-count">📚 <?= $count ?> book<?= $count !== 1 ? 's' : '' ?></div>
      </a>
    <?php endforeach; ?>
  </div>

  <div style="margin:20px 0 12px">
    <strong style="font-size:.85rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.5px">Secondary Level</strong>
  </div>
  <div class="class-grid">
    <?php foreach ($classes as $cid => $cls):
      if ($cls['level'] !== 'secondary') continue;
      $count = count(getBooksByClass($allBooks, $cid));
    ?>
      <a href="by_class.php?class=<?= urlencode($cid) ?>" class="class-card">
        <span class="class-card-level level-secondary">Secondary</span>
        <div class="class-card-name"><?= htmlspecialchars($cls['name']) ?></div>
        <div class="class-card-info">Age: <?= $cls['age_range'] ?> years</div>
        <div class="class-book-count">📚 <?= $count ?> book<?= $count !== 1 ? 's' : '' ?></div>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- ===== RECENTLY BORROWED ===== -->
  <div class="section-header" style="margin-top:36px">
    <div class="section-bar" style="background:#e53935"></div>
    <h2>Recent Borrowing Activity</h2>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Book Title</th>
          <th>Borrower</th>
          <th>Class</th>
          <th>Borrow Date</th>
          <th>Due Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (array_slice($records, 0, 5) as $i => $rec):
          $book = findBookById($allBooks, $rec['book_ref']);
        ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= htmlspecialchars($book ? $book['title'] : $rec['book_ref']) ?></strong></td>
            <td><?= htmlspecialchars($rec['borrower_name']) ?></td>
            <td><span class="class-tag"><?= htmlspecialchars($rec['borrower_class']) ?></span></td>
            <td><?= htmlspecialchars($rec['borrow_date']) ?></td>
            <td><?= htmlspecialchars($rec['due_date']) ?></td>
            <td>
              <span class="status-<?= $rec['status'] ?>">
                <?= ucfirst($rec['status']) ?>
                <?= $rec['status'] === 'overdue' ? ' ⚠️' : '' ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p style="margin-top:10px;font-size:.83rem">
    <a href="borrowing.php" style="color:var(--primary);font-weight:600">View all borrowing records →</a>
  </p>

</div><!-- /page-wrap -->

<footer class="site-footer">
  <strong>Greenfield School Library</strong> — Kampala, Uganda &nbsp;|&nbsp;
  Powered by PHP &amp; XML &nbsp;|&nbsp; <?= date('Y') ?>
</footer>
</body>
</html>
