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

    if (n) n.querySelectorAll('a').forEach(link => link.addEventListener('click', closeNav));

    document.addEventListener('click', e => { if (n && t && n.classList.contains('open') && !n.contains(e.target) && !t.contains(e.target)) closeNav(); });

    const s = document.querySelector('.side-toggle');
    const side = document.querySelector('.side-nav');
    const closeSide = () => {
        if (side) side.classList.remove('open');
        if (s) s.setAttribute('aria-expanded', 'false');
    };

    if (s && side) s.addEventListener('click', () => {
        const open = side.classList.toggle('open');
        s.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    if (side) side.querySelectorAll('a').forEach(link => link.addEventListener('click', closeSide));

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeNav();
            closeSide();
        }
    });

    document.addEventListener('click', e => { if (side && s && side.classList.contains('open') && !side.contains(e.target) && !s.contains(e.target)) closeSide(); });

    document.querySelectorAll('[data-confirm]').forEach(el => el.addEventListener('click', e => { if (!confirm(el.dataset.confirm)) e.preventDefault(); }));

    document.querySelectorAll('input[type="file"][name="cover_image"]').forEach(input => {
        input.addEventListener('change', () => {
            const marker = input.form?.querySelector('input[name="cover_selected"]');
            if (marker) marker.value = input.files?.length ? '1' : '0';
        });
    });

    const cs = document.querySelector('[data-category-search]'),
        sel = document.querySelector('#category');
    if (cs && sel) {
        cs.addEventListener('input', () => {
            const q = cs.value.trim().toLowerCase();
            [...sel.options].forEach((o, i) => { if (i === 0) { o.hidden = false; return; } o.hidden = q !== '' && !((o.dataset.categoryName || '').includes(q)); });
        });
    }
});
