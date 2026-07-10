/* global axios */

import { ensureV3Token } from './recaptcha-forms';

const MODAL_NAMES = {
    login: 'auth-login',
    register: 'auth-register',
    forgot: 'auth-forgot',
    verifyStatus: 'auth-verify-status',
    'reset-success': 'auth-reset-success',
};

function toModalName(keyOrName) {
    return MODAL_NAMES[keyOrName] || keyOrName;
}

function openModal(keyOrName) {
    window.dispatchEvent(new CustomEvent('open-modal', { detail: toModalName(keyOrName) }));
}

function closeModal(keyOrName) {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: toModalName(keyOrName) }));
}

function setAlert(root, message, variant = 'info') {
    const alert = root.querySelector('[data-auth-alert]');
    if (!alert) return;

    alert.classList.remove('hidden');
    alert.textContent = message || '';

    // Minimal variant styling hooks (optional)
    alert.dataset.variant = variant;
}

function clearAlert(root) {
    const alert = root.querySelector('[data-auth-alert]');
    if (!alert) return;

    alert.classList.add('hidden');
    alert.textContent = '';
    delete alert.dataset.variant;
}

function clearErrors(root) {
    root.querySelectorAll('[data-auth-error-for]').forEach((el) => {
        el.textContent = '';
    });
}

function showVerificationPanel(modalRoot, email, message) {
    const formPanel = modalRoot.querySelector('[data-auth-panel="register-form"]');
    const verifyPanel = modalRoot.querySelector('[data-auth-panel="register-verify"]');
    if (!formPanel || !verifyPanel) {
        setAlert(modalRoot, message || 'Verification link sent.', 'success');
        return;
    }

    formPanel.classList.add('hidden');
    verifyPanel.classList.remove('hidden');

    const emailEl = verifyPanel.querySelector('[data-verify-email]');
    if (emailEl) emailEl.textContent = email || '';

    const input = verifyPanel.querySelector('input[name="email"]');
    if (input) input.value = email || '';

    const changeEmailLink = verifyPanel.querySelector('[data-verify-change-email-link]');
    if (changeEmailLink) {
        const basePath = '/email/verify';
        changeEmailLink.setAttribute(
            'href',
            email ? `${basePath}?email=${encodeURIComponent(email)}` : basePath,
        );
    }

    const msgEl = verifyPanel.querySelector('[data-verify-message]');
    if (msgEl && message) msgEl.textContent = message;
}

function showLoginVerificationPanel(modalRoot, email, message) {
    const formPanel = modalRoot.querySelector('[data-auth-panel="login-form"]');
    const verifyPanel = modalRoot.querySelector('[data-auth-panel="login-verify"]');
    if (!formPanel || !verifyPanel) {
        setAlert(modalRoot, message || 'Please verify your email. Check your inbox for the verification link.', 'success');
        return;
    }

    formPanel.classList.add('hidden');
    verifyPanel.classList.remove('hidden');

    const emailEl = verifyPanel.querySelector('[data-verify-email]');
    if (emailEl) emailEl.textContent = email || '';

    const input = verifyPanel.querySelector('input[name="email"]');
    if (input) input.value = email || '';

    const changeEmailLink = verifyPanel.querySelector('[data-verify-change-email-link]');
    if (changeEmailLink) {
        const basePath = '/email/verify';
        changeEmailLink.setAttribute(
            'href',
            email ? `${basePath}?email=${encodeURIComponent(email)}` : basePath,
        );
    }

    const msgEl = verifyPanel.querySelector('[data-verify-message]');
    if (msgEl && message) msgEl.textContent = message;
}

function showErrors(root, errors) {
    if (!errors) return;

    Object.entries(errors).forEach(([field, messages]) => {
        const target = root.querySelector(`[data-auth-error-for="${field}"]`);
        if (!target) return;
        const list = Array.isArray(messages) ? messages : [String(messages)];
        target.textContent = '';
        list.forEach((m) => {
            const div = document.createElement('div');
            div.textContent = m;
            target.appendChild(div);
        });
    });
}

function isAjaxAuthRequest(form) {
    return !!form?.dataset?.authForm;
}

function normalizeRedirectTo(value) {
    if (!value) return '';
    const s = String(value).trim();
    if (!s) return '';

    // Keep redirects same-origin.
    try {
        const url = new URL(s, window.location.origin);
        if (url.origin !== window.location.origin) return '';
        return url.pathname + url.search + url.hash;
    } catch (e) {
        return '';
    }
}

function setRedirectToForAuthForms(redirectTo) {
    const normalized = normalizeRedirectTo(redirectTo);
    window.__authRedirectTo = normalized;

    document.querySelectorAll('form[data-auth-form="login"], form[data-auth-form="register"]').forEach((form) => {
        const input = form.querySelector('input[name="redirect_to"], [data-auth-redirect-to]');
        if (!input) return;
        input.value = normalized;
    });
}

