<?php
$bcaNav = null;
$semesterNav = [];
try {
    $bs = db()->query("SELECT id,name FROM categories WHERE parent_id IS NULL AND LOWER(name)='bca' LIMIT 1");
    $bcaNav = $bs->fetch();
    if ($bcaNav) {
        $ss = db()->prepare('SELECT id,name FROM categories WHERE parent_id=? ORDER BY id');
        $ss->execute([(int)$bcaNav['id']]);
        $semesterNav = $ss->fetchAll();
        foreach ($semesterNav as &$sem) {
            $st = db()->prepare('SELECT id,name FROM categories WHERE parent_id=? ORDER BY name');
            $st->execute([(int)$sem['id']]);
            $sem['subjects'] = $st->fetchAll();
        }
        unset($sem);
    }
} catch (Throwable $e) {
    $bcaNav = null;
    $semesterNav = [];
}
?>
<header class="site-header">
    <div class="container nav"><a class="brand" href="<?= url('index.php') ?>">📚 E-Library</a><button
            class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">☰</button>
        <nav class="nav-links">
            <a href="<?= url('books.php') ?>">Books</a>
            <div class="nav-dropdown"><a class="nav-dropdown-trigger" href="<?= url('bca.php') ?>">BCA <span
                        aria-hidden="true">⌄</span></a><?php if ($semesterNav): ?><div class="nav-dropdown-menu">
                    <?php foreach ($semesterNav as $sem): ?><div class="semester-menu"><a class="semester-link"
                            href="<?= url('books.php?category=' . (int)$sem['id']) ?>"><?= e($sem['name']) ?></a><?php if ($sem['subjects']): ?>
                        <div class="subject-menu"><?php foreach ($sem['subjects'] as $subject): ?><a
                                href="<?= url('books.php?category=' . (int)$subject['id']) ?>"><?= e($subject['name']) ?></a><?php endforeach; ?>
                        </div><?php endif; ?>
                    </div><?php endforeach; ?></div>
                <?php endif; ?></div>
            <a href="<?= url('about.php') ?>">About</a><a href="<?= url('faq.php') ?>">FAQ</a><a
                href="<?= url('contact.php') ?>">Contact</a>
            <?php if (current_user()): ?><a
                href="<?= url(dashboard_for_role(current_user()['role'])) ?>">Dashboard</a><?php if (current_user()['role'] === 'user'): ?><a
                href="<?= url('user/my-library.php') ?>">My Library</a>
            <a href="<?= url('user/profile.php') ?>">Profile</a>
            <a href="<?= url('user/settings.php') ?>">Settings</a><?php endif; ?><a
                href="<?= url('logout.php') ?>">Logout</a>
            <?php else: ?>
            <a class="btn btn-sm" href="<?= url('login.php') ?>">Login</a><?php endif; ?>
        </nav>
    </div>
</header>