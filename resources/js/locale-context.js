const KEY = 'ui_locale';

export function getCurrentLocale() {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.localStorage.getItem(KEY);
}

export function setCurrentLocale(locale) {
    if (typeof window === 'undefined') {
        return;
    }

    if (!locale) {
        window.localStorage.removeItem(KEY);
        return;
    }

    window.localStorage.setItem(KEY, String(locale));
}
