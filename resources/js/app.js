

import * as bootstrap from 'bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const categorySelect = document.querySelector('select[name="category_id"]');
const specificationGroups = document.querySelectorAll('[data-spec-category]');

function updateSpecificationGroups() {
    const slug = categorySelect?.selectedOptions[0]?.dataset.categorySlug;
    specificationGroups.forEach((group) => {
        group.hidden = group.dataset.specCategory !== slug;
        group.querySelectorAll('input, select, textarea').forEach((control) => {
            control.disabled = group.hidden;
        });
    });
}

if (categorySelect && specificationGroups.length) {
    categorySelect.addEventListener('change', updateSpecificationGroups);
    updateSpecificationGroups();
}

const logoutDialogElement = document.querySelector('#logoutDialog');
const logoutDialog = logoutDialogElement ? new bootstrap.Modal(logoutDialogElement) : null;
let pendingLogoutForm = null;

document.querySelectorAll(`form[action$="/logout"]`).forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (form.dataset.logoutConfirmed === 'true') return;
        event.preventDefault();
        pendingLogoutForm = form;
        logoutDialog?.show();
    });
});

document.querySelector('[data-confirm-logout]')?.addEventListener('click', () => {
    if (!pendingLogoutForm) return;
    pendingLogoutForm.dataset.logoutConfirmed = 'true';
    pendingLogoutForm.requestSubmit();
});

logoutDialogElement?.addEventListener('hidden.bs.modal', () => {
    pendingLogoutForm = null;
});

const consentCookie = 'winner_cookie_consent';
const consentPanel = document.querySelector('[data-cookie-consent]');

function readCookie(name) {
    return document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith(`${name}=`))
        ?.split('=')[1];
}

function showCookiePanel() {
    if (consentPanel) {
        consentPanel.hidden = false;
    }
}

if (consentPanel && !readCookie(consentCookie)) {
    showCookiePanel();
}

document.querySelectorAll('[data-cookie-choice]').forEach((button) => {
    button.addEventListener('click', () => {
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${consentCookie}=${button.dataset.cookieChoice}; Path=/; Max-Age=31536000; SameSite=Lax${secure}`;
        consentPanel.hidden = true;
    });
});

document.querySelectorAll('[data-cookie-settings]').forEach((button) => {
    button.addEventListener('click', () => {
        document.cookie = `${consentCookie}=; Path=/; Max-Age=0; SameSite=Lax`;
        showCookiePanel();
        consentPanel?.focus();
    });
});
