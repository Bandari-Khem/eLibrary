<?php
require_once __DIR__ . '/../../includes/auth.php';
require_role('librarian');
require_post();
verify_csrf();

$bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
if (!$bookId || $bookId < 1) {
    flash('error', 'Choose a valid book to delete.');
    redirect('librarian/books/index.php');
}

$pdo = db();
$bookQuery = $pdo->prepare('SELECT title,cover_image FROM books WHERE id=?');
$bookQuery->execute([$bookId]);
$book = $bookQuery->fetch();
if (!$book) {
    flash('error', 'That book no longer exists.');
    redirect('librarian/books/index.php');
}

$fileQuery = $pdo->prepare('SELECT file_path FROM book_files WHERE book_id=?');
$fileQuery->execute([$bookId]);
$storedFiles = array_column($fileQuery->fetchAll(), 'file_path');
if ($book['cover_image']) {
    $storedFiles[] = $book['cover_image'];
}

try {
    $pdo->beginTransaction();
    $delete = $pdo->prepare('DELETE FROM books WHERE id=?');
    $delete->execute([$bookId]);
    if ($delete->rowCount() !== 1) {
        throw new RuntimeException('The book was not deleted.');
    }
    $pdo->commit();
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash('error', 'Could not delete this book. Please try again.');
    redirect('librarian/books/index.php');
}

foreach (array_unique($storedFiles) as $storedFile) {
    delete_stored_file($storedFile);
}

log_activity((int)current_user()['id'], 'delete_book', 'book', (int)$bookId, $book['title']);
flash('success', 'Book and its associated files and reader records were deleted.');
redirect('librarian/books/index.php');
