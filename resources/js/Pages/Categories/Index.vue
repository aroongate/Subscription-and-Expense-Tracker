<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useI18n } from '../../composables/useI18n';

const { t } = useI18n();

const categories = ref([]);
const loading = ref(false);
const error = ref('');

const form = ref({
    type: 'expense',
    name: '',
    color: '#0d9488',
    is_active: true,
});

const editId = ref(null);

const loadCategories = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await axios.get('/api/v1/categories');
        categories.value = response.data.data;
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('categories.loadError');
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    editId.value = null;
    form.value = {
        type: 'expense',
        name: '',
        color: '#0d9488',
        is_active: true,
    };
};

const saveCategory = async () => {
    error.value = '';

    try {
        if (editId.value) {
            await axios.patch(`/api/v1/categories/${editId.value}`, form.value);
        } else {
            await axios.post('/api/v1/categories', form.value);
        }

        resetForm();
        await loadCategories();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('categories.saveError');
    }
};

const startEdit = (category) => {
    editId.value = category.id;
    form.value = {
        type: category.type,
        name: category.name,
        color: category.color,
        is_active: category.is_active,
    };
};

const removeCategory = async (categoryId) => {
    error.value = '';

    try {
        await axios.delete(`/api/v1/categories/${categoryId}`);
        await loadCategories();
    } catch (err) {
        error.value = err?.response?.data?.message ?? t('categories.deleteError');
    }
};

onMounted(loadCategories);
</script>

<template>
    <AppLayout>
        <section class="space-y-6">
            <div>
                <h2 class="text-2xl font-semibold">{{ t('categories.title') }}</h2>
                <p class="text-sm text-slate-600">{{ t('categories.subtitle') }}</p>
            </div>

            <p v-if="error" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ error }}</p>

            <div class="card space-y-4">
                <h3 class="text-lg font-semibold">{{ editId ? t('categories.edit') : t('categories.create') }}</h3>

                <div class="grid gap-3 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.type') }}</label>
                        <select v-model="form.type" class="select">
                            <option value="expense">{{ t('categories.expense') }}</option>
                            <option value="subscription">{{ t('categories.subscription') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.name') }}</label>
                        <input v-model="form.name" class="input" type="text" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs text-slate-500">{{ t('common.color') }}</label>
                        <input v-model="form.color" class="input" type="text" />
                    </div>

                    <div class="flex items-end gap-2">
                        <button class="btn-primary" type="button" @click="saveCategory">{{ editId ? t('common.update') : t('common.create') }}</button>
                        <button class="btn-secondary" type="button" @click="resetForm">{{ t('common.clear') }}</button>
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-4 text-lg font-semibold">{{ t('categories.list') }}</h3>

                <p v-if="loading" class="text-sm text-slate-500">{{ t('categories.loading') }}</p>

                <div v-else class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ t('common.name') }}</th>
                                <th>{{ t('common.type') }}</th>
                                <th>{{ t('common.status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="category in categories" :key="category.id">
                                <td>
                                    <span class="mr-2 inline-block h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: category.color }"></span>
                                    {{ category.name }}
                                </td>
                                <td>{{ category.type === 'expense' ? t('categories.expense') : t('categories.subscription') }}</td>
                                <td>
                                    <span :class="category.is_active ? 'badge-success' : 'badge-muted'">
                                        {{ category.is_active ? t('common.active') : t('common.inactive') }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" class="btn-secondary" @click="startEdit(category)">{{ t('common.edit') }}</button>
                                        <button type="button" class="btn-secondary" @click="removeCategory(category.id)">{{ t('common.delete') }}</button>
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
