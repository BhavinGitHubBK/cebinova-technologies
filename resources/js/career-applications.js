const labels = {
    name: 'full name', email: 'email address', phone: 'phone number', current_location: 'current city or location',
    area_of_interest: 'area of interest', experience: 'experience (you can enter Fresher)', availability: 'availability or notice period',
    skills: 'relevant skills', message: 'short introduction', portfolio_url: 'portfolio link', github_url: 'GitHub link',
    linkedin_url: 'LinkedIn link', resume: 'resume', consent: 'consent', freelance_preference: 'engagement preference', expected_rate: 'expected rate',
};

export function careerFieldMessage(field) {
    if (field.name === 'resume') {
        const file = field.files?.[0];
        if (!file) return 'Please choose your resume before submitting.';
        if (file.size > 5 * 1024 * 1024) return 'Your resume is too large. Please choose a file no larger than 5 MB.';
        if (!/\.(pdf|doc|docx)$/i.test(file.name)) return 'Please upload your resume as a PDF, DOC or DOCX file.';
    }
    if (field.name === 'consent' && !field.checked) return 'Please tick the consent box so we can review and store your application.';
    if (field.required && !String(field.value || '').trim()) return `Please enter your ${labels[field.name] || 'details'}.`;
    if (!String(field.value || '').trim()) return '';
    if (field.name === 'email' && field.validity.typeMismatch) return 'Please enter a valid email address, such as name@example.com.';
    if (field.type === 'url' && field.validity.typeMismatch) return 'Please enter a valid website link, such as https://example.com.';
    if (field.name === 'phone' && !/^[0-9+()\-\s]{7,40}$/.test(field.value)) return 'Please enter a valid phone number using digits, spaces, +, brackets or hyphens.';
    if (field.validity.rangeUnderflow || field.validity.rangeOverflow || field.validity.stepMismatch || field.validity.badInput) return `Please enter a whole number between ${field.min || '1'} and ${field.max || '100000000'}.`;
    if (field.maxLength > 0 && field.value.length > field.maxLength) return `Please keep your ${labels[field.name]} within ${field.maxLength} characters.`;
    return '';
}

export function careerRequestError(status) {
    if (status === 419 || status === 401) return 'Your session has expired. Please refresh this page before trying again. Copy any details you want to keep first; you will need to select your resume again.';
    if (status === 413) return 'The upload is too large. Please choose a resume no larger than 5 MB.';
    if (status === 429) return 'Too many attempts were made recently. Please wait a few minutes, then try again. Your entries are still here.';
    if (status === 404 || status === 403) return 'This opening may no longer be available. Please check the job description before trying again.';
    return 'We could not confirm that your application was received. Please try again later. Your entries and selected resume are still on this page.';
}

function successAlert(title, message) {
    if (typeof window.Swal?.fire !== 'function') return;
    // Use text, never HTML, for server-provided confirmation wording.
    return window.Swal.fire({ icon: 'success', title, text: message, confirmButtonText: 'Got it, thank you!',
        confirmButtonColor: '#013a9d', buttonsStyling: false,
        customClass: { popup: 'recruitment-success-popup', confirmButton: 'cn-btn cn-btn--md cn-btn--primary' } });
}

