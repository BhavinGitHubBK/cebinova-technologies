(function () {
    var node = document.getElementById('cebinova-analytics');
    if (!node) return;

    var cfg;
    try {
        cfg = JSON.parse(node.textContent || '');
    } catch (error) {
        return;
    }

    if (!cfg || !cfg.visit) return;

    var storageKey = 'cn_visitor';
    var signingIn = false;
    var popup = document.querySelector('[data-visitor-popup]');
    if (cfg.loggedOut) {
        localStorage.removeItem(storageKey);
        localStorage.removeItem('cn_location_asked');
    }
    var visitor = localStorage.getItem(storageKey) || '';

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': cfg.csrf,
                Accept: 'application/json',
            },
            body: JSON.stringify(Object.assign({ _token: cfg.csrf, visitor: visitor || null }, body)),
            credentials: 'same-origin',
            keepalive: true,
        }).then(function (response) {
            return response.json().then(function (data) {
                data = data || {};
                data.status = response.status;
                return data;
            }).catch(function () {
                return { status: response.status };
            });
        });
    }

    function remember(data) {
        if (data && data.visitor) {
            visitor = data.visitor;
            localStorage.setItem(storageKey, visitor);
        }
    }

    document.addEventListener('cn:chat-account', function (event) {
        showAccount({ account: event.detail });
    });

    function showAccount(data) {
        if (!data || !data.account || !data.account.name) return;
        document.dispatchEvent(new CustomEvent('cn:account', { detail: data.account }));
        var initial = data.account.name.charAt(0).toUpperCase();
        document.querySelectorAll('[data-site-account]').forEach(function (node) {
            node.hidden = false;
            node.querySelectorAll('[data-site-account-name]').forEach(function (name) {
                name.textContent = data.account.name;
            });
            node.querySelectorAll('[data-site-account-mark]').forEach(function (mark) {
                mark.textContent = initial;
            });
            var mobile = node.querySelector('[data-site-account-mobile]');
            if (mobile && data.account.mobile) mobile.textContent = data.account.mobile;
        });
    }

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-visitor-signin]')) {
            event.preventDefault();
            signingIn = true;
            showPopup();
            return;
        }
        if (signingIn && event.target === popup) {
            signingIn = false;
            hidePopup();
            return;
        }
        var button = event.target.closest('[data-site-account-trigger]');
        if (!button) return;
        var account = button.closest('[data-site-account]');
        if (!account) return;
        if (window.matchMedia('(hover: hover) and (min-width: 1280px)').matches) return;
        account.classList.toggle('is-open');
    });

    var form = document.querySelector('[data-visitor-form]');
    var error = document.querySelector('[data-visitor-error]');

    function showPopup() {
        if (popup) popup.hidden = false;
    }

    function hidePopup() {
        if (popup) popup.hidden = true;
    }

    function shareLocation() {
        if (!navigator.geolocation || !cfg.locate) return;
        localStorage.setItem('cn_location_asked', '1');
        navigator.geolocation.getCurrentPosition(function (position) {
            post(cfg.locate, {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: Math.round(position.coords.accuracy || 0),
            }).catch(function () {});
        }, function () {}, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0,
        });
    }

    post(cfg.visit, {
        path: window.location.pathname || cfg.path || '/',
        title: document.title || '',
        referrer: document.referrer || '',
    }).then(function (data) {
        remember(data);
        showAccount(data);
        if (data && data.tracked && !data.identified && !data.signed_out) showPopup();
        maybeLocate(data);
        visitorReady();
    }).catch(visitorReady);

    function visitorReady() {
        window.cnVisitorReady = true;
        document.dispatchEvent(new CustomEvent('cn:visitor-ready'));
    }

    function maybeLocate(data) {
        if (!data || !data.tracked || data.located || !navigator.geolocation) return;
        var asked = !!localStorage.getItem('cn_location_asked');
        if (!navigator.permissions || !navigator.permissions.query) {
            if (!asked) shareLocation();
            return;
        }
        navigator.permissions.query({ name: 'geolocation' }).then(function (status) {
            if (status.state === 'granted' || (status.state === 'prompt' && !asked)) shareLocation();
        }).catch(function () {
            if (!asked) shareLocation();
        });
    }

    if (!form) return;

    var submit = form.querySelector('[data-visitor-submit]');
    var label = form.querySelector('[data-visitor-label]');

    function setBusy(busy) {
        form.classList.toggle('is-busy', busy);
        form.setAttribute('aria-busy', busy ? 'true' : 'false');
        if (submit) submit.disabled = busy;
        if (label) label.textContent = busy ? 'Saving your details…' : 'Continue';
        form.elements.name.readOnly = busy;
        form.elements.mobile.readOnly = busy;
    }

    function showError(message) {
        setBusy(false);
        if (error) {
            error.textContent = message;
            error.hidden = false;
        }
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (form.classList.contains('is-busy')) return;
        if (error) error.hidden = true;
        var name = form.elements.name.value.trim();
        var mobile = form.elements.mobile.value.trim();
        setBusy(true);
        post(cfg.identify, {
            name: name,
            mobile: mobile,
            path: window.location.pathname || '/',
        }).then(function (data) {
            remember(data);
            showAccount(data);
            if (data && data.identified) {
                if (signingIn) {
                    if (label) label.textContent = 'Signing you in…';
                    window.location.reload();
                    return;
                }
                hidePopup();
                setBusy(false);
                shareLocation();
                return;
            }
            var message = 'Please check your name and mobile number.';
            if (data && data.errors && data.errors.mobile) message = data.errors.mobile[0];
            if (data && data.errors && data.errors.name) message = data.errors.name[0];
            showError(message);
        }).catch(function () {
            showError('Could not save just now. Please try again.');
        });
    });
})();
