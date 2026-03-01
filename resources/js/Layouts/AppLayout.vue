<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import LanguageSwitcher from '../Components/LanguageSwitcher.vue';
import { useI18n } from '../composables/useI18n';
import { getCurrentOrganizationId, setCurrentOrganizationId } from '../org-context';

const page = usePage();
const { t } = useI18n();

const user = computed(() => page.props.auth?.user ?? null);
const organizations = computed(() => {
    const raw = page.props.organizations;

    if (Array.isArray(raw)) {
        return raw;
    }

    if (raw?.data && Array.isArray(raw.data)) {
        return raw.data;
    }

    return [];
});

const selectedOrganizationId = ref(null);
const orgSwitchError = ref('');

const navigation = computed(() => [
    { label: t('nav.dashboard'), href: '/dashboard' },
    { label: t('nav.expenses'), href: '/expenses' },
    { label: t('nav.subscriptions'), href: '/subscriptions' },
    { label: t('nav.categories'), href: '/categories' },
    { label: t('nav.settings'), href: '/settings/organization' },
]);

const initializeOrganizationSelection = () => {
    const fromStorage = getCurrentOrganizationId();
    const fromServer = page.props.currentOrganizationId;

    selectedOrganizationId.value = fromStorage ?? fromServer ?? organizations.value[0]?.id ?? null;

    if (selectedOrganizationId.value) {
        setCurrentOrganizationId(selectedOrganizationId.value);
    }
};

initializeOrganizationSelection();

watch(
    () => organizations.value,
    (next) => {
        if (!selectedOrganizationId.value && next.length) {
            selectedOrganizationId.value = next[0].id;
            setCurrentOrganizationId(next[0].id);
        }
    },
    { deep: true },
);

const isActive = (href) => page.url.startsWith(href);

const switchOrganization = async () => {
    orgSwitchError.value = '';

    if (!selectedOrganizationId.value) {
        return;
    }

    try {
        await axios.post(`/api/v1/organizations/${selectedOrganizationId.value}/switch`);
        setCurrentOrganizationId(selectedOrganizationId.value);

        router.reload({
            only: ['organizations', 'currentOrganizationId'],
        });
    } catch (error) {
        orgSwitchError.value = t('layout.switchOrganizationError');
    }
};
</script>

<template>
    <div class="app-shell">
        <header class="mb-6 rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm backdrop-blur">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.16em] text-teal-700">{{ t('app.name') }}</p>
                        <h1 class="text-lg font-semibold text-slate-900">{{ user?.name || t('app.workspace') }}</h1>
                    </div>
                </div>

                <div class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">{{ t('layout.organization') }}</label>
                        <div class="flex gap-2">
                            <select v-model="selectedOrganizationId" class="select">
                                <option v-for="organization in organizations" :key="organization.id" :value="organization.id">
                                    {{ organization.name }}
                                </option>
                            </select>
                            <button type="button" class="btn-secondary" @click="switchOrganization">{{ t('common.apply') }}</button>
                        </div>
                        <p v-if="orgSwitchError" class="mt-1 text-xs text-rose-600">{{ orgSwitchError }}</p>
                    </div>

                    <LanguageSwitcher />

                    <form method="post" action="/logout" class="flex items-end">
                        <input type="hidden" name="_token" :value="page.props?.csrf_token" />
                        <button class="btn-secondary w-full" type="submit">{{ t('layout.logout') }}</button>
                    </form>
                </div>
            </div>
        </header>

        <nav class="mb-6 flex flex-wrap gap-2">
            <Link
                v-for="item in navigation"
                :key="item.href"
                :href="item.href"
                class="btn-secondary"
                :class="isActive(item.href) ? 'border-teal-600 bg-teal-50 text-teal-700' : ''"
            >
                {{ item.label }}
            </Link>
        </nav>

        <main class="flex-1">
            <slot />
        </main>
    </div>
</template>
