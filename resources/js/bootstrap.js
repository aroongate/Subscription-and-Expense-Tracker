import axios from 'axios';
import { getCurrentLocale } from './locale-context';
import { getCurrentOrganizationId } from './org-context';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common.Accept = 'application/json';

const token = document.head.querySelector('meta[name="csrf-token"]');

if (token?.content) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.axios.interceptors.request.use((config) => {
    const organizationId = getCurrentOrganizationId();
    const locale = getCurrentLocale();

    if (organizationId) {
        config.headers['X-Org-Id'] = organizationId;
    }

    if (locale) {
        config.headers['X-Locale'] = locale;
    }

    return config;
});
