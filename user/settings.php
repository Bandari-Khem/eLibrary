<?php $pageTitle = 'Settings';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = (int)current_user()['id'];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $s = db()->prepare('SELECT password FROM users WHERE id=?');
    $s->execute([$uid]);
    $hash = (string)$s->fetchColumn();
    if (!password_verify($current, $hash)) $errors[] = 'Current password is incorrect.';
    if (strlen($new) < 8) $errors[] = 'New password must be at least 8 characters.';
    if ($new !== $confirm) $errors[] = 'New passwords do not match.';
    if (!$errors) {
        $s = db()->prepare('UPDATE users SET password=? WHERE id=?');
        $s->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
        log_activity($uid, 'change_password', 'user', $uid);
        flash('success', 'Password changed successfully.');
        redirect('user/settings.php');
    }
} ?>
<section class="section">
    <div class="form-card">
        <h1>Account Settings</h1><?php foreach ($errors as $err): ?><div class="alert error"><?= e($err) ?></div>
        <?php endforeach; ?><h2>Change password</h2>
        <form method="post"><?= csrf_field() ?><div class="field"><label>Current password</label><input type="password"
                    name="current_password" required autocomplete="current-password"></div>
            <div class="field"><label>New password</label><input type="password" name="new_password" minlength="8"
                    required autocomplete="new-password"></div>
            <div class="field"><label>Confirm new password</label><input type="password" name="confirm_password"
                    minlength="8" required autocomplete="new-password"></div><button class="btn">Change
                password</button>
        </form>
        <hr>
        <p class="muted">Email verification and email-delivered password reset require the future mail-service
            configuration.</p>
    </div>
</section><?php require __DIR__ . '/../includes/footer.php'; ?>