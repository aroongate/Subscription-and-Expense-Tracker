<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import LanguageSwitcher from '../../Components/LanguageSwitcher.vue';
import { useI18n } from '../../composables/useI18n';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
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
                <h1 class="mt-2 text-2xl font-semibold">{{ t('auth.createAccount') }}</h1>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm text-slate-600">{{ t('common.name') }}</label>
                    <input v-model="form.name" type="text" class="input" required />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
                </div>

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

                <div>
                    <label class="mb-1 block text-sm text-slate-600">{{ t('auth.confirmPassword') }}</label>
                    <input v-model="form.password_confirmation" type="password" class="input" required />
                </div>

                <button class="btn-primary w-full" type="submit" :disabled="form.processing">
                    {{ form.processing ? t('auth.creating') : t('auth.createAccount') }}
                </button>
            </form>

            <p class="text-sm text-slate-600">
                {{ t('auth.alreadyRegistered') }}
                <Link href="/login" class="text-teal-700 hover:underline">{{ t('auth.signIn') }}</Link>
            </p>
        </div>
    </div>
</template>
