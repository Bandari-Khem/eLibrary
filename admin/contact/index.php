<?php $pageTitle = 'Contact Messages';
require __DIR__ . '/../../includes/admin-layout.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)$_POST['id'];
    $status = $_POST['status'] ?? 'read';
    if (in_array($status, ['unread', 'read', 'replied'], true)) {
        db()->prepare('UPDATE contact_messages SET status=? WHERE id=?')->execute([$status, $id]);
        flash('success', 'Message updated.');
    }
    redirect('admin/contact/index.php');
}
$rows = db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 300')->fetchAll(); ?>
<section class="section">
    <h1>Contact Messages</h1>
    <div class="grid grid-2"><?php foreach ($rows as $r): ?><article class="card">
            <h3><?= e($r['subject']) ?></h3>
            <p><strong><?= e($r['name']) ?></strong> · <?= e($r['email']) ?></p>
            <p><?= nl2br(e($r['message'])) ?></p>
            <form method="post" class="actions">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <select name="status">
                    <option <?= $r['status'] === 'unread' ? 'selected' : '' ?>>unread</option>
                    <option <?= $r['status'] === 'read' ? 'selected' : '' ?>>read</option>
                    <option <?= $r['status'] === 'replied' ? 'selected' : '' ?>>replied</option>
                </select><button class="btn btn-sm">Save</button>
            </form>
        </article><?php endforeach; ?></div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>