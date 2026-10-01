<?php require_once __DIR__ . '/../includes/auth.php';
require_login();
require_post();
verify_csrf();
$uid = (int)current_user()['id'];
$book = (int)($_POST['book_id'] ?? 0);
$page = max(1, (int)($_POST['page_number'] ?? 1));
$note = trim($_POST['note'] ?? '');
$s = db()->prepare("SELECT id FROM books WHERE id=? AND status='published'");
$s->execute([$book]);
if (!$s->fetch()) {
    http_response_code(404);
    exit('Book not found');
}
$s = db()->prepare('SELECT id FROM bookmarks WHERE user_id=? AND book_id=? AND page_number=?');
$s->execute([$uid, $book, $page]);
$existing = $s->fetchColumn();
if ($existing) {
    $d = db()->prepare('DELETE FROM bookmarks WHERE id=?');
    $d->execute([$existing]);
    $message = 'Bookmark removed.';
    $active = false;
} else {
    $d = db()->prepare('INSERT INTO bookmarks(user_id,book_id,page_number,note) VALUES(?,?,?,?)');
    $d->execute([$uid, $book, $page, $note]);
    $message = 'Bookmark saved.';
    $active = true;
}
if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
    json_response(['ok' => true, 'active' => $active, 'message' => $message]);
}
flash('success', $message);
redirect('user/reader.php?book=' . $book . '&page=' . $page);
