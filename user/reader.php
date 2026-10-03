<?php $pageTitle = 'Reader';
require __DIR__ . '/../includes/header.php';
require_login();
$book = (int)($_GET['book'] ?? 0);
$file = (int)($_GET['file'] ?? 0);
$s = db()->prepare("SELECT b.*,bf.id file_id,bf.file_type FROM books b JOIN book_files bf ON bf.book_id=b.id WHERE b.id=? AND b.status='published' AND bf.id=?");
if ($file) {
    $s->execute([$book, $file]);
} else {
    $s = db()->prepare("SELECT b.*,bf.id file_id,bf.file_type FROM books b JOIN book_files bf ON bf.book_id=b.id WHERE b.id=? AND b.status='published' AND bf.file_type='pdf' ORDER BY bf.is_main DESC,bf.id LIMIT 1");
    $s->execute([$book]);
}
$b = $s->fetch();
if (!$b || $b['file_type'] !== 'pdf') {
    flash('error', 'Only published PDF files can be opened in the browser reader.');
    redirect('book-details.php?id=' . $book);
}
$uid = (int)current_user()['id'];
$p = db()->prepare('SELECT current_page,total_pages FROM reading_progress WHERE user_id=? AND book_id=?');
$p->execute([$uid, $book]);
$prog = $p->fetch() ?: ['current_page' => 1, 'total_pages' => null]; ?>
<section class="section">
    <div class="section-head">
        <div>
            <h1><?= e($b['title']) ?></h1>
            <p class="muted">Progress is saved automatically.</p>
        </div><a class="btn btn-secondary" href="<?= url('book-details.php?id=' . $book) ?>">Back</a>
    </div>
    <div class="reader-shell">
        <div class="reader-toolbar">
            <div><button id="prev" class="btn btn-sm btn-secondary">←</button> <button id="next"
                    class="btn btn-sm btn-secondary">→</button> <span>Page <span id="page_num">1</span> / <span
                        id="page_count">?</span></span></div>
        </div>
        <div class="reader-canvas"><canvas id="pdf-canvas"></canvas></div>
    </div>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs" type="module"></script>
<script type="module">
    import * as pdfjsLib from 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs';
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.worker.min.mjs';
    const url = <?= json_encode(url('user/download.php?file=' . $b['file_id'] . '&inline=1')) ?>;
    let pdfDoc = null,
        pageNum = <?= max(1, (int)$prog['current_page']) ?>,
        pageRendering = false,
        pageNumPending = null;
    const canvas = document.getElementById('pdf-canvas'),
        ctx = canvas.getContext('2d');
    async function renderPage(n) {
        pageRendering = true;
        const p = await pdfDoc.getPage(n);
        const vp = p.getViewport({
            scale: 1.25
        });
        canvas.height = vp.height;
        canvas.width = vp.width;
        await p.render({
            canvasContext: ctx,
            viewport: vp
        }).promise;
        document.getElementById('page_num').textContent = n;
        pageRendering = false;
        if (pageNumPending !== null) {
            renderPage(pageNumPending);
            pageNumPending = null;
        }
        saveProgress(n, pdfDoc.numPages);
    }

    function queue(n) {
        if (n < 1 || n > pdfDoc.numPages) return;
        if (pageRendering) pageNumPending = n;
        else renderPage(n);
    }
    async function saveProgress(page, total) {
        try {
            await fetch(<?= json_encode(url('user/api/save-progress.php')) ?>, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    csrf_token: <?= json_encode(csrf_token()) ?>,
                    book_id: <?= $book ?>,
                    current_page: page,
                    total_pages: total
                })
            });
        } catch (e) {}
    }
    document.getElementById('prev').onclick = () => {
        if (pageNum > 1) {
            pageNum--;
            queue(pageNum)
        }
    };
    document.getElementById('next').onclick = () => {
        if (pdfDoc && pageNum < pdfDoc.numPages) {
            pageNum++;
            queue(pageNum)
        }
    };
    pdfjsLib.getDocument(url).promise.then(p => {
        pdfDoc = p;
        document.getElementById('page_count').textContent = p.numPages;
        pageNum = Math.min(pageNum, p.numPages);
        renderPage(pageNum);
    });
</script><?php require __DIR__ . '/../includes/footer.php'; ?>