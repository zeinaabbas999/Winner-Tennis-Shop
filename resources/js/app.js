

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
const offerElement = document.querySelector('[data-offer-modal]');
const offerModal = offerElement ? new bootstrap.Modal(offerElement, {backdrop: 'static'}) : null;
const reminder = document.querySelector('[data-store-reminder]');
let offerClaimed = false;

function readCookie(name) {
    return document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith(`${name}=`))
        ?.split('=')[1];
}

function showCookiePanel() {
    if (consentPanel) {
        consentPanel.hidden = false;
        consentPanel.focus();
    }
}

function optionalConsentAccepted() {
    return readCookie(consentCookie) === 'accepted';
}

function scheduleReminder() {
    if (! reminder || ! optionalConsentAccepted() || sessionStorage.getItem('winner_reminder_shown')) return;
    const pageViews = Number(sessionStorage.getItem('winner_normal_page_views') || 0) + 1;
    sessionStorage.setItem('winner_normal_page_views', String(pageViews));
    if (pageViews < 3 || Math.random() > .15) return;
    const type = reminder.dataset.storeReminder;
    const dismissedAt = Number(localStorage.getItem(`winner_${type}_reminder_dismissed`) || 0);
    if (Date.now() - dismissedAt < 6 * 60 * 60 * 1000) return;
    window.setTimeout(() => {
        if (! document.querySelector('.modal.show') && consentPanel?.hidden !== false) {
            reminder.hidden = false;
            sessionStorage.setItem('winner_reminder_shown', '1');
        }
    }, 2600);
}

function continueEngagementFlow() {
    if (offerModal) {
        window.setTimeout(() => {
            offerElement.inert = false;
            offerModal.show();
        }, 850);
    } else {
        scheduleReminder();
    }
}

if (! readCookie(consentCookie)) {
    showCookiePanel();
} else {
    continueEngagementFlow();
}

document.querySelectorAll('[data-cookie-choice]').forEach((button) => {
    button.addEventListener('click', () => {
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${consentCookie}=${button.dataset.cookieChoice}; Path=/; Max-Age=31536000; SameSite=Lax${secure}`;
        consentPanel.hidden = true;
        if (button.dataset.cookieChoice !== 'accepted') {
            document.querySelectorAll('[data-optional-personalization]').forEach((section) => section.remove());
            Object.keys(localStorage).filter((key) => key.startsWith('winner_')).forEach((key) => localStorage.removeItem(key));
            Object.keys(sessionStorage).filter((key) => key.startsWith('winner_') && ! key.startsWith('winner_order_celebrated_')).forEach((key) => sessionStorage.removeItem(key));
        }
        continueEngagementFlow();
    });
});

document.querySelector('[data-spin-offer]')?.addEventListener('click', async (event) => {
    const button = event.currentTarget;
    const result = offerElement.querySelector('[data-spin-result]');
    const wheel = offerElement.querySelector('.offer-wheel');
    button.disabled = true;
    wheel.classList.add('is-spinning');
    result.textContent = 'Selecting your offer...';
    try {
        const response = await fetch(offerElement.dataset.spinUrl, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'},
        });
        const payload = await response.json();
        if (! response.ok) throw new Error(payload.message || Object.values(payload.errors || {})[0]?.[0] || 'Unable to claim offer.');
        result.textContent = `You unlocked ${payload.label}. It will apply automatically at checkout.`;
        offerClaimed = true;
        button.remove();
    } catch (error) {
        result.textContent = error.message;
        button.disabled = false;
        wheel.classList.remove('is-spinning');
    }
});

offerElement?.addEventListener('hidden.bs.modal', () => {
    if (! offerClaimed) {
        fetch(offerElement.dataset.dismissUrl, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json'},
        });
    }
});

reminder?.querySelector('[data-dismiss-reminder]')?.addEventListener('click', () => {
    if (optionalConsentAccepted()) {
        localStorage.setItem(`winner_${reminder.dataset.storeReminder}_reminder_dismissed`, String(Date.now()));
    }
    reminder.remove();
});

document.querySelectorAll('[data-cookie-settings]').forEach((button) => {
    button.addEventListener('click', () => {
        offerModal?.hide();
        if (reminder) reminder.hidden = true;
        document.cookie = `${consentCookie}=; Path=/; Max-Age=0; SameSite=Lax`;
        showCookiePanel();
    });
});
