<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_post();
verify_csrf();

$bookId = (int)($_POST['book_id'] ?? 0);
if ((current_user()['role'] ?? '') !== 'user') {
    flash('error', 'Only learners can add books to favourites.');
    redirect($bookId > 0 ? 'book-details.php?id=' . $bookId : 'books.php');
}

$bookQuery = db()->prepare("SELECT id FROM books WHERE id=? AND status='published'");
$bookQuery->execute([$bookId]);
if (!$bookQuery->fetch()) redirect('404.php');

$favoriteQuery = db()->prepare('SELECT id FROM favorites WHERE user_id=? AND book_id=?');
$favoriteQuery->execute([(int)current_user()['id'], $bookId]);
if ($favoriteQuery->fetch()) {
    $delete = db()->prepare('DELETE FROM favorites WHERE user_id=? AND book_id=?');
    $delete->execute([(int)current_user()['id'], $bookId]);
    flash('success', 'Removed from favourites.');
} else {
    $insert = db()->prepare('INSERT INTO favorites(user_id,book_id) VALUES(?,?)');
    $insert->execute([(int)current_user()['id'], $bookId]);
    flash('success', 'Added to favourites.');
}

redirect('book-details.php?id=' . $bookId);
