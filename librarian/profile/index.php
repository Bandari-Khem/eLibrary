<?php $pageTitle = 'Profile';
require __DIR__ . '/../includes/header.php';
require_login();
$uid = (int)current_user()['id'];
$s = db()->prepare('SELECT id,full_name,email,avatar,created_at FROM users WHERE id=?');
$s->execute([$uid]);
$profile = $s->fetch();
$avatars = ['📚', '🧑‍💻', '👨‍🎓', '👩‍🎓', '🧑‍🏫', '🌱', '⭐', '🚀'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $avatar = $_POST['avatar'] ?? '📚';
    if (strlen($name) >= 2 && strlen($name) <= 100 && in_array($avatar, $avatars, true)) {
        $s = db()->prepare('UPDATE users SET full_name=?,avatar=? WHERE id=?');
        $s->execute([$name, $avatar, $uid]);
        $_SESSION['user']['full_name'] = $name;
        flash('success', 'Profile updated.');
        redirect('user/profile.php');
    }
    flash('error', 'Invalid profile information.');
} ?>
<section class="section">
    <div class="form-card">
        <h1>Profile</h1>
        <div class="profile-avatar"><?= e($profile['avatar'] ?: '📚') ?></div>
        <form method="post"><?= csrf_field() ?>
            <div class="field">
                <label>Full name</label>
                <input name="full_name" value="<?= e($profile['full_name']) ?>" required maxlength="100">
            </div>
            <div class="field">
                <label>Email</label>
                <input value="<?= e($profile['email']) ?>" disabled>
            </div>
            <div class="field">
                <label>Member since</label>
                <input value="<?= e(date('F j, Y', strtotime($profile['created_at']))) ?>" disabled>
            </div>
            <div class="field">
                <label>Avatar</label>
                <div class="avatar-options">
                    <?php foreach ($avatars as $a): ?>
                        <label><input type="radio" name="avatar" value="<?= e($a) ?>"
                                <?= $profile['avatar'] === $a ? 'checked' : '' ?>>
                            <span><?= $a ?></span></label><?php endforeach; ?>
                </div>
            </div><button class="btn">Save changes</button>
        </form>
    </div>
</section><?php require __DIR__ . '/../includes/footer.php'; ?>