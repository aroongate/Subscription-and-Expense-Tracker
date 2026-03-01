const KEY = 'current_org_id';

export function getCurrentOrganizationId() {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.localStorage.getItem(KEY);
}

export function setCurrentOrganizationId(organizationId) {
    if (typeof window === 'undefined') {
        return;
    }

    if (organizationId === null || organizationId === undefined || organizationId === '') {
        window.localStorage.removeItem(KEY);
        return;
    }

    window.localStorage.setItem(KEY, String(organizationId));
}
