<?php require_once __DIR__ . '/../../includes/auth.php';
require_admin();
require_post();
verify_csrf();
$id = (int)($_POST['user_id'] ?? 0);
$role = $_POST['role'] ?? '';
if (!in_array($role, ['user', 'librarian', 'admin'], true)) {
    flash('error', 'Invalid role.');
    redirect('admin/users/index.php');
}
$s = db()->prepare('SELECT * FROM users WHERE id=?');
$s->execute([$id]);
$u = $s->fetch();
if (!$u) {
    flash('error', 'User not found.');
    redirect('admin/users/index.php');
}
if ($id === (int)current_user()['id'] && $role !== 'admin') {
    flash('error', 'You cannot remove your own admin role.');
    redirect('admin/users/index.php');
}
if ($u['role'] === 'admin' && $role !== 'admin') {
    $n = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='admin' AND status='active'")->fetchColumn();
    if ($n <= 1) {
        flash('error', 'The last active admin cannot be demoted.');
        redirect('admin/users/index.php');
    }
}
$s = db()->prepare('UPDATE users SET role=? WHERE id=?');
$s->execute([$role, $id]);
log_activity((int)current_user()['id'], 'change_role', 'user', $id, 'new_role=' . $role);
flash('success', 'User role updated.');
redirect('admin/users/index.php');
