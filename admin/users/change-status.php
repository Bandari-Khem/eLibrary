<?php require_once __DIR__ . '/../../includes/auth.php';
require_admin();
require_post();
verify_csrf();
$id = (int)($_POST['user_id'] ?? 0);
$status = $_POST['status'] ?? '';
if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
    flash('error', 'Invalid status.');
    redirect('admin/users/index.php');
}
$s = db()->prepare('SELECT * FROM users WHERE id=?');
$s->execute([$id]);
$u = $s->fetch();
if (!$u) {
    redirect('admin/users/index.php');
}
if ($u['role'] === 'admin' && $status !== 'active') {
    $n = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='admin' AND status='active'")->fetchColumn();
    if ($n <= 1) {
        flash('error', 'The last active admin cannot be disabled.');
        redirect('admin/users/index.php');
    }
}
$s = db()->prepare('UPDATE users SET status=? WHERE id=?');
$s->execute([$status, $id]);
log_activity((int)current_user()['id'], 'change_status', 'user', $id, 'new_status=' . $status);
flash('success', 'User status updated.');
redirect('admin/users/index.php');
