export function initNavbar() {
    const nav = document.getElementById('site-nav');
    const openBtn = document.getElementById('menu-open');
    const closeBtn = document.getElementById('menu-close');
    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-overlay');

    if (nav) {
        const onScroll = () => {
            const compact = window.scrollY > 12;
            nav.classList.toggle('is-scrolled', compact);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    if (!openBtn || !closeBtn || !menu || !overlay) {
        return;
    }

    const setOpen = (open) => {
        menu.classList.toggle('is-open', open);
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        overlay.classList.toggle('is-open', open);
        overlay.hidden = !open;
        document.body.classList.toggle('overflow-hidden', open);
    };

    openBtn.addEventListener('click', () => setOpen(true));
    closeBtn.addEventListener('click', () => setOpen(false));
    overlay.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
}
