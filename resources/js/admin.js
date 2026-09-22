document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('[data-admin-shell]');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    const closeSidebar = () => shell?.classList.remove('is-sidebar-open');
    const openSidebar = () => shell?.classList.add('is-sidebar-open');

    toggle?.addEventListener('click', () => {
        shell?.classList.toggle('is-sidebar-open');
    });
    backdrop?.addEventListener('click', closeSidebar);

    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (event) => {
            const message = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-copy]').forEach((el) => {
        el.addEventListener('click', async () => {
            const value = el.getAttribute('data-copy');
            if (!value) return;
            try {
                await navigator.clipboard.writeText(value);
                el.textContent = 'Copied';
                setTimeout(() => { el.textContent = 'Copy URL'; }, 1200);
            } catch (_) {
                // ignore
            }
        });
    });

    const planList = document.querySelector('[data-plan-list]');
    const planTemplate = document.getElementById('plan-row-template');
    const planAdd = document.querySelector('[data-plan-add]');

    const nextPlanIndex = () => {
        const indexes = [...(planList?.querySelectorAll('[data-plan-row]') || [])]
            .map((row) => {
                const name = row.querySelector('input, textarea')?.getAttribute('name') || '';
                const match = name.match(/plans\[(\d+)\]/);
                return match ? Number(match[1]) : -1;
            })
            .filter((n) => n >= 0);
        return indexes.length ? Math.max(...indexes) + 1 : 0;
    };

    planAdd?.addEventListener('click', () => {
        if (!planList || !planTemplate) return;
        const index = nextPlanIndex();
        const html = planTemplate.innerHTML.replaceAll('__INDEX__', String(index));
        planList.insertAdjacentHTML('beforeend', html);
    });

    planList?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-plan-remove]');
        if (!button) return;
        const row = button.closest('[data-plan-row]');
        if (!row) return;
        const rows = planList.querySelectorAll('[data-plan-row]');
        if (rows.length <= 1) {
            row.querySelectorAll('input, textarea').forEach((field) => {
                field.value = '';
            });
            return;
        }
        row.remove();
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrap = button.closest('.admin-password');
            const input = wrap?.querySelector('input');
            if (!input) return;

            const showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            button.setAttribute('aria-pressed', showing ? 'true' : 'false');
            button.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
            button.setAttribute('title', showing ? 'Hide password' : 'Show password');
            wrap?.classList.toggle('is-visible', showing);
        });
    });
});
