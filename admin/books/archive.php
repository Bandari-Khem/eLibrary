<?php require_once __DIR__ . '/../../includes/auth.php';
require_admin();
require_post();
verify_csrf();
$id = (int)($_POST['id'] ?? 0);
$s = db()->prepare("UPDATE books SET status='archived' WHERE id=? AND status<>'archived'");
$s->execute([$id]);
if ($s->rowCount()) {
    log_activity((int)current_user()['id'], 'archive_book', 'book', $id);
    flash('success', 'Book archived. Files were retained for audit/recovery.');
} else flash('error', 'Only active books can be archived.');
redirect('admin/books/index.php');
