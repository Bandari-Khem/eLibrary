<?php require_once __DIR__ . '/../../includes/auth.php';
require_admin();
require_post();
verify_csrf();
$id = (int)($_POST['id'] ?? 0);
$s = db()->prepare("UPDATE books SET status='draft' WHERE id=? AND status='archived'");
$s->execute([$id]);
if ($s->rowCount()) {
    log_activity((int)current_user()['id'], 'restore_book', 'book', $id);
    flash('success', 'Book restored as draft. Review and publish it when ready.');
} else flash('error', 'Only archived books can be restored.');
redirect('admin/books/index.php');
