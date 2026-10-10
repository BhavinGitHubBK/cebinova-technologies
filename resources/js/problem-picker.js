export function initProblemPicker() {
    document.querySelectorAll('[data-problem-picker]').forEach((root) => {
        const tabs = [...root.querySelectorAll('[role="tab"]')];
        const panels = [...root.querySelectorAll('[role="tabpanel"]')];

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const panelId = tab.getAttribute('aria-controls');

                tabs.forEach((item) => {
                    const selected = item === tab;
                    item.setAttribute('aria-selected', selected ? 'true' : 'false');
                    item.classList.toggle('is-active', selected);
                });

                panels.forEach((panel) => {
                    const active = panel.id === panelId;
                    panel.hidden = !active;
                    panel.classList.toggle('is-active', active);
                });
            });
        });
    });
}
