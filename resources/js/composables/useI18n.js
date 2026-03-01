import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import messages from '../i18n/messages';

const readKey = (source, key) =>
    key.split('.').reduce((current, segment) => {
        if (!current || typeof current !== 'object') {
            return undefined;
        }

        return current[segment];
    }, source);

const interpolate = (template, params) =>
    template.replace(/\{(\w+)\}/g, (_, key) => {
        if (Object.hasOwn(params, key)) {
            return String(params[key]);
        }

        return `{${key}}`;
    });

export function useI18n() {
    const page = usePage();

    const locale = computed(() => page.props.locale ?? 'en');
    const availableLocales = computed(() => page.props.availableLocales ?? []);

    const t = (key, params = {}) => {
        const localeMessages = messages[locale.value] ?? messages.en;
        const fallbackMessages = messages.en;

        const value = readKey(localeMessages, key) ?? readKey(fallbackMessages, key);

        if (typeof value !== 'string') {
            return key;
        }

        return interpolate(value, params);
    };

    return {
        t,
        locale,
        availableLocales,
    };
}
