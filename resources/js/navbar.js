const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function initNavbar() {
    const nav = document.getElementById('site-nav');
    const openButton = document.getElementById('menu-open');
    const closeButton = document.getElementById('menu-close');
    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-overlay');
    const desktopQuery = window.matchMedia('(min-width: 1400px)');
    const dropdowns = [...document.querySelectorAll('[data-nav-dropdown]')];
    let menuOpen = false;
    let previousFocus = null;

    const setScrolled = () => nav?.classList.toggle('is-scrolled', window.scrollY > 12);

    const setDropdown = (dropdown, open, restoreFocus = false) => {
        const toggle = dropdown.querySelector('[data-nav-dropdown-toggle]');
        const panel = dropdown.querySelector('[data-nav-dropdown-menu]');
        if (!toggle || !panel) return;

        dropdown.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (!open && restoreFocus) toggle.focus();
    };

    const closeDropdowns = (except = null) => {
        dropdowns.forEach((dropdown) => {
            if (dropdown !== except) setDropdown(dropdown, false);
        });
    };

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-nav-dropdown-toggle]');
        const panel = dropdown.querySelector('[data-nav-dropdown-menu]');
        if (!toggle || !panel) return;

        toggle.addEventListener('click', () => {
            const opening = toggle.getAttribute('aria-expanded') !== 'true';
            closeDropdowns(dropdown);
            setDropdown(dropdown, opening);
            if (opening) panel.querySelector(focusableSelector)?.focus();
        });

        dropdown.addEventListener('pointerenter', (event) => {
            if (event.pointerType === 'mouse' && desktopQuery.matches) {
                closeDropdowns(dropdown);
                setDropdown(dropdown, true);
            }
        });

        dropdown.addEventListener('pointerleave', (event) => {
            if (event.pointerType === 'mouse' && desktopQuery.matches && !dropdown.contains(document.activeElement)) {
                setDropdown(dropdown, false);
            }
        });

        dropdown.addEventListener('focusin', () => {
            if (desktopQuery.matches) {
                closeDropdowns(dropdown);
                setDropdown(dropdown, true);
            }
        });

        dropdown.addEventListener('focusout', () => {
            window.setTimeout(() => {
                if (!dropdown.contains(document.activeElement)) setDropdown(dropdown, false);
            }, 0);
        });
    });

    const setMobileSubmenu = (toggle, open) => {
        const panel = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!panel) return;

        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.closest('.mobile-menu-item')?.classList.toggle('is-expanded', open);
        panel.hidden = !open;
    };

    document.querySelectorAll('[data-mobile-submenu-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const opening = toggle.getAttribute('aria-expanded') !== 'true';
            document.querySelectorAll('[data-mobile-submenu-toggle]').forEach((other) => {
                if (other !== toggle) setMobileSubmenu(other, false);
            });
            setMobileSubmenu(toggle, opening);
        });
    });

    const setMenu = (open, restoreFocus = true) => {
        if (!openButton || !menu || !overlay) return;

        menuOpen = open;
        menu.classList.toggle('is-open', open);
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        overlay.classList.toggle('is-open', open);
        overlay.hidden = !open;
        document.body.classList.toggle('nav-drawer-open', open);

        if (open) {
            previousFocus = document.activeElement;
            closeButton?.focus();
        } else if (restoreFocus && previousFocus instanceof HTMLElement) {
            previousFocus.focus();
        }
    };

    openButton?.addEventListener('click', () => setMenu(true));
    closeButton?.addEventListener('click', () => setMenu(false));
    overlay?.addEventListener('click', () => setMenu(false));
    menu?.querySelectorAll('a[href]').forEach((link) => link.addEventListener('click', () => setMenu(false, false)));

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-nav-dropdown]')) closeDropdowns();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const openDropdown = dropdowns.find((dropdown) => dropdown.classList.contains('is-open'));
            if (openDropdown) {
                event.preventDefault();
                setDropdown(openDropdown, false, true);
                return;
            }
            if (menuOpen) {
                event.preventDefault();
                setMenu(false);
            }
        }

        if (event.key === 'Tab' && menuOpen && menu) {
            const focusable = [...menu.querySelectorAll(focusableSelector)].filter((element) => !element.closest('[hidden]'));
            if (focusable.length === 0) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    desktopQuery.addEventListener('change', (event) => {
        closeDropdowns();
        if (event.matches && menuOpen) setMenu(false, false);
    });

    setScrolled();
    window.addEventListener('scroll', setScrolled, { passive: true });
}
