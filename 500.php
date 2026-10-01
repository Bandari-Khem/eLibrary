<?php http_response_code(500);
$pageTitle = 'Server error';
require __DIR__ . '/includes/header.php'; ?><div class="empty">
    <h1>500 — Server error</h1>
    <p>Something went wrong on the server.</p><a class="btn" href="<?= url('index.php') ?>">Go home</a>
</div><?php require __DIR__ . '/includes/footer.php'; ?>