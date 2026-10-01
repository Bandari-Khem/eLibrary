<?php $pageTitle = 'FAQ';
require __DIR__ . '/../../includes/admin-layout.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'add';
    if ($action === 'add') {
        db()->prepare('INSERT INTO faq(question,answer,display_order,status) 
        VALUES(?,?,?,?)')->execute([trim($_POST['question'] ?? ''), trim($_POST['answer'] ?? ''), (int)($_POST['display_order'] ?? 0), isset($_POST['status']) ? 1 : 0]);
        flash('success', 'FAQ added.');
    } elseif ($action === 'toggle') {
        db()->prepare('UPDATE faq SET status=NOT status WHERE id=?')->execute([(int)$_POST['id']]);
        flash('success', 'FAQ status changed.');
    }
    redirect('admin/faq/index.php');
}
$rows = db()->query('SELECT * FROM faq ORDER BY display_order,id')->fetchAll(); ?>
<section class="section">
    <div class="grid grid-2">
        <div class="form-card">
            <h1>Add FAQ</h1>
            <form method="post"><?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <div class="field">
                    <label>Question</label>
                    <input name="question" required maxlength="255">
                </div>
                <div class="field">
                    <label>Answer</label>
                    <textarea name="answer" required></textarea>
                </div>
                <div class="field">
                    <label>Display order</label>
                    <input type="number" name="display_order" value="0">
                </div>
                <label>
                    <input type="checkbox" name="status" checked> Active</label><br><br>
                <button class="btn">Add
                    FAQ</button>
            </form>
        </div>
        <div class="grid">
            <?php foreach ($rows as $r): ?>
            <article class="card"><strong><?= e($r['question']) ?></strong>
                <p><?= nl2br(e($r['answer'])) ?></p><span class="chip">order <?= $r['display_order'] ?> ·
                    <?= $r['status'] ? 'active' : 'hidden' ?></span>
                <form method="post" style="margin-top:10px">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button class="btn btn-sm btn-secondary">Toggle</button>
                </form>
            </article><?php endforeach; ?>
        </div>
    </div>
</section><?php require __DIR__ . '/../../includes/panel-footer.php'; ?>