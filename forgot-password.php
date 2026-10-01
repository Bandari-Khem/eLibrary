<?php $pageTitle = 'Forgot Password';
require __DIR__ . '/includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,63}$/', $email)) {
        flash('error', 'Enter a valid email address.');
    } else {
        log_activity(null, 'password_reset_requested', 'user', null, 'email=' . substr($email, 0, 120));
        flash('success', 'If an account matches that email, reset instructions will be available once email delivery is configured.');
    }
} ?>
<div class="auth-wrap">
    <div class="form-card auth-card">
        <h1>Forgot Password</h1>
        <p class="muted">Enter your account email. Email delivery is not enabled in the current eLibrary v1 deployment.
        </p>
        <form method="post"><?= csrf_field() ?>
            <div class="field"><label>Email</label><input type="email" name="email" required maxlength="150"
                    autocomplete="email"></div>
            <button class="btn" style="width:100%">Request
                reset</button>
        </form>
        <p><a href="<?= url('login.php') ?>">Back to login</a></p>
    </div>
</div><?php require __DIR__ . '/includes/footer.php'; ?>