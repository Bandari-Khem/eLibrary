<?php $pageTitle = 'FAQ';
require __DIR__ . '/includes/header.php'; ?><section class="section">
    <h1>Frequently Asked Questions</h1>
    <?php $q = db()->query('SELECT question,answer FROM faq WHERE status=1 ORDER BY display_order,id');
    foreach ($q as $f): ?>
        <details class="faq-item">
            <summary><?= e($f['question']) ?></summary>
            <p><?= nl2br(e($f['answer'])) ?></p>
        </details><?php endforeach; ?>
</section><?php require __DIR__ . '/includes/footer.php'; ?>