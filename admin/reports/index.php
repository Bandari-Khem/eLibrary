<?php $pageTitle = 'Reports';
require __DIR__ . '/../../includes/admin-layout.php';
$stats = [
    'downloads' => (int)db()->query('SELECT COUNT(*) FROM downloads')->fetchColumn(),
    'reads' => (int)db()->query("SELECT COUNT(*) FROM reading_history WHERE action='read'")->fetchColumn(),
    'reviews' => (int)db()->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
    'messages' => (int)db()->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn()
]; ?>
<section class="section">
    <h1>Reports</h1>
    <div class="stats"><?php foreach ($stats as $k => $v): ?><div class="stat">
            <?= e(ucfirst($k)) ?><strong><?= $v ?></strong>
        </div><?php endforeach; ?></div>
    <div class="card" style="margin-top:20px">
        <h2>Most read books</h2>
        <?php $rows = db()->query("SELECT b.title,COUNT(*) total FROM reading_history h 
            JOIN books b ON b.id=h.book_id WHERE h.action='read' GROUP BY h.book_id ORDER BY total DESC LIMIT 10");
        foreach ($rows as $r): ?>
        <p><?= e($r['title']) ?> <span class="chip"><?= $r['total'] ?> reads</span></p><?php endforeach; ?>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>