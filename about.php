<?php
$pageTitle = 'About';
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <span class="chip">About the library</span>
    <h1>A simple place to find and read books</h1>
    <p class="lead">The eLibrary catalogue brings learning and general reading resources together in one searchable
        place.</p>
</section>
<section class="section" id="services">
    <div class="section-head">
        <div>
            <h2>Our Services</h2>
            <p class="muted">Simple tools for finding, reading and discussing books.</p>
        </div>
    </div>
    <div class="services-grid">
        <article class="card service-card">
            <h3>Subject catalogue</h3>
            <p>Browse subjects and see how many books are available in each category.</p>
        </article>
        <article class="card service-card">
            <h3>Online reading and downloads</h3>
            <p>Open supported PDF books in the browser or download them for offline reading.</p>
        </article>
        <article class="card service-card">
            <h3>Personal reading list</h3>
            <p>Save favourite books and keep your reading progress for your next visit.</p>
        </article>
        <article class="card service-card">
            <h3>Ratings and reviews</h3>
            <p>Share a rating and short review to help other learners choose a book.</p>
        </article>
    </div>
</section>
<section class="section" id="references">
    <h2>References</h2>
    <p class="muted">Official documentation consulted for the technologies used in this project.</p>
    <ul class="reference-list">
        <li><a href="https://www.php.net/manual/en/" target="_blank" rel="noopener noreferrer">PHP Manual</a> <span
                class="muted">— server-side programming</span></li>
        <li><a href="https://dev.mysql.com/doc/refman/8.0/en/" target="_blank" rel="noopener noreferrer">MySQL 8.0
                Reference Manual</a> <span class="muted">— relational database and SQL</span></li>
        <li><a href="https://developer.mozilla.org/en-US/docs/Web" target="_blank" rel="noopener noreferrer">MDN Web
                Docs</a> <span class="muted">— HTML, CSS and JavaScript</span></li>
        <li><a href="https://mozilla.github.io/pdf.js/getting_started/" target="_blank" rel="noopener noreferrer">PDF.js
                Getting Started</a> <span class="muted">— browser PDF reading</span></li>
    </ul>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>