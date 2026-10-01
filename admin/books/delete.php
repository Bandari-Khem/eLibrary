<?php require_once __DIR__ . '/../../includes/auth.php';
require_admin();
require_post();
verify_csrf();
$id = (int)($_POST['id'] ?? 0);
$pdo = db();
$s = $pdo->prepare("SELECT id,title,status,cover_image FROM books WHERE id=?");
$s->execute([$id]);
$book = $s->fetch();
if (!$book || $book['status'] !== 'archived') {
    flash('error', 'Only archived books can be permanently deleted.');
    redirect('admin/books/index.php');
}
$s = $pdo->prepare('SELECT file_path FROM book_files WHERE book_id=?');
$s->execute([$id]);
$files = $s->fetchAll(PDO::FETCH_COLUMN);
$pdo->beginTransaction();
try {
    $pdo->prepare('DELETE FROM books WHERE id=? AND status=\'archived\'')->execute([$id]);
    $pdo->commit();
    foreach ($files as $path) delete_stored_file($path);
    delete_stored_file($book['cover_image']);
    log_activity((int)current_user()['id'], 'permanent_delete_book', 'book', $id, 'title=' . substr((string)$book['title'], 0, 180));
    flash('success', 'Archived book permanently deleted and associated files removed.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash('error', 'Permanent deletion failed.');
}
redirect('admin/books/index.php');
