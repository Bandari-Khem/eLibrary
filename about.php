<?php $pageTitle = 'About';
require __DIR__ . '/includes/header.php';
$reviews = [];
try {
    $reviews = db()->query("SELECT r.rating,r.review_text,u.full_name,b.title FROM reviews r JOIN users u ON u.id=r.user_id JOIN books b ON b.id=r.book_id WHERE r.status='approved' ORDER BY r.created_at DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) {
} ?>
<section class="section">
    <h1>About E-Library</h1>
    <p>E-Library is a web-based digital library designed for students and faculty to discover, read and manage
        electronic learning resources. The catalogue is organized around a practical BCA semester-and-subject structure
        while allowing a limited set of other useful books.</p>
    <div class="grid grid-3">
        <div class="card">
            <h3>Readers</h3>
            <p>Browse, search, read, download, bookmark, favourite and review books.</p>
        </div>
        <div class="card">
            <h3>Librarians</h3>
            <p>Manage books, authors, categories, files and review moderation.</p>
        </div>
        <div class="card">
            <h3>Administrators</h3>
            <p>Manage users, roles, settings, reports, lifecycle actions and system activity.</p>
        </div>
    </div>
</section>
<section class="section">
    <h2>Design references</h2>
    <div class="card">
        <p>The system takes practical inspiration from established digital-library patterns such as catalogue search,
            subject browsing, online reading, personal lists and controlled access. The project is intentionally focused
            on a defined learning collection rather than attempting to reproduce a global book index.</p>
        <p class="muted">Reference services considered during design: Google Books, Project Gutenberg, OpenStax and Open
            Library/Internet Archive.</p>
    </div>
</section><?php if ($reviews): ?><section class="section">
        <h2>Reader reviews</h2>
        <div class="grid grid-3"><?php foreach ($reviews as $r): ?><article class="card">
                    <div class="stars"><?= str_repeat('★', (int)$r['rating']) ?><?= str_repeat('☆', 5 - (int)$r['rating']) ?>
                    </div>
                    <p><?= e($r['review_text'] ?: 'Rated this book.') ?></p>
                    <p class="muted">— <?= e($r['full_name']) ?> · <?= e($r['title']) ?></p>
                </article><?php endforeach; ?></div>
    </section><?php endif; ?><?php require __DIR__ . '/includes/footer.php'; ?>