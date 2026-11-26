<script setup>
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ReservationActions from '@/Components/ReservationActions.vue';
import Pagination from '@/Components/Pagination.vue';
import { rupiah, jam, tanggalPendek, namaHari } from '@/lib/format';

const props = defineProps({ reservations: Object, filters: Object, statuses: Object, services: Array });
const f = reactive({
    status: props.filters.status || '',
    date: props.filters.date || '',
    service_id: props.filters.service_id || '',
    search: props.filters.search || '',
});

let t;
watch(f, () => {
    clearTimeout(t);
    t = setTimeout(() => {
        const q = Object.fromEntries(Object.entries(f).filter(([, v]) => v));
        router.get('/admin/reservasi', q, { preserveState: true, preserveScroll: true, replace: true });
    }, 300);
});
const reset = () => Object.assign(f, { status: '', date: '', service_id: '', search: '' });
</script>

<template>
    <AdminLayout title="Reservasi">
        <!-- Filter -->
        <div class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
            <input v-model="f.search" class="input lg:col-span-2" placeholder="Cari nama, no. WA, atau kode…" />
            <select v-model="f.status" class="input">
                <option value="">Semua status</option>
                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
            </select>
            <select v-model="f.service_id" class="input">
                <option value="">Semua layanan</option>
                <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <div class="flex gap-2">
                <input v-model="f.date" type="date" class="input" />
                <button v-if="Object.values(f).some(Boolean)" class="btn-secondary px-3" title="Reset filter" @click="reset">✕</button>
            </div>
        </div>

        <p class="mb-3 text-sm text-slate-500">{{ reservations.total }} reservasi</p>

        <!-- Tabel (desktop) -->
        <div class="card hidden overflow-x-auto md:block">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Jadwal</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Layanan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="r in reservations.data" :key="r.id" class="align-top hover:bg-slate-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <p class="font-semibold">{{ namaHari(r.date, true) }}, {{ tanggalPendek(r.date) }}</p>
                            <p class="text-xs text-slate-500">{{ jam(r.start_time) }} – {{ jam(r.end_time) }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ r.customer_name }}</p>
                            <p class="text-xs text-slate-500">+{{ r.customer_phone }} · <span class="font-mono">{{ r.code }}</span></p>
                            <p v-if="r.notes" class="mt-1 max-w-xs text-xs text-slate-500 italic">“{{ r.notes }}”</p>
                        </td>
                        <td class="px-4 py-3">
                            <p>{{ r.service.name }}</p>
                            <p class="text-xs text-slate-500">{{ rupiah(r.price) }}</p>
                        </td>
                        <td class="px-4 py-3"><StatusBadge :status="r.status" :label="r.status_label" /></td>
                        <td class="w-px px-4 py-3"><ReservationActions :reservation="r" nowrap /></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Kartu (mobile) -->
        <div class="space-y-3 md:hidden">
            <div v-for="r in reservations.data" :key="r.id" class="card p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold">{{ r.customer_name }}</p>
                        <p class="text-xs text-slate-500">{{ r.service.name }} · {{ rupiah(r.price) }}</p>
                    </div>
                    <StatusBadge :status="r.status" :label="r.status_label" />
                </div>
                <p class="mt-2 text-sm">📅 {{ namaHari(r.date, true) }}, {{ tanggalPendek(r.date) }} · {{ jam(r.start_time) }}–{{ jam(r.end_time) }}</p>
                <p class="text-xs text-slate-500">+{{ r.customer_phone }} · <span class="font-mono">{{ r.code }}</span></p>
                <p v-if="r.notes" class="mt-1 text-xs text-slate-500 italic">“{{ r.notes }}”</p>
                <ReservationActions :reservation="r" class="mt-3" />
            </div>
        </div>

        <p v-if="!reservations.data.length" class="card p-8 text-center text-slate-500">Tidak ada reservasi yang cocok dengan filter.</p>
        <div class="mt-4"><Pagination :links="reservations.links" /></div>
    </AdminLayout>
</template>
