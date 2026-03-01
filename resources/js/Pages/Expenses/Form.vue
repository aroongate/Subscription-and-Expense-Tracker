<script setup>
import AppLayout from '../../Layouts/AppLayout.vue';
import { useI18n } from '../../composables/useI18n';

const { t } = useI18n();
</script>

<template>
    <AppLayout>
        <div class="card">
            <h2 class="text-xl font-semibold">{{ t('placeholders.expenseFormTitle') }}</h2>
            <p class="text-sm text-slate-600">{{ t('placeholders.expenseFormText') }}</p>
        </div>
    </AppLayout>
</template>
