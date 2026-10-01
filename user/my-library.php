<?php $pageTitle = 'My Library';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = (int)current_user()['id'];
$s = db()->prepare("SELECT b.*,f.created_at saved_at FROM favorites f JOIN books b ON b.id=f.book_id WHERE f.user_id=? AND b.status='published' ORDER BY f.created_at DESC");
$s->execute([$uid]);
$books = $s->fetchAll(); ?>
<section class="section">
    <h1>My Library</h1>
    <div class="book-grid">
        <?php foreach ($books as $b): ?>
            <article class="book-card">
                <div class="cover">📖</div>
                <div class="book-body">
                    <h3><?= e($b['title']) ?></h3><a class="btn btn-sm"
                        href="<?= url('book-details.php?id=' . $b['id']) ?>">Open</a>
                </div>
            </article><?php endforeach; ?><?php if (!$books): ?><div class="empty">Your favourites will appear here.</div>
        <?php endif; ?>
    </div>
</section><?php require __DIR__ . '/../includes/footer.php'; ?>