export function initCareerApplications() {
    document.querySelectorAll('[data-career-success]').forEach((notice) => {
        successAlert(notice.dataset.successTitle || 'Application received!', notice.dataset.successMessage);
    });
    document.querySelectorAll('[data-career-application]').forEach((form) => {
        form.noValidate = true;
        const fields = [...form.querySelectorAll('input, select, textarea')].filter((field) => labels[field.name]);
        const fieldFor = (name) => fields.find((field) => field.name === name);
        const summary = form.querySelector('[data-application-error-summary]');
        const summaryMessage = summary.querySelector('[data-application-error-message]');
        const summaryList = summary.querySelector('[data-application-error-list]');
        let errors = {};
        let pending = false;
        let submitted = false;
        fields.forEach((field) => {
            const slot = form.querySelector(`[data-career-error="${field.name}"]`);
            if (slot && !slot.hidden && slot.textContent.trim()) errors[field.name] = slot.textContent.trim();
        });
        function renderErrors(focus = false) {
            summaryList.replaceChildren();
            fields.forEach((field) => {
                const slot = form.querySelector(`[data-career-error="${field.name}"]`);
                const message = errors[field.name] || '';
                if (slot) { slot.textContent = message; slot.hidden = !message; }
                const described = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter((id) => id && id !== slot?.id);
                if (message) {
                    field.setAttribute('aria-invalid', 'true');
                    if (slot) described.push(slot.id);
                    const details = field.closest('details');
                    if (details) details.open = true;
                } else field.removeAttribute('aria-invalid');
                if (described.length) field.setAttribute('aria-describedby', described.join(' '));
                else field.removeAttribute('aria-describedby');
            });
            Object.entries(errors).forEach(([name, message]) => {
                const item = document.createElement('li');
                const field = fieldFor(name);
                if (field) {
                    const link = document.createElement('a');
                    link.href = `#${field.id}`;
                    link.textContent = message;
                    link.addEventListener('click', (event) => { event.preventDefault(); field.focus(); });
                    item.append(link);
                } else item.textContent = message;
                summaryList.append(item);
            });
            summary.hidden = Object.keys(errors).length === 0;
            summaryMessage.textContent = 'Please check the messages below. Your entries and selected resume have been kept.';
            if (focus && !summary.hidden) {
                const first = fields.find((field) => errors[field.name]);
                (first || summary).focus();
                (first || summary).scrollIntoView({ block: 'center', behavior: 'auto' });
            }
        }
        function normalizeLink(field) {
            if (field.type === 'url' && field.value.trim() && !/^https?:\/\//i.test(field.value.trim())) field.value = `https://${field.value.trim()}`;
        }
        fields.forEach((field) => {
            const update = () => {
                if (!errors[field.name]) return;
                const message = careerFieldMessage(field);
                if (message) errors[field.name] = message;
                else delete errors[field.name];
                renderErrors();
            };
            field.addEventListener('blur', () => { normalizeLink(field); update(); });
            field.addEventListener(field.type === 'file' || field.type === 'checkbox' || field.tagName === 'SELECT' ? 'change' : 'input', update);
        });
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (pending || submitted) return;
            errors = {};
            fields.forEach((field) => {
                normalizeLink(field);
                const message = careerFieldMessage(field);
                if (message) errors[field.name] = message;
            });
            renderErrors(true);
            if (Object.keys(errors).length) return;
            pending = true;
            form.setAttribute('aria-busy', 'true');
            const button = form.querySelector('button[type="submit"]');
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Submitting your application…';
            try {
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await response.json().catch(() => null);
                if (!response.ok || data?.ok !== true) {
                    if (response.status === 422 && data?.errors) {
                        Object.entries(data.errors).forEach(([name, messages]) => {
                            errors[name] = Array.isArray(messages) ? messages[0] : String(messages);
                            const slot = form.querySelector(`[data-career-error="${name}"]`);
                            if (slot) { slot.textContent = errors[name]; slot.hidden = false; }
                        });
                    } else if (response.status === 419) {
                        let refreshed = false;
                        try {
                            const freshPage = await fetch(window.location.href, { cache: 'no-store', headers: { Accept: 'text/html' } });
                            if (freshPage.ok) {
                                const doc = new DOMParser().parseFromString(await freshPage.text(), 'text/html');
                                const token = doc.querySelector('[data-career-application] input[name="_token"]')?.value;
                                if (token) { form.querySelector('input[name="_token"]').value = token; refreshed = true; }
                            }
                        } catch { /* Keep the original entries and explain how to recover. */ }
                        errors.application = refreshed ? 'Your session expired and has now been refreshed. Your entries and selected resume are still here. Please submit your application again.' : careerRequestError(419);
                    } else if (response.status === 413) errors.resume = careerRequestError(413);
                    else errors.application = response.status === 422 && data?.message ? data.message : careerRequestError(response.status);
                    renderErrors(true);
                    return;
                }
                submitted = true;
                const panel = document.createElement('div');
                panel.className = 'recruitment-candidate-form recruitment-submitted';
                panel.setAttribute('role', 'status');
                const heading = document.createElement('h2');
                heading.className = 'section-title';
                heading.tabIndex = -1;
                heading.textContent = data.title || 'Application received!';
                const message = document.createElement('p');
                message.className = 'section-support';
                message.textContent = data.message;
                const link = document.createElement('a');
                link.className = 'cn-btn cn-btn--md cn-btn--primary';
                link.href = form.dataset.careersUrl;
                link.textContent = 'Explore careers →';
                panel.append(heading, message, link);
                form.replaceWith(panel);
                heading.focus();
                try { await successAlert(heading.textContent, data.message); } catch { /* The inline confirmation remains visible. */ }
            } catch {
                errors.application = 'We could not confirm whether your application was received. Please check your internet connection before trying again. Your entries and selected resume are still here.';
                renderErrors(true);
            } finally {
                pending = false;
                form.removeAttribute('aria-busy');
                if (!submitted) {
                    button.disabled = false; button.textContent = originalText;
                }
            }
        });
        if (Object.keys(errors).length) renderErrors(true);
    });
}
