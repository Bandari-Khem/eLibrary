<?php $pageTitle = 'Home';
require __DIR__ . '/includes/header.php'; ?>
<section class="hero">
    <div class="hero-grid">
        <div><span class="chip">Digital Library</span>
            <h1>One place for your next great read.</h1>
            <p>Browse academic and non-academic books, read online, download permitted files, track your progress and
                build your personal library.</p>
            <div class="actions"><a class="btn" href="<?= url('books.php') ?>">Browse
                    Books</a><?php if (!current_user()): ?><a class="btn btn-secondary"
                    href="<?= url('register.php') ?>">Create Account</a><?php endif; ?></div>
        </div>
        <div class="hero-card">
            <h2>Built for learners</h2>
            <p>📚 Organized catalogue</p>
            <p>🔎 Search & filters</p>
            <p>📖 Continue reading</p>
            <p>⭐ Reviews & ratings</p>
            <p>🔖 Bookmarks & favourites</p>
            <p>🛡️ Role-based administration</p>
        </div>
    </div>
</section>
<?php
$editors = db()->query("SELECT b.*,
           GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS category,
           COALESCE(AVG(CASE WHEN r.status='approved' THEN r.rating END),0) AS avg_rating,
           COUNT(DISTINCT CASE WHEN r.status='approved' THEN r.id END) AS review_count
    FROM books b
    LEFT JOIN book_categories bc ON bc.book_id=b.id
    LEFT JOIN categories c ON c.id=bc.category_id
    LEFT JOIN reviews r ON r.book_id=b.id
    WHERE b.status='published'
    GROUP BY b.id
    ORDER BY avg_rating DESC, review_count DESC, b.created_at DESC
    LIMIT 3
")->fetchAll();

$recent = db()->query(" SELECT b.*, GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS category
    FROM books b
    LEFT JOIN book_categories bc ON bc.book_id=b.id
    LEFT JOIN categories c ON c.id=bc.category_id
    WHERE b.status='published'
    GROUP BY b.id
    ORDER BY b.created_at DESC
    LIMIT 3
")->fetchAll();

$trending = db()->query(" SELECT b.*,
           GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS category,
           COUNT(DISTINCT CASE WHEN h.action='read'  AND h.read_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN h.id END) AS recent_reads,
           COUNT(DISTINCT CASE WHEN d.downloaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN d.id END) AS recent_downloads
    FROM books b
    LEFT JOIN book_categories bc ON bc.book_id=b.id
    LEFT JOIN categories c ON c.id=bc.category_id
    LEFT JOIN reading_history h ON h.book_id=b.id
    LEFT JOIN downloads d ON d.book_id=b.id
    WHERE b.status='published'
    GROUP BY b.id
    ORDER BY (recent_reads + recent_downloads) DESC, b.created_at DESC
    LIMIT 3
")->fetchAll();

function render_home_books(array $books): void
{
    if (!$books) {
        echo '<div class="empty">No books available yet.</div>';
        return;
    }
    foreach ($books as $b): ?>
<article class="book-card">
    <div class="cover"><?php if ($b['cover_image']): ?><img src="<?= url($b['cover_image']) ?>"
            alt="<?= e($b['title']) ?> cover"><?php else: ?>📖<?php endif; ?></div>
    <div class="book-body">
        <h3><?= e($b['title']) ?></h3>
        <div class="muted"><?= e($b['category'] ?? 'Uncategorized') ?></div>
        <a class="btn btn-sm" href="<?= url('book-details.php?id=' . $b['id']) ?>">Details</a>
    </div>
</article>
<?php endforeach;
}
?>
<section class="section">
    <div class="section-head">
        <h2>Editors' Favorites</h2><a href="<?= url('books.php') ?>">View all →</a>
    </div>
    <div class="book-grid"><?php render_home_books($editors); ?></div>
</section>
<section class="section">
    <div class="section-head">
        <h2>Recently Added</h2><a href="<?= url('books.php') ?>?sort=newest">View all →</a>
    </div>
    <div class="book-grid"><?php render_home_books($recent); ?></div>
</section>
<section class="section">
    <div class="section-head">
        <h2>Top Trending</h2><a href="<?= url('books.php') ?>">Explore books →</a>
    </div>
    <div class="book-grid"><?php render_home_books($trending); ?></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>