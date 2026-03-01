<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AppLayout from '../../Layouts/AppLayout.vue';
import MetricCard from '../../Components/MetricCard.vue';
import FiltersBar from '../../Components/FiltersBar.vue';
import { useI18n } from '../../composables/useI18n';

const { t, locale } = useI18n();

const filters = ref({
    from: '',
    to: '',
    category_id: '',
});

const summary = ref(null);
const series = ref([]);
const loading = ref(false);
const error = ref('');

const formatMoney = (minor, currency) => {
    const value = Number(minor ?? 0) / 100;
    const numberLocale = locale.value === 'ru' ? 'ru-RU' : 'en-US';

    return new Intl.NumberFormat(numberLocale, {
        style: 'currency',
        currency: currency || 'RUB',
        maximumFractionDigits: 2,
    }).format(value);
};

const loadData = async () => {
    loading.value = true;
    error.value = '';

    try {
        const [summaryResponse, seriesResponse] = await Promise.all([
            axios.get('/api/v1/dashboard/summary', { params: filters.value }),
            axios.get('/api/v1/dashboard/series', { params: { ...filters.value, group_by: 'month' } }),
        ]);

        summary.value = summaryResponse.data.data;
        series.value = seriesResponse.data.data.series;
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('dashboard.loadError');
    } finally {
        loading.value = false;
    }
};

const resetFilters = () => {
    filters.value = {
        from: '',
        to: '',
        category_id: '',
    };

    loadData();
};

onMounted(loadData);
</script>

<template>
    <AppLayout>
        <section class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold">{{ t('dashboard.title') }}</h2>
                <p class="text-sm text-slate-600">{{ t('dashboard.subtitle') }}</p>
            </div>

            <FiltersBar
                v-model="filters"
                :categories="[]"
                @apply="loadData"
                @reset="resetFilters"
            />

            <p v-if="error" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ error }}</p>

            <div v-if="summary" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <MetricCard
                    :label="t('dashboard.expenses')"
                    :value="formatMoney(summary.totals.expenses_base_minor, summary.base_currency_code)"
                    :hint="t('dashboard.selectedPeriod')"
                />
                <MetricCard
                    :label="t('dashboard.subscriptions')"
                    :value="formatMoney(summary.totals.subscriptions_base_minor, summary.base_currency_code)"
                    :hint="t('dashboard.activeOnly')"
                />
                <MetricCard
                    :label="t('dashboard.combined')"
                    :value="formatMoney(summary.totals.combined_base_minor, summary.base_currency_code)"
                    :hint="t('dashboard.expensesPlusSubscriptions')"
                />
                <MetricCard
                    :label="t('dashboard.upcoming30d')"
                    :value="formatMoney(summary.upcoming_charges_base_minor, summary.base_currency_code)"
                    :hint="t('dashboard.chargesCount', { count: summary.counts.upcoming_charges })"
                />
            </div>

            <div class="card">
                <h3 class="mb-4 text-lg font-semibold">{{ t('dashboard.monthlyTrend') }}</h3>

                <p v-if="loading" class="text-sm text-slate-500">{{ t('dashboard.loadingMetrics') }}</p>

                <div v-else class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ t('common.month') }}</th>
                                <th>{{ t('dashboard.expenses') }}</th>
                                <th>{{ t('dashboard.subscriptions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in series" :key="row.month">
                                <td>{{ row.month }}</td>
                                <td>{{ formatMoney(row.expenses_base_minor, summary?.base_currency_code) }}</td>
                                <td>{{ formatMoney(row.subscriptions_base_minor, summary?.base_currency_code) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
