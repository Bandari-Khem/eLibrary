<?php $pageTitle = 'Bookmarks';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = (int)current_user()['id'];
$s = db()->prepare('SELECT bm.*,b.title FROM bookmarks bm JOIN books b ON b.id=bm.book_id WHERE bm.user_id=? ORDER BY bm.created_at DESC');
$s->execute([$uid]);
$rows = $s->fetchAll(); ?>
<section class="section">
    <h1>Bookmarks</h1>
    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Book</th>
                <th>Page</th>
                <th>Note</th>
                <th>Action</th>
            </tr><?php foreach ($rows as $r): ?><tr>
                    <td><?= e($r['title']) ?></td>
                    <td><?= $r['page_number'] ?></td>
                    <td><?= e($r['note']) ?></td>
                    <td><a class="btn btn-sm"
                            href="<?= url('user/reader.php?book=' . $r['book_id'] . '&page=' . $r['page_number']) ?>">Open</a>
                    </td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</section><?php require __DIR__ . '/../includes/footer.php'; ?>