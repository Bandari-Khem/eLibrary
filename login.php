<?php
$pageTitle = 'Login';
require __DIR__ . '/includes/auth.php';

$returnTo = (string)($_POST['return'] ?? $_GET['return'] ?? '');
$safeReturn = preg_match('/^book-details\.php\?id=[1-9][0-9]*$/D', $returnTo) === 1;
$afterLogin = $safeReturn ? $returnTo : null;

if (current_user()) {
    redirect($afterLogin ?? dashboard_for_role(current_user()['role']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        flash('error', 'Enter a valid email and password.');
    } else {
        $query = db()->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
        $query->execute([$email]);
        $user = $query->fetch();
        if ($user && password_verify($password, $user['password']) && $user['status'] === 'active') {
            login_user($user);
            log_activity((int)$user['id'], 'login', 'user', (int)$user['id']);
            redirect($afterLogin ?? dashboard_for_role($user['role']));
        }
        log_activity($user ? (int)$user['id'] : null, 'login_failed', null, null, 'email=' . substr($email, 0, 120));
        flash('error', 'Invalid credentials or inactive account.');
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
    <div class="form-card auth-card">
        <h1>Welcome back</h1>
        <form method="post">
            <?= csrf_field() ?>
            <?php if ($safeReturn): ?><input type="hidden" name="return" value="<?= e($returnTo) ?>"><?php endif; ?>
            <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" required autocomplete="email"></div>
            <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
            <button class="btn" style="width:100%;margin-top:12px">Login</button>
        </form>
        <p><a href="<?= url('forgot-password.php') ?>">Forgot password?</a></p>
        <p>New here? <a href="<?= url('register.php') ?>">Create an account</a></p>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
