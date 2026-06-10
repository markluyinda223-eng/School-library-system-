<?php
/**
 * book_card.php — Reusable book card component
 * Expects: $book (array), $categories (array), $classes (array)
 */

// Emoji icons per category for spine
$spineIcons = [
    'CAT001' => '🔬', 'CAT002' => '📐', 'CAT003' => '📝',
    'CAT004' => '🏛️',  'CAT005' => '✝️',  'CAT006' => '🌍',
    'CAT007' => '📚',  'CAT008' => '🎨',
];

function renderBookCard(array $book, array $categories, array $classes): void {
    global $spineIcons;
    $cat     = $categories[$book['category_ref']] ?? ['name'=>'Unknown','color'=>'#999'];
    $icon    = $spineIcons[$book['category_ref']] ?? '📗';
    $bgLight = $cat['color'] . '22'; // light tint
    ?>
    <div class="book-card">

      <!-- Header: spine icon + title/author -->
      <div class="book-card-header">
        <div class="book-spine" style="background:<?= $bgLight ?>;border:2px solid <?= $cat['color'] ?>20">
          <?= $icon ?>
        </div>
        <div class="book-title-block">
          <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
          <div class="book-author">✍️ <?= htmlspecialchars($book['author']) ?></div>
        </div>
      </div>

      <!-- Body -->
      <div class="book-card-body">

        <!-- Description -->
        <p class="book-desc"><?= htmlspecialchars($book['description']) ?></p>

        <!-- Classes for this book -->
        <div class="class-tags">
          <?php foreach ($book['classes'] as $cid):
            $cls = $classes[$cid] ?? null;
            if (!$cls) continue;
            $isSecondary = $cls['level'] === 'secondary';
          ?>
            <span class="class-tag <?= $isSecondary ? 'secondary' : '' ?>"><?= htmlspecialchars($cid) ?></span>
          <?php endforeach; ?>
        </div>

        <!-- Meta info grid -->
        <div class="book-meta">
          <div class="meta-item">
            <span class="meta-label">Publisher</span>
            <span class="meta-value"><?= htmlspecialchars($book['publisher']) ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Year / Edition</span>
            <span class="meta-value"><?= $book['year'] ?> &nbsp;·&nbsp; <?= htmlspecialchars($book['edition']) ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">ISBN</span>
            <span class="meta-value" style="font-size:.7rem"><?= htmlspecialchars($book['isbn']) ?></span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Shelf</span>
            <span class="meta-value"><?= htmlspecialchars($book['shelf']) ?></span>
          </div>
        </div>

        <!-- Availability bar -->
        <?php if ($book['status'] !== 'reference_only'): ?>
          <?= availabilityBar($book['copies_total'], $book['copies_avail']) ?>
          <div class="avail-label"><?= $book['copies_avail'] ?> of <?= $book['copies_total'] ?> copies available</div>
        <?php endif; ?>
      </div>

      <!-- Footer: category tag + status badge -->
      <div class="book-card-footer">
        <span class="cat-tag" style="background:<?= $cat['color'] ?>">
          <?= $icon ?> <?= htmlspecialchars($cat['name']) ?>
        </span>
        <?= statusBadge($book['status']) ?>
      </div>

    </div>
    <?php
}
