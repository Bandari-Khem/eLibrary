<?php http_response_code(403);
$pageTitle = 'Access denied';
require __DIR__ . '/includes/header.php'; ?><div class="empty">
    <h1>403 — Access denied</h1>
    <p>You do not have permission to access this page.</p><a class="btn" href="<?= url('index.php') ?>">Go home</a>
</div><?php require __DIR__ . '/includes/footer.php'; ?>