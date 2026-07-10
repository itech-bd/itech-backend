/* global grecaptcha */

function isV3Enabled() {
    return window.__recaptcha?.enabled
        && window.__recaptcha?.version === 'v3'
        && typeof window.__recaptcha?.siteKey === 'string'
        && window.__recaptcha.siteKey.length > 0;
}

async function ensureV3Token(form, action) {
    if (!isV3Enabled()) return;
    if (!window.grecaptcha?.execute || !window.grecaptcha?.ready) {
        throw new Error('grecaptcha_not_loaded');
    }

    const siteKey = window.__recaptcha.siteKey;

    await new Promise((resolve) => {
        window.grecaptcha.ready(resolve);
    });

    const token = await window.grecaptcha.execute(siteKey, { action });

    let input = form.querySelector('input[name="g-recaptcha-response"]');
    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'g-recaptcha-response';
        form.appendChild(input);
    }

    input.value = token;
}

// Non-AJAX forms (e.g. /login, /register pages)
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    const action = form.dataset.recaptchaAction;
    if (!action) return;

    // Auth modal forms are handled via AJAX by auth-modals.js which already
    // calls ensureV3Token() internally. Skip them here to prevent a duplicate
    // native form.submit() firing alongside the AJAX submission.
    if (form.dataset.authForm) return;

    if (!isV3Enabled()) return;

    // Prevent infinite loops when we re-submit after token insertion.
    if (form.dataset.recaptchaTokenReady === '1') {
        form.dataset.recaptchaTokenReady = '0';
        return;
    }

    event.preventDefault();

    try {
        await ensureV3Token(form, action);
        form.dataset.recaptchaTokenReady = '1';
        form.submit();
    } catch (_) {
        // If reCAPTCHA fails to load/execute, don't submit without a token.
        window.alert('Captcha failed to load. Please refresh and try again.');
    }
}, true);

export { ensureV3Token };
