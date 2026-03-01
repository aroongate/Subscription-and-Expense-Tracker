<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import LanguageSwitcher from '../../Components/LanguageSwitcher.vue';
import { useI18n } from '../../composables/useI18n';

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login');
};
</script>

<template>
    <div class="mx-auto flex min-h-screen max-w-md items-center px-4">
        <div class="card w-full space-y-5">
            <div class="flex justify-end">
                <div class="w-48">
                    <LanguageSwitcher />
                </div>
            </div>

            <div>
                <p class="text-xs uppercase tracking-[0.18em] text-teal-700">{{ t('app.name') }}</p>
                <h1 class="mt-2 text-2xl font-semibold">{{ t('auth.signIn') }}</h1>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm text-slate-600">{{ t('common.email') }}</label>
                    <input v-model="form.email" type="email" class="input" required />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm text-slate-600">{{ t('common.password') }}</label>
                    <input v-model="form.password" type="password" class="input" required />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
                </div>

                <button class="btn-primary w-full" type="submit" :disabled="form.processing">
                    {{ form.processing ? t('auth.signingIn') : t('auth.signIn') }}
                </button>
            </form>

            <p class="text-sm text-slate-600">
                {{ t('auth.noAccount') }}
                <Link href="/register" class="text-teal-700 hover:underline">{{ t('auth.createOne') }}</Link>
            </p>
        </div>
    </div>
</template>
