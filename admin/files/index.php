<?php $pageTitle = 'Files';
require __DIR__ . '/../../includes/admin-layout.php';
$rows = db()->query('SELECT bf.*,b.title FROM book_files bf JOIN books b ON b.id=bf.book_id ORDER BY bf.uploaded_at DESC LIMIT 300')->fetchAll(); ?>
<section class="section">
    <h1>Book Files</h1>
    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Book</th>
                <th>Original name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Hash</th>
            </tr><?php foreach ($rows as $r): ?><tr>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e($r['file_name']) ?></td>
                    <td><?= e($r['file_type']) ?></td>
                    <td><?= format_bytes((int)$r['file_size']) ?></td>
                    <td><code><?= e(substr($r['file_hash'] ?? '', 0, 16)) ?>…</code></td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>