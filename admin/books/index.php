<?php $pageTitle = 'Books';
require __DIR__ . '/../../includes/admin-layout.php';
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';
$where = [];
$p = [];
if ($q !== '') {
    $where[] = '(b.title LIKE ? OR b.slug LIKE ? OR EXISTS(SELECT 1 FROM book_authors ba JOIN authors a ON a.id=ba.author_id WHERE ba.book_id=b.id AND a.name LIKE ?))';
    $like = "%$q%";
    array_push($p, $like, $like, $like);
}
if (in_array($status, ['draft', 'published', 'archived'], true)) {
    $where[] = 'b.status=?';
    $p[] = $status;
}
$sql = "SELECT b.*,GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') 
    category,u.full_name added_by_name,(SELECT COUNT(*) FROM book_files bf WHERE bf.book_id=b.id) 
    file_count FROM books b LEFT JOIN book_categories bc ON bc.book_id=b.id LEFT JOIN categories c ON c.id=bc.category_id 
    JOIN users u ON u.id=b.added_by" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " GROUP BY b.id ORDER BY b.created_at DESC LIMIT 300";
$s = db()->prepare($sql);
$s->execute($p);
$books = $s->fetchAll(); ?>
<section class="section">
    <div class="section-head">
        <h1>Book Management</h1><a class="btn" href="<?= url('admin/books/add.php') ?>">+ Add book</a>
    </div>
    <form class="search-bar" method="get">
        <input name="q" value="<?= e($q) ?>" placeholder="Search title, author or slug">
        <button class="btn btn-secondary">Search</button>
    </form>
    <div class="actions" style="margin-bottom:18px">
        <?php foreach (['all', 'published', 'draft', 'archived'] as $st): ?><a
                class="btn btn-sm <?= $status === $st ? '' : 'btn-secondary' ?>"
                href="?<?= http_build_query(['q' => $q, 'status' => $st]) ?>"><?= ucfirst($st) ?></a><?php endforeach; ?>
    </div>
    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Book</th>
                <th>Category</th>
                <th>Status</th>
                <th>Files</th>
                <th>Actions</th>
            </tr><?php foreach ($books as $b): ?><tr>
                    <td><strong><?= e($b['title']) ?></strong><br><small><?= e($b['slug']) ?></small></td>
                    <td><?= e($b['category']) ?></td>
                    <td><?= e($b['status']) ?></td>
                    <td><?= $b['file_count'] ?></td>
                    <td>
                        <div class="actions"><a class="btn btn-sm"
                                href="<?= url('admin/books/edit.php?id=' . $b['id']) ?>">Edit</a><?php if ($b['status'] !== 'archived'): ?>
                                <form method="post" action="<?= url('admin/books/archive.php') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button class="btn btn-sm btn-danger" data-confirm="Archive this book?">Archive</button>
                                </form><?php else: ?>
                                <form method="post" action="<?= url('admin/books/restore.php') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button class="btn btn-sm btn-success">Restore</button>
                                </form>
                                <form method="post" action="<?= url('admin/books/delete.php') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button class="btn btn-sm btn-danger"
                                        data-confirm="Permanently delete this archived book and its files?">Delete
                                        permanently</button>
                                </form><?php endif; ?>
                        </div>
                    </td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>