<?php $pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/admin-layout.php';
$pdo = db();
$stats = [];
foreach (
    [
        'users' => 'SELECT COUNT(*) FROM users',
        'books' => "SELECT COUNT(*) FROM books WHERE status='published'",
    ] as $k => $sql
) $stats[$k] = (int)$pdo->query($sql)->fetchColumn(); ?>
<section class="section">
    <h1>System Overview</h1>
    <div class="stats">
        <div class="stat">Users<strong><?= $stats['users'] ?></strong></div>
        <div class="stat">Published books<strong><?= $stats['books'] ?></strong></div>
    </div>
</section>
<section class="section grid grid-3"><a class="card" href="<?= url('admin/users/index.php') ?>">
        <h3>Manage users</h3>
        <p>Search accounts and change roles.</p>
    </a><a class="card" href="<?= url('admin/activity-logs/index.php') ?>">
        <h3>Activity monitoring</h3>
        <p>Review recent account and catalogue actions.</p>
    </a></section><?php require __DIR__ . '/../includes/panel-footer.php'; ?>
