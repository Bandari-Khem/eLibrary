<header class="site-header">
    <div class="container nav"><a class="brand" href="<?= url('index.php') ?>">📚 E-Library</a><button
            class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="site-navigation">☰</button>
        <nav class="nav-links" id="site-navigation">
            <a href="<?= url('books.php') ?>">Books</a>
            <a href="<?= url('about.php') ?>">About</a>
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
