<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AppLayout from '../../Layouts/AppLayout.vue';
import FiltersBar from '../../Components/FiltersBar.vue';
import { useI18n } from '../../composables/useI18n';

const { t, locale } = useI18n();

const subscriptions = ref([]);
const categories = ref([]);
const loading = ref(false);
const error = ref('');

const filters = ref({
    from: '',
    to: '',
    category_id: '',
    status: '',
});

const statusOptions = ['active', 'paused', 'cancelled'];

const form = ref({
    name: '',
    vendor: '',
    amount_minor: 0,
    currency_code: 'RUB',
    exchange_rate: 1,
    billing_cycle: 'monthly',
    next_charge_at: new Date().toISOString().slice(0, 10),
    status: 'active',
    category_id: '',
    notes: '',
});

const editId = ref(null);

const formatMoney = (minor, currency) => {
    const value = Number(minor ?? 0) / 100;
    const numberLocale = locale.value === 'ru' ? 'ru-RU' : 'en-US';

    return new Intl.NumberFormat(numberLocale, {
        style: 'currency',
        currency: currency || 'RUB',
        maximumFractionDigits: 2,
    }).format(value);
};

const loadCategories = async () => {
    const response = await axios.get('/api/v1/categories', {
        params: { type: 'subscription', is_active: 1 },
    });

    categories.value = response.data.data;
};

const loadSubscriptions = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await axios.get('/api/v1/subscriptions', { params: filters.value });
        subscriptions.value = response.data.data;
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('subscriptions.loadError');
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    editId.value = null;
    form.value = {
        name: '',
        vendor: '',
        amount_minor: 0,
        currency_code: 'RUB',
        exchange_rate: 1,
        billing_cycle: 'monthly',
        next_charge_at: new Date().toISOString().slice(0, 10),
        status: 'active',
        category_id: '',
        notes: '',
    };
};

const saveSubscription = async () => {
    error.value = '';

    try {
        if (editId.value) {
            await axios.patch(`/api/v1/subscriptions/${editId.value}`, form.value);
        } else {
            await axios.post('/api/v1/subscriptions', form.value);
        }

        resetForm();
        await loadSubscriptions();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('subscriptions.saveError');
    }
};

const startEdit = (subscription) => {
    editId.value = subscription.id;
    form.value = {
        name: subscription.name,
        vendor: subscription.vendor ?? '',
        amount_minor: subscription.amount_minor,
        currency_code: subscription.currency_code,
        exchange_rate: subscription.exchange_rate,
        billing_cycle: subscription.billing_cycle,
        next_charge_at: subscription.next_charge_at,
        status: subscription.status,
        category_id: subscription.category_id ?? '',
        notes: subscription.notes ?? '',
    };
};

const removeSubscription = async (subscriptionId) => {
    try {
        await axios.delete(`/api/v1/subscriptions/${subscriptionId}`);
        await loadSubscriptions();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('subscriptions.deleteError');
    }
};

const resetFilters = () => {
    filters.value = {
        from: '',
        to: '',
        category_id: '',
        status: '',
    };

    loadSubscriptions();
};

const statusLabel = (status) => t(`subscriptions.${status}`);

onMounted(async () => {
    try {
        await loadCategories();
        await loadSubscriptions();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('subscriptions.initError');
    }
});
</script>

<template>
    <AppLayout>
        <section class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold">{{ t('subscriptions.title') }}</h2>
                <p class="text-sm text-slate-600">{{ t('subscriptions.subtitle') }}</p>
            </div>

            <FiltersBar
                v-model="filters"
                :categories="categories"
                :show-status="true"
                :status-options="statusOptions"
                @apply="loadSubscriptions"
                @reset="resetFilters"
            />

            <p v-if="error" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ error }}</p>

            <div class="card space-y-4">
                <h3 class="text-lg font-semibold">{{ editId ? t('subscriptions.edit') : t('subscriptions.create') }}</h3>

                <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.name') }}</label>
                        <input v-model="form.name" class="input" type="text" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('subscriptions.vendor') }}</label>
                        <input v-model="form.vendor" class="input" type="text" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.amountMinor') }}</label>
                        <input v-model.number="form.amount_minor" class="input" type="number" min="1" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.currency') }}</label>
                        <input v-model="form.currency_code" class="input" type="text" maxlength="3" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.rate') }}</label>
                        <input v-model.number="form.exchange_rate" class="input" type="number" min="0.000001" step="0.000001" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.category') }}</label>
                        <select v-model="form.category_id" class="select">
                            <option value="">{{ t('common.noCategory') }}</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('subscriptions.billingCycle') }}</label>
                        <select v-model="form.billing_cycle" class="select">
                            <option value="monthly">{{ t('subscriptions.monthly') }}</option>
                            <option value="yearly">{{ t('subscriptions.yearly') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('subscriptions.nextCharge') }}</label>
                        <input v-model="form.next_charge_at" class="input" type="date" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.status') }}</label>
                        <select v-model="form.status" class="select">
                            <option value="active">{{ t('subscriptions.active') }}</option>
                            <option value="paused">{{ t('subscriptions.paused') }}</option>
                            <option value="cancelled">{{ t('subscriptions.cancelled') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs text-slate-500">{{ t('subscriptions.notes') }}</label>
                    <textarea v-model="form.notes" class="textarea" rows="2"></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="button" class="btn-primary" @click="saveSubscription">{{ editId ? t('common.update') : t('common.create') }}</button>
                    <button type="button" class="btn-secondary" @click="resetForm">{{ t('common.clear') }}</button>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-4 text-lg font-semibold">{{ t('subscriptions.list') }}</h3>

                <p v-if="loading" class="text-sm text-slate-500">{{ t('subscriptions.loading') }}</p>

                <div v-else class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ t('subscriptions.nextCharge') }}</th>
                                <th>{{ t('common.name') }}</th>
                                <th>{{ t('common.status') }}</th>
                                <th>{{ t('common.category') }}</th>
                                <th>{{ t('common.amountMinor') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="subscription in subscriptions" :key="subscription.id">
                                <td>{{ subscription.next_charge_at }}</td>
                                <td>
                                    <p>{{ subscription.name }}</p>
                                    <p class="text-xs text-slate-500">{{ subscription.vendor || t('subscriptions.noVendor') }}</p>
                                </td>
                                <td>
                                    <span :class="subscription.status === 'active' ? 'badge-success' : 'badge-muted'">
                                        {{ statusLabel(subscription.status) }}
                                    </span>
                                </td>
                                <td>{{ subscription.category?.name || '—' }}</td>
                                <td>{{ formatMoney(subscription.amount_minor, subscription.currency_code) }}</td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button class="btn-secondary" type="button" @click="startEdit(subscription)">{{ t('common.edit') }}</button>
                                        <button class="btn-secondary" type="button" @click="removeSubscription(subscription.id)">{{ t('common.delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
