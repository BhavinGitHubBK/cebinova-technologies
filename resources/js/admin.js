function adminAlert(title, text, icon) {
    if (typeof Swal === 'undefined') {
        window.alert(text || title);
        return Promise.resolve();
    }
    return Swal.fire({
        title,
        text,
        icon,
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: { popup: 'admin-swal', confirmButton: 'admin-swal-btn' },
    });
}

function adminListUrl(from) {
    const marked = document.querySelector('[data-admin-page]')?.getAttribute('data-admin-list');
    if (marked) return marked;
    let url;
    try {
        url = new URL(from || window.location.href, window.location.origin);
    } catch (error) {
        return null;
    }
    const original = url.pathname.replace(/\/+$/, '').split('/').filter(Boolean);
    if (original[0] !== 'admin' || original.length < 3) return null;
    const parts = original.slice();
    const tail = parts[parts.length - 1];
    if (tail === 'edit' || tail === 'create') parts.pop();
    if (parts.length >= 3 && /^\d+$/.test(parts[parts.length - 1])) parts.pop();
    if (parts.length !== 2) return null;
    const list = new URL(`/${parts.join('/')}`, window.location.origin);
    const group = url.searchParams.get('group')
        || document.querySelector('[data-package-group]')?.getAttribute('data-package-group');
    if (group === 'marketing') list.searchParams.set('group', 'marketing');
    return `${list.pathname}${list.search}`;
}

function adminSavedAlert(text, listUrl) {
    if (!listUrl) return adminAlert('Done', text, 'success').then(() => 'ok');
    if (typeof Swal === 'undefined') {
        window.alert(text || 'Saved.');
        return Promise.resolve('ok');
    }
    return Swal.fire({
        title: 'Done',
        text,
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: 'OK',
        cancelButtonText: 'View list',
        buttonsStyling: false,
        customClass: { popup: 'admin-swal', confirmButton: 'admin-swal-btn', cancelButton: 'admin-swal-btn is-ghost' },
    }).then((result) => (result.dismiss === Swal.DismissReason.cancel ? 'list' : 'ok'));
}

function openActivityStory(button) {
    let story;
    try {
        story = JSON.parse(button.getAttribute('data-story') || '{}');
    } catch (error) {
        return;
    }
    const rows = Array.isArray(story.rows) ? story.rows : [];
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[char]));
    const html = rows.map((row) => {
        const single = story.mode !== 'edit';
        const valueLabel = story.mode === 'add' ? 'Added' : (story.mode === 'remove' ? 'Removed' : '');
        const values = single
            ? `<div><span>${valueLabel}</span><p>${escapeHtml(story.mode === 'add' ? row.after : row.before)}</p></div>`
            : `<div><span>Before</span><p>${escapeHtml(row.before)}</p></div><div><span>After</span><p>${escapeHtml(row.after)}</p></div>`;
        return `<div class="activity-modal-row"><strong>${escapeHtml(row.label)}</strong><div class="activity-modal-values${single ? ' is-single' : ''}">${values}</div></div>`;
    }).join('');
    Swal.fire({
        title: story.title || 'Changes',
        html,
        width: '42rem',
        confirmButtonText: 'Close',
        buttonsStyling: false,
        customClass: { popup: 'admin-swal activity-swal', confirmButton: 'admin-swal-btn' },
    });
}

function adminConfirm(text) {
    if (typeof Swal === 'undefined') return Promise.resolve(window.confirm(text));
    return Swal.fire({
        title: 'Please confirm',
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: { popup: 'admin-swal', confirmButton: 'admin-swal-btn', cancelButton: 'admin-swal-btn is-ghost' },
    }).then((result) => result.isConfirmed);
}

function leadBadgeClass(status) {
    return {
        New: 'is-new',
        Contacted: 'is-contacted',
        'Follow-up': 'is-follow',
        Qualified: 'is-follow',
        'Proposal Sent': 'is-follow',
        Converted: 'is-won',
        Won: 'is-won',
        Lost: 'is-lost',
    }[status] || '';
}

function formFieldChanged(field) {
    if (!field.name || field.disabled) return false;
    if (field.type === 'hidden' || field.name === '_token' || field.name === '_method') return false;
    if (field.type === 'checkbox' || field.type === 'radio') return field.checked !== field.defaultChecked;
    if (field instanceof HTMLSelectElement) {
        return [...field.options].some((option) => option.selected !== option.defaultSelected);
    }
    return field.value !== field.defaultValue;
}

