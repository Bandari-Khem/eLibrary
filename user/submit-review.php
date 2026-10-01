<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('user');
require_post();
verify_csrf();

$bookId = (int)($_POST['book_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$reviewText = trim((string)($_POST['review_text'] ?? ''));
$textLength = function_exists('mb_strlen') ? mb_strlen($reviewText) : strlen($reviewText);

if ($rating < 1 || $rating > 5 || $textLength < 1 || $textLength > 1000) {
    flash('error', 'Choose a rating and enter a review message of up to 1,000 characters.');
    redirect('book-details.php?id=' . $bookId);
}

$bookQuery = db()->prepare("SELECT id FROM books WHERE id=? AND status='published'");
$bookQuery->execute([$bookId]);
if (!$bookQuery->fetchColumn()) {
    redirect('404.php');
}

$save = db()->prepare("INSERT INTO reviews(user_id,book_id,rating,review_text,status)
    VALUES(?,?,?,?,'approved')
    ON DUPLICATE KEY UPDATE rating=VALUES(rating),review_text=VALUES(review_text),status='approved',updated_at=CURRENT_TIMESTAMP");
$save->execute([(int)current_user()['id'], $bookId, $rating, $reviewText]);
log_activity((int)current_user()['id'], 'submit_review', 'book', $bookId);
flash('success', 'Your review has been saved.');
redirect('book-details.php?id=' . $bookId);
