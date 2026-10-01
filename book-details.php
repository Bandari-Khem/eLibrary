<?php
$id = (int)($_GET['id'] ?? 0);
require __DIR__ . '/includes/auth.php';
$s = db()->prepare("SELECT b.*,
    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS category,
    GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ') AS authors
    FROM books b
    LEFT JOIN book_categories bc ON bc.book_id=b.id
    LEFT JOIN categories c ON c.id=bc.category_id
    LEFT JOIN book_authors ba ON ba.book_id=b.id
    LEFT JOIN authors a ON a.id=ba.author_id
    WHERE b.id=? AND b.status='published'
    GROUP BY b.id");
$s->execute([$id]);
$book = $s->fetch();
if (!$book) {
    http_response_code(404);
    $pageTitle = 'Book not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty"><h1>Book not found</h1><a class="btn" href="' . url('books.php') . '">Back to books</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$files = db()->prepare('SELECT * FROM book_files WHERE book_id=? ORDER BY is_main DESC,id');
$files->execute([$id]);
$files = $files->fetchAll();
$rating = db()->prepare("SELECT ROUND(AVG(rating),1) AS average_rating,COUNT(*) AS reviewer_count
    FROM reviews WHERE book_id=? AND status<>'hidden'");
$rating->execute([$id]);
$rating = $rating->fetch();
$reviews = db()->prepare("SELECT r.rating,r.review_text,r.created_at,u.full_name
    FROM reviews r JOIN users u ON u.id=r.user_id
    WHERE r.book_id=? AND r.status<>'hidden'
    ORDER BY r.created_at DESC LIMIT 10");
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();
$myReview = null;
$isFavorite = false;
if (current_user()) {
    $myReviewQuery = db()->prepare('SELECT rating,review_text FROM reviews WHERE user_id=? AND book_id=?');
    $myReviewQuery->execute([(int)current_user()['id'], $id]);
    $myReview = $myReviewQuery->fetch() ?: null;
    $favoriteQuery = db()->prepare('SELECT id FROM favorites WHERE user_id=? AND book_id=?');
    $favoriteQuery->execute([(int)current_user()['id'], $id]);
    $isFavorite = (bool)$favoriteQuery->fetchColumn();
}
$pageTitle = $book['title'];
require __DIR__ . '/includes/header.php';
$loginUrl = url('login.php?return=' . rawurlencode('book-details.php?id=' . $id));
?>
<div class="detail-grid section">
    <div>
        <div class="cover-lg">
            <?php if ($book['cover_image']): ?><img src="<?= url($book['cover_image']) ?>" alt="<?= e($book['title']) ?> cover">
            <?php else: ?>📖<?php endif; ?>
        </div>
    </div>
    <div>
        <span class="chip"><?= e($book['category'] ?: 'Uncategorized') ?></span>
        <h1><?= e($book['title']) ?></h1>
        <p class="muted">By <?= e($book['authors'] ?: 'Unknown author') ?><?= $book['publication_year'] ? ' · ' . e((string)$book['publication_year']) : '' ?></p>
        <p><?= nl2br(e($book['description'] ?: 'No description available.')) ?></p>
        <p><strong>Average rating:</strong> <?= $rating['average_rating'] !== null ? e((string)$rating['average_rating']) . ' / 5' : 'Not rated yet' ?> · <?= (int)$rating['reviewer_count'] ?> review<?= (int)$rating['reviewer_count'] === 1 ? '' : 's' ?></p>
        <div class="actions">
            <?php foreach ($files as $file): ?>
                <?php if ($file['file_type'] === 'pdf'): ?>
                    <?php if (current_user()): ?>
                        <a class="btn" href="<?= url('user/reader.php?book=' . $id . '&file=' . (int)$file['id']) ?>">Read online</a>
                    <?php else: ?>
                        <a class="btn" href="<?= e($loginUrl) ?>">Log in to read</a>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (current_user()): ?>
                    <a class="btn btn-secondary" href="<?= url('user/download.php?file=' . (int)$file['id']) ?>">Download PDF</a>
                <?php else: ?>
                    <a class="btn btn-secondary" href="<?= e($loginUrl) ?>">Log in to download</a>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (current_user()): ?>
                <form method="post" action="<?= url('user/toggle-favorite.php') ?>">
                    <?= csrf_field() ?><input type="hidden" name="book_id" value="<?= $id ?>">
                    <button class="btn btn-secondary"><?= $isFavorite ? '♥ Remove from favourites' : '♡ Add to favourites' ?></button>
                </form>
            <?php else: ?>
                <a class="btn btn-secondary" href="<?= e($loginUrl) ?>">Log in to save favourite</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="section">
    <div class="section-head"><h2>Reader reviews</h2></div>
    <?php if (current_user() && current_user()['role'] === 'user'): ?>
        <div class="form-card">
            <h3><?= $myReview ? 'Update your review' : 'Leave a review' ?></h3>
            <form method="post" action="<?= url('user/submit-review.php') ?>" class="form-grid">
                <?= csrf_field() ?><input type="hidden" name="book_id" value="<?= $id ?>">
                <div class="field">
                    <label for="rating">Rating</label>
                    <select id="rating" name="rating" required>
                        <option value="">Choose a rating</option>
                        <?php for ($score = 5; $score >= 1; $score--): ?>
                            <option value="<?= $score ?>" <?= (int)($myReview['rating'] ?? 0) === $score ? 'selected' : '' ?>><?= $score ?> out of 5</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="field full">
                    <label for="review_text">Your message</label>
                    <textarea id="review_text" name="review_text" maxlength="1000" required><?= e($myReview['review_text'] ?? '') ?></textarea>
                </div>
                <div><button class="btn"><?= $myReview ? 'Update review' : 'Submit review' ?></button></div>
            </form>
        </div>
    <?php elseif (!current_user()): ?>
        <p><a class="btn btn-secondary" href="<?= e($loginUrl) ?>">Log in to leave a review</a></p>
    <?php endif; ?>
    <div class="grid grid-2" style="margin-top:18px">
        <?php foreach ($reviews as $review): ?>
            <article class="card review-card">
                <div class="stars"><?= str_repeat('★', (int)$review['rating']) ?><?= str_repeat('☆', 5 - (int)$review['rating']) ?></div>
                <p><?= nl2br(e($review['review_text'])) ?></p>
                <div class="muted">— <?= e($review['full_name']) ?></div>
            </article>
        <?php endforeach; ?>
        <?php if (!$reviews): ?><div class="empty">No reviews yet. Be the first to share your thoughts.</div><?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
