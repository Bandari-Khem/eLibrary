<?php require_once __DIR__ . '/auth.php';
require_admin();
$adminUser = current_user();
$pageTitle = $pageTitle ?? 'Admin';
$flashes = get_flashes(); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($pageTitle) ?> · Admin</title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>

<body>
    <div class="app-shell">
        <aside class="side-nav" id="panel-navigation"><a class="brand" href="<?= url('admin/dashboard.php') ?>">📚 Admin</a><a
                href="<?= url('admin/dashboard.php') ?>">Overview</a>
            <a href="<?= url('admin/users/index.php') ?>">Users</a>
            <a href="<?= url('admin/activity-logs/index.php') ?>">Activity logs</a>
            <a href="<?= url('index.php') ?>">View
                site</a>
            <a href="<?= url('logout.php') ?>">Logout</a>
        </aside>
        <main class="panel-main">
            <div class="mobile-top">
                <button class="side-toggle" type="button" aria-label="Toggle admin menu" aria-expanded="false" aria-controls="panel-navigation">☰</button><strong><?= e($pageTitle) ?></strong>
            </div>
            <div class="container">
                <?php foreach ($flashes as $f): ?>
                <div class="alert <?= e($f['type']) ?>">
                    <?= e($f['message']) ?></div>
                <?php endforeach; ?>
