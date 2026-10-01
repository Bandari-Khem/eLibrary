<?php $pageTitle = 'Activity Logs';
require __DIR__ . '/../../includes/admin-layout.php';
$q = trim($_GET['q'] ?? '');
$action = trim($_GET['action'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
$where = [];
$p = [];
if ($q !== '') {
    $where[] = '(u.email LIKE ? OR al.ip_address LIKE ? OR al.details LIKE ? OR al.action LIKE ?)';
    $like = "%$q%";
    array_push($p, $like, $like, $like, $like);
}
if ($action !== '') {
    $where[] = 'al.action=?';
    $p[] = $action;
}
if ($from !== '') {
    $where[] = 'DATE(al.created_at)>=?';
    $p[] = $from;
}
if ($to !== '') {
    $where[] = 'DATE(al.created_at)<=?';
    $p[] = $to;
}
$sql = 'SELECT al.*,u.email FROM activity_logs al LEFT JOIN users u ON u.id=al.user_id' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY al.created_at DESC LIMIT 500';
$s = db()->prepare($sql);
$s->execute($p);
$rows = $s->fetchAll();
$actions = db()->query('SELECT DISTINCT action FROM activity_logs ORDER BY action')->fetchAll(PDO::FETCH_COLUMN); ?>
<section class="section">
    <div class="section-head">
        <h1>Activity Logs</h1><span class="chip"><?= count($rows) ?> records</span>
    </div>
    <form method="get" class="form-grid">
        <div class="field">
            <label>Search</label><input name="q" value="<?= e($q) ?>" placeholder="Email, IP, action or details">
        </div>
        <div class="field">
            <label>Action</label>
            <select name="action">
                <option value="">All actions</option><?php foreach ($actions as $a): ?><option
                        <?= $action === $a ? 'selected' : '' ?>><?= e($a) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>From</label><input type="date" name="from" value="<?= e($from) ?>">
        </div>
        <div class="field">
            <label>To</label>
            <input type="date" name="to" value="<?= e($to) ?>">
        </div>
        <div>
            <button class="btn">Filter</button>
            <a class="btn btn-secondary" href="<?= url('admin/activity-logs/index.php') ?>">Reset</a>
        </div>
    </form>
    <div class="table-wrap">
        <table class="table">
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Target</th>
                <th>IP</th>
                <th>Details</th>
            </tr><?php foreach ($rows as $r): ?><tr>
                    <td><?= e($r['created_at']) ?></td>
                    <td><?= e($r['email'] ?? 'System/Guest') ?></td>
                    <td><?= e($r['action']) ?></td>
                    <td><?= e(($r['target_type'] ?? '') . ' ' . ($r['target_id'] ?? '')) ?></td>
                    <td><?= e($r['ip_address']) ?></td>
                    <td><?= e($r['details']) ?></td>
                </tr><?php endforeach; ?>
        </table>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>