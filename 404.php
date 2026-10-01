<?php http_response_code(404);
$pageTitle = 'Page not found';
require __DIR__ . '/includes/header.php'; ?><div class="empty">
    <h1>404 — Page not found</h1>
    <p>The requested page does not exist.</p><a class="btn" href="<?= url('index.php') ?>">Go home</a>
</div><?php require __DIR__ . '/includes/footer.php'; ?>