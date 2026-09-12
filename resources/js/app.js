

import 'bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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
