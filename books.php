<?php $pageTitle = 'Books';
require __DIR__ . '/includes/header.php';
$term = trim($_GET['q'] ?? '');
$cat = (int)($_GET['category'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';
$order = match ($sort) {
    'title' => 'b.title ASC',
    'oldest' => 'b.created_at ASC',
    default => 'b.created_at DESC'
};
$where = ["b.status='published'"];
$p = [];
if ($term !== '') {
    $where[] = '(b.title LIKE ? OR b.description LIKE ? OR EXISTS(SELECT 1 FROM book_authors ba JOIN authors a ON a.id=ba.author_id WHERE ba.book_id=b.id AND a.name LIKE ?))';
    $like = "%$term%";
    $p = [$like, $like, $like];
}
if ($cat) {
    $where[] = 'EXISTS(SELECT 1 FROM book_categories bc WHERE bc.book_id=b.id AND bc.category_id=?)';
    $p[] = $cat;
}
$count = db()->prepare('SELECT COUNT(*) FROM books b WHERE ' . implode(' AND ', $where));
$count->execute($p);
[$page, $pages, $offset] = paginate((int)($_GET['page'] ?? 1), 12, (int)$count->fetchColumn());
$s = db()->prepare("SELECT b.*,
    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') category FROM books b LEFT JOIN book_categories bc ON bc.book_id=b.id LEFT JOIN categories c ON c.id=bc.category_id
     WHERE " . implode(' AND ', $where) . " GROUP BY b.id ORDER BY $order LIMIT 12 OFFSET $offset");
$s->execute($p);
$books = $s->fetchAll();
$cats = db()->query('SELECT id,name,parent_id FROM categories ORDER BY name')->fetchAll();
?>
<div class="section-head">
    <div>
        <h1>Book Catalogue</h1>
        <p class="muted">Search and filter the library.</p>
    </div>
</div>
<form class="search-bar" method="get">
    <input name="q" value="<?= e($term) ?>" placeholder="Search title, description or author">
    <button class="btn">Search</button>
</form>
<form id="filterform" method="get" class="form-grid">
    <input type="hidden" name="q" value="<?= e($term) ?>">
    <div class="field">
        <label for="category-search">Category search</label>
        <input list="categories" name="category">
        <datalist id="category">
            <option value="0">All categories</option>
            <?php foreach ($cats as $c): ?>

                <option value="<?= $c['id'] ?>" data-category-name="<?= e(strtolower($c['name'])) ?>"
                    <?= $cat === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?></option>
            <?php endforeach; ?>
        </datalist>
    </div>
    <div class="field">
        <label>Sort</label>
        <select name="sort">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest</option>
            <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title</option>
        </select>
    </div>
    <div><button class="btn btn-secondary">Apply filters</button></div>
</form>
<section class="section">
    <div class="book-grid">
        <?php foreach ($books as $b): ?>
            <article class="book-card">
                <div class="cover"><?php if ($b['cover_image']): ?><img src="<?= url($b['cover_image']) ?>"
                            alt="<?= e($b['title']) ?> cover"><?php else: ?>📖<?php endif; ?></div>
                <div class="book-body">
                    <h3><?= e($b['title']) ?></h3>
                    <div class="muted"><?= e($b['category']) ?></div><?php if ($b['language']): ?><span
                            class="chip"><?= e($b['language']) ?></span><?php endif; ?><a class="btn btn-sm"
                        href="<?= url('book-details.php?id=' . $b['id']) ?>">View details</a>
                </div>
            </article><?php endforeach; ?>
    </div><?php if (!$books): ?><div class="empty">No books matched your search.</div>
    <?php endif; ?><div class="pagination"><?php for ($i = 1; $i <= $pages; $i++): ?><a
                class="<?= $i === $page ? 'current' : '' ?>"
                href="?<?= http_build_query(['q' => $term, 'category' => $cat, 'sort' => $sort, 'page' => $i]) ?>"><?= $i ?></a><?php endfor; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>