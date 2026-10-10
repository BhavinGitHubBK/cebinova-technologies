function enquiryAlert(title, text, icon) {
    if (typeof Swal === 'undefined') {
        return Promise.resolve(false);
    }

    return Swal.fire({
        title,
        text,
        icon,
        iconColor: icon === 'success' ? '#013a9d' : '#9a3412',
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: {
            popup: 'enquiry-swal',
            confirmButton: 'enquiry-swal-btn',
        },
    }).then(() => true);
}

function resetSubmit(submit, label, loading) {
    submit?.removeAttribute('disabled');
    submit?.removeAttribute('aria-busy');
    label?.classList.remove('hidden');
    loading?.classList.add('hidden');
}

export function initContactForm() {
    const forms = document.querySelectorAll('.js-lead-form');

    forms.forEach((form) => {
        let submitting = false;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (submitting) {
                return;
            }

            const submit = form.querySelector('.js-submit');
            const label = form.querySelector('.js-submit-label');
            const loading = form.querySelector('.js-submit-loading');
            const globalError = form.querySelector('.js-form-error');
            const fieldsWrap = form.querySelector('.js-form-fields');
            const success = form.querySelector('.js-form-success');
            const successText = form.querySelector('.js-form-success-text');

            form.querySelectorAll('.js-error').forEach((el) => {
                el.textContent = '';
            });
            if (globalError) {
                globalError.hidden = true;
                globalError.classList.add('hidden');
                globalError.textContent = '';
            }

            submitting = true;
            submit?.setAttribute('disabled', 'disabled');
            submit?.setAttribute('aria-busy', 'true');
            label?.classList.add('hidden');
            loading?.classList.remove('hidden');

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await window.axios.post(form.getAttribute('action'), new FormData(form), {
                    headers: {
                        'X-CSRF-TOKEN': token,
                        Accept: 'application/json',
                    },
                });

                window.cebinovaAnalyticsSubmit?.();
                const title = response.data.message || 'Thank you! Your enquiry has been received.';
                const detail = response.data.detail || 'Our team will contact you shortly.';
                const shown = await enquiryAlert(title, detail, 'success');

                if (shown) {
                    form.reset();
                    submitting = false;
                    resetSubmit(submit, label, loading);
                } else if (success && fieldsWrap) {
                    fieldsWrap.classList.add('hidden');
                    fieldsWrap.hidden = true;
                    success.hidden = false;
                    success.classList.remove('hidden');
                    if (successText) {
                        successText.textContent = detail;
                    }
                }
            } catch (error) {
                submitting = false;
                resetSubmit(submit, label, loading);

                const status = error.response?.status;
                const errors = error.response?.data?.errors;

                if (status === 422 && errors) {
                    Object.entries(errors).forEach(([key, messages]) => {
                        const target = form.querySelector(`[data-error="${key}"]`);
                        if (target) {
                            target.textContent = messages[0];
                        }
                    });
                } else {
                    const message = status === 429
                        ? 'Please wait a moment before sending another enquiry.'
                        : 'Something went wrong. Please try again, or message us on WhatsApp.';
                    const shown = await enquiryAlert('Could not send', message, 'error');
                    if (!shown && globalError) {
                        globalError.hidden = false;
                        globalError.classList.remove('hidden');
                        globalError.textContent = message;
                    }
                }
            }
        });
    });
}
