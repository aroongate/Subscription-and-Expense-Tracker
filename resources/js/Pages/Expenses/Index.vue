<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AppLayout from '../../Layouts/AppLayout.vue';
import FiltersBar from '../../Components/FiltersBar.vue';
import { useI18n } from '../../composables/useI18n';

const { t, locale } = useI18n();

const expenses = ref([]);
const categories = ref([]);
const loading = ref(false);
const error = ref('');

const filters = ref({
    from: '',
    to: '',
    category_id: '',
});

const form = ref({
    title: '',
    amount_minor: 0,
    currency_code: 'RUB',
    exchange_rate: 1,
    spent_at: new Date().toISOString().slice(0, 10),
    category_id: '',
    note: '',
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
        params: { type: 'expense', is_active: 1 },
    });

    categories.value = response.data.data;
};

const loadExpenses = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await axios.get('/api/v1/expenses', { params: filters.value });
        expenses.value = response.data.data;
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('expenses.loadError');
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    editId.value = null;
    form.value = {
        title: '',
        amount_minor: 0,
        currency_code: 'RUB',
        exchange_rate: 1,
        spent_at: new Date().toISOString().slice(0, 10),
        category_id: '',
        note: '',
    };
};

const saveExpense = async () => {
    error.value = '';

    try {
        if (editId.value) {
            await axios.patch(`/api/v1/expenses/${editId.value}`, form.value);
        } else {
            await axios.post('/api/v1/expenses', form.value);
        }

        resetForm();
        await loadExpenses();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('expenses.saveError');
    }
};

const startEdit = (expense) => {
    editId.value = expense.id;
    form.value = {
        title: expense.title,
        amount_minor: expense.amount_minor,
        currency_code: expense.currency_code,
        exchange_rate: expense.exchange_rate,
        spent_at: expense.spent_at,
        category_id: expense.category_id ?? '',
        note: expense.note ?? '',
    };
};

const removeExpense = async (expenseId) => {
    try {
        await axios.delete(`/api/v1/expenses/${expenseId}`);
        await loadExpenses();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('expenses.deleteError');
    }
};

const resetFilters = () => {
    filters.value = {
        from: '',
        to: '',
        category_id: '',
    };

    loadExpenses();
};

onMounted(async () => {
    try {
        await loadCategories();
        await loadExpenses();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('expenses.initError');
    }
});
</script>

<template>
    <AppLayout>
        <section class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold">{{ t('expenses.title') }}</h2>
                <p class="text-sm text-slate-600">{{ t('expenses.subtitle') }}</p>
            </div>

            <FiltersBar
                v-model="filters"
                :categories="categories"
                @apply="loadExpenses"
                @reset="resetFilters"
            />

            <p v-if="error" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ error }}</p>

            <div class="card space-y-4">
                <h3 class="text-lg font-semibold">{{ editId ? t('expenses.edit') : t('expenses.create') }}</h3>

                <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('expenses.titleLabel') }}</label>
                        <input v-model="form.title" class="input" type="text" />
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
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.date') }}</label>
                        <input v-model="form.spent_at" class="input" type="date" />
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

                <div>
                    <label class="mb-1 block text-xs text-slate-500">{{ t('common.note') }}</label>
                    <textarea v-model="form.note" class="textarea" rows="2"></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="button" class="btn-primary" @click="saveExpense">{{ editId ? t('common.update') : t('common.create') }}</button>
                    <button type="button" class="btn-secondary" @click="resetForm">{{ t('common.clear') }}</button>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-4 text-lg font-semibold">{{ t('expenses.list') }}</h3>

                <p v-if="loading" class="text-sm text-slate-500">{{ t('expenses.loading') }}</p>

                <div v-else class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ t('common.date') }}</th>
                                <th>{{ t('expenses.titleLabel') }}</th>
                                <th>{{ t('common.category') }}</th>
                                <th>{{ t('common.amountMinor') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="expense in expenses" :key="expense.id">
                                <td>{{ expense.spent_at }}</td>
                                <td>{{ expense.title }}</td>
                                <td>{{ expense.category?.name || '—' }}</td>
                                <td>{{ formatMoney(expense.amount_minor, expense.currency_code) }}</td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button class="btn-secondary" type="button" @click="startEdit(expense)">{{ t('common.edit') }}</button>
                                        <button class="btn-secondary" type="button" @click="removeExpense(expense.id)">{{ t('common.delete') }}</button>
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
