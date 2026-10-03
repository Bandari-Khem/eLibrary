<?php
require __DIR__ . '/../../includes/auth.php';
require_role('librarian');

$pageTitle = 'Profile';
$uid = (int)current_user()['id'];
$profileQuery = db()->prepare('SELECT full_name,email,created_at FROM users WHERE id=?');
$profileQuery->execute([$uid]);
$profile = $profileQuery->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));

    if (strlen($name) < 2 || strlen($name) > 100 || !valid_email_address($email)) {
        flash('error', 'Enter a valid name and email address.');
    } else {
        $duplicate = db()->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
        $duplicate->execute([$email, $uid]);
        if ($duplicate->fetch()) {
            flash('error', 'That email address is already used by another account.');
        } else {
            try {
                $save = db()->prepare('UPDATE users SET full_name=?,email=? WHERE id=?');
                $save->execute([$name, $email, $uid]);
                $_SESSION['user']['full_name'] = $name;
                $_SESSION['user']['email'] = $email;
                log_activity($uid, 'update_profile', 'user', $uid);
                flash('success', 'Profile updated.');
                redirect('librarian/profile/index.php');
            } catch (PDOException $e) {
                flash('error', 'The profile could not be saved. Check the email address and try again.');
            }
        }
    }
    redirect('librarian/profile/index.php');
}

require __DIR__ . '/../../includes/librarian-layout.php';
?>
<section class="section">
    <div class="form-card">
        <h1>Profile</h1>
        <div class="profile-avatar" aria-hidden="true">🧑‍🏫</div>
        <form method="post"><?= csrf_field() ?>
            <div class="field">
                <label for="full-name">Full name</label>
                <input id="full-name" name="full_name" value="<?= e($profile['full_name']) ?>" required maxlength="100"
                    autocomplete="name">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= e($profile['email']) ?>" required maxlength="150"
                    autocomplete="email">
                <small class="muted">This demo checks email format but does not verify mailbox ownership.</small>
            </div>
            <div class="field">
                <label for="member-since">Member since</label>
                <input id="member-since" value="<?= e(date('F j, Y', strtotime($profile['created_at']))) ?>" disabled>
            </div>
            <button class="btn">Save changes</button>
        </form>
    </div>
</section>
<?php require __DIR__ . '/../../includes/panel-footer.php'; ?>