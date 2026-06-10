<?php
/**
 * borrowing.php — Borrowing Records & Management
 */
require_once __DIR__ . '/includes/library_functions.php';

$xml        = loadLibraryXML();
$categories = getCategories($xml);
$classes    = getClasses($xml);
$allBooks   = getAllBooks($xml);
$records    = getBorrowingRecords($xml);

// Filter
$filterStatus = $_GET['status'] ?? '';
$filterClass  = $_GET['class']  ?? '';

$filtered = $records;
if ($filterStatus) $filtered = array_filter($filtered, fn($r) => $r['status'] === $filterStatus);
if ($filterClass)  $filtered = array_filter($filtered, fn($r) => $r['borrower_class'] === $filterClass);
$filtered = array_values($filtered);

// Counts
$total    = count($records);
$borrowed = count(array_filter($records, fn($r) => $r['status'] === 'borrowed'));
$overdue  = count(array_filter($records, fn($r) => $r['status'] === 'overdue'));
$returned = count(array_filter($records, fn($r) => $r['status'] === 'returned'));

$pageTitle  = 'Borrowing Records';
$activePage = 'borrowing';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrap">

  <h1 class="page-title">📋 Borrowing Records</h1>
  <p class="page-subtitle">Track all book loans, due dates, and returns across the school.</p>

  <!-- Stats -->
  <div class="stats-row" style="margin-bottom:24px">
    <div class="stat-card">
      <div class="stat-num"><?= $total ?></div>
      <div class="stat-lbl">Total Records</div>
    </div>
    <div class="stat-card accent">
      <div class="stat-num"><?= $borrowed ?></div>
      <div class="stat-lbl">Active Loans</div>
    </div>
    <div class="stat-card danger">
      <div class="stat-num"><?= $overdue ?></div>
      <div class="stat-lbl">Overdue</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $returned ?></div>
      <div class="stat-lbl">Returned</div>
    </div>
  </div>

  <!-- Overdue Alert -->
  <?php if ($overdue > 0): ?>
    <div style="background:#fff8e1;border-left:4px solid #ffa000;padding:14px 18px;border-radius:8px;margin-bottom:20px;font-size:.9rem;color:#5d4037">
      ⚠️ <strong><?= $overdue ?> book<?= $overdue!==1?'s':'' ?></strong> are currently overdue.
      Please follow up with the respective borrowers immediately.
    </div>
  <?php endif; ?>

  <!-- Filter Bar -->
  <form method="GET" action="borrowing.php" style="display:contents">
    <div class="filter-bar" style="margin-bottom:20px">
      <div class="filter-group">
        <label>📌 Filter by Status</label>
        <select name="status">
          <option value="">All Statuses</option>
          <option value="borrowed"  <?= $filterStatus==='borrowed'  ? 'selected':'' ?>>Active Loans</option>
          <option value="overdue"   <?= $filterStatus==='overdue'   ? 'selected':'' ?>>Overdue</option>
          <option value="returned"  <?= $filterStatus==='returned'  ? 'selected':'' ?>>Returned</option>
        </select>
      </div>
      <div class="filter-group">
        <label>🏫 Filter by Class</label>
        <select name="class">
          <option value="">All Classes</option>
          <?php foreach ($classes as $cid => $cls): ?>
            <option value="<?= $cid ?>" <?= $filterClass===$cid?'selected':'' ?>>
              <?= htmlspecialchars($cls['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn-filter">Filter</button>
      <a href="borrowing.php" class="btn-reset">Reset</a>
    </div>
  </form>

  <p class="results-info">Showing <strong><?= count($filtered) ?></strong> of <?= $total ?> records</p>

  <!-- Records Table -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Record ID</th>
          <th>Book Title</th>
          <th>Shelf</th>
          <th>Borrower Name</th>
          <th>Class</th>
          <th>Borrowed On</th>
          <th>Due Date</th>
          <th>Returned On</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($filtered) > 0):
          foreach ($filtered as $rec):
            $book = findBookById($allBooks, $rec['book_ref']);
            $isOverdue = $rec['status'] === 'overdue';
        ?>
          <tr style="<?= $isOverdue ? 'background:#fff5f5' : '' ?>">
            <td style="font-weight:600;color:var(--text-light);font-size:.8rem"><?= htmlspecialchars($rec['id']) ?></td>
            <td>
              <strong><?= htmlspecialchars($book ? $book['title'] : $rec['book_ref']) ?></strong>
              <?php if ($book): ?>
                <br/><span style="font-size:.75rem;color:var(--text-light)">✍️ <?= htmlspecialchars($book['author']) ?></span>
              <?php endif; ?>
            </td>
            <td style="font-size:.8rem;font-weight:600"><?= htmlspecialchars($book ? $book['shelf'] : '—') ?></td>
            <td><?= htmlspecialchars($rec['borrower_name']) ?></td>
            <td>
              <?php $cls = $classes[$rec['borrower_class']] ?? null; ?>
              <span class="class-tag <?= ($cls && $cls['level']==='secondary') ? 'secondary':'' ?>">
                <?= htmlspecialchars($rec['borrower_class']) ?>
              </span>
            </td>
            <td><?= htmlspecialchars($rec['borrow_date']) ?></td>
            <td style="<?= $isOverdue ? 'color:#c62828;font-weight:700' : '' ?>">
              <?= htmlspecialchars($rec['due_date']) ?>
              <?= $isOverdue ? ' ⚠️' : '' ?>
            </td>
            <td>
              <?= $rec['return_date'] ? htmlspecialchars($rec['return_date']) : '<em style="color:#aaa">Not yet</em>' ?>
            </td>
            <td>
              <span class="status-<?= $rec['status'] ?>">
                <?= ucfirst($rec['status']) ?>
              </span>
            </td>
          </tr>
        <?php endforeach;
        else: ?>
          <tr>
            <td colspan="9" style="text-align:center;padding:32px;color:var(--text-light)">
              No records match the selected filters.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Borrowing Rules -->
  <div style="margin-top:32px;background:var(--card-bg);border-radius:var(--radius);padding:22px 24px;box-shadow:var(--shadow);border-left:4px solid var(--primary)">
    <h3 style="margin-bottom:12px;color:var(--primary)">📜 Library Borrowing Rules</h3>
    <ul style="list-style:none;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:8px;font-size:.87rem;color:var(--text-light)">
      <li>📅 Standard loan period: <strong>14 days</strong></li>
      <li>📚 Maximum books per student: <strong>2 at a time</strong></li>
      <li>💰 Overdue fine: <strong>UGX 500 per day</strong></li>
      <li>📌 Reference books are <strong>not for borrowing</strong></li>
      <li>🔄 Books can be renewed <strong>once</strong> if not reserved</li>
      <li>🏫 Teacher loans: <strong>up to 30 days</strong></li>
    </ul>
  </div>

</div><!-- /page-wrap -->

<footer class="site-footer">
  <strong>Greenfield School Library</strong> — Kampala, Uganda &nbsp;|&nbsp; <?= date('Y') ?>
</footer>
</body>
</html>
