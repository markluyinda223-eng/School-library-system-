<?php
/**
 * catalog.php — Full Book Catalog with Search & Filter
 */
require_once __DIR__ . '/includes/library_functions.php';
require_once __DIR__ . '/includes/book_card.php';

$xml        = loadLibraryXML();
$categories = getCategories($xml);
$classes    = getClasses($xml);
$allBooks   = getAllBooks($xml);

// --- Read filter inputs ---
$filterCat    = $_GET['cat']    ?? '';
$filterClass  = $_GET['class']  ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterSearch = trim($_GET['q'] ?? '');

// --- Apply filters ---
$filtered = $allBooks;

if ($filterSearch !== '')     $filtered = searchBooks($filtered, $filterSearch);
if ($filterCat    !== '')     $filtered = array_values(getBooksByCategory($filtered, $filterCat));
if ($filterClass  !== '')     $filtered = array_values(getBooksByClass($filtered, $filterClass));
if ($filterStatus !== '')     $filtered = array_values(array_filter($filtered, fn($b) => $b['status'] === $filterStatus));

$count = count($filtered);

$pageTitle  = 'Full Catalog';
$activePage = 'catalog';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrap">

  <h1 class="page-title">📖 Full Book Catalog</h1>
  <p class="page-subtitle">Browse, search and filter all <?= count($allBooks) ?> book titles in the library.</p>

  <!-- ===== FILTER BAR ===== -->
  <form method="GET" action="catalog.php" style="display:contents">
    <div class="filter-bar">

      <div class="filter-group" style="flex:2;min-width:220px">
        <label for="q">🔍 Search Title / Author</label>
        <input type="text" id="q" name="q" placeholder="e.g. Mathematics, Orwell …"
               value="<?= htmlspecialchars($filterSearch) ?>"/>
      </div>

      <div class="filter-group">
        <label for="cat">🗂️ Category</label>
        <select id="cat" name="cat">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cid => $cat): ?>
            <option value="<?= $cid ?>" <?= $filterCat === $cid ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-group">
        <label for="class">🏫 Class</label>
        <select id="class" name="class">
          <option value="">All Classes</option>
          <optgroup label="Primary">
            <?php foreach ($classes as $cid => $cls):
              if ($cls['level'] !== 'primary') continue; ?>
              <option value="<?= $cid ?>" <?= $filterClass === $cid ? 'selected' : '' ?>>
                <?= htmlspecialchars($cls['name']) ?>
              </option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="Secondary">
            <?php foreach ($classes as $cid => $cls):
              if ($cls['level'] !== 'secondary') continue; ?>
              <option value="<?= $cid ?>" <?= $filterClass === $cid ? 'selected' : '' ?>>
                <?= htmlspecialchars($cls['name']) ?>
              </option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      </div>

      <div class="filter-group">
        <label for="status">📌 Status</label>
        <select id="status" name="status">
          <option value="">All Statuses</option>
          <option value="available"      <?= $filterStatus==='available'      ? 'selected':'' ?>>Available</option>
          <option value="borrowed"       <?= $filterStatus==='borrowed'       ? 'selected':'' ?>>Borrowed</option>
          <option value="reference_only" <?= $filterStatus==='reference_only' ? 'selected':'' ?>>Reference Only</option>
        </select>
      </div>

      <button type="submit" class="btn-filter">Apply Filters</button>
      <a href="catalog.php" class="btn-reset">Reset</a>
    </div>
  </form>

  <!-- Results count -->
  <p class="results-info">
    Showing <strong><?= $count ?></strong> of <?= count($allBooks) ?> books
    <?php if ($filterSearch): ?> &nbsp;·&nbsp; Search: "<em><?= htmlspecialchars($filterSearch) ?></em>"<?php endif; ?>
    <?php if ($filterCat): ?> &nbsp;·&nbsp; Category: <em><?= htmlspecialchars($categories[$filterCat]['name'] ?? $filterCat) ?></em><?php endif; ?>
    <?php if ($filterClass): ?> &nbsp;·&nbsp; Class: <em><?= htmlspecialchars($classes[$filterClass]['name'] ?? $filterClass) ?></em><?php endif; ?>
  </p>

  <!-- ===== BOOK GRID ===== -->
  <?php if ($count > 0): ?>
    <div class="books-grid">
      <?php foreach ($filtered as $book):
        renderBookCard($book, $categories, $classes);
      endforeach; ?>
    </div>
  <?php else: ?>
    <div class="no-results">
      <div class="no-icon">🔍</div>
      <h3>No books found</h3>
      <p>Try adjusting your search or filter criteria.</p>
      <br/><a href="catalog.php" class="btn-filter" style="display:inline-block;text-decoration:none;margin-top:8px">Clear All Filters</a>
    </div>
  <?php endif; ?>

</div><!-- /page-wrap -->

<footer class="site-footer">
  <strong>Greenfield School Library</strong> — Kampala, Uganda &nbsp;|&nbsp; <?= date('Y') ?>
</footer>
</body>
</html>
