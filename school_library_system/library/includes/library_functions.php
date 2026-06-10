<?php
/**
 * library_functions.php
 * Core helper functions for the School Library System
 * Loads and parses the XML data file and provides utility methods
 */

define('XML_FILE', __DIR__ . '/../data/library.xml');

/**
 * Load and return the SimpleXML object from library.xml
 */
function loadLibraryXML(): SimpleXMLElement {
    if (!file_exists(XML_FILE)) {
        die('<p class="error">ERROR: Library data file not found at ' . XML_FILE . '</p>');
    }
    $xml = simplexml_load_file(XML_FILE);
    if ($xml === false) {
        die('<p class="error">ERROR: Could not parse library XML file.</p>');
    }
    return $xml;
}

/**
 * Get all categories as an associative array: id => ['name', 'color']
 */
function getCategories(SimpleXMLElement $xml): array {
    $cats = [];
    foreach ($xml->categories->category as $cat) {
        $id = (string)$cat['id'];
        $cats[$id] = [
            'name'  => (string)$cat,
            'color' => (string)$cat['color']
        ];
    }
    return $cats;
}

/**
 * Get all classes as an associative array: id => ['name', 'level', 'age_range']
 */
function getClasses(SimpleXMLElement $xml): array {
    $classes = [];
    foreach ($xml->classes->class as $cls) {
        $id = (string)$cls['id'];
        $classes[$id] = [
            'name'      => (string)$cls['name'],
            'level'     => (string)$cls['level'],
            'age_range' => (string)$cls['age_range']
        ];
    }
    return $classes;
}

/**
 * Get all books as array of associative arrays
 */
function getAllBooks(SimpleXMLElement $xml): array {
    $books = [];
    foreach ($xml->books->book as $book) {
        $books[] = parseBook($book);
    }
    return $books;
}

/**
 * Parse a single book XML node into an array
 */
function parseBook(SimpleXMLElement $book): array {
    $classRefs = [];
    foreach ($book->classes->class_ref as $cr) {
        $classRefs[] = (string)$cr;
    }
    return [
        'id'            => (string)$book['id'],
        'title'         => (string)$book->title,
        'author'        => (string)$book->author,
        'isbn'          => (string)$book->isbn,
        'publisher'     => (string)$book->publisher,
        'year'          => (string)$book->year,
        'edition'       => (string)$book->edition,
        'copies_total'  => (int)$book->copies['total'],
        'copies_avail'  => (int)$book->copies['available'],
        'category_ref'  => (string)$book->category_ref,
        'classes'       => $classRefs,
        'description'   => (string)$book->description,
        'shelf'         => (string)$book->shelf_location,
        'status'        => (string)$book->status,
    ];
}

/**
 * Filter books by class ID
 */
function getBooksByClass(array $books, string $classId): array {
    return array_filter($books, function($b) use ($classId) {
        return in_array($classId, $b['classes']);
    });
}

/**
 * Filter books by category ID
 */
function getBooksByCategory(array $books, string $catId): array {
    return array_filter($books, function($b) use ($catId) {
        return $b['category_ref'] === $catId;
    });
}

/**
 * Search books by title or author keyword
 */
function searchBooks(array $books, string $keyword): array {
    $kw = strtolower(trim($keyword));
    if ($kw === '') return $books;
    return array_filter($books, function($b) use ($kw) {
        return str_contains(strtolower($b['title']), $kw)
            || str_contains(strtolower($b['author']), $kw)
            || str_contains(strtolower($b['description']), $kw);
    });
}

/**
 * Get all borrowing records
 */
function getBorrowingRecords(SimpleXMLElement $xml): array {
    $records = [];
    foreach ($xml->borrowing_records->record as $rec) {
        $records[] = [
            'id'             => (string)$rec['id'],
            'book_ref'       => (string)$rec->book_ref,
            'borrower_name'  => (string)$rec->borrower_name,
            'borrower_class' => (string)$rec->borrower_class,
            'borrow_date'    => (string)$rec->borrow_date,
            'due_date'       => (string)$rec->due_date,
            'return_date'    => (string)$rec->return_date,
            'status'         => (string)$rec->status,
        ];
    }
    return $records;
}

/**
 * Find a book by ID
 */
function findBookById(array $books, string $id): ?array {
    foreach ($books as $b) {
        if ($b['id'] === $id) return $b;
    }
    return null;
}

/**
 * Get library stats summary
 */
function getStats(array $books, array $records): array {
    $totalBooks   = count($books);
    $totalCopies  = array_sum(array_column($books, 'copies_total'));
    $availCopies  = array_sum(array_column($books, 'copies_avail'));
    $borrowed     = count(array_filter($records, fn($r) => $r['status'] === 'borrowed'));
    $overdue      = count(array_filter($records, fn($r) => $r['status'] === 'overdue'));
    return compact('totalBooks', 'totalCopies', 'availCopies', 'borrowed', 'overdue');
}

/**
 * Return a badge HTML for book status
 */
function statusBadge(string $status): string {
    $map = [
        'available'      => ['✅', 'badge-available',     'Available'],
        'borrowed'       => ['📤', 'badge-borrowed',      'Borrowed'],
        'reference_only' => ['📌', 'badge-reference',     'Reference Only'],
        'maintenance'    => ['🔧', 'badge-maintenance',   'Maintenance'],
    ];
    [$icon, $cls, $label] = $map[$status] ?? ['❓', 'badge-unknown', ucfirst($status)];
    return "<span class=\"badge $cls\">$icon $label</span>";
}

/**
 * Availability bar HTML
 */
function availabilityBar(int $total, int $avail): string {
    $pct = $total > 0 ? round(($avail / $total) * 100) : 0;
    $color = $pct >= 60 ? '#4CAF50' : ($pct >= 30 ? '#FF9800' : '#F44336');
    return "
    <div class='avail-bar-wrap' title='$avail of $total copies available'>
      <div class='avail-bar' style='width:{$pct}%;background:{$color}'></div>
      <span class='avail-label'>$avail / $total</span>
    </div>";
}
