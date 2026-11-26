<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import BrandLogo from '@/Components/BrandLogo.vue';
import { waLink } from '@/lib/format';

const props = defineProps({ title: String });
const brand = computed(() => usePage().props.brand);
const fullTitle = computed(() => (props.title ? `${props.title} · ${brand.value.name}` : brand.value.name));
</script>

<template>
    <Head :title="fullTitle" />
    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3">
                <Link href="/"><BrandLogo /></Link>
                <nav class="flex shrink-0 items-center text-sm font-medium">
                    <Link href="/" class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100">Booking</Link>
                    <Link href="/cek-booking" class="rounded-lg px-3 py-2 whitespace-nowrap text-slate-600 hover:bg-slate-100">Cek Booking</Link>
                </nav>
            </div>
        </header>

        <main class="flex-1"><slot /></main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto grid max-w-5xl gap-6 px-4 py-8 text-sm text-slate-600 sm:grid-cols-3">
                <div>
                    <BrandLogo size="sm" />
                    <p class="mt-2">{{ brand.tagline }}</p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Alamat</p>
                    <p class="mt-1">{{ brand.address }}</p>
                    <a v-if="brand.maps_url" :href="brand.maps_url" target="_blank" class="mt-1 inline-block text-brand hover:underline">Lihat di Google Maps →</a>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Kontak</p>
                    <a :href="waLink(brand.phone)" target="_blank" class="mt-1 block hover:text-brand">WhatsApp: +{{ brand.phone }}</a>
                    <a v-if="brand.email" :href="`mailto:${brand.email}`" class="block hover:text-brand">{{ brand.email }}</a>
                    <a v-if="brand.instagram" :href="`https://instagram.com/${brand.instagram}`" target="_blank" class="block hover:text-brand">Instagram: @{{ brand.instagram }}</a>
                </div>
            </div>
            <p class="pb-6 text-center text-xs text-slate-400">© {{ new Date().getFullYear() }} {{ brand.name }}</p>
        </footer>
    </div>
</template>
