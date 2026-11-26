<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import BrandLogo from '@/Components/BrandLogo.vue';

const brand = computed(() => usePage().props.brand);
const form = useForm({ email: '', password: '', remember: true });
const submit = () => form.post('/admin/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <Head :title="`Masuk Admin · ${brand.name}`" />
    <div class="grid min-h-screen place-items-center bg-gradient-to-br from-brand-50 to-slate-100 px-4">
        <div class="w-full max-w-sm">
            <div class="mb-6 flex justify-center"><BrandLogo /></div>
            <form class="card space-y-4 p-6" @submit.prevent="submit">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Masuk dashboard</h1>
                    <p class="text-sm text-slate-500">Khusus pemilik / staf.</p>
                </div>
                <div>
                    <label class="label" for="email">Email</label>
                    <input id="email" v-model="form.email" type="email" class="input" autocomplete="username" autofocus />
                    <p v-if="form.errors.email" class="error">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label class="label" for="password">Kata sandi</label>
                    <input id="password" v-model="form.password" type="password" class="input" autocomplete="current-password" />
                    <p v-if="form.errors.password" class="error">{{ form.errors.password }}</p>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-brand" /> Ingat saya
                </label>
                <button class="btn-primary w-full" :disabled="form.processing">Masuk</button>
                <p v-if="$page.props.demo" class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800">
                    Akun demo: <b>admin@demo.test</b> / <b>password</b>
                </p>
            </form>
            <p class="mt-4 text-center text-sm"><Link href="/" class="text-slate-500 hover:text-brand">← Kembali ke halaman booking</Link></p>
        </div>
    </div>
</template>