function adminFormUnchanged(form) {
    const fields = [...form.querySelectorAll('input, select, textarea')];
    const editable = fields.some((field) => field.name && !field.disabled && field.type !== 'hidden' && field.name !== '_token' && field.name !== '_method');
    if (!editable) return false;
    return !fields.some(formFieldChanged);
}

function rememberFormDefaults(form) {
    form.querySelectorAll('input, textarea').forEach((field) => {
        if (field.type === 'checkbox' || field.type === 'radio') field.defaultChecked = field.checked;
        else if (field.type !== 'hidden' && field.type !== 'file') field.defaultValue = field.value;
    });
    form.querySelectorAll('select option').forEach((option) => {
        option.defaultSelected = option.selected;
    });
}

function rememberLeadForm(form) {
    const status = form.querySelector('[name="status"]')?.value || '';
    const previous = form.dataset.currentStatus || '';
    rememberFormDefaults(form);
    const badge = document.querySelector('.lead-detail-meta .admin-badge');
    if (status && badge) {
        badge.className = 'admin-badge ' + leadBadgeClass(status);
        badge.textContent = status;
    }
    if (previous !== status) {
        const count = document.querySelector('[data-new-count]');
        const nav = document.querySelector('.admin-nav-count');
        let next = Number(count?.textContent || nav?.querySelector('strong')?.textContent || 0);
        if (previous === 'New') next -= 1;
        if (status === 'New') next += 1;
        next = Math.max(0, next);
        if (count) count.textContent = String(next);
        if (nav) {
            const strong = nav.querySelector('strong');
            if (next <= 0) nav.hidden = true;
            else if (strong) {
                nav.hidden = false;
                strong.textContent = String(next);
            }
        }
    }
    form.dataset.currentStatus = status;
}

function applyAdminUpdate(form, data) {
    if (!Array.isArray(data.ids) || !data.status) return;
    const count = document.querySelector('[data-new-count]');
    const nav = document.querySelector('.admin-nav-count');
    let next = Number(count?.textContent || nav?.querySelector('strong')?.textContent || 0);
    data.ids.forEach((id) => {
        const row = form.querySelector('[data-lead-id="' + id + '"]');
        if (!row) return;
        if (row.dataset.leadStatus === 'New' && data.status !== 'New') next -= 1;
        if (row.dataset.leadStatus !== 'New' && data.status === 'New') next += 1;
        row.dataset.leadStatus = data.status;
        const badge = row.querySelector('[data-lead-badge]');
        if (badge) {
            badge.className = 'admin-badge ' + leadBadgeClass(data.status);
            badge.textContent = data.status;
        }
        const box = row.querySelector('input[type="checkbox"]');
        if (box) box.checked = false;
    });
    if (count) count.textContent = String(Math.max(0, next));
    if (nav) {
        const strong = nav.querySelector('strong');
        if (next <= 0) nav.hidden = true;
        else if (strong) {
            nav.hidden = false;
            strong.textContent = String(next);
            nav.title = next + ' new ' + (next === 1 ? 'lead' : 'leads') + ' waiting to be contacted';
        }
    }
}

