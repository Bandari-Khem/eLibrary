<?php
$pageTitle = 'Categories';
require __DIR__ . '/../../includes/librarian-layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '' || strlen($name) > 100) {
            flash('error', 'Enter a category name up to 100 characters.');
        } else {
            $exists = db()->prepare('SELECT id FROM categories WHERE name=? LIMIT 1');
            $exists->execute([$name]);
            if ($exists->fetch()) {
                flash('error', 'A category with that name already exists.');
            } else {
                $insert = db()->prepare('INSERT INTO categories(name,description) VALUES(?,?)');
                $insert->execute([$name, $description]);
                $categoryId = (int)db()->lastInsertId();
                log_activity((int)current_user()['id'], 'create_category', 'category', $categoryId);
                flash('success', 'Category added.');
            }
        }
    } elseif ($action === 'delete') {
        $categoryId = (int)($_POST['id'] ?? 0);
        try {
            db()->prepare('DELETE FROM categories WHERE id=?')->execute([$categoryId]);
            log_activity((int)current_user()['id'], 'delete_category', 'category', $categoryId);
            flash('success', 'Category deleted.');
        } catch (Throwable $e) {
            flash('error', 'Category cannot be deleted while books use it.');
        }
    }
    redirect('librarian/categories/index.php');
}

$categories = db()->query('SELECT c.*, COUNT(bc.book_id) AS book_count FROM categories c LEFT JOIN book_categories bc ON bc.category_id=c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
?>
<section class="section">
    <div class="grid grid-2">
        <div class="form-card">
            <h1>Add Category</h1>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" required maxlength="100" placeholder="e.g. C Programming">
                </div>
                <div class="field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <button class="btn">Add category</button>
            </form>
        </div>
        <div class="card">
            <h2>Book categories</h2>
            <?php if (!$categories): ?><p class="muted">No categories have been added yet.</p><?php endif; ?>
            <?php foreach ($categories as $category): ?>
            <div class="section-head">
                <p><strong><?= e($category['name']) ?></strong> <span class="muted">·
                        <?= (int)$category['book_count'] ?> books</span></p>
                <form method="post" data-confirm="Delete this category?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$category['id'] ?>"><button
                        class="btn btn-sm btn-secondary">Delete</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../../includes/panel-footer.php'; ?>