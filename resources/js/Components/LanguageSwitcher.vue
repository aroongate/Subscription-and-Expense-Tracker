<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { useI18n } from '../composables/useI18n';
import { setCurrentLocale } from '../locale-context';

const { t, locale, availableLocales } = useI18n();

const selectedLocale = ref(locale.value);
const switchError = ref('');

watch(
    locale,
    (nextLocale) => {
        selectedLocale.value = nextLocale;
        setCurrentLocale(nextLocale);
    },
    { immediate: true },
);

const locales = computed(() => availableLocales.value);

const switchLocale = async () => {
    switchError.value = '';

    try {
        await axios.post('/locale', { locale: selectedLocale.value });
        setCurrentLocale(selectedLocale.value);

        router.reload({
            only: ['locale', 'availableLocales'],
        });
    } catch (error) {
        switchError.value = t('layout.switchLanguageError');
    }
};
</script>

<template>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-500">{{ t('layout.language') }}</label>
        <div class="flex gap-2">
            <select v-model="selectedLocale" class="select">
                <option v-for="option in locales" :key="option.code" :value="option.code">
                    {{ option.label }}
                </option>
            </select>
            <button type="button" class="btn-secondary" @click="switchLocale">{{ t('common.apply') }}</button>
        </div>
        <p v-if="switchError" class="mt-1 text-xs text-rose-600">{{ switchError }}</p>
    </div>
</template>
