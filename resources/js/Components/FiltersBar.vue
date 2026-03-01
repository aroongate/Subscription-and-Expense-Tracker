<script setup>
import { useI18n } from '../composables/useI18n';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
    showStatus: {
        type: Boolean,
        default: false,
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'apply', 'reset']);
const { t } = useI18n();

const updateField = (field, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [field]: value,
    });
};

const statusLabel = (status) => t(`subscriptions.${status}`);
</script>

<template>
    <div class="card">
        <div class="grid gap-3 md:grid-cols-5">
            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ t('common.from') }}</label>
                <input
                    :value="modelValue.from"
                    type="date"
                    class="input"
                    @input="updateField('from', $event.target.value)"
                />
            </div>

            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ t('common.to') }}</label>
                <input
                    :value="modelValue.to"
                    type="date"
                    class="input"
                    @input="updateField('to', $event.target.value)"
                />
            </div>

            <div>
                <label class="mb-1 block text-xs text-slate-500">{{ t('common.category') }}</label>
                <select
                    :value="modelValue.category_id"
                    class="select"
                    @change="updateField('category_id', $event.target.value)"
                >
                    <option value="">{{ t('common.allCategories') }}</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <div v-if="showStatus">
                <label class="mb-1 block text-xs text-slate-500">{{ t('common.status') }}</label>
                <select
                    :value="modelValue.status"
                    class="select"
                    @change="updateField('status', $event.target.value)"
                >
                    <option value="">{{ t('common.allStatuses') }}</option>
                    <option v-for="status in statusOptions" :key="status" :value="status">{{ statusLabel(status) }}</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button class="btn-primary" type="button" @click="emit('apply')">{{ t('common.apply') }}</button>
                <button class="btn-secondary" type="button" @click="emit('reset')">{{ t('filters.reset') }}</button>
            </div>
        </div>
    </div>
</template>
