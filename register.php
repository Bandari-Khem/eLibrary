<?php $pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
if (current_user()) redirect(dashboard_for_role(current_user()['role']));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($name) < 2 || strlen($name) > 100 || !valid_email_address($email)) {
        flash('error', 'Use a valid name/email.');
    } elseif (strlen($pass) < 8 || $pass !== $confirm) {
        flash('error', "Password is not secure or Doesnot matched.");
    } else {
        $s = db()->prepare('SELECT id FROM users WHERE email=?');
        $s->execute([$email]);
        if ($s->fetch()) {
            flash('error', 'An account with that email already exists.');
        } else {
            $s = db()->prepare('INSERT INTO users(full_name,email,password,role,status) VALUES(?,?,? ,\'user\',\'active\')');
            $s->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            $id = (int)db()->lastInsertId();
            log_activity($id, 'register', 'user', $id);
            flash('success', 'Registration successful. You can now log in.');
            redirect('login.php');
        }
    }
} ?>
<div class="auth-wrap">
    <div class="form-card auth-card">
        <h1>Create account</h1>
        <form method="post">
            <div class="field">
                <label>Full name</label>
                <input name="full_name" required maxlength="100" autocomplete="name">
            </div>
            <div class="field">
                <label for="register-email">Email</label>
                <input id="register-email" type="email" name="email" required maxlength="150" autocomplete="email">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" minlength="8" required autocomplete="new-password">
            </div>
            <div class="field">
                <label>Confirm password</label>
                <input type="password" name="confirm_password" minlength="8" required autocomplete="new-password">
            </div><br>
            <?= csrf_field() ?>
            <button class="btn" style="width:100%">Register</button>
        </form>
        <p>Already registered? <a href="<?= url('login.php') ?>">Login</a></p>
    </div>
</div><?php require __DIR__ . '/includes/footer.php'; ?>