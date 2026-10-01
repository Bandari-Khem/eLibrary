<?php $pageTitle = 'Books';
require __DIR__ . '/../../includes/librarian-layout.php';
$q = trim($_GET['q'] ?? '');
$s = db()->prepare("SELECT b.*,
    GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS category,
    (SELECT COUNT(*) FROM book_files bf WHERE bf.book_id=b.id) AS file_count
    FROM books b
    LEFT JOIN book_categories bc ON bc.book_id=b.id
    LEFT JOIN categories c ON c.id=bc.category_id
    WHERE b.title LIKE ?
    GROUP BY b.id
    ORDER BY b.created_at DESC
    LIMIT 200");
$s->execute(["%$q%"]);
$books = $s->fetchAll(); ?>
<section class="section">
    <div class="section-head">
        <h1>Books</h1><a class="btn" href="<?= url('librarian/books/add.php') ?>">+ Add book</a>
    </div>
    <form class="search-bar"><input name="q" value="<?= e($q) ?>" placeholder="Search title"><button
            class="btn">Search</button></form>
    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Files</th>
                <th>Action</th>
            </tr><?php foreach ($books as $b): ?><tr>
                    <td><?= e($b['title']) ?></td>
                    <td><?= e($b['category']) ?></td>
                    <td><?= e($b['status']) ?></td>
                    <td><?= $b['file_count'] ?></td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-sm" href="<?= url('librarian/books/edit.php?id=' . $b['id']) ?>">Edit</a>
                            <form method="post" action="<?= url('librarian/books/delete.php') ?>" data-confirm="Delete this book and its uploaded files, reading records, favourites and reviews? This cannot be undone.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                                <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>
