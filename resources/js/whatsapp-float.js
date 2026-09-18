export function initWhatsappFloat() {
    const root = document.querySelector('[data-wa-widget]');
    if (!root) {
        return;
    }

    const panel = root.querySelector('#waWidgetPanel');
    const toggle = root.querySelector('[data-wa-toggle]');
    const closeBtn = root.querySelector('[data-wa-close]');
    if (!panel || !toggle) {
        return;
    }

    const setOpen = (open) => {
        root.classList.toggle('is-open', open);
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close WhatsApp chat' : 'Open WhatsApp chat');
        if (open) {
            closeBtn?.focus();
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(panel.hidden);
    });

    closeBtn?.addEventListener('click', () => {
        setOpen(false);
        toggle.focus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.classList.contains('is-open')) {
            setOpen(false);
            toggle.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!root.classList.contains('is-open')) {
            return;
        }
        if (!root.contains(event.target)) {
            setOpen(false);
        }
    });
}