function mountAdminDates() {
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const parseValue = (input) => {
        const match = /^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}):(\d{2}))?/.exec(input.value);
        if (!match) return null;
        return {
            year: Number(match[1]),
            month: Number(match[2]) - 1,
            day: Number(match[3]),
            hour: match[4] ?? '00',
            minute: match[5] ?? '00',
        };
    };

    const pad = (value) => String(value).padStart(2, '0');

    document.querySelectorAll('input[type="date"], input[type="datetime-local"]').forEach((input) => {
        if (input.closest('.admin-date')) return;
        const withTime = input.type === 'datetime-local';
        const wrap = document.createElement('div');
        wrap.className = 'admin-date';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.classList.add('admin-date-native');

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'admin-date-button';
        button.innerHTML = '<span></span><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 10h17M8 3.5v4M16 3.5v4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>';
        wrap.appendChild(button);

        const pop = document.createElement('div');
        pop.className = 'admin-date-pop';
        pop.hidden = true;
        wrap.appendChild(pop);

        let view = parseValue(input) || (() => {
            const now = new Date();
            return { year: now.getFullYear(), month: now.getMonth(), day: now.getDate(), hour: pad(now.getHours()), minute: pad(now.getMinutes()) };
        })();

        const label = () => {
            const selected = parseValue(input);
            const text = button.querySelector('span');
            if (!selected) {
                button.classList.add('is-empty');
                text.textContent = withTime ? 'Choose date and time' : 'Choose a date';
                return;
            }
            button.classList.remove('is-empty');
            text.textContent = pad(selected.day) + ' ' + shortMonths[selected.month] + ' ' + selected.year + (withTime ? ' · ' + selected.hour + ':' + selected.minute : '');
        };

        const write = (day) => {
            const selected = parseValue(input);
            const hour = withTime ? (pop.querySelector('[data-date-hour]')?.value || selected?.hour || '09') : '00';
            const minute = withTime ? (pop.querySelector('[data-date-minute]')?.value || selected?.minute || '00') : '00';
            input.value = view.year + '-' + pad(view.month + 1) + '-' + pad(day) + (withTime ? 'T' + hour + ':' + minute : '');
            input.dispatchEvent(new Event('change', { bubbles: true }));
            label();
        };

        const paint = () => {
            const selected = parseValue(input);
            const first = new Date(view.year, view.month, 1);
            const start = (first.getDay() + 6) % 7;
            const days = new Date(view.year, view.month + 1, 0).getDate();
            const today = new Date();
            let cells = '';
            for (let i = 0; i < start; i += 1) cells += '<span></span>';
            for (let day = 1; day <= days; day += 1) {
                const isSelected = selected && selected.year === view.year && selected.month === view.month && selected.day === day;
                const isToday = today.getFullYear() === view.year && today.getMonth() === view.month && today.getDate() === day;
                cells += '<button type="button" class="admin-date-day' + (isSelected ? ' is-selected' : '') + (isToday ? ' is-today' : '') + '" data-day="' + day + '">' + day + '</button>';
            }
            pop.innerHTML = '<div class="admin-date-head"><button type="button" data-date-nav="-1" aria-label="Previous month">‹</button><strong>' + months[view.month] + ' ' + view.year + '</strong><button type="button" data-date-nav="1" aria-label="Next month">›</button></div><div class="admin-date-week"><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span></div><div class="admin-date-grid">' + cells + '</div>' + (withTime ? '<div class="admin-date-time"><label>Time<input type="time" data-date-time value="' + (selected ? selected.hour + ':' + selected.minute : '09:00') + '"></label></div>' : '') + '<div class="admin-date-foot"><button type="button" data-date-clear>Clear</button><button type="button" data-date-today>Today</button></div>';
        };

        const close = () => { pop.hidden = true; };
        const open = () => {
            document.querySelectorAll('.admin-date-pop').forEach((other) => { other.hidden = true; });
            const selected = parseValue(input);
            if (selected) view = { ...selected };
            paint();
            pop.hidden = false;
        };

        button.addEventListener('click', (event) => {
            event.stopPropagation();
            if (pop.hidden) open();
            else close();
        });

        pop.addEventListener('click', (event) => {
            event.stopPropagation();
            const nav = event.target.closest('[data-date-nav]');
            const day = event.target.closest('[data-day]');
            if (nav) {
                const next = new Date(view.year, view.month + Number(nav.dataset.dateNav), 1);
                view.year = next.getFullYear();
                view.month = next.getMonth();
                paint();
                return;
            }
            if (day) {
                write(Number(day.dataset.day));
                if (!withTime) close();
                return;
            }
            if (event.target.closest('[data-date-clear]')) {
                input.value = '';
                input.dispatchEvent(new Event('change', { bubbles: true }));
                label();
                close();
                return;
            }
            if (event.target.closest('[data-date-today]')) {
                const now = new Date();
                view.year = now.getFullYear();
                view.month = now.getMonth();
                write(now.getDate());
                if (!withTime) close();
            }
        });

        pop.addEventListener('change', (event) => {
            if (!event.target.matches('[data-date-time]')) return;
            const [hour, minute] = event.target.value.split(':');
            const selected = parseValue(input);
            if (!selected) return;
            input.value = selected.year + '-' + pad(selected.month + 1) + '-' + pad(selected.day) + 'T' + hour + ':' + minute;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            label();
        });

        document.addEventListener('click', close);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });
        label();
    });
}

