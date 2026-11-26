<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { jam, tanggalPanjang } from '@/lib/format';

const props = defineProps({ filters: Object, result: Object, searched: Boolean });
const form = useForm({ code: props.filters.code || '', phone: props.filters.phone || '' });
const search = () => form.get('/cek-booking', { preserveState: true });
</script>

<template>
    <PublicLayout title="Cek Booking">
        <div class="mx-auto max-w-lg px-4 py-10">
            <h1 class="text-2xl font-bold text-slate-900">Cek status booking</h1>
            <p class="mt-1 text-slate-500">Masukkan kode booking dan nomor WhatsApp yang dipakai saat memesan.</p>

            <form class="card mt-6 space-y-4 p-5" @submit.prevent="search">
                <div>
                    <label class="label" for="code">Kode booking</label>
                    <input id="code" v-model="form.code" class="input font-mono uppercase" placeholder="RSV-XXXXXX" />
                    <p v-if="form.errors.code" class="error">{{ form.errors.code }}</p>
                </div>
                <div>
                    <label class="label" for="phone">Nomor WhatsApp</label>
                    <input id="phone" v-model="form.phone" class="input" type="tel" placeholder="081234567890" />
                    <p v-if="form.errors.phone" class="error">{{ form.errors.phone }}</p>
                </div>
                <button class="btn-primary w-full" :disabled="form.processing">Cek Status</button>
            </form>

            <div v-if="searched && result" class="card mt-6 p-5">
                <div class="flex items-center justify-between">
                    <p class="font-mono font-bold">{{ result.code }}</p>
                    <StatusBadge :status="result.status" :label="result.status_label" />
                </div>
                <p class="mt-3 font-semibold">{{ result.service.name }}</p>
                <p class="text-sm text-slate-500">{{ tanggalPanjang(result.date) }} · {{ jam(result.start_time) }} WIB</p>
                <Link :href="`/booking/${result.code}`" class="mt-4 inline-block text-sm font-semibold text-brand hover:underline">Lihat detail →</Link>
            </div>
            <p v-else-if="searched && !Object.keys(form.errors).length" class="card mt-6 p-5 text-center text-slate-500">
                Booking tidak ditemukan. Pastikan kode dan nomor WhatsApp sudah benar.
            </p>
        </div>
    </PublicLayout>
</template>