async function submitAuthForm(form) {
    const modalRoot = form.closest('[data-auth-modal]') || form;
    const submitBtn = form.querySelector('[data-auth-submit]');

    clearAlert(modalRoot);
    clearErrors(modalRoot);

    if (!window.axios) {
        setAlert(modalRoot, 'AJAX is not available. Please refresh.', 'error');
        return;
    }

    const action = form.getAttribute('action');
    const formData = new FormData(form);

    const originalText = submitBtn?.textContent;
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = submitBtn.dataset.loadingText || 'Please wait...';
    }

    try {
        // reCAPTCHA v3: generate a fresh token per submission.
        if (window.__recaptcha?.enabled && window.__recaptcha?.version === 'v3') {
            const recaptchaAction = form.dataset.recaptchaAction || form.dataset.authForm || 'submit';
            try {
                await ensureV3Token(form, recaptchaAction);
            } catch (e) {
                setAlert(modalRoot, 'Captcha failed to load. Please refresh and try again.', 'error');
                return;
            }
        }

        const response = await axios.post(action, formData, {
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = response?.data || {};

        if (form.dataset.authForm === 'forgot') {
            setAlert(modalRoot, data.message || 'Reset link sent.', 'success');
            return;
        }

        // Login/register: redirect or reload
        if (form.dataset.authForm === 'register' && data?.verification_required) {
            showVerificationPanel(modalRoot, data?.email, data?.message);
            return;
        }

        const redirectTo = data.redirect;
        if (redirectTo) {
            window.location.assign(redirectTo);
            return;
        }

        closeModal(form.dataset.authForm);
        window.location.reload();
    } catch (err) {
        const status = err?.response?.status;
        const data = err?.response?.data;

        if (status === 422) {
            // Unverified email on login — switch to verification panel.
            if (form.dataset.authForm === 'login' && data?.verification_required) {
                showLoginVerificationPanel(modalRoot, data?.email, data?.message);
                return;
            }

            showErrors(modalRoot, data?.errors);
            setAlert(modalRoot, data?.message || 'Please fix the errors and try again.', 'error');

            // Always reset the reCAPTCHA v2 widget after any validation error so
            // the user can solve it again. The previous token is spent/expired once
            // the first request was made, regardless of which field caused the error.
            if (window.__recaptcha?.version !== 'v3' && window.grecaptcha?.reset) {
                try {
                    window.grecaptcha.reset();
                } catch (e) {
                    // ignore
                }
            }
            return;
        }

        const message = data?.message || 'Something went wrong. Please try again.';
        setAlert(modalRoot, message, 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText || submitBtn.textContent;
        }
    }
}

function openFromUrlOrFlash() {
    const params = new URLSearchParams(window.location.search);
    const fromQuery = params.get('auth');
    const fromFlash = window.__authModalToOpen;

    const redirectFromQuery = params.get('redirect_to');
    if (redirectFromQuery) {
        setRedirectToForAuthForms(redirectFromQuery);
    }

    const target = fromFlash || fromQuery;
    if (!target) return;

    openModal(target);
}

document.addEventListener('click', (event) => {
    const trigger = event.target?.closest?.('[data-auth-trigger]');
    if (trigger) {
        event.preventDefault();

        if (trigger.dataset.authRedirect) {
            setRedirectToForAuthForms(trigger.dataset.authRedirect);
        }

        openModal(trigger.dataset.authTrigger);
        return;
    }

    const switcher = event.target?.closest?.('[data-auth-switch]');
    if (switcher) {
        event.preventDefault();
        const target = switcher.dataset.authSwitch;

        // Close all and open target to avoid stacked modals
        Object.keys(MODAL_NAMES).forEach((k) => closeModal(k));

        // If the switcher carries a reset flag, restore the form panel of the
        // target modal (e.g. "Back to login" from the login-verify panel).
        if (switcher.dataset.authSwitchReset) {
            const targetModalName = MODAL_NAMES[switcher.dataset.authSwitchReset] || switcher.dataset.authSwitchReset;
            const targetModalEl = document.querySelector(`[data-auth-modal="${switcher.dataset.authSwitchReset}"]`);
            if (targetModalEl) {
                const formPanel = targetModalEl.querySelector('[data-auth-panel="login-form"]');
                const verifyPanel = targetModalEl.querySelector('[data-auth-panel="login-verify"]');
                if (formPanel) formPanel.classList.remove('hidden');
                if (verifyPanel) verifyPanel.classList.add('hidden');
                clearAlert(targetModalEl);
                clearErrors(targetModalEl);
            }
        }

        openModal(target);

        if (window.__authRedirectTo) {
            setRedirectToForAuthForms(window.__authRedirectTo);
        }
        return;
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!isAjaxAuthRequest(form)) return;

    event.preventDefault();
    submitAuthForm(form);
});

document.addEventListener('click', async (event) => {
    const btn = event.target?.closest?.('[data-auth-resend-verification]');
    if (!btn) return;
    event.preventDefault();

    const root = btn.closest('[data-auth-modal]') || document;
    const form = btn.closest('form');
    if (!(form instanceof HTMLFormElement)) return;

    clearAlert(root);

    try {
        const formData = new FormData(form);
        const response = await axios.post(form.getAttribute('action'), formData, {
            headers: { 'Accept': 'application/json' },
        });
        const data = response?.data || {};
        setAlert(root, data.message || 'Verification link sent.', 'success');
    } catch (err) {
        const data = err?.response?.data;
        setAlert(root, data?.message || 'Unable to send verification email.', 'error');
    }
});

// Auto-open on initial load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', openFromUrlOrFlash);
} else {
    openFromUrlOrFlash();
}

// Expose for debugging
window.__openAuthModal = openModal;
