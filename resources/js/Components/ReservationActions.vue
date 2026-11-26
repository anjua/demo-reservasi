<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { jam, tanggalPanjang, waLink } from '@/lib/format';

const props = defineProps({ reservation: Object, compact: Boolean, nowrap: Boolean });
const brand = computed(() => usePage().props.brand);
const r = computed(() => props.reservation);
const showCancel = ref(false);
const cancelNote = ref('');
const busy = ref(false);

function setStatus(status, admin_note = undefined) {
    busy.value = true;
    router.patch(`/admin/reservasi/${r.value.id}/status`, { status, admin_note: admin_note ?? r.value.admin_note }, {
        preserveScroll: true,
        onFinish: () => { busy.value = false; showCancel.value = false; },
    });
}

const waMessage = computed(() => {
    const jadwal = `${tanggalPanjang(r.value.date)} pukul ${jam(r.value.start_time)} WIB`;
    const layanan = r.value.service?.name;
    if (r.value.status === 'cancelled') {
        return `Halo Kak ${r.value.customer_name}, mohon maaf booking ${layanan} (${r.value.code}) pada ${jadwal} kami batalkan.` +
            (r.value.admin_note ? `\nAlasan: ${r.value.admin_note}` : '') + `\nSilakan booking ulang di jadwal lain ya 🙏\n— ${brand.value.name}`;
    }
    return `Halo Kak ${r.value.customer_name} 👋\nBooking ${layanan} (${r.value.code}) pada ${jadwal} sudah *DIKONFIRMASI*.\n` +
        `Lokasi: ${brand.value.address}\nSampai jumpa! — ${brand.value.name}`;
});
</script>

<template>
    <div :class="['flex items-center gap-1.5', nowrap ? 'flex-nowrap' : 'flex-wrap']">
        <button v-if="r.status === 'pending'" class="btn-primary btn-sm" :disabled="busy" @click="setStatus('confirmed')">Konfirmasi</button>
        <button v-if="r.status === 'confirmed'" class="btn-sm btn bg-emerald-600 text-white hover:bg-emerald-700" :disabled="busy" @click="setStatus('completed')">Selesai</button>
        <button v-if="['pending', 'confirmed'].includes(r.status)" class="btn-secondary btn-sm" :disabled="busy" @click="showCancel = true">Batalkan</button>
        <button v-if="['cancelled', 'completed'].includes(r.status) && !compact" class="btn-secondary btn-sm" :disabled="busy" @click="setStatus('pending')" title="Kembalikan ke status menunggu">↺</button>
        <a :href="waLink(r.customer_phone, waMessage)" target="_blank" class="btn-sm btn border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100" title="Kirim pesan WhatsApp ke pelanggan">WA</a>

        <Modal :show="showCancel" title="Batalkan reservasi" @close="showCancel = false">
            <p class="text-sm text-slate-600">Batalkan booking <b>{{ r.code }}</b> atas nama <b>{{ r.customer_name }}</b>? Slot akan dibuka lagi untuk pelanggan lain.</p>
            <label class="label mt-4" for="note">Alasan (opsional, ditampilkan ke pelanggan)</label>
            <textarea id="note" v-model="cancelNote" rows="3" class="input" placeholder="mis. Kapster berhalangan hadir" />
            <div class="mt-5 flex justify-end gap-2">
                <button class="btn-secondary" @click="showCancel = false">Kembali</button>
                <button class="btn-danger" :disabled="busy" @click="setStatus('cancelled', cancelNote)">Ya, batalkan</button>
            </div>
        </Modal>
    </div>
</template>
