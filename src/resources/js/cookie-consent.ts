const consentRoot = document.querySelector<HTMLElement>('[data-cookie-consent]');
const preferencesModal = document.querySelector<HTMLElement>('[data-cookie-preferences-modal]');
const optionalToggle = document.querySelector<HTMLInputElement>('[data-cookie-preferences-optional]');

const consentStorageKey = 'voidforgestore.cookie-consent';

type ConsentValue = 'all' | 'essential';

function readConsent(): ConsentValue | null {
    const value = window.localStorage.getItem(consentStorageKey);

    if (value === 'all' || value === 'essential') {
        return value;
    }

    return null;
}

function writeConsent(value: ConsentValue): void {
    window.localStorage.setItem(consentStorageKey, value);
    syncOptionalToggle(value);
    hideConsentBanner();
    closePreferences();
}

function syncOptionalToggle(value: ConsentValue | null): void {
    if (!optionalToggle) {
        return;
    }

    optionalToggle.checked = value === 'all';
}

function hideConsentBanner(): void {
    if (!consentRoot) {
        return;
    }

    consentRoot.hidden = true;
}

function showConsentBanner(): void {
    if (!consentRoot) {
        return;
    }

    consentRoot.hidden = false;
}

function openPreferences(): void {
    if (!preferencesModal) {
        return;
    }

    preferencesModal.hidden = false;
}

function closePreferences(): void {
    if (!preferencesModal) {
        return;
    }

    preferencesModal.hidden = true;
}

function initializeCookieConsent(): void {
    if (!consentRoot) {
        return;
    }

    const existingConsent = readConsent();

    syncOptionalToggle(existingConsent);

    if (existingConsent === null) {
        showConsentBanner();
    }

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        if (target.hasAttribute('data-cookie-consent-accept')) {
            writeConsent('all');
            return;
        }

        if (target.hasAttribute('data-cookie-consent-reject') || target.hasAttribute('data-cookie-preferences-essential')) {
            writeConsent('essential');
            return;
        }

        if (target.hasAttribute('data-cookie-consent-open-preferences')) {
            openPreferences();
            return;
        }

        if (target.hasAttribute('data-cookie-preferences-save')) {
            writeConsent(optionalToggle?.checked ? 'all' : 'essential');
            return;
        }

        if (target.hasAttribute('data-cookie-preferences-close')) {
            closePreferences();
        }
    });
}

initializeCookieConsent();
