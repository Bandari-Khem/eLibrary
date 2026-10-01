<?php $pageTitle = 'My Reviews';
require __DIR__ . '/../includes/header.php';
require_login();
$s = db()->prepare('SELECT r.*,b.title FROM reviews r JOIN books b ON b.id=r.book_id WHERE r.user_id=? ORDER BY r.created_at DESC');
$s->execute([(int)current_user()['id']]);
$rows = $s->fetchAll(); ?>
<section class="section">
    <h1>My Reviews</h1>
    <div class="grid grid-2"><?php foreach ($rows as $r): ?><article class="card">
                <h3><?= e($r['title']) ?></h3>
                <div class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                <span class="chip"><?= e($r['status']) ?></span>
                <p><?= nl2br(e($r['review_text'])) ?></p>
            </article><?php endforeach; ?></div>
</section><?php require __DIR__ . '/../includes/footer.php'; ?>