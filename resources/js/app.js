

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

document.querySelector('[data-spin-offer]')?.addEventListener('click', async (event) => {
    const button = event.currentTarget;
    const result = document.querySelector('[data-spin-result]');
    const wheel = button.closest('.first-order-reward')?.querySelector('.offer-wheel');
    button.disabled = true;
    wheel?.classList.add('is-spinning');
    result.textContent = 'Selecting your offer...';
    try {
        const response = await fetch(button.dataset.url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'},
        });
        const payload = await response.json();
        if (! response.ok) throw new Error(payload.message || Object.values(payload.errors || {})[0]?.[0] || 'Unable to claim offer.');
        result.innerHTML = `Your reward: <strong>${payload.label}</strong>. It will be applied automatically.`;
        button.remove();
    } catch (error) {
        result.textContent = error.message;
        button.disabled = false;
        wheel?.classList.remove('is-spinning');
    }
});

const reminder = document.querySelector('[data-store-reminder]');
if (reminder) {
    const type = reminder.dataset.storeReminder;
    const dismissedAt = Number(localStorage.getItem(`winner_${type}_reminder_dismissed`) || 0);
    const shownThisSession = sessionStorage.getItem(`winner_${type}_reminder_shown`);
    if (! shownThisSession && Date.now() - dismissedAt > 7 * 24 * 60 * 60 * 1000) {
        window.setTimeout(() => {
            reminder.hidden = false;
            sessionStorage.setItem(`winner_${type}_reminder_shown`, '1');
        }, 2200);
    }
    reminder.querySelector('[data-dismiss-reminder]')?.addEventListener('click', () => {
        localStorage.setItem(`winner_${type}_reminder_dismissed`, String(Date.now()));
        reminder.remove();
    });
}

const celebration = document.querySelector('[data-order-celebration]');
if (celebration && ! matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const key = `winner_order_celebrated_${celebration.dataset.orderCelebration}`;
    if (! sessionStorage.getItem(key)) {
        sessionStorage.setItem(key, '1');
        celebration.className = 'order-confetti';
        const colors = ['#163f32', '#9cbd4b', '#f4efdF', '#252723'];
        for (let index = 0; index < 28; index += 1) {
            const piece = document.createElement('i');
            piece.style.setProperty('--x', `${Math.random() * 100}vw`);
            piece.style.setProperty('--delay', `${Math.random() * .35}s`);
            piece.style.setProperty('--color', colors[index % colors.length]);
            celebration.appendChild(piece);
        }
        window.setTimeout(() => celebration.remove(), 2400);
    }
}

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
