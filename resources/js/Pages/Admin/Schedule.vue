<script setup>
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { tanggalPanjang, todayStr } from '@/lib/format';

const props = defineProps({ hours: Array, closedDates: Array, settings: Object });

const hoursForm = useForm({
    hours: props.hours.map((h) => ({ day_of_week: h.day_of_week, day_name: h.day_name, is_open: h.is_open, open_time: h.open_time, close_time: h.close_time })),
});
const settingsForm = useForm({ ...props.settings });
const closedForm = useForm({ date: '', reason: '' });

const saveHours = () => hoursForm.transform((d) => ({ hours: d.hours.map(({ day_name, ...h }) => h) })).put('/admin/jadwal/jam', { preserveScroll: true });
const saveSettings = () => settingsForm.put('/admin/jadwal/pengaturan', { preserveScroll: true });
const addClosed = () => closedForm.post('/admin/jadwal/libur', { preserveScroll: true, onSuccess: () => closedForm.reset() });
const removeClosed = (c) => router.delete(`/admin/jadwal/libur/${c.id}`, { preserveScroll: true });
const copyMonday = () => {
    const mon = hoursForm.hours.find((h) => h.day_of_week === 1);
    hoursForm.hours.forEach((h) => { if (h.is_open) { h.open_time = mon.open_time; h.close_time = mon.close_time; } });
};
</script>

<template>
    <AdminLayout title="Jadwal & Slot">
        <div class="grid gap-6 xl:grid-cols-3">
            <!-- Jam operasional -->
            <section class="card xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="font-semibold text-slate-900">Jam operasional</h2>
                        <p class="text-xs text-slate-500">Slot booking dibuat otomatis dari jam buka–tutup.</p>
                    </div>
                    <button type="button" class="text-xs font-medium text-brand hover:underline" @click="copyMonday">Samakan dengan Senin</button>
                </div>
                <form @submit.prevent="saveHours">
                    <div class="divide-y divide-slate-100">
                        <div v-for="(h, i) in hoursForm.hours" :key="h.day_of_week" class="flex flex-wrap items-center gap-3 px-5 py-3">
                            <label class="flex w-28 items-center gap-2 font-medium">
                                <input v-model="h.is_open" type="checkbox" class="rounded border-slate-300" /> {{ h.day_name }}
                            </label>
                            <template v-if="h.is_open">
                                <input v-model="h.open_time" type="time" class="input w-32 py-1.5" />
                                <span class="text-slate-400">s.d.</span>
                                <input v-model="h.close_time" type="time" class="input w-32 py-1.5" />
                            </template>
                            <span v-else class="text-sm text-slate-400">Tutup</span>
                            <p v-if="hoursForm.errors[`hours.${i}.close_time`]" class="error w-full">{{ hoursForm.errors[`hours.${i}.close_time`] }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end border-t border-slate-100 px-5 py-4">
                        <button class="btn-primary" :disabled="hoursForm.processing">Simpan jam operasional</button>
                    </div>
                </form>
            </section>

            <!-- Pengaturan slot -->
            <section class="card">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Pengaturan slot</h2>
                </div>
                <form class="space-y-4 p-5" @submit.prevent="saveSettings">
                    <div>
                        <label class="label" for="interval">Jarak antar slot</label>
                        <select id="interval" v-model.number="settingsForm.slot_interval" class="input">
                            <option v-for="m in [15, 30, 45, 60]" :key="m" :value="m">Setiap {{ m }} menit</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="cap">Kapasitas per slot</label>
                        <input id="cap" v-model.number="settingsForm.capacity_per_slot" type="number" min="1" class="input" />
                        <p class="mt-1 text-xs text-slate-400">Jumlah pelanggan yang bisa dilayani bersamaan (mis. jumlah kapster / dokter / mekanik).</p>
                        <p v-if="settingsForm.errors.capacity_per_slot" class="error">{{ settingsForm.errors.capacity_per_slot }}</p>
                    </div>
                    <div>
                        <label class="label" for="notice">Minimal booking sebelum jam mulai (menit)</label>
                        <input id="notice" v-model.number="settingsForm.min_notice_minutes" type="number" min="0" step="15" class="input" />
                        <p v-if="settingsForm.errors.min_notice_minutes" class="error">{{ settingsForm.errors.min_notice_minutes }}</p>
                    </div>
                    <div>
                        <label class="label" for="ahead">Booking maksimal berapa hari ke depan</label>
                        <input id="ahead" v-model.number="settingsForm.max_days_ahead" type="number" min="1" class="input" />
                        <p v-if="settingsForm.errors.max_days_ahead" class="error">{{ settingsForm.errors.max_days_ahead }}</p>
                    </div>
                    <button class="btn-primary w-full" :disabled="settingsForm.processing">Simpan pengaturan</button>
                </form>
            </section>

            <!-- Tanggal libur -->
            <section class="card xl:col-span-3">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">Tanggal libur</h2>
                    <p class="text-xs text-slate-500">Pelanggan tidak bisa booking di tanggal ini (mis. hari raya, cuti bersama).</p>
                </div>
                <form class="flex flex-wrap items-start gap-3 p-5" @submit.prevent="addClosed">
                    <div>
                        <input v-model="closedForm.date" type="date" :min="todayStr()" class="input" />
                        <p v-if="closedForm.errors.date" class="error">{{ closedForm.errors.date }}</p>
                    </div>
                    <input v-model="closedForm.reason" class="input max-w-xs flex-1" placeholder="Keterangan, mis. Idul Fitri" />
                    <button class="btn-primary" :disabled="closedForm.processing">+ Tambah</button>
                </form>
                <ul class="divide-y divide-slate-100 border-t border-slate-100">
                    <li v-for="c in closedDates" :key="c.id" class="flex items-center justify-between px-5 py-3 text-sm">
                        <div>
                            <p class="font-medium">{{ tanggalPanjang(c.date) }}</p>
                            <p class="text-xs text-slate-500">{{ c.reason || 'Libur' }}</p>
                        </div>
                        <button class="text-xs font-medium text-rose-600 hover:underline" @click="removeClosed(c)">Hapus</button>
                    </li>
                    <li v-if="!closedDates.length" class="px-5 py-6 text-center text-sm text-slate-500">Belum ada tanggal libur.</li>
                </ul>
            </section>
        </div>
    </AdminLayout>
</template>
