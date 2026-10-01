<?php require_once __DIR__ . '/auth.php';
require_role('librarian');
$pageTitle = $pageTitle ?? 'Librarian';
$flashes = get_flashes(); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($pageTitle) ?> · Librarian</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>

<body>
    <div class="app-shell">
        <aside class="side-nav"><a class="brand" href="<?= url('librarian/dashboard.php') ?>">📚 Librarian</a><a
                href="<?= url('librarian/dashboard.php') ?>">Overview</a><a
                href="<?= url('librarian/books/index.php') ?>">Books</a><a
                href="<?= url('librarian/categories/index.php') ?>">Categories</a><a
                href="<?= url('librarian/authors/index.php') ?>">Authors</a><a
            <a href="<?= url('index.php') ?>">View
                site</a>
            <a href="<?= url('librarian/profile/index.php') ?>">Profile</a>
            <a href="<?= url('logout.php') ?>">Logout</a>
        </aside>
        <main class="panel-main">
            <div class="mobile-top"><button class="side-toggle">☰</button><strong><?= e($pageTitle) ?></strong></div>
            <div class="container"><?php foreach ($flashes as $f): ?><div class="alert <?= e($f['type']) ?>">
                        <?= e($f['message']) ?></div><?php endforeach; ?>
