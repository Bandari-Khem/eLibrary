<?php
require_once __DIR__ . '/includes/functions.php';

$term = trim($_GET['q'] ?? '');
$categoryId = (int)($_GET['category'] ?? 0);
$categoryName = trim($_GET['category_name'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$order = match ($sort) {
    'title' => 'b.title ASC',
    'oldest' => 'b.created_at ASC',
    default => 'b.created_at DESC'
};

$categories = db()->query("SELECT c.id,c.name,COUNT(DISTINCT b.id) AS book_count
    FROM categories c
    LEFT JOIN book_categories bc ON bc.category_id=c.id
    LEFT JOIN books b ON b.id=bc.book_id AND b.status='published'
    GROUP BY c.id,c.name
    ORDER BY c.name")->fetchAll();

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

$isCategoryView = $categoryId > 0;
$pageTitle = $isCategoryView
    ? ($selectedCategoryName !== '' ? $selectedCategoryName . ' Books' : 'Category not found')
    : 'Browse Categories';
require __DIR__ . '/includes/header.php';
?>
<?php if (!$isCategoryView): ?>
    <section class="section">
        <div class="section-head">
            <div>
                <h1>Browse categories</h1>
                <p class="muted">Choose a subject to see the books listed under it.</p>
            </div>
        </div>

        <form class="category-search-form" method="get">
            <div class="field">
                <label for="category-name">Find a category</label>
                <input id="category-name" name="category_name" list="category-options" value="<?= e($categoryName) ?>" placeholder="Start typing a subject" autocomplete="off">
                <datalist id="category-options">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['name']) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            <button class="btn" type="submit">Open category</button>
        </form>

        <?php if ($invalidCategory): ?>
            <div class="empty">That category was not found. Choose one from the list below.</div>
        <?php endif; ?>

        <?php if ($categories): ?>
            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                    <a class="card category-card" href="<?= url('books.php?category=' . (int)$category['id']) ?>">
                        <h2><?= e($category['name']) ?></h2>
                        <p class="muted"><?= (int)$category['book_count'] ?> book<?= (int)$category['book_count'] === 1 ? '' : 's' ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty">Categories will appear here when the librarian adds them.</div>
        <?php endif; ?>
    </section>
<?php else: ?>
    <?php
    $where = ["b.status='published'", 'EXISTS(SELECT 1 FROM book_categories bc WHERE bc.book_id=b.id AND bc.category_id=?)'];
    $params = [$categoryId];
    if ($term !== '') {
        $where[] = '(b.title LIKE ? OR b.description LIKE ? OR EXISTS(
            SELECT 1 FROM book_authors ba JOIN authors a ON a.id=ba.author_id
            WHERE ba.book_id=b.id AND a.name LIKE ?))';
        $like = '%' . $term . '%';
        array_push($params, $like, $like, $like);
    }
    if ($invalidCategory || $selectedCategoryName === '') {
        $where[] = '1=0';
    }

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
    <section class="section">
        <div class="section-head">
            <div>
                <h1><?= e($selectedCategoryName ?: 'Category not found') ?></h1>
                <p class="muted">Books in this category.</p>
            </div>
            <a href="<?= url('books.php') ?>">All categories</a>
        </div>

        <form class="search-bar" method="get">
            <input type="hidden" name="category" value="<?= $categoryId ?>">
            <input name="q" value="<?= e($term) ?>" placeholder="Search this category by title or author" aria-label="Search this category by title or author">
            <button class="btn" type="submit">Search</button>
        </form>
        <form method="get" class="catalogue-sort">
            <input type="hidden" name="category" value="<?= $categoryId ?>">
            <input type="hidden" name="q" value="<?= e($term) ?>">
            <div class="field">
                <label for="sort">Sort books</label>
                <select id="sort" name="sort">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest</option>
                    <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title</option>
                </select>
            </div>
            <button class="btn btn-secondary" type="submit">Sort</button>
        </form>

        <?php if ($books): ?>
            <div class="book-grid">
                <?php foreach ($books as $book): ?>
                    <article class="book-card">
                        <div class="cover"><?php if ($book['cover_image']): ?><img src="<?= url($book['cover_image']) ?>" alt="<?= e($book['title']) ?> cover"><?php else: ?>📖<?php endif; ?></div>
                        <div class="book-body">
                            <h2><?= e($book['title']) ?></h2>
                            <div class="muted">By <?= e($book['authors'] ?: 'Unknown author') ?></div>
                            <div class="muted"><?= e($book['category'] ?: 'Uncategorized') ?><?= $book['publication_year'] ? ' · ' . e((string)$book['publication_year']) : '' ?></div>
                            <div class="stars">★ <?= $book['average_rating'] !== null ? e((string)$book['average_rating']) . ' / 5' : 'Not rated' ?> <span class="muted">· <?= (int)$book['reviewer_count'] ?> review<?= (int)$book['reviewer_count'] === 1 ? '' : 's' ?></span></div>
                            <a class="btn btn-sm" href="<?= url('book-details.php?id=' . (int)$book['id']) ?>">Book details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty">No books are listed in this category yet.</div>
        <?php endif; ?>

        <?php if ($pages > 1): ?>
            <nav class="pagination" aria-label="Book pages">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a class="<?= $i === $page ? 'current' : '' ?>" href="?<?= http_build_query(['category' => $categoryId, 'q' => $term, 'sort' => $sort, 'page' => $i]) ?>" <?= $i === $page ? 'aria-current="page"' : '' ?>><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
