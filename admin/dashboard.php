<?php $pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/admin-layout.php';
$pdo = db();
$stats = [];
foreach (
    [
        'users' => 'SELECT COUNT(*) FROM users',
        'books' => "SELECT COUNT(*) FROM books WHERE status='published'",
        'pending_reviews' => "SELECT COUNT(*) FROM reviews WHERE status='pending'",
        'messages' => "SELECT COUNT(*) FROM contact_messages WHERE status='unread'"
    ] as $k => $sql
) $stats[$k] = (int)$pdo->query($sql)->fetchColumn(); ?>
<section class="section">
    <h1>System Overview</h1>
    <div class="stats">
        <div class="stat">Users<strong><?= $stats['users'] ?></strong></div>
        <div class="stat">Published books<strong><?= $stats['books'] ?></strong></div>
        <div class="stat">Pending reviews<strong><?= $stats['pending_reviews'] ?></strong></div>
        <div class="stat">Unread messages<strong><?= $stats['messages'] ?></strong></div>
    </div>
</section>
<section class="section grid grid-3"><a class="card" href="<?= url('admin/users/index.php') ?>">
        <h3>Manage users</h3>
        <p>Search accounts and change roles.</p>
    </a><a class="card" href="<?= url('admin/books/index.php') ?>">
        <h3>Manage books</h3>
        <p>Create, edit, archive and publish books.</p>
    </a><a class="card" href="<?= url('admin/settings/index.php') ?>">
        <h3>Settings</h3>
        <p>Configure upload rules and maintenance mode.</p>
    </a></section><?php require __DIR__ . '/../includes/panel-footer.php'; ?>