function fitAdminTextarea(field) {
    if (!(field instanceof HTMLTextAreaElement)) return;
    field.style.height = 'auto';
    field.style.height = `${field.scrollHeight}px`;
}

function fitAdminTextareas(root = document) {
    root.querySelectorAll('textarea').forEach(fitAdminTextarea);
}

function canSoftNavigate(link, event) {
    if (link.target === '_blank' || link.hasAttribute('download') || link.dataset.noAjax === '1') return false;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return false;
    let url;
    try { url = new URL(link.href, window.location.origin); } catch (error) { return false; }
    if (url.origin !== window.location.origin) return false;
    if (!url.pathname.startsWith('/admin')) return false;
    if (url.pathname.includes('/export')) return false;
    if (url.pathname === '/admin' || url.pathname === '/admin/') return false;
    return true;
}

function showAdminFlash(root) {
    root.querySelectorAll('[data-admin-flash]').forEach((node) => {
        if (node.dataset.adminFlash === 'error') {
            adminAlert('Please check this', node.textContent.trim(), 'error');
            return;
        }
        const listUrl = adminListUrl();
        adminSavedAlert(node.textContent.trim(), listUrl).then((choice) => {
            if (choice === 'list') loadAdminPage(listUrl);
        });
    });
}

window.startAdminProgress = startAdminProgress;
window.finishAdminProgress = finishAdminProgress;

let progressPending = document.body.classList.contains('is-busy') ? 1 : 0;

function lockAdminShell() {
    document.body.classList.add('is-busy');
    document.querySelector('[data-admin-shell]')?.setAttribute('inert', '');
}

function unlockAdminShell() {
    document.body.classList.remove('is-busy');
    document.querySelector('[data-admin-shell]')?.removeAttribute('inert');
}

function blockAdminShellEvent(event) {
    if (!document.body.classList.contains('is-busy')) return;
    const shell = document.querySelector('[data-admin-shell]');
    if (!(event.target instanceof Node) || !shell?.contains(event.target)) return;
    event.preventDefault();
    event.stopPropagation();
}

['click', 'dblclick', 'input', 'change', 'submit', 'keydown', 'keyup'].forEach((type) => {
    document.addEventListener(type, blockAdminShellEvent, true);
});

function startAdminProgress() {
    progressPending += 1;
    lockAdminShell();
    const bar = document.querySelector('[data-admin-progress]');
    if (!bar) return;
    bar.hidden = false;
    bar.classList.remove('is-active', 'is-done');
    void bar.offsetWidth;
    bar.classList.add('is-active');
}

function finishAdminProgress() {
    progressPending = Math.max(0, progressPending - 1);
    const bar = document.querySelector('[data-admin-progress]');
    if (progressPending > 0) return;
    if (!bar) {
        unlockAdminShell();
        return;
    }
    bar.classList.remove('is-active');
    bar.classList.add('is-done');
    window.setTimeout(() => {
        if (progressPending > 0) return;
        bar.hidden = true;
        bar.classList.remove('is-done');
        unlockAdminShell();
    }, 220);
}

