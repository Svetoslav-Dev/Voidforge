const consentRoot = document.querySelector<HTMLElement>('[data-cookie-consent]');
const preferencesModal = document.querySelector<HTMLElement>('[data-cookie-preferences-modal]');
const optionalToggle = document.querySelector<HTMLInputElement>('[data-cookie-preferences-optional]');
const openPreferencesButtons = Array.from(document.querySelectorAll<HTMLElement>('[data-cookie-consent-open-preferences]'));
const consentOptionButtons = Array.from(document.querySelectorAll<HTMLElement>('[data-cookie-consent-select]'));
const savePreferencesButton = document.querySelector<HTMLElement>('[data-cookie-preferences-save]');
const essentialPreferencesButton = document.querySelector<HTMLElement>('[data-cookie-preferences-essential]');
const closePreferencesButtons = Array.from(document.querySelectorAll<HTMLElement>('[data-cookie-preferences-close]'));

const consentStorageKey = 'voidforgestore.cookie-consent';
const consentCookieName = 'voidforgestore_cookie_consent';

type ConsentValue = 'all' | 'essential';

function parseConsentValue(value: string | null): ConsentValue | null {
    if (value === 'all' || value === 'essential') {
        return value;
    }

    return null;
}

function readConsentCookie(): ConsentValue | null {
    const cookie = document.cookie
        .split('; ')
        .find((entry) => entry.startsWith(`${consentCookieName}=`));

    if (!cookie) {
        return null;
    }

    const [, rawValue = ''] = cookie.split('=');

    return parseConsentValue(decodeURIComponent(rawValue));
}

function writeConsentCookie(value: ConsentValue): void {
    const maxAge = 60 * 60 * 24 * 365;
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${consentCookieName}=${encodeURIComponent(value)}; path=/; max-age=${maxAge}; SameSite=Lax${secure}`;
}

function readConsent(): ConsentValue | null {
    try {
        return parseConsentValue(window.localStorage.getItem(consentStorageKey)) ?? readConsentCookie();
    } catch {
        return readConsentCookie();
    }
}

function persistConsent(value: ConsentValue): void {
    try {
        window.localStorage.setItem(consentStorageKey, value);
    } catch {
        // Fall back to a regular cookie when browser storage is unavailable.
    }

    try {
        writeConsentCookie(value);
    } catch {
        // Ignore cookie write failures and still let the UI continue.
    }
}

function syncWithServer(value: ConsentValue): void {
    const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
    fetch('/cookie-consent', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ consent: value }),
    }).catch(() => {
        // Client-side consent is already stored; server sync is best-effort.
    });
}

function writeConsent(value: ConsentValue): void {
    syncOptionalToggle(value);
    hideConsentBanner();
    closePreferences();
    persistConsent(value);
    syncWithServer(value);
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
    } else {
        hideConsentBanner();
    }

    essentialPreferencesButton?.addEventListener('click', () => {
        writeConsent('essential');
    });

    openPreferencesButtons.forEach((button) => {
        button.addEventListener('click', () => {
            openPreferences();
        });
    });

    consentOptionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.cookieConsentSelect;

            if (action === 'all') {
                writeConsent('all');
                return;
            }

            if (action === 'essential') {
                writeConsent('essential');
                return;
            }

            if (action === 'preferences') {
                openPreferences();
            }
        });
    });

    savePreferencesButton?.addEventListener('click', () => {
        writeConsent(optionalToggle?.checked ? 'all' : 'essential');
    });

    closePreferencesButtons.forEach((button) => {
        button.addEventListener('click', () => {
            closePreferences();
        });
    });
}

initializeCookieConsent();
