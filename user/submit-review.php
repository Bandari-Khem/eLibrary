<?php require_once __DIR__ . '/../includes/auth.php';
require_login();
require_post();
verify_csrf();
$uid = (int)current_user()['id'];
$book = (int)($_POST['book_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$reason = trim($_POST['review_reason'] ?? '');
$text = trim($_POST['review_text'] ?? '');
if ($rating < 1 || $rating > 5) {
    flash('error', 'Rating must be between 1 and 5.');
    redirect('book-details.php?id=' . $book);
}
if (strlen($reason) > 150 || strlen($text) > 2000) {
    flash('error', 'Review is too long.');
    redirect('book-details.php?id=' . $book);
}
$s = db()->prepare("SELECT id FROM books WHERE id=? AND status='published'");
$s->execute([$book]);
if (!$s->fetch()) {
    redirect('404.php');
}
$s = db()->prepare('INSERT INTO reviews(user_id,book_id,rating,review_reason,review_text,status)
     VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating),review_reason=VALUES(review_reason),review_text=VALUES(review_text),status=\'pending\',updated_at=CURRENT_TIMESTAMP');
$s->execute([$uid, $book, $rating, $reason, $text, 'pending']);
log_activity($uid, 'submit_review', 'book', $book);
flash('success', $rating === 1 ? 'We\'re sorry this book did not meet your expectations. Please tell us why so we can improve. Your review was submitted for moderation.' : 'Your review was submitted for moderation.');
redirect('book-details.php?id=' . $book);
