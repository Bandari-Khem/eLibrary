<?php $id = (int)($_GET['id'] ?? 0);
include __DIR__ . '/config/database.php';
$s = db()->prepare("SELECT b.*,GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') category,GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ') authors FROM books b LEFT JOIN book_categories bc ON bc.book_id=b.id LEFT JOIN categories c ON c.id=bc.category_id LEFT JOIN book_authors ba ON ba.book_id=b.id LEFT JOIN authors a ON a.id=ba.author_id WHERE b.id=? AND b.status='published' GROUP BY b.id");
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
$rating = db()->prepare("SELECT COALESCE(AVG(rating),0) avg_rating,COUNT(*) total FROM reviews WHERE book_id=? AND status='approved'");
$rating->execute([$id]);
$rating = $rating->fetch();
$pageTitle = $book['title'];
require __DIR__ . '/includes/header.php'; ?>
<div class="detail-grid section">
    <div>
        <div class="cover-lg"><?php if ($book['cover_image']): ?><img src="<?= url($book['cover_image']) ?>"
                    alt="<?= e($book['title']) ?> cover"><?php else: ?>📖<?php endif; ?></div>
    </div>
    <div><span class="chip"><?= e($book['category']) ?></span>
        <h1><?= e($book['title']) ?></h1>
        <p class="muted">By <?= e($book['authors'] ?: 'Unknown author') ?></p>
        <p><?= nl2br(e($book['description'] ?: 'No description available.')) ?></p>
        <p><strong>Language:</strong> <?= e($book['language']) ?> · <strong>Year:</strong>
            <?= e((string)$book['publication_year']) ?></p>
        <p><strong>Rating:</strong> <?= number_format((float)$rating['avg_rating'], 1) ?> / 5 (<?= $rating['total'] ?>
            reviews)</p>
        <div class="actions"><?php foreach ($files as $f): ?><?php if ($f['file_type'] === 'pdf'): ?><a class="btn"
                href="<?= url('user/reader.php?book=' . $id . '&file=' . $f['id']) ?>">Read
                online</a><?php endif; ?><?php if (current_user()): ?><a class="btn btn-secondary"
                href="<?= url('user/download.php?file=' . $f['id']) ?>">Download</a><?php else: ?><a
                class="btn btn-secondary" href="<?= url('login.php') ?>">Login to
                download</a><?php endif; ?><?php endforeach; ?><?php if (current_user()): ?><?php $fs = db()->prepare('SELECT id FROM favorites WHERE user_id=? AND book_id=?');
                                                                                            $fs->execute([(int)current_user()['id'], $id]);
                                                                                            $isFavorite = (bool)$fs->fetchColumn(); ?>
            <form method="post" action="<?= url('user/toggle-favorite.php') ?>"><?= csrf_field() ?><input type="hidden"
                    name="book_id" value="<?= $id ?>"><button
                    class="btn btn-secondary"><?= $isFavorite ? '♥ Remove from favourite' : '♡ Add to favourite' ?></button>
            </form><?php endif; ?>
        </div>
    </div>
</div>
<?php if (current_user()): ?><section class="section form-card">
        <h2>Rate this book</h2>
        <form method="post" action="<?= url('user/submit-review.php') ?>"><?= csrf_field() ?><input type="hidden"
                name="book_id" value="<?= $id ?>">
            <div class="rating-options"><?php foreach (range(1, 5) as $r): ?><label><input type="radio" name="rating"
                            value="<?= $r ?>" required> <?= $r ?> ★</label><?php endforeach; ?></div>
            <div class="field" style="margin-top:12px"><label>Why did you choose this rating?</label><input
                    name="review_reason" maxlength="150" placeholder="Optional"></div>
            <div class="field"><label>Your review</label><textarea name="review_text" maxlength="2000"
                    placeholder="Tell other readers what you think"></textarea></div><button class="btn">Submit
                review</button>
        </form>
    </section><?php endif; ?><section class="section">
    <h2>Approved reviews</h2>
    <?php $rs = db()->prepare("SELECT r.*,u.full_name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.book_id=? AND r.status='approved' ORDER BY r.created_at DESC");
    $rs->execute([$id]);
    foreach ($rs as $r): ?>
        <article class="card" style="margin-top:12px"><strong><?= e($r['full_name']) ?></strong> <span
                class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></span>
            <p><?= nl2br(e($r['review_text'] ?? '')) ?></p>
        </article><?php endforeach; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>