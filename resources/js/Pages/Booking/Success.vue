<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { rupiah, jam, tanggalPanjang, waLink, durasi } from '@/lib/format';

const props = defineProps({ reservation: Object });
const brand = computed(() => usePage().props.brand);
const r = computed(() => props.reservation);
const copied = ref(false);

const waText = computed(() =>
    `Halo ${brand.value.name}, saya sudah booking online.\n\n` +
    `Kode: ${r.value.code}\nNama: ${r.value.customer_name}\nLayanan: ${r.value.service.name}\n` +
    `Jadwal: ${tanggalPanjang(r.value.date)}, ${jam(r.value.start_time)} WIB\n\nMohon konfirmasinya, terima kasih 🙏`
);

function copyCode() {
    navigator.clipboard?.writeText(r.value.code);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
}
</script>

<template>
    <PublicLayout title="Booking Berhasil">
        <div class="mx-auto max-w-lg px-4 py-10">
            <div class="text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-100 text-3xl">✅</div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900">
                    {{ r.status === 'pending' ? 'Booking diterima!' : 'Detail booking' }}
                </h1>
                <p v-if="r.status === 'pending'" class="mt-1 text-slate-500">Kami akan mengonfirmasi lewat WhatsApp. Simpan kode booking kamu.</p>
            </div>

            <div class="card mt-6 overflow-hidden">
                <div class="flex items-center justify-between bg-brand-50 px-5 py-4">
                    <div>
                        <p class="text-xs text-slate-500">Kode booking</p>
                        <p class="font-mono text-xl font-bold tracking-wider text-brand-800">{{ r.code }}</p>
                    </div>
                    <button class="btn-secondary btn-sm" @click="copyCode">{{ copied ? 'Tersalin ✓' : 'Salin' }}</button>
                </div>
                <dl class="divide-y divide-slate-100 text-sm">
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-slate-500">Status</dt><dd><StatusBadge :status="r.status" :label="r.status_label" /></dd></div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-slate-500">Nama</dt><dd class="text-right font-medium">{{ r.customer_name }}</dd></div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-slate-500">Layanan</dt><dd class="text-right font-medium">{{ r.service.name }} <span class="block text-xs font-normal text-slate-400">{{ durasi(r.service.duration_minutes) }}</span></dd></div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-slate-500">Tanggal</dt><dd class="text-right font-medium">{{ tanggalPanjang(r.date) }}</dd></div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-slate-500">Jam</dt><dd class="text-right font-medium">{{ jam(r.start_time) }} – {{ jam(r.end_time) }} WIB</dd></div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-slate-500">Biaya</dt><dd class="text-right font-bold text-brand">{{ rupiah(r.price) }}</dd></div>
                    <div v-if="r.admin_note" class="px-5 py-3"><dt class="text-slate-500">Catatan dari kami</dt><dd class="mt-1">{{ r.admin_note }}</dd></div>
                </dl>
            </div>

            <div class="mt-6 grid gap-3">
                <a :href="waLink(brand.phone, waText)" target="_blank" class="btn-primary !bg-emerald-600 hover:!bg-emerald-700">💬 Kirim ke WhatsApp {{ brand.name }}</a>
                <Link href="/" class="btn-secondary">Booking lagi</Link>
            </div>
            <p class="mt-4 text-center text-xs text-slate-400">Cek status kapan saja di menu <Link href="/cek-booking" class="text-brand hover:underline">Cek Booking</Link>.</p>
        </div>
    </PublicLayout>
</template>
