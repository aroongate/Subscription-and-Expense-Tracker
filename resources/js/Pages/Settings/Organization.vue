<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useI18n } from '../../composables/useI18n';
import { getCurrentOrganizationId } from '../../org-context';

const { t } = useI18n();

const organizations = ref([]);
const members = ref([]);
const loadingMembers = ref(false);
const error = ref('');

const newOrganization = ref({
    name: '',
    base_currency_code: 'RUB',
});

const memberForm = ref({
    email: '',
    role: 'member',
});

const selectedOrganizationId = computed(() => getCurrentOrganizationId());

const roleLabel = (role) => {
    if (role === 'admin') {
        return t('settings.admin');
    }

    if (role === 'owner') {
        return t('settings.owner');
    }

    return t('settings.member');
};

const loadOrganizations = async () => {
    const response = await axios.get('/api/v1/organizations');
    organizations.value = response.data.data;
};

const loadMembers = async () => {
    if (!selectedOrganizationId.value) {
        members.value = [];
        return;
    }

    loadingMembers.value = true;
    error.value = '';

    try {
        const response = await axios.get(`/api/v1/organizations/${selectedOrganizationId.value}/members`);
        members.value = response.data.data;
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('settings.loadMembersError');
    } finally {
        loadingMembers.value = false;
    }
};

const createOrganization = async () => {
    error.value = '';

    try {
        await axios.post('/api/v1/organizations', newOrganization.value);
        newOrganization.value = {
            name: '',
            base_currency_code: 'RUB',
        };
        await loadOrganizations();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('settings.createOrganizationError');
    }
};

const addMember = async () => {
    error.value = '';

    if (!selectedOrganizationId.value) {
        error.value = t('settings.selectOrganizationFirst');
        return;
    }

    try {
        await axios.post(`/api/v1/organizations/${selectedOrganizationId.value}/members`, memberForm.value);
        memberForm.value = { email: '', role: 'member' };
        await loadMembers();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('settings.addMemberError');
    }
};

const updateRole = async (userId, role) => {
    error.value = '';

    try {
        await axios.patch(`/api/v1/organizations/${selectedOrganizationId.value}/members/${userId}`, { role });
        await loadMembers();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('settings.updateMemberError');
    }
};

const removeMember = async (userId) => {
    error.value = '';

    try {
        await axios.delete(`/api/v1/organizations/${selectedOrganizationId.value}/members/${userId}`);
        await loadMembers();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('settings.removeMemberError');
    }
};

onMounted(async () => {
    try {
        await loadOrganizations();
        await loadMembers();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('settings.initError');
    }
});
</script>

<template>
    <AppLayout>
        <section class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold">{{ t('settings.title') }}</h2>
                <p class="text-sm text-slate-600">{{ t('settings.subtitle') }}</p>
            </div>

            <p v-if="error" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ error }}</p>

            <div class="card space-y-4">
                <h3 class="text-lg font-semibold">{{ t('settings.createOrganization') }}</h3>

                <div class="grid gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.name') }}</label>
                        <input v-model="newOrganization.name" class="input" type="text" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.baseCurrency') }}</label>
                        <input v-model="newOrganization.base_currency_code" class="input" type="text" maxlength="3" />
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="btn-primary" @click="createOrganization">{{ t('common.create') }}</button>
                    </div>
                </div>
            </div>

            <div class="card space-y-4">
                <h3 class="text-lg font-semibold">{{ t('settings.members') }}</h3>

                <p class="text-sm text-slate-600">{{ t('settings.currentOrganizationId', { id: selectedOrganizationId || t('common.notSelected') }) }}</p>

                <div class="grid gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('settings.memberEmail') }}</label>
                        <input v-model="memberForm.email" class="input" type="email" />
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.role') }}</label>
                        <select v-model="memberForm.role" class="select">
                            <option value="admin">{{ t('settings.admin') }}</option>
                            <option value="member">{{ t('settings.member') }}</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" class="btn-primary" @click="addMember">{{ t('settings.addMember') }}</button>
                    </div>
                </div>

                <p v-if="loadingMembers" class="text-sm text-slate-500">{{ t('settings.loadingMembers') }}</p>

                <div v-else class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ t('common.name') }}</th>
                                <th>{{ t('common.email') }}</th>
                                <th>{{ t('common.role') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="member in members" :key="member.id">
                                <td>{{ member.name }}</td>
                                <td>{{ member.email }}</td>
                                <td>{{ roleLabel(member.pivot?.role || 'member') }}</td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button class="btn-secondary" type="button" @click="updateRole(member.id, 'admin')">{{ t('settings.admin') }}</button>
                                        <button class="btn-secondary" type="button" @click="updateRole(member.id, 'member')">{{ t('settings.member') }}</button>
                                        <button class="btn-secondary" type="button" @click="removeMember(member.id)">{{ t('settings.remove') }}</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3 class="text-lg font-semibold">{{ t('settings.yourOrganizations') }}</h3>
                <ul class="mt-3 space-y-2">
                    <li v-for="organization in organizations" :key="organization.id" class="rounded-lg border border-slate-200 px-3 py-2">
                        <p class="font-medium text-slate-900">{{ organization.name }}</p>
                        <p class="text-xs text-slate-500">{{ t('common.baseCurrency') }}: {{ organization.base_currency_code }}</p>
                    </li>
                </ul>
            </div>
        </section>
    </AppLayout>
</template>
