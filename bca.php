<?php
$pageTitle = 'BCA';
require __DIR__ . '/includes/header.php';

$bca = db()->prepare("SELECT id,name,description FROM categories WHERE parent_id IS NULL AND LOWER(name)='bca' LIMIT 1");
$bca->execute();
$bca = $bca->fetch();
$semesters = [];
if ($bca) {
    $s = db()->prepare('SELECT id,name,description FROM categories WHERE parent_id=? ORDER BY id');
    $s->execute([(int)$bca['id']]);
    $semesters = $s->fetchAll();
}
?>
<section class="section">
    <div class="section-head">
        <div>
            <h1>BCA Learning Resources</h1>
            <p class="muted">Browse books by semester and subject.</p>
        </div><a class="btn btn-secondary" href="<?= url('books.php') ?>">All books</a>
    </div>
    <?php if (!$bca): ?>
        <div class="empty">The BCA category has not been configured yet.</div>
    <?php elseif (!$semesters): ?>
        <div class="empty">No BCA semesters have been configured yet.</div>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($semesters as $semester):
                $ss = db()->prepare('SELECT id,name,description FROM categories WHERE parent_id=? ORDER BY name');
                $ss->execute([(int)$semester['id']]);
                $subjects = $ss->fetchAll();
            ?>
                <article class="card">
                    <h2><?= e($semester['name']) ?></h2>
                    <?php if ($semester['description']): ?><p class="muted"><?= e($semester['description']) ?></p>
                    <?php endif; ?>
                    <?php if ($subjects): ?><div class="subject-list"><?php foreach ($subjects as $subject): ?><a
                                    class="chip subject-chip"
                                    href="<?= url('books.php?category=' . (int)$subject['id']) ?>"><?= e($subject['name']) ?></a><?php endforeach; ?>
                        </div><?php else: ?><p class="muted">No subjects configured.</p><?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>