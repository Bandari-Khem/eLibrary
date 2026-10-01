<?php $pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = (int)current_user()['id'];
$us = db()->prepare('SELECT avatar FROM users WHERE id=?');
$us->execute([$uid]);
$avatar = (string)($us->fetchColumn() ?: '📚');
$stats = [];
foreach (['favorites' => 'SELECT COUNT(*) FROM favorites WHERE user_id=?', 'bookmarks' => 'SELECT COUNT(*) FROM bookmarks WHERE user_id=?', 'reviews' => 'SELECT COUNT(*) FROM reviews WHERE user_id=?', 'history' => 'SELECT COUNT(*) FROM reading_history WHERE user_id=?'] as $k => $sql) {
    $s = db()->prepare($sql);
    $s->execute([$uid]);
    $stats[$k] = (int)$s->fetchColumn();
}
$s = db()->prepare("SELECT b.id,b.title,rp.current_page,rp.total_pages,rp.last_read_at FROM reading_progress rp JOIN books b ON b.id=rp.book_id WHERE rp.user_id=? ORDER BY rp.last_read_at DESC LIMIT 6");
$s->execute([$uid]);
$reading = $s->fetchAll(); ?>
<section class="section">
    <div class="dashboard-greeting">
        <div class="profile-avatar small"><?= e($avatar) ?></div>
        <div>
            <h1>Hi, <?= e(current_user()['full_name']) ?> 👋</h1>
        </div>
    </div>
    <div class="stats">
        <div class="stat"><span>Favourites</span><strong><?= $stats['favorites'] ?></strong></div>
        <div class="stat"><span>Bookmarks</span><strong><?= $stats['bookmarks'] ?></strong></div>
        <div class="stat"><span>Reviews</span><strong><?= $stats['reviews'] ?></strong></div>
        <div class="stat"><span>History</span><strong><?= $stats['history'] ?></strong></div>
    </div>
</section>
<section class="section">
    <div class="section-head">
        <h2>Continue reading</h2><a href="<?= url('user/history.php') ?>">History</a>
    </div>
    <div class="grid grid-3">
        <?php foreach ($reading as $r): $pct = $r['total_pages'] ? min(100, round($r['current_page'] / $r['total_pages'] * 100)) : 0; ?>
            <div class="card">
                <h3><?= e($r['title']) ?></h3>
                <p class="muted">Page <?= $r['current_page'] ?><?= $r['total_pages'] ? ' of ' . $r['total_pages'] : '' ?>
                </p>
                <div style="height:8px;background:#e8edf4;border-radius:99px">
                    <div style="width:<?= $pct ?>%;height:100%;background:#2457d6;border-radius:99px"></div>
                </div><a class="btn btn-sm" style="margin-top:12px"
                    href="<?= url('user/reader.php?book=' . $r['id']) ?>">Continue</a>
            </div><?php endforeach; ?><?php if (!$reading): ?><div class="empty">You haven't started reading a book yet.
            </div>
        <?php endif; ?>
    </div>
</section><?php require __DIR__ . '/../includes/footer.php'; ?>