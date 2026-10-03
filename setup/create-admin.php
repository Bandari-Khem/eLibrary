<?php require_once __DIR__ . '/../includes/functions.php';
if (current_user()) redirect('index.php');
$done = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
if ($done > 0) exit('An administrator already exists. Delete or protect setup/create-admin.php after installation.');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/csrf.php';
    verify_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 12) {
        flash('error', 'Use a valid name/email and a password of at least 12 characters.');
    } else {
        $s = db()->prepare('INSERT INTO users(full_name,email,password,role,status) VALUES(?,?,?,\'admin\',\'active\')');
        $s->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
        flash('success', 'Admin created. Delete or rename the setup directory now.');
        redirect('login.php');
    }
}
$pageTitle = 'Create administrator';
require __DIR__ . '/../includes/header.php'; ?>
<div class="auth-wrap">
    <div class="form-card auth-card">
        <h1>Initial administrator</h1>
        <p>Use this only after importing the database. Remove this setup directory after creating the admin.</p>
        <form method="post"><?= csrf_field() ?>
            <div class="field">
                <label>Full name</label>
                <input name="full_name" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" minlength="12" required>
            </div>
            <button class="btn">Create admin</button>
        </form>
    </div>
</div><?php require __DIR__ . '/../includes/footer.php'; ?>