async function loadAdminPage(href, options = {}) {
    const push = options.push !== false;
    const url = new URL(href, window.location.origin);
    if (url.pathname === '/admin' || url.pathname === '/admin/') {
        startAdminProgress();
        window.location.assign(url.href);
        return;
    }
    const page = document.querySelector('[data-admin-page]');
    if (!page) {
        window.location.assign(url.href);
        return;
    }
    page.classList.add('is-loading');
    page.setAttribute('aria-busy', 'true');
    startAdminProgress();
    try {
        const response = await fetch(url.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
        });
        if (!response.ok) throw new Error('Unable to load');
        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        const next = doc.querySelector('[data-admin-page]');
        if (!next) throw new Error('Missing page');
        if (doc.querySelector('[data-admin-page-scripts] script, [data-admin-page] script')) {
            window.location.assign(url.href);
            return;
        }
        page.innerHTML = next.innerHTML;
        const list = next.getAttribute('data-admin-list');
        if (list) page.setAttribute('data-admin-list', list);
        else page.removeAttribute('data-admin-list');
        const heading = document.querySelector('[data-admin-heading]');
        const nextHeading = doc.querySelector('[data-admin-heading]');
        if (heading && nextHeading) heading.textContent = nextHeading.textContent;
        const crumb = document.querySelector('[data-admin-crumb]');
        const nextCrumb = doc.querySelector('[data-admin-crumb]');
        if (crumb && nextCrumb) crumb.textContent = nextCrumb.textContent;
        if (doc.title) document.title = doc.title;
        if (push) history.pushState({ adminNav: true }, '', url.href);
        document.querySelector('.admin-main')?.scrollTo({ top: 0 });
        document.querySelectorAll('.admin-nav a').forEach((link) => {
            const linkUrl = new URL(link.href, window.location.origin);
            let active = linkUrl.pathname === url.pathname || url.pathname.startsWith(linkUrl.pathname + '/');
            if (linkUrl.pathname === '/admin') active = url.pathname === '/admin';
            if (linkUrl.pathname.endsWith('/packages')) {
                const pageGroup = page.querySelector('[data-package-group]')?.getAttribute('data-package-group')
                    || url.searchParams.get('group')
                    || '';
                active = active && (linkUrl.searchParams.get('group') || '') === (pageGroup === 'marketing' ? 'marketing' : '');
            }
            link.classList.toggle('is-active', active);
        });
        mountAdminDates();
        fitAdminTextareas(page);
        showAdminFlash(page);
    } catch (error) {
        window.location.assign(url.href);
    } finally {
        page.classList.remove('is-loading');
        page.removeAttribute('aria-busy');
        finishAdminProgress();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    finishAdminProgress();
    const shell = document.querySelector('[data-admin-shell]');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    const closeSidebar = () => shell?.classList.remove('is-sidebar-open');
    const openSidebar = () => shell?.classList.add('is-sidebar-open');

    toggle?.addEventListener('click', () => {
        shell?.classList.toggle('is-sidebar-open');
    });
    backdrop?.addEventListener('click', closeSidebar);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post') return;
        if (event.submitter?.dataset.confirm && form.dataset.confirmed !== '1') {
            event.preventDefault();
            adminConfirm(event.submitter.dataset.confirm).then((confirmed) => {
                if (!confirmed) return;
                form.dataset.confirmed = '1';
                form.requestSubmit(event.submitter);
            });
        }
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post' || form.dataset.skipAjax === '1') return;
        if (form.action.includes('/logout')) return;
        if (form.classList.contains('admin-auth-form') || !document.querySelector('[data-admin-shell]')) return;
        if (event.submitter?.dataset.confirm && form.dataset.confirmed !== '1') return;

        const method = (form.querySelector('[name="_method"]')?.value || 'POST').toUpperCase();
        if (method !== 'DELETE' && !form.hasAttribute('data-lead-bulk') && adminFormUnchanged(form)) {
            event.preventDefault();
            form.dataset.confirmed = '';
            if (form.hasAttribute('data-lead-save')) {
                const status = form.querySelector('[name="status"]')?.value || 'this status';
                adminAlert('Already in this status', 'This lead is already ' + status + '.', 'info');
            } else {
                adminAlert('No changes', 'No field was edited.', 'info');
            }
            return;
        }

        if (form.hasAttribute('data-lead-bulk')) {
            const checked = [...form.querySelectorAll('input[name="ids[]"]:checked')];
            const status = form.querySelector('[name="status"]')?.value || '';
            if (checked.length === 0) {
                event.preventDefault();
                form.dataset.confirmed = '';
                adminAlert('Select a lead', 'Select at least one lead before updating.', 'warning');
                return;
            }
            if (checked.every((input) => input.closest('tr')?.dataset.leadStatus === status)) {
                event.preventDefault();
                form.dataset.confirmed = '';
                adminAlert('Already in this status', 'The selected leads are already ' + status + '.', 'info');
                return;
            }
        }

        event.preventDefault();
        const button = event.submitter;
        const original = button?.textContent;
        if (button) {
            button.disabled = true;
            button.textContent = 'Please wait…';
        }
        Swal.fire({
            title: 'Please wait',
            text: 'Updating…',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const body = new FormData(form);
            if (button?.name) body.set(button.name, button.value);
            const response = await fetch(form.action, {
                method: 'POST',
                body,
                headers: {
                    'Accept': 'application/json',
                    'X-Admin-Ajax': '1',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.ok === false) {
                const details = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : (data.message || 'Please try again.');
                adminAlert('Could not update', details, 'error');
                return;
            }
            applyAdminUpdate(form, data);
            rememberFormDefaults(form);
            if (form.hasAttribute('data-lead-save')) rememberLeadForm(form);
            const redirect = data.redirect ? new URL(data.redirect, window.location.origin) : null;
            const hasFile = [...form.querySelectorAll('input[type="file"]')].some((input) => input.files?.length);
            const listUrl = adminListUrl();
            const choice = await adminSavedAlert(data.message || 'Saved.', listUrl);
            if (choice === 'list') {
                await loadAdminPage(listUrl);
                return;
            }
            if (redirect && redirect.pathname !== window.location.pathname) {
                await loadAdminPage(data.redirect);
                return;
            }
            if (hasFile) {
                await loadAdminPage(data.redirect || window.location.href, { push: false });
            }
        } catch (error) {
            adminAlert('Could not update', 'Please try again.', 'error');
        } finally {
            form.dataset.confirmed = '';
            if (button) {
                button.disabled = false;
                if (original) button.textContent = original;
            }
        }
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

    const nextPlanIndex = (planList) => {
        const indexes = [...planList.querySelectorAll('[data-plan-row]')]
            .map((row) => {
                const name = row.querySelector('input, textarea')?.getAttribute('name') || '';
                const match = name.match(/plans\[(\d+)\]/);
                return match ? Number(match[1]) : -1;
            })
            .filter((n) => n >= 0);
        return indexes.length ? Math.max(...indexes) + 1 : 0;
    };

    const renumberPlans = (planList) => {
        planList.querySelectorAll('[data-plan-index]').forEach((el, index) => {
            el.textContent = String(index + 1).padStart(2, '0');
        });
    };

    const syncPlanHead = (row) => {
        const label = row.querySelector('[data-plan-label]');
        const title = row.querySelector('[data-plan-title]');
        if (label && title) title.textContent = label.value.trim() || 'New plan';
        const amount = row.querySelector('[data-plan-amount]');
        const price = row.querySelector('[data-plan-price]');
        if (!amount || !price) return;
        const value = amount.value.trim();
        price.textContent = value === '' || Number.isNaN(Number(value))
            ? ''
            : new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(Number(value));
    };

    document.addEventListener('click', (event) => {
        const add = event.target.closest('[data-plan-add]');
        if (add) {
            const planList = add.closest('form')?.querySelector('[data-plan-list]') || document.querySelector('[data-plan-list]');
            const planTemplate = document.getElementById('plan-row-template');
            if (!planList || !planTemplate) return;
            const html = planTemplate.innerHTML.replaceAll('__INDEX__', String(nextPlanIndex(planList)));
            planList.insertAdjacentHTML('beforeend', html);
            renumberPlans(planList);
            return;
        }

        const featureAdd = event.target.closest('[data-feature-add]');
        if (featureAdd) {
            const featureList = featureAdd.closest('form')?.querySelector('[data-feature-list]');
            const featureTemplate = document.getElementById('feature-row-template');
            if (!featureList || !featureTemplate) return;
            const indexes = [...featureList.querySelectorAll('[data-feature-row]')]
                .map((row) => {
                    const match = (row.querySelector('input, textarea')?.getAttribute('name') || '').match(/features\[(\d+)\]/);
                    return match ? Number(match[1]) : -1;
                })
                .filter((n) => n >= 0);
            const index = indexes.length ? Math.max(...indexes) + 1 : 0;
            featureList.insertAdjacentHTML('beforeend', featureTemplate.innerHTML.replaceAll('__INDEX__', String(index)));
            return;
        }

        const featureRemove = event.target.closest('[data-feature-remove]');
        if (featureRemove) {
            const row = featureRemove.closest('[data-feature-row]');
            const featureList = row?.closest('[data-feature-list]');
            if (!row || !featureList) return;
            if (featureList.querySelectorAll('[data-feature-row]').length <= 1) {
                row.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), textarea').forEach((field) => {
                    field.value = '';
                });
                return;
            }
            row.remove();
            return;
        }

        const button = event.target.closest('[data-plan-remove]');
        if (!button) return;
        const row = button.closest('[data-plan-row]');
        const planList = row?.closest('[data-plan-list]');
        if (!row || !planList) return;
        if (planList.querySelectorAll('[data-plan-row]').length <= 1) {
            row.querySelectorAll('input, textarea').forEach((field) => {
                field.value = '';
            });
            syncPlanHead(row);
            return;
        }
        row.remove();
        renumberPlans(planList);
    });

    document.addEventListener('input', (event) => {
        const row = event.target.closest?.('[data-plan-row]');
        if (!row || !event.target.matches('[data-plan-label], [data-plan-amount]')) return;
        syncPlanHead(row);
    });

    document.querySelectorAll('[data-menu]').forEach((root) => {
        const toggle = root.querySelector('[data-menu-toggle]');
        const panel = root.querySelector('[data-menu-panel]');
        if (!toggle || !panel) return;

        let closeTimer;
        const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

        const open = () => {
            clearTimeout(closeTimer);
            document.querySelectorAll('[data-menu-panel]').forEach((other) => {
                if (other !== panel) other.setAttribute('hidden', '');
            });
            document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
                if (button !== toggle) button.setAttribute('aria-expanded', 'false');
            });
            panel.removeAttribute('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        };

        const close = () => {
            panel.setAttribute('hidden', '');
            toggle.setAttribute('aria-expanded', 'false');
        };

        if (canHover) {
            root.addEventListener('mouseenter', open);
            root.addEventListener('mouseleave', () => {
                closeTimer = setTimeout(close, 140);
            });
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            if (panel.hasAttribute('hidden')) {
                open();
            } else {
                close();
            }
        });

        if (root.hasAttribute('data-notifications')) {
            const markSeen = () => {
                const badge = root.querySelector('[data-notification-count]');
                if (!badge || root.dataset.seen === '1') return;
                root.dataset.seen = '1';
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch(root.getAttribute('data-notifications'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json',
                    },
                }).finally(() => badge.remove());
            };
            root.addEventListener('mouseenter', markSeen);
            toggle.addEventListener('click', markSeen);
        }
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-menu-panel]').forEach((panel) => panel.setAttribute('hidden', ''));
        document.querySelectorAll('[data-menu-toggle]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    });

    document.querySelectorAll('[data-nav-group]').forEach((group) => {
        const key = 'admin-nav-' + group.getAttribute('data-nav-group');
        const toggle = group.querySelector('[data-nav-toggle]');
        if (localStorage.getItem(key) === 'collapsed') {
            group.classList.add('is-collapsed');
            toggle?.setAttribute('aria-expanded', 'false');
        }
        toggle?.addEventListener('click', () => {
            const collapsed = group.classList.toggle('is-collapsed');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            localStorage.setItem(key, collapsed ? 'collapsed' : 'open');
        });
    });

    document.querySelectorAll('.lead-choices').forEach((group) => {
        group.addEventListener('change', () => {
            group.querySelectorAll('label').forEach((label) => {
                label.classList.toggle('is-active', Boolean(label.querySelector('input')?.checked));
            });
            group.closest('form')?.requestSubmit();
        });
    });

    mountAdminDates();
    fitAdminTextareas();

    document.addEventListener('input', (event) => {
        if (event.target instanceof HTMLTextAreaElement) fitAdminTextarea(event.target);
    });

    document.addEventListener('click', (event) => {
        const storyButton = event.target.closest('[data-activity-open]');
        if (storyButton) {
            event.preventDefault();
            openActivityStory(storyButton);
            return;
        }

        const toggle = event.target.closest('[data-filter-toggle]');
        if (toggle) {
            const panel = toggle.closest('[data-lead-filters]');
            const body = panel?.querySelector('.lead-filter-body');
            if (!panel || !body) return;
            const open = body.hasAttribute('hidden');
            body.toggleAttribute('hidden', !open);
            panel.classList.toggle('is-open', open);
            panel.classList.toggle('is-collapsed', !open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            return;
        }

        const link = event.target.closest('a');
        if (!link || !canSoftNavigate(link, event)) return;
        event.preventDefault();
        loadAdminPage(link.href);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'get') return;
        if (!form.closest('[data-admin-page]')) return;
        event.preventDefault();
        const action = new URL(form.getAttribute('action') || window.location.href, window.location.origin);
        const params = new URLSearchParams();
        new FormData(form).forEach((value, key) => {
            if (String(value) !== '') params.append(key, value);
        });
        action.search = params.toString();
        loadAdminPage(action.href);
    });

    window.addEventListener('popstate', () => {
        loadAdminPage(window.location.href, { push: false });
    });

    showAdminFlash(document);

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
