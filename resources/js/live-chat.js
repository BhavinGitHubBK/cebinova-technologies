const storageKey = 'cn_live_chat';
const pageKey = 'cn_live_chat_page';

export function initLiveChat() {
    const root = document.querySelector('[data-live-chat]');
    if (!root) {
        return;
    }

    const panel = root.querySelector('[data-chat-panel]');
    const toggle = root.querySelector('[data-chat-toggle]');
    const closeBtn = root.querySelector('[data-chat-close]');
    const log = root.querySelector('[data-chat-log]');
    const form = root.querySelector('[data-chat-form]');
    const input = root.querySelector('[data-chat-input]');
    const questions = root.querySelector('[data-chat-questions]');
    const more = root.querySelector('[data-chat-more]');
    const error = root.querySelector('[data-chat-error]');
    const url = root.dataset.chatUrl;
    if (!panel || !toggle || !log || !form || !input || !questions) {
        return;
    }

    localStorage.removeItem(storageKey);
    const person = root.dataset.chatName || '';
    const saved = readSaved();
    const sameVisitor = (saved.person || '') === person;
    const state = {
        person,
        stage: person ? 'questions' : (sameVisitor && saved.stage === 'mobile' ? 'mobile' : 'name'),
        name: sameVisitor && !person ? saved.name || '' : '',
        mobile: '',
        id: sameVisitor ? saved.id || null : null,
        token: sameVisitor ? saved.token || '' : '',
        started: false,
    };

    const setOpen = (open, focus = true) => {
        root.classList.toggle('is-open', open);
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close live chat' : 'Open live chat');
        if (open) {
            start();
            if (focus && !form.hidden) {
                input.focus();
            }
        }
    };

    const start = () => {
        if (state.started) {
            return;
        }
        state.started = true;
        if (state.person) {
            add('bot', `Hello ${state.person}! How can I help you today?`);
            form.hidden = true;
            renderQuestions(readTopics());
            return;
        }
        add('bot', 'Hi there! How can I help you today?');
        if (state.stage === 'mobile' && state.name) {
            add('bot', `${state.name}, please share your mobile number so we can reply.`);
            input.placeholder = '10-digit mobile number';
            input.setAttribute('inputmode', 'numeric');
            return;
        }
        add('bot', 'May I know your name first?');
        input.placeholder = 'Your name';
    };

    const isNewPage = () => {
        const page = window.location.pathname;
        let last = null;
        try {
            last = sessionStorage.getItem(pageKey);
            sessionStorage.setItem(pageKey, page);
        } catch (err) {
            return true;
        }
        return last !== page;
    };

    let reopenAfterOffer = false;
    const offerOnScreen = () => document.querySelector('.promo-dialog') !== null;
    new MutationObserver(() => {
        if (offerOnScreen() && !panel.hidden) {
            reopenAfterOffer = true;
            setOpen(false);
        } else if (!offerOnScreen() && reopenAfterOffer) {
            reopenAfterOffer = false;
            setOpen(true, false);
        }
    }).observe(document.body, { childList: true });

    if (isNewPage()) {
        openAfterVisitorPopup(() => {
            if (offerOnScreen()) {
                reopenAfterOffer = true;
                return;
            }
            setOpen(true, false);
        });
    }

    document.addEventListener('cn:account', (event) => {
        const first = String(event.detail?.name || '').trim().split(/\s+/)[0];
        if (!first || state.person) {
            return;
        }
        state.person = first.charAt(0).toUpperCase() + first.slice(1);
        state.stage = 'questions';
        state.id = null;
        state.token = '';
        remember();
        if (state.started) {
            clearError();
            add('bot', `Hello ${state.person}! How can I help you today?`);
            form.hidden = true;
            renderQuestions(readTopics());
        }
    });

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    closeBtn?.addEventListener('click', () => {
        setOpen(false);
        toggle.focus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) {
            setOpen(false);
            toggle.focus();
        }
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearError();
        const value = input.value.trim();
        if (state.stage === 'name') {
            if (value.length < 2) {
                showError('Enter your name.');
                return;
            }
            state.name = value.slice(0, 80);
            add('visitor', state.name);
            input.value = '';
            state.stage = 'mobile';
            input.placeholder = '10-digit mobile number';
            input.setAttribute('inputmode', 'numeric');
            add('bot', `Thanks, ${state.name}. What is your mobile number?`);
            remember();
            return;
        }

        const digits = value.replace(/\D+/g, '').slice(-10);
        if (!/^[6-9]\d{9}$/.test(digits)) {
            showError('Enter a 10-digit mobile number.');
            return;
        }
        state.mobile = digits;
        add('visitor', digits);
        input.value = '';
        input.disabled = true;
        try {
            const payload = await post({ name: state.name, mobile: state.mobile });
            keep(payload);
            state.stage = 'questions';
            add('bot', payload.reply);
            form.hidden = true;
            renderQuestions(readTopics());
        } catch (err) {
            showError(err.message || 'Could not save your details.');
        } finally {
            input.disabled = false;
        }
    });

    function openAfterVisitorPopup(open) {
        const popup = document.querySelector('[data-visitor-popup]');
        const tracking = document.getElementById('cebinova-analytics');
        const whenPopupClosed = () => {
            if (!popup || popup.hidden) {
                open();
                return;
            }
            const watcher = new MutationObserver(() => {
                if (popup.hidden) {
                    watcher.disconnect();
                    open();
                }
            });
            watcher.observe(popup, { attributes: true, attributeFilter: ['hidden'] });
        };

        if (!tracking || window.cnVisitorReady) {
            whenPopupClosed();
            return;
        }
        let done = false;
        const once = () => {
            if (!done) {
                done = true;
                whenPopupClosed();
            }
        };
        document.addEventListener('cn:visitor-ready', once, { once: true });
        window.setTimeout(once, 4000);
    }

    function readTopics() {
        try {
            return JSON.parse(root.dataset.chatTopics || '[]');
        } catch (err) {
            return [];
        }
    }

    more?.addEventListener('click', () => {
        renderQuestions(readTopics());
    });

    function renderQuestions(items) {
        questions.hidden = false;
        if (more) {
            more.hidden = true;
        }
        const label = questions.querySelector('.live-chat-questions-label');
        questions.replaceChildren();
        if (label) {
            questions.append(label);
        }
        items.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'live-chat-question';
            button.textContent = item.label;
            button.addEventListener('click', () => ask(item, button));
            questions.append(button);
        });
        log.scrollTop = log.scrollHeight;
    }

    function closeQuestions() {
        questions.hidden = true;
        if (more) {
            more.hidden = false;
        }
    }

    async function ask(item, button) {
        clearError();
        button.disabled = true;
        add('visitor', item.label);
        closeQuestions();
        try {
            const payload = await post({
                name: state.name,
                mobile: state.mobile,
                chat_id: state.id,
                token: state.token,
                question: item.key,
            });
            keep(payload);
            add('bot', payload.reply, payload.link);
        } catch (err) {
            showError(err.message || 'Could not send that question.');
            renderQuestions(readTopics());
        }
    }

    async function post(body) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({
                ...body,
                visitor: localStorage.getItem('cn_visitor') || null,
                path: window.location.pathname,
            }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = data.errors?.mobile?.[0] || data.errors?.name?.[0] || data.message || 'Could not save your details.';
            throw new Error(message);
        }
        return data;
    }

    function add(from, text, link = null) {
        const bubble = document.createElement('div');
        bubble.className = `live-chat-bubble is-${from}`;
        const name = from === 'bot' ? 'CEBINOVA Support' : state.person || state.name;
        if (name) {
            const who = document.createElement('p');
            who.className = 'live-chat-who';
            who.textContent = name;
            bubble.append(who);
        }
        const body = document.createElement('p');
        body.className = 'live-chat-text';
        body.textContent = text;
        bubble.append(body);
        if (link?.url && link?.label) {
            const anchor = document.createElement('a');
            anchor.className = 'live-chat-link';
            anchor.href = link.url;
            anchor.textContent = link.label;
            if (new URL(link.url, window.location.href).origin !== window.location.origin) {
                anchor.target = '_blank';
                anchor.rel = 'noopener';
            }
            bubble.append(anchor);
        }
        log.append(bubble);
        log.scrollTop = log.scrollHeight;
    }

    function showError(message) {
        if (!error) {
            return;
        }
        error.hidden = false;
        error.textContent = message;
    }

    function clearError() {
        if (!error) {
            return;
        }
        error.hidden = true;
        error.textContent = '';
    }

    function keep(payload) {
        state.id = payload.id;
        state.token = payload.token;
        if (payload.name) {
            state.person = payload.name;
        }
        if (payload.visitor) {
            localStorage.setItem('cn_visitor', payload.visitor);
        }
        if (payload.account) {
            document.dispatchEvent(new CustomEvent('cn:chat-account', { detail: payload.account }));
        }
        remember();
    }

    function remember() {
        try {
            sessionStorage.setItem(storageKey, JSON.stringify({
                person: state.person,
                stage: state.stage,
                name: state.name,
                id: state.id,
                token: state.token,
            }));
        } catch (err) {
            // Private browsing can block storage; the chat still works for this page.
        }
    }

    function readSaved() {
        try {
            return JSON.parse(sessionStorage.getItem(storageKey) || '{}');
        } catch (err) {
            return {};
        }
    }
}
