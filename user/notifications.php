<?php $pageTitle = 'Notifications';
require __DIR__ . '/../includes/header.php';
require_login();
$s = db()->prepare('SELECT * FROM notifications WHERE user_id=? OR user_id IS NULL ORDER BY created_at DESC LIMIT 50');
$s->execute([(int)current_user()['id']]);
$rows = $s->fetchAll(); ?>
<section class="section">
    <h1>Notifications</h1><?php foreach ($rows as $n): ?><article class="card" style="margin-bottom:10px">
            <strong><?= e($n['title']) ?></strong>
            <p><?= nl2br(e($n['message'])) ?></p><small class="muted"><?= e($n['created_at']) ?></small>
        </article><?php endforeach; ?>
</section><?php require __DIR__ . '/../includes/footer.php'; ?>