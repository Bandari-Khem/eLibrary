document.addEventListener('DOMContentLoaded', () => {
    const t = document.querySelector('.nav-toggle'),
        n = document.querySelector('.nav-links');

    const closeNav = () => {
        if (n) n.classList.remove('open');
        if (t) t.setAttribute('aria-expanded', 'false');
    };

    if (t && n) t.addEventListener('click', () => {
        const open = n.classList.toggle('open');
        t.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', e => { if (n && n.classList.contains('open') && !n.contains(e.target) && !t.contains(e.target)) closeNav(); });

    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeNav(); document.querySelector('.side-nav.open')?.classList.remove('open'); } });

    const s = document.querySelector('.side-toggle'),
        side = document.querySelector('.side-nav'); if (s && side) s.addEventListener('click', () => side.classList.toggle('open'));

    document.addEventListener('click', e => { if (side && side.classList.contains('open') && !side.contains(e.target) && s && !s.contains(e.target)) side.classList.remove('open'); });

    document.querySelectorAll('[data-confirm]').forEach(el => el.addEventListener('click', e => { if (!confirm(el.dataset.confirm)) e.preventDefault(); }));

    const cs = document.querySelector('[data-category-search]'),
        sel = document.querySelector('#category');
    if (cs && sel) {
        cs.addEventListener('input', () => {
            const q = cs.value.trim().toLowerCase();
            [...sel.options].forEach((o, i) => { if (i === 0) { o.hidden = false; return; } o.hidden = q !== '' && !((o.dataset.categoryName || '').includes(q)); });
        });
    }
});