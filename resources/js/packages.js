export function initPackageTabs() {
    const explorer = document.querySelector('.js-pack-explorer');

    if (!explorer) {
        return;
    }

    const catButtons = explorer.querySelectorAll('.js-pack-cat');
    const catPanels = explorer.querySelectorAll('.js-pack-cat-panel');
    const durationButtons = explorer.querySelectorAll('.js-pack-duration');
    const planPanels = explorer.querySelectorAll('.js-pack-plan');
    const jumpLinks = document.querySelectorAll('.js-pack-jump');
    const growthButtons = document.querySelectorAll('.js-growth-duration');
    const growthCta = document.querySelector('.js-growth-cta');
    const sticky = document.querySelector('.js-pack-sticky');
    const stickyName = document.querySelector('.js-pack-sticky-name');
    const stickyPrice = document.querySelector('.js-pack-sticky-price');
    const stickyCta = document.querySelector('.js-pack-sticky-cta');
    const stickyWa = document.querySelector('.js-pack-sticky-wa');

    const syncSticky = (source) => {
        if (!sticky || !source) {
            return;
        }

        if (stickyName && source.dataset.stickyName) {
            stickyName.textContent = source.dataset.stickyName;
        }
        if (stickyPrice && source.dataset.stickyPrice) {
            stickyPrice.textContent = source.dataset.stickyPrice;
        }
        if (stickyCta && source.dataset.stickyCta) {
            stickyCta.setAttribute('href', source.dataset.stickyCta);
        }
        if (stickyWa && source.dataset.stickyWa) {
            stickyWa.setAttribute('href', source.dataset.stickyWa);
        }
    };

    const setCategory = (id) => {
        catButtons.forEach((item) => {
            const active = item.dataset.cat === id;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        catPanels.forEach((panel) => {
            const show = panel.dataset.cat === id;
            panel.classList.toggle('hidden', !show);
            panel.hidden = !show;
        });
    };

    const setDuration = (cat, duration) => {
        durationButtons.forEach((item) => {
            if (item.dataset.cat !== cat) {
                return;
            }
            const active = item.dataset.duration === duration;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        planPanels.forEach((panel) => {
            if (panel.dataset.cat !== cat) {
                return;
            }
            const show = panel.dataset.duration === duration;
            panel.classList.toggle('hidden', !show);
            panel.hidden = !show;
        });
    };

    catButtons.forEach((tab) => {
        tab.addEventListener('click', () => {
            setCategory(tab.dataset.cat);
            const activeDuration = explorer.querySelector(`.js-pack-duration.is-active[data-cat="${tab.dataset.cat}"]`);
            syncSticky(activeDuration);
        });
    });

    durationButtons.forEach((tab) => {
        tab.addEventListener('click', () => {
            setDuration(tab.dataset.cat, tab.dataset.duration);
            syncSticky(tab);
        });
    });

    jumpLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const cat = link.dataset.cat;
            if (cat === 'growth') {
                document.getElementById('complete-growth')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            setCategory(cat);
            const activeDuration = explorer.querySelector(`.js-pack-duration.is-active[data-cat="${cat}"]`);
            syncSticky(activeDuration);
            document.getElementById('marketing-plans')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    growthButtons.forEach((button) => {
        button.addEventListener('click', () => {
            growthButtons.forEach((item) => {
                item.classList.toggle('is-active', item === button);
            });
            if (growthCta && button.dataset.url) {
                growthCta.setAttribute('href', button.dataset.url);
            }
            const growthWhatsapp = document.querySelector('.js-growth-whatsapp');
            if (growthWhatsapp && button.dataset.whatsapp) {
                growthWhatsapp.setAttribute('href', button.dataset.whatsapp);
            }
            const price = document.querySelector('.js-growth-price');
            const period = document.querySelector('.js-growth-period');
            const benefit = document.querySelector('.js-growth-benefit');
            const stack = document.querySelector('.js-growth-stack');
            if (price && button.dataset.price) {
                price.textContent = button.dataset.price;
            }
            if (period && button.dataset.period) {
                period.textContent = button.dataset.period;
            }
            if (stack && button.dataset.stack) {
                stack.textContent = button.dataset.stack;
            }
            if (benefit) {
                const showYearly = button.dataset.duration === 'Yearly';
                benefit.classList.toggle('hidden', !showYearly);
                benefit.hidden = !showYearly;
            }
            syncSticky(button);
        });
    });

    const defaultDuration = explorer.querySelector('.js-pack-duration.is-active');
    syncSticky(defaultDuration);
}

export function initPackageFields() {
    const forms = document.querySelectorAll('.js-lead-form');

    forms.forEach((form) => {
        const service = form.querySelector('.js-service-select');
        const fields = form.querySelector('.js-package-fields');
        const category = form.querySelector('.js-package-category');
        const duration = form.querySelector('.js-plan-duration');
        const summary = form.querySelector('.js-package-summary');
        const summaryText = form.querySelector('.js-package-summary-text');
        const summaryPrice = form.querySelector('.js-package-summary-price');
        const packageServices = fields ? JSON.parse(fields.getAttribute('data-package-services') || '[]') : [];
        const priceMap = fields ? JSON.parse(fields.getAttribute('data-package-prices') || '{}') : {};
        const locked = fields?.getAttribute('data-locked') === '1';

        if (!service || !fields || locked) {
            return;
        }

        const updateSummary = () => {
            const cat = category?.value || '';
            const dur = duration?.value || '';
            const price = priceMap[cat]?.[dur] || '';

            if (summary && summaryText && summaryPrice) {
                const showSummary = Boolean(cat && dur && price);
                summary.classList.toggle('hidden', !showSummary);
                summary.hidden = !showSummary;
                if (showSummary) {
                    summaryText.textContent = `${cat} · ${dur}`;
                    summaryPrice.textContent = price;
                }
            }
        };

        const sync = () => {
            const show = packageServices.includes(service.value);
            fields.classList.toggle('hidden', !show);
            fields.hidden = !show;

            if (!show && category) {
                category.value = '';
                if (duration) {
                    duration.value = '';
                }
            }

            updateSummary();
        };

        service.addEventListener('change', sync);
        category?.addEventListener('change', updateSummary);
        duration?.addEventListener('change', updateSummary);
        sync();
    });
}
