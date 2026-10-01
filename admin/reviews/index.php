<?php $pageTitle = 'Review Moderation';
require __DIR__ . '/../../includes/admin-layout.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)$_POST['id'];
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['pending', 'approved', 'hidden'], true)) {
        $s = db()->prepare('UPDATE reviews SET status=? WHERE id=?');
        $s->execute([$status, $id]);
        log_activity((int)current_user()['id'], 'moderate_review', 'review', $id, 'status=' . $status);
        flash('success', 'Review status updated.');
    }
    redirect('admin/reviews/index.php');
}
$rows = db()->query('SELECT r.*,b.title,u.full_name FROM reviews r 
    JOIN books b ON b.id=r.book_id JOIN users u ON u.id=r.user_id ORDER BY r.created_at DESC LIMIT 300')->fetchAll(); ?>
<section class="section">
    <h1>Review Moderation</h1>
    <div class="grid grid-2"><?php foreach ($rows as $r): ?><article class="card">
                <div><strong><?= e($r['title']) ?></strong> · <?= e($r['full_name']) ?></div>
                <div class="stars"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
                <p><?= nl2br(e($r['review_text'])) ?></p>
                <form method="post" class="actions">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <select name="status">
                        <option value="pending" <?= $r['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $r['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="hidden" <?= $r['status'] === 'hidden' ? 'selected' : '' ?>>Hidden</option>
                    </select><button class="btn btn-sm">Save</button>
                </form>
            </article><?php endforeach; ?></div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>