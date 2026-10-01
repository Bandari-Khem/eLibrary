<?php $pageTitle = 'Users';
require __DIR__ . '/../../includes/admin-layout.php';
$q = trim($_GET['q'] ?? '');
$role = $_GET['role'] ?? '';
$where = ['1=1'];
$p = [];
if ($q !== '') {
    $where[] = '(full_name LIKE ? OR email LIKE ?)';
    $p[] = "%$q%";
    $p[] = "%$q%";
}
if (in_array($role, ['user', 'librarian', 'admin'], true)) {
    $where[] = 'role=?';
    $p[] = $role;
}
$s = db()->prepare('SELECT * FROM users WHERE ' . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT 200');
$s->execute($p);
$users = $s->fetchAll(); ?>
<section class="section">
    <div class="section-head">
        <h1>User Management</h1>
    </div>
    <form class="search-bar" method="get">
        <input name="q" value="<?= e($q) ?>" placeholder="Search by name or email">
        <button class="btn">Search</button>
    </form>
    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr><?php foreach ($users as $u): ?><tr>
                    <td><strong><?= e($u['full_name']) ?></strong><br><span class="muted"><?= e($u['email']) ?></span></td>
                    <td><span class="chip"><?= e($u['role']) ?></span></td>
                    <td><?= e($u['status']) ?></td>
                    <td><?= e($u['created_at']) ?></td>
                    <td>
                        <div class="actions">
                            <form method="post" action="<?= url('admin/users/change-role.php') ?>"><?= csrf_field() ?><input
                                    type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="role">
                                    <option <?= $u['role'] === 'user' ? 'selected' : '' ?>>user</option>
                                    <option <?= $u['role'] === 'librarian' ? 'selected' : '' ?>>librarian</option>
                                    <option <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                                </select><button class="btn btn-sm" data-confirm="Change this user's role?">Save</button>
                            </form>
                            <form method="post" action="<?= url('admin/users/change-status.php') ?>">
                                <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <select name="status">
                                    <option <?= $u['status'] === 'active' ? 'selected' : '' ?>>active</option>
                                    <option <?= $u['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                                    <option <?= $u['status'] === 'suspended' ? 'selected' : '' ?>>suspended</option>
                                </select><button class="btn btn-sm btn-secondary">Save</button>
                            </form>
                        </div>
                    </td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>