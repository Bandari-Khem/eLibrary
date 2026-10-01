<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
$flashes = get_flashes();
$title = $pageTitle ?? APP_NAME;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Digital library for browsing, reading and managing electronic books">
    <title><?= e($title) ?> · <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>

<body>
    <?php require __DIR__ . '/navbar.php'; ?>
    <main class="site-main">
        <div class="container">
            <?php foreach ($flashes as $f): ?><div class="alert <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
            <?php endforeach; ?>