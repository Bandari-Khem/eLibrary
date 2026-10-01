<?php $id = (int)($_GET['id'] ?? 0);
$s = db()->prepare('SELECT * FROM books WHERE id=?');
$s->execute([$id]);
$b = $s->fetch();
if (!$b) {
    redirect('404.php');
}
$pageTitle = 'Edit Book';
require __DIR__ . '/../../includes/admin-layout.php';
$cats = db()->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$sc = db()->prepare('SELECT category_id FROM book_categories WHERE book_id=?');
$sc->execute([$id]);
$selectedCategories = array_map('intval', array_column($sc->fetchAll(), 'category_id'));
$authors = db()->query('SELECT id,name FROM authors ORDER BY name')->fetchAll();
$sa = db()->prepare('SELECT author_id FROM book_authors WHERE book_id=?');
$sa->execute([$id]);
$selected = array_map('intval', array_column($sa->fetchAll(), 'author_id'));
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $categoryIds = array_values(array_unique(array_filter(array_map('intval', $_POST['categories'] ?? []))));
    $status = $_POST['status'] ?? 'draft';
    $publisher = trim($_POST['publisher'] ?? '');
    $year = $_POST['publication_year'] !== '' ? (int)$_POST['publication_year'] : null;
    $isbn = trim($_POST['isbn'] ?? '');
    $language = trim($_POST['language'] ?? 'English');
    $coverPath = null;
    if (!$title || !$categoryIds || !in_array($status, ['draft', 'published', 'archived'], true)) $errors[] = 'Title, at least one category and status are required.';
    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $slug = unique_slug($title, $id);
            $oldCover = $b['cover_image'];
            $coverPath = save_cover_upload($_FILES['cover_image'] ?? null);
            $newCover = $coverPath ?: $oldCover;
            $s = $pdo->prepare('UPDATE books SET title=?,slug=?,description=?,publisher=?,publication_year=?,isbn=?,language=?,status=?,cover_image=? WHERE id=?');
            $s->execute([$title, $slug, $desc, $publisher, $year, $isbn, $language, $status, $newCover, $id]);
            $pdo->prepare('DELETE FROM book_categories WHERE book_id=?')->execute([$id]);
            $cs = $pdo->prepare('INSERT INTO book_categories(book_id,category_id) VALUES(?,?)');
            foreach ($categoryIds as $cid) $cs->execute([$id, $cid]);
            $pdo->prepare('DELETE FROM book_authors WHERE book_id=?')->execute([$id]);
            foreach (array_map('intval', $_POST['authors'] ?? []) as $aid) $pdo->prepare('INSERT IGNORE INTO book_authors(book_id,author_id) VALUES(?,?)')->execute([$id, $aid]);
            $pdo->commit();
            if ($coverPath && $oldCover) delete_stored_file($oldCover);
            log_activity((int)current_user()['id'], 'update_book', 'book', $id, $coverPath ? 'cover_replaced' : 'metadata_updated');
            flash('success', 'Book updated.');
            redirect('admin/books/index.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($coverPath) delete_stored_file($coverPath);
            $errors[] = 'Update failed.';
        }
    }
} ?>
<section class="section">
    <div class="form-card">
        <h1>Edit Book</h1><?php foreach ($errors as $err): ?><div class="alert error"><?= e($err) ?></div>
        <?php endforeach; ?>
        <form method="post" enctype="multipart/form-data" class="form-grid"><?= csrf_field() ?><div class="field full">
                <label>Title</label>
                <input name="title" required value="<?= e($b['title']) ?>">
            </div>
            <div class="field full">
                <label>Description</label><textarea name="description"><?= e($b['description']) ?></textarea>
            </div>
            <div class="field">
                <label>Categories</label>
                <select name="categories[]" required multiple size="6"><?php foreach ($cats as $c): ?><option
                        value="<?= $c['id'] ?>"
                        <?= in_array((int)$c['id'], $selectedCategories, true) ? 'selected' : '' ?>><?= e($c['name']) ?>
                    </option>
                    <?php endforeach; ?></select>
            </div>
            <div class="field">
                <label>Authors</label>
                <select name="authors[]" multiple size="5"><?php foreach ($authors as $a): ?><option
                        value="<?= $a['id'] ?>" <?= in_array((int)$a['id'], $selected, true) ? 'selected' : '' ?>>
                        <?= e($a['name']) ?></option>
                    <?php endforeach; ?></select>
            </div>
            <div class="field">
                <label>Publisher</label><input name="publisher" value="<?= e($b['publisher']) ?>">
            </div>
            <div class="field">
                <label>Publication year</label><input type="number" name="publication_year"
                    value="<?= e((string)$b['publication_year']) ?>">
            </div>
            <div class="field">
                <label>ISBN</label><input name="isbn" value="<?= e($b['isbn']) ?>">
            </div>
            <div class="field">
                <label>Language</label><input name="language" value="<?= e($b['language']) ?>">
            </div>
            <div class="field">
                <label>Status</label><select name="status">
                    <option value="draft" <?= $b['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $b['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $b['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="field">
                <label>Replace cover</label>
                <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp"><small class="muted">Optional ·
                    JPG, PNG or WebP · Max 5 MB</small>
            </div>
            <div>
                <button class="btn">Save changes</button>
            </div>
        </form>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>