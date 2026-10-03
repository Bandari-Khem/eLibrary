<?php
require __DIR__ . '/../../includes/auth.php';
require_role('librarian');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$bookQuery = db()->prepare('SELECT * FROM books WHERE id=?');
$bookQuery->execute([$id]);
$book = $bookQuery->fetch();
if (!$book) redirect('404.php');

$pageTitle = 'Edit Book';
$categories = db()->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$authors = db()->query('SELECT id,name FROM authors ORDER BY name')->fetchAll();
$categoryQuery = db()->prepare('SELECT category_id FROM book_categories WHERE book_id=?');
$categoryQuery->execute([$id]);
$selectedCategories = array_map('intval', array_column($categoryQuery->fetchAll(), 'category_id'));
$authorQuery = db()->prepare('SELECT author_id FROM book_authors WHERE book_id=?');
$authorQuery->execute([$id]);
$selectedAuthors = array_map('intval', array_column($authorQuery->fetchAll(), 'author_id'));
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $categoryIds = array_values(array_unique(array_filter(array_map('intval', $_POST['categories'] ?? []))));
    $authorIds = array_values(array_unique(array_filter(array_map('intval', $_POST['authors'] ?? []))));
    $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : '';
    $publisher = trim((string)($_POST['publisher'] ?? ''));
    $yearInput = trim((string)($_POST['publication_year'] ?? ''));
    $year = $yearInput !== '' ? (int)$yearInput : null;
    $isbn = trim((string)($_POST['isbn'] ?? ''));
    $language = trim((string)($_POST['language'] ?? 'English'));
    $cover = $_FILES['cover_image'] ?? null;
    $coverSelected = ($_POST['cover_selected'] ?? '0') === '1';

    if ($title === '' || strlen($title) > 255 || !$categoryIds || $status === '') $errors[] = 'Title, at least one category and a valid status are required.';
    if ($year !== null && ($year < 1000 || $year > (int)date('Y') + 1)) $errors[] = 'Invalid publication year.';
    if ($coverSelected && (!$cover || ($cover['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)) {
        $errors[] = 'The cover was selected but did not reach the server. Please choose it again and retry.';
    }

    if (!$errors) {
        $pdo = db();
        $newCoverPath = null;
        $pdo->beginTransaction();
        try {
            $newCoverPath = save_cover_upload($cover);
            $coverPath = $newCoverPath ?: $book['cover_image'];
            $update = $pdo->prepare('UPDATE books SET title=?,slug=?,description=?,publisher=?,publication_year=?,isbn=?,language=?,status=?,cover_image=? WHERE id=?');
            $update->execute([$title, unique_slug($title, $id), $description, $publisher, $year, $isbn, $language, $status, $coverPath, $id]);

            $pdo->prepare('DELETE FROM book_categories WHERE book_id=?')->execute([$id]);
            $categoryInsert = $pdo->prepare('INSERT INTO book_categories(book_id,category_id) VALUES(?,?)');
            foreach ($categoryIds as $categoryId) $categoryInsert->execute([$id, $categoryId]);

            $pdo->prepare('DELETE FROM book_authors WHERE book_id=?')->execute([$id]);
            $authorInsert = $pdo->prepare('INSERT IGNORE INTO book_authors(book_id,author_id) VALUES(?,?)');
            foreach ($authorIds as $authorId) $authorInsert->execute([$id, $authorId]);

            $pdo->commit();
            if ($newCoverPath && $book['cover_image']) delete_stored_file($book['cover_image']);
            log_activity((int)current_user()['id'], 'update_book', 'book', $id, $newCoverPath ? 'cover_replaced' : 'metadata_updated');
            flash('success', 'Book updated.');
            redirect('librarian/books/index.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($newCoverPath) delete_stored_file($newCoverPath);
            $errors[] = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'The book could not be updated. Check the database and upload directory.';
        }
    }
}

$pageTitle = 'Edit Book';
require __DIR__ . '/../../includes/librarian-layout.php';
?>
<section class="section">
    <div class="form-card">
        <h1>Edit Book</h1>
        <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="cover_selected" value="0">
            <div class="field full"><label for="title">Title</label><input id="title" name="title" required
                    maxlength="255" value="<?= e($book['title']) ?>"></div>
            <div class="field full"><label for="description">Description</label><textarea id="description"
                    name="description"><?= e($book['description']) ?></textarea></div>
            <div class="field"><label for="categories">Categories</label><select id="categories" name="categories[]"
                    required multiple size="6"><?php foreach ($categories as $category): ?><option
                            value="<?= (int)$category['id'] ?>"
                            <?= in_array((int)$category['id'], $selectedCategories, true) ? 'selected' : '' ?>>
                            <?= e($category['name']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="authors">Authors</label><select id="authors" name="authors[]" multiple
                    size="5"><?php foreach ($authors as $author): ?><option value="<?= (int)$author['id'] ?>"
                            <?= in_array((int)$author['id'], $selectedAuthors, true) ? 'selected' : '' ?>>
                            <?= e($author['name']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label for="publisher">Publisher</label><input id="publisher" name="publisher"
                    value="<?= e($book['publisher']) ?>"></div>
            <div class="field"><label for="publication-year">Publication year</label><input id="publication-year"
                    type="number" name="publication_year" min="1000" max="<?= (int)date('Y') + 1 ?>"
                    value="<?= e((string)$book['publication_year']) ?>"></div>
            <div class="field"><label for="isbn">ISBN</label><input id="isbn" name="isbn" maxlength="30"
                    value="<?= e($book['isbn']) ?>"></div>
            <div class="field"><label for="language">Language</label><input id="language" name="language" maxlength="50"
                    value="<?= e($book['language']) ?>"></div>
            <div class="field"><label for="status">Status</label><select id="status" name="status">
                    <option value="draft" <?= $book['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $book['status'] === 'published' ? 'selected' : '' ?>>Published
                    </option>
                    <option value="archived" <?= $book['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select></div>
            <div class="field"><label for="cover-image">Replace cover</label><input id="cover-image" type="file"
                    name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small
                    class="muted">Optional · JPG, PNG or WebP · Max 5 MB</small></div>
            <div><button class="btn">Save changes</button></div>
        </form>
    </div>
</section>
<?php require __DIR__ . '/../../includes/panel-footer.php'; ?>