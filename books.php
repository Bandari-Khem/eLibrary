<?php
$pageTitle = 'Books';
require __DIR__ . '/includes/header.php';

$term = trim($_GET['q'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);
$categoryName = trim($_GET['category_name'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$order = match ($sort) {
    'title' => 'b.title ASC',
    'oldest' => 'b.created_at ASC',
    default => 'b.created_at DESC'
};
$categories = db()->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$selectedCategoryName = '';
$invalidCategory = false;

if ($categoryName !== '') {
    $findCategory = db()->prepare('SELECT id,name FROM categories WHERE LOWER(name)=LOWER(?) LIMIT 1');
    $findCategory->execute([$categoryName]);
    $selectedCategory = $findCategory->fetch();
    if ($selectedCategory) {
        $categoryId = (int)$selectedCategory['id'];
        $selectedCategoryName = $selectedCategory['name'];
    } else {
        $invalidCategory = true;
    }
} elseif ($categoryId > 0) {
    $findCategory = db()->prepare('SELECT name FROM categories WHERE id=?');
    $findCategory->execute([$categoryId]);
    $selectedCategoryName = (string)($findCategory->fetchColumn() ?: '');
}

$where = ["b.status='published'"];
$params = [];
if ($term !== '') {
    $where[] = '(b.title LIKE ? OR b.description LIKE ? OR EXISTS(
        SELECT 1 FROM book_authors ba JOIN authors a ON a.id=ba.author_id
        WHERE ba.book_id=b.id AND a.name LIKE ?))';
    $like = '%' . $term . '%';
    array_push($params, $like, $like, $like);
}
if ($categoryId > 0) {
    $where[] = 'EXISTS(SELECT 1 FROM book_categories bc WHERE bc.book_id=b.id AND bc.category_id=?)';
    $params[] = $categoryId;
}
if ($invalidCategory) $where[] = '1=0';

$whereSql = implode(' AND ', $where);
$count = db()->prepare('SELECT COUNT(*) FROM books b WHERE ' . $whereSql);
$count->execute($params);
[$page, $pages, $offset] = paginate((int)($_GET['page'] ?? 1), 12, (int)$count->fetchColumn());

$query = db()->prepare("SELECT b.*,
    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS category,
    GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ') AS authors,
    (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.book_id=b.id AND r.status<>'hidden') AS average_rating,
    (SELECT COUNT(*) FROM reviews r WHERE r.book_id=b.id AND r.status<>'hidden') AS reviewer_count
    FROM books b
    LEFT JOIN book_categories bc ON bc.book_id=b.id
    LEFT JOIN categories c ON c.id=bc.category_id
    LEFT JOIN book_authors ba ON ba.book_id=b.id
    LEFT JOIN authors a ON a.id=ba.author_id
    WHERE $whereSql
    GROUP BY b.id
    ORDER BY $order
    LIMIT 12 OFFSET $offset");
$query->execute($params);
$books = $query->fetchAll();
?>
<div class="section-head">
    <div>
        <h1><?= $selectedCategoryName ? e($selectedCategoryName) : 'Book Catalogue' ?></h1>
        <p class="muted"><?= $selectedCategoryName ? 'Books in this category.' : 'Search by title or author, or filter by category.' ?></p>
    </div>
</div>
<form class="search-bar" method="get">
    <?php if ($categoryId): ?><input type="hidden" name="category" value="<?= $categoryId ?>"><?php endif; ?>
    <input name="q" value="<?= e($term) ?>" placeholder="Search title, description or author">
    <button class="btn">Search</button>
</form>
<form id="filterform" method="get" class="form-grid">
    <input type="hidden" name="q" value="<?= e($term) ?>">
    <div class="field">
        <label for="category-search">Category</label>
        <input id="category-search" list="category-options" name="category_name" value="<?= e($selectedCategoryName) ?>" placeholder="Type a category name">
        <datalist id="category-options">
            <?php foreach ($categories as $category): ?><option value="<?= e($category['name']) ?>"><?php endforeach; ?>
        </datalist>
    </div>
    <div class="field">
        <label for="sort">Sort</label>
        <select id="sort" name="sort">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest</option>
            <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title</option>
        </select>
    </div>
    <div><button class="btn btn-secondary">Apply filters</button></div>
</form>
<section class="section">
    <?php if ($books): ?>
        <div class="book-grid">
            <?php foreach ($books as $book): ?>
                <article class="book-card">
                    <div class="cover"><?php if ($book['cover_image']): ?><img src="<?= url($book['cover_image']) ?>" alt="<?= e($book['title']) ?> cover"><?php else: ?>📖<?php endif; ?></div>
                    <div class="book-body">
                        <h3><?= e($book['title']) ?></h3>
                        <div class="muted">By <?= e($book['authors'] ?: 'Unknown author') ?></div>
                        <div class="muted"><?= e($book['category'] ?: 'Uncategorized') ?><?= $book['publication_year'] ? ' · ' . e((string)$book['publication_year']) : '' ?></div>
                        <div class="stars">★ <?= $book['average_rating'] !== null ? e((string)$book['average_rating']) . ' / 5' : 'Not rated' ?> <span class="muted">· <?= (int)$book['reviewer_count'] ?> review<?= (int)$book['reviewer_count'] === 1 ? '' : 's' ?></span></div>
                        <a class="btn btn-sm" href="<?= url('book-details.php?id=' . (int)$book['id']) ?>">Book details</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?><div class="empty">No books matched your search.</div><?php endif; ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a class="<?= $i === $page ? 'current' : '' ?>" href="?<?= http_build_query(['q' => $term, 'category' => $categoryId, 'category_name' => $categoryName, 'sort' => $sort, 'page' => $i]) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
