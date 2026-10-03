<?php
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';

$categories = db()->query("SELECT c.id,c.name,COUNT(DISTINCT b.id) AS book_count
    FROM categories c
    JOIN book_categories bc ON bc.category_id=c.id
    JOIN books b ON b.id=bc.book_id AND b.status='published'
    GROUP BY c.id,c.name
    ORDER BY c.name
    LIMIT 8")->fetchAll();

$trending = db()->query("SELECT b.*,
    (SELECT GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ')
       FROM book_categories bc JOIN categories c ON c.id=bc.category_id WHERE bc.book_id=b.id) AS category,
    (SELECT GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ')
       FROM book_authors ba JOIN authors a ON a.id=ba.author_id WHERE ba.book_id=b.id) AS authors,
    (SELECT COUNT(DISTINCT h.user_id) FROM reading_history h WHERE h.book_id=b.id AND h.action='read') AS read_count
    FROM books b
    WHERE b.status='published'
      AND EXISTS (SELECT 1 FROM reading_history h WHERE h.book_id=b.id AND h.action='read')
    ORDER BY read_count DESC,b.created_at DESC
    LIMIT 4")->fetchAll();

$loved = db()->query("SELECT b.*,
    (SELECT GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ')
       FROM book_categories bc JOIN categories c ON c.id=bc.category_id WHERE bc.book_id=b.id) AS category,
    (SELECT GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ')
       FROM book_authors ba JOIN authors a ON a.id=ba.author_id WHERE ba.book_id=b.id) AS authors,
    (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.book_id=b.id AND r.status<>'hidden') AS average_rating,
    (SELECT COUNT(*) FROM reviews r WHERE r.book_id=b.id AND r.status<>'hidden') AS reviewer_count
    FROM books b
    WHERE b.status='published'
      AND EXISTS (SELECT 1 FROM reviews r WHERE r.book_id=b.id AND r.status<>'hidden')
    ORDER BY average_rating DESC,reviewer_count DESC,b.created_at DESC
    LIMIT 4")->fetchAll();

$reviews = db()->query("SELECT r.rating,r.review_text,r.created_at,u.full_name,b.title,b.id AS book_id
    FROM reviews r
    JOIN users u ON u.id=r.user_id
    JOIN books b ON b.id=r.book_id
    WHERE r.status<>'hidden' AND b.status='published'
    ORDER BY r.created_at DESC
    LIMIT 3")->fetchAll();

function render_home_books(array $books, string $metric = ''): void
{
    if (!$books) {
        echo '<div class="empty">No books to show yet.</div>';
        return;
    }
    foreach ($books as $book): ?>
        <article class="book-card">
            <div class="cover">
                <?php if (!empty($book['cover_image'])): ?>
                    <img src="<?= url($book['cover_image']) ?>" alt="<?= e($book['title']) ?> cover">
                <?php else: ?>📖<?php endif; ?>
            </div>
            <div class="book-body">
                <h3><?= e($book['title']) ?></h3>
                <div class="muted"><?= e($book['authors'] ?: 'Unknown author') ?></div>
                <div class="muted"><?= e($book['category'] ?: 'Uncategorized') ?><?= $book['publication_year'] ? ' · ' . e((string)$book['publication_year']) : '' ?></div>
                <?php if ($metric === 'reads'): ?>
                    <div class="muted"><?= (int)$book['read_count'] ?> reader<?= (int)$book['read_count'] === 1 ? '' : 's' ?></div>
                <?php elseif ($metric === 'reviews'): ?>
                    <div class="stars" aria-label="Average rating <?= e((string)$book['average_rating']) ?> out of 5">
                        ★ <?= e((string)$book['average_rating']) ?> <span class="muted">· <?= (int)$book['reviewer_count'] ?> review<?= (int)$book['reviewer_count'] === 1 ? '' : 's' ?></span>
                    </div>
                <?php endif; ?>
                <a class="btn btn-sm" href="<?= url('book-details.php?id=' . (int)$book['id']) ?>">Book details</a>
            </div>
        </article>
    <?php endforeach;
}
?>
<section class="hero">
    <div class="hero-grid">
        <div>
            <span class="chip">Digital Library</span>
            <h1>Find your next useful read.</h1>
            <p>Explore books by subject, read PDFs online, save favourites and pick up where you left off.</p>
            <div class="actions">
                <a class="btn" href="<?= url('books.php') ?>">Browse books</a>
                <?php if (!current_user()): ?><a class="btn btn-secondary" href="<?= url('register.php') ?>">Create account</a><?php endif; ?>
            </div>
        </div>
        <div class="hero-card">
            <h2>A library for curious minds</h2>
            <p>📚 Browse by subject</p>
            <p>🔎 Find books by title or author</p>
            <p>📖 Read PDF books online</p>
            <p>♥ Save favourites and reading progress</p>
        </div>
    </div>
</section>

<section class="section" id="categories">
    <div class="section-head"><h2>Browse by category</h2><a href="<?= url('books.php') ?>">All categories →</a></div>
    <?php if ($categories): ?>
        <div class="grid grid-4">
            <?php foreach ($categories as $category): ?>
                <a class="card category-card" href="<?= url('books.php?category=' . (int)$category['id']) ?>">
                    <h3><?= e($category['name']) ?></h3>
                    <p class="muted"><?= (int)$category['book_count'] ?> book<?= (int)$category['book_count'] === 1 ? '' : 's' ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?><div class="empty">Categories will appear here as the catalogue is added.</div><?php endif; ?>
</section>

<section class="section">
    <div class="section-head"><h2>Trending Reads</h2><a href="<?= url('books.php') ?>">Explore books →</a></div>
    <div class="book-grid discovery-grid"><?php render_home_books($trending, 'reads'); ?></div>
</section>

<section class="section">
    <div class="section-head"><h2>Loved by Learners</h2><a href="<?= url('books.php') ?>">Explore books →</a></div>
    <div class="book-grid discovery-grid"><?php render_home_books($loved, 'reviews'); ?></div>
</section>

<section class="section">
    <div class="section-head"><h2>What Readers Say</h2></div>
    <?php if ($reviews): ?>
        <div class="grid grid-3">
            <?php foreach ($reviews as $review): ?>
                <article class="card review-card">
                    <div class="stars" aria-label="<?= (int)$review['rating'] ?> out of 5 stars"><?= str_repeat('★', (int)$review['rating']) ?><?= str_repeat('☆', 5 - (int)$review['rating']) ?></div>
                    <p>“<?= nl2br(e($review['review_text'])) ?>”</p>
                    <div class="muted">— <?= e($review['full_name']) ?> on <a href="<?= url('book-details.php?id=' . (int)$review['book_id']) ?>"><?= e($review['title']) ?></a></div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?><div class="empty">Reader reviews will appear here.</div><?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
