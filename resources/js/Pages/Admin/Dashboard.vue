<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ReservationActions from '@/Components/ReservationActions.vue';
import { rupiah, jam, tanggalPanjang, tanggalPendek, todayStr } from '@/lib/format';

defineProps({ stats: Object, todayList: Array, pendingList: Array });
const cards = (s) => [
    { label: 'Reservasi hari ini', value: s.today, icon: '📅' },
    { label: 'Menunggu konfirmasi', value: s.pending, icon: '⏳', highlight: s.pending > 0 },
    { label: 'Reservasi bulan ini', value: s.month, icon: '📈' },
    { label: 'Pendapatan bulan ini', value: rupiah(s.revenue), icon: '💰', hint: 'dari reservasi selesai' },
];
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div v-for="c in cards(stats)" :key="c.label" :class="['card p-4', c.highlight && 'border-amber-300 bg-amber-50']">
                <p class="text-xs text-slate-500">{{ c.icon }} {{ c.label }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ c.value }}</p>
                <p v-if="c.hint" class="text-[11px] text-slate-400">{{ c.hint }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <section class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-slate-900">Jadwal hari ini</h2>
                        <p class="text-xs text-slate-500">{{ tanggalPanjang(todayStr()) }}</p>
                    </div>
                    <Link :href="`/admin/reservasi?date=${todayStr()}`" class="text-sm font-medium text-brand hover:underline">Semua →</Link>
                </div>
                <ul class="divide-y divide-slate-100">
                    <li v-for="r in todayList" :key="r.id" class="flex flex-wrap items-center gap-3 px-5 py-3">
                        <div class="w-14 text-center">
                            <p class="font-bold text-slate-900">{{ jam(r.start_time) }}</p>
                            <p class="text-[11px] text-slate-400">s.d. {{ jam(r.end_time) }}</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">{{ r.customer_name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ r.service.name }}</p>
                        </div>
                        <StatusBadge :status="r.status" :label="r.status_label" />
                        <ReservationActions :reservation="r" compact class="w-full pl-17" />
                    </li>
                    <li v-if="!todayList.length" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada reservasi hari ini.</li>
                </ul>
            </section>

            <section class="card">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Perlu dikonfirmasi</h2>
                    <Link href="/admin/reservasi?status=pending" class="text-sm font-medium text-brand hover:underline">Semua →</Link>
                </div>
                <ul class="divide-y divide-slate-100">
                    <li v-for="r in pendingList" :key="r.id" class="flex flex-wrap items-center gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium">{{ r.customer_name }} <span class="font-mono text-xs text-slate-400">{{ r.code }}</span></p>
                            <p class="truncate text-xs text-slate-500">{{ r.service.name }} · {{ tanggalPendek(r.date) }}, {{ jam(r.start_time) }}</p>
                        </div>
                        <ReservationActions :reservation="r" compact class="w-full" />
                    </li>
                    <li v-if="!pendingList.length" class="px-5 py-8 text-center text-sm text-slate-500">Semua reservasi sudah dikonfirmasi 🎉</li>
                </ul>
            </section>
        </div>
    </AdminLayout>
</template>
