<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import BrandLogo from '@/Components/BrandLogo.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const props = defineProps({ title: String });
const page = usePage();
const brand = computed(() => page.props.brand);
const user = computed(() => page.props.auth.user);
const open = ref(false);

const menu = [
    { label: 'Dashboard', href: '/admin', icon: '🏠', match: (u) => u === '/admin' },
    { label: 'Reservasi', href: '/admin/reservasi', icon: '📅', match: (u) => u.startsWith('/admin/reservasi') },
    { label: 'Layanan', href: '/admin/layanan', icon: '✂️', match: (u) => u.startsWith('/admin/layanan') },
    { label: 'Jadwal & Slot', href: '/admin/jadwal', icon: '⏰', match: (u) => u.startsWith('/admin/jadwal') },
];
const currentPath = computed(() => page.url.split('?')[0]);
const logout = () => router.post('/admin/logout');
</script>

<template>
    <Head :title="`${title} · Admin ${brand.name}`" />
    <div class="min-h-screen lg:flex">
        <!-- Sidebar -->
        <aside :class="['fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 p-4 text-slate-300 transition lg:static lg:translate-x-0', open ? 'translate-x-0' : '-translate-x-full']">
            <div class="mb-8 px-2 pt-1"><BrandLogo light /></div>
            <nav class="space-y-1">
                <Link v-for="item in menu" :key="item.href" :href="item.href" @click="open = false"
                    :class="['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium', item.match(currentPath) ? 'bg-brand text-white' : 'hover:bg-slate-800 hover:text-white']">
                    <span>{{ item.icon }}</span>{{ item.label }}
                </Link>
                <a href="/" target="_blank" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium hover:bg-slate-800 hover:text-white">
                    <span>🌐</span>Lihat Halaman Booking
                </a>
            </nav>
            <div class="absolute inset-x-4 bottom-4 rounded-xl bg-slate-800 p-3 text-xs">
                <p class="font-semibold text-white">{{ user?.name }}</p>
                <p class="truncate text-slate-400">{{ user?.email }}</p>
                <button class="mt-2 text-rose-300 hover:text-rose-200" @click="logout">Keluar</button>
            </div>
        </aside>
        <div v-if="open" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="open = false" />

        <div class="flex-1 min-w-0">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur lg:px-8">
                <button class="rounded-lg p-2 hover:bg-slate-100 lg:hidden" @click="open = true" aria-label="Menu">☰</button>
                <h1 class="text-lg font-bold text-slate-900">{{ title }}</h1>
                <div class="ml-auto"><slot name="actions" /></div>
            </header>
            <main class="p-4 lg:p-8"><slot /></main>
        </div>
        <FlashMessage />
    </div>
</template>
