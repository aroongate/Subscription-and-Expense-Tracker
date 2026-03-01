<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    hint: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="card">
        <p class="text-xs uppercase tracking-wide text-slate-500">{{ label }}</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ value }}</p>
        <p v-if="hint" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
    </div>
</template>
