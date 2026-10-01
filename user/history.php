<?php $pageTitle = 'Reading History';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = (int)current_user()['id'];
$s = db()->prepare('SELECT h.*,b.title FROM reading_history h JOIN books b ON b.id=h.book_id WHERE h.user_id=? ORDER BY h.read_at DESC LIMIT 100');
$s->execute([$uid]);
$rows = $s->fetchAll(); ?>
<section class="section">
    <h1>Reading History</h1>
    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Book</th>
                <th>Action</th>
                <th>Time</th>
                <th></th>
            </tr><?php foreach ($rows as $r): ?><tr>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e($r['action']) ?></td>
                    <td><?= e($r['read_at']) ?></td>
                    <td><a class="btn btn-sm" href="<?= url('book-details.php?id=' . $r['book_id']) ?>">Open</a></td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</section><?php require __DIR__ . '/../includes/footer.php'; ?>