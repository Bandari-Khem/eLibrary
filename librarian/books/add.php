<?php
$pageTitle = 'Add Book';
require __DIR__ . '/../../includes/librarian-layout.php';

$categories = db()->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$authors = db()->query('SELECT id,name FROM authors ORDER BY name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $categoryIds = array_values(array_unique(array_filter(array_map('intval', $_POST['categories'] ?? []))));
    $authorIds = array_values(array_unique(array_filter(array_map('intval', $_POST['authors'] ?? []))));
    $publisher = trim((string)($_POST['publisher'] ?? ''));
    $yearInput = trim((string)($_POST['publication_year'] ?? ''));
    $year = $yearInput !== '' ? (int)$yearInput : null;
    $isbn = trim((string)($_POST['isbn'] ?? ''));
    $language = trim((string)($_POST['language'] ?? 'English'));
    $status = in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft';
    $file = $_FILES['book_file'] ?? null;
    $cover = $_FILES['cover_image'] ?? null;
    $coverSelected = ($_POST['cover_selected'] ?? '0') === '1';

    if ($title === '' || strlen($title) > 255) $errors[] = 'Title is required.';
    if (!$categoryIds) $errors[] = 'At least one category is required.';
    if ($year !== null && ($year < 1000 || $year > (int)date('Y') + 1)) $errors[] = 'Invalid publication year.';
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) $errors[] = 'A book PDF is required.';
    if ($file && ($file['size'] ?? 0) > DEFAULT_MAX_UPLOAD_MB * 1024 * 1024) $errors[] = 'Book file exceeds the configured size limit.';
    $extension = $file ? strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION)) : '';
    $type = file_type_from_ext($extension);
    if ($file && !in_array($type, allowed_file_types(), true)) $errors[] = 'Only PDF book files are currently supported.';
    if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK && $type === 'pdf') {
        $handle = @fopen($file['tmp_name'], 'rb');
        $signature = $handle ? fread($handle, 5) : '';
        if ($handle) fclose($handle);
        if ($signature !== '%PDF-') $errors[] = 'The uploaded book file is not a valid PDF.';
    }
    if ($coverSelected && (!$cover || ($cover['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE)) {
        $errors[] = 'The cover was selected but did not reach the server. Please choose it again and retry.';
    }

    if (!$errors) {
        $pdo = db();
        $bookPath = null;
        $coverPath = null;
        $pdo->beginTransaction();
        try {
            $insert = $pdo->prepare('INSERT INTO books(title,slug,description,publisher,publication_year,isbn,language,status,added_by) VALUES(?,?,?,?,?,?,?,?,?)');
            $insert->execute([$title, unique_slug($title), $description, $publisher, $year, $isbn, $language, $status, (int)current_user()['id']]);
            $bookId = (int)$pdo->lastInsertId();

            $categoryInsert = $pdo->prepare('INSERT INTO book_categories(book_id,category_id) VALUES(?,?)');
            foreach ($categoryIds as $categoryId) $categoryInsert->execute([$bookId, $categoryId]);
            $authorInsert = $pdo->prepare('INSERT IGNORE INTO book_authors(book_id,author_id) VALUES(?,?)');
            foreach ($authorIds as $authorId) $authorInsert->execute([$bookId, $authorId]);

            $storedBookName = bin2hex(random_bytes(16)) . '.' . $extension;
            if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
                throw new RuntimeException('Book upload directory is unavailable.');
            }
            $bookPath = UPLOAD_DIR . DIRECTORY_SEPARATOR . $storedBookName;
            if (!move_uploaded_file($file['tmp_name'], $bookPath))
                throw new RuntimeException('Could not save the uploaded book file.');
            $bookHash = hash_file('sha256', $bookPath);
            $fileInsert = $pdo->prepare('INSERT INTO book_files(book_id,file_name,file_path,file_type,file_size,file_hash,is_main) VALUES(?,?,?,?,?,?,1)');
            $fileInsert->execute([$bookId, basename((string)$file['name']), UPLOAD_WEB_PATH . '/' . $storedBookName, $type, (int)$file['size'], $bookHash]);

            $coverPath = save_cover_upload($cover);
            if ($coverPath) $pdo->prepare('UPDATE books SET cover_image=? WHERE id=?')->execute([$coverPath, $bookId]);
            $pdo->commit();
            log_activity((int)current_user()['id'], 'create_book', 'book', $bookId);
            flash('success', 'Book created successfully.');
            redirect('librarian/books/index.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($bookPath && is_file($bookPath)) @unlink($bookPath);
            if ($coverPath) delete_stored_file($coverPath);
            $errors[] = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Could not create the book. Check the database and upload directory.';
        }
    }
}
?>
<section class="section">
    <div class="form-card">
        <h1>Add Book</h1>
        <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
        <form method="post" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="cover_selected" value="0">
            <div class="field full"><label for="title">Title</label><input id="title" name="title" required
                    maxlength="255" value="<?= old('title') ?>"></div>
            <div class="field full">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= old('description') ?></textarea>
            </div>
            <div class="field">
                <label for="categories">Categories</label>
                <select id="categories" name="categories[]" required multiple size="6">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>">
                            <?= e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="authors">Authors</label>
                <select id="authors" name="authors[]" multiple size="5">
                    <?php foreach ($authors as $author): ?><option value="<?= (int)$author['id'] ?>">
                            <?= e($author['name']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="field">
                <label for="publisher">Publisher</label>
                <input id="publisher" name="publisher">
            </div>
            <div class="field">
                <label for="publication-year">Publication year</label>
                <input id="publication-year" type="number" name="publication_year" min="1000"
                    max="<?= (int)date('Y') + 1 ?>">
            </div>
            <div class="field">
                <label for="isbn">ISBN</label>
                <input id="isbn" name="isbn" maxlength="30">
            </div>
            <div class="field">
                <label for="language">Language</label>
                <input id="language" name="language" value="English" maxlength="50">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            <div class="field">
                <label for="book-file">Book file (PDF only)</label>
                <input id="book-file" type="file" name="book_file" required accept=".pdf,application/pdf"><small
                    class="muted">PDF · Max
                    <?= DEFAULT_MAX_UPLOAD_MB ?> MB</small>
            </div>
            <div class="field">
                <label for="cover-image">Optional cover</label>
                <input id="cover-image" type="file" name="cover_image"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><small class="muted">JPG, PNG or WebP
                    · Max 5 MB</small>
            </div>
            <div>
                <button class="btn">Create book</button>
            </div>
        </form>
    </div>
</section>
<?php require __DIR__ . '/../../includes/panel-footer.php'; ?>