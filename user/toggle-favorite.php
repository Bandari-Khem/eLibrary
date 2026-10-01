<?php require_once __DIR__ . '/../includes/auth.php';
require_login();
require_post();
verify_csrf();
$id = (int)($_POST['book_id'] ?? 0);
$s = db()->prepare("SELECT id FROM books WHERE id=? AND status='published'");
$s->execute([$id]);
if (!$s->fetch()) {
    redirect('404.php');
}
$s = db()->prepare('SELECT id FROM favorites WHERE user_id=? AND book_id=?');
$s->execute([(int)current_user()['id'], $id]);
if ($s->fetch()) {
    $d = db()->prepare('DELETE FROM favorites WHERE user_id=? AND book_id=?');
    $d->execute([(int)current_user()['id'], $id]);
    flash('success', 'Removed from favourites.');
} else {
    $d = db()->prepare('INSERT INTO favorites(user_id,book_id) VALUES(?,?)');
    $d->execute([(int)current_user()['id'], $id]);
    flash('success', 'Added to favourites.');
}
redirect('book-details.php?id=' . $id);
