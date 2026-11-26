<script setup>
import { computed, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { rupiah, durasi, jam, tanggalPanjang, namaHari, parseDate } from '@/lib/format';

const props = defineProps({ services: Array, days: Array, hours: Array });
const brand = computed(() => usePage().props.brand);

const step = ref(1);
const slots = ref([]);
const closedReason = ref(null);
const loadingSlots = ref(false);

const form = useForm({
    service_id: null,
    date: null,
    time: null,
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    notes: '',
});

const openSummary = computed(() => {
    const open = props.hours.filter((h) => h.is_open);
    if (!open.length) return 'Jadwal belum diatur';
    const closed = props.hours.filter((h) => !h.is_open).map((h) => h.day_name);
    return closed.length ? `Buka setiap hari kecuali ${closed.join(', ')}` : 'Buka setiap hari';
});
const selectedService = computed(() => props.services.find((s) => s.id === form.service_id));
const selectedSlot = computed(() => slots.value.find((s) => s.time === form.time));
const firstOpenDay = computed(() => props.days.find((d) => !d.closed)?.date);

function chooseService(service) {
    form.service_id = service.id;
    form.time = null;
    if (!form.date) form.date = firstOpenDay.value;
    step.value = 2;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function loadSlots() {
    if (!form.service_id || !form.date) return;
    loadingSlots.value = true;
    try {
        const res = await fetch(`/slot?service_id=${form.service_id}&date=${form.date}`, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        slots.value = data.slots || [];
        closedReason.value = data.closed_reason;
        if (form.time && !slots.value.find((s) => s.time === form.time && s.available)) form.time = null;
    } finally {
        loadingSlots.value = false;
    }
}
watch(() => [form.service_id, form.date], loadSlots);

const availableCount = computed(() => slots.value.filter((s) => s.available).length);
const slotGroups = computed(() => {
    const groups = { Pagi: [], Siang: [], Sore: [] };
    for (const s of slots.value) {
        const h = Number(s.time.slice(0, 2));
        (h < 12 ? groups.Pagi : h < 15 ? groups.Siang : groups.Sore).push(s);
    }
    return Object.entries(groups).filter(([, list]) => list.length);
});

function submit() {
    form.post('/booking', {
        preserveScroll: true,
        onError: (errors) => {
            if (errors.time || errors.date || errors.service_id) {
                step.value = 2;
                loadSlots();
            }
        },
    });
}

function goStep3() {
    step.value = 3;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

const steps = ['Layanan', 'Jadwal', 'Data Diri'];
const canGoTo = (n) => n === 1 || (n === 2 && form.service_id) || (n === 3 && form.time);
</script>

<template>
    <PublicLayout title="Booking Online">
        <!-- Hero -->
        <section class="bg-gradient-to-br from-brand to-brand-800 text-white">
            <div class="mx-auto max-w-5xl px-4 py-10 sm:py-14">
                <p class="text-sm font-medium text-white/80">Booking online · konfirmasi via WhatsApp</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ brand.name }}</h1>
                <p class="mt-2 max-w-xl text-white/90">{{ brand.tagline }}</p>
                <div class="mt-5 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full bg-white/15 px-3 py-1">🕘 {{ openSummary }}</span>
                    <span class="rounded-full bg-white/15 px-3 py-1">📍 {{ brand.address }}</span>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-5xl px-4 pb-32 lg:pb-12">
            <!-- Stepper -->
            <ol class="-mt-6 mb-6 grid grid-cols-3 gap-2 rounded-2xl bg-white p-2 shadow-md">
                <li v-for="(label, i) in steps" :key="label">
                    <button type="button" :disabled="!canGoTo(i + 1)" @click="step = i + 1"
                        :class="['flex w-full items-center justify-center gap-2 rounded-xl px-2 py-2.5 text-xs font-semibold sm:text-sm', step === i + 1 ? 'bg-brand text-white' : canGoTo(i + 1) ? 'text-slate-700 hover:bg-slate-100' : 'text-slate-400']">
                        <span :class="['grid h-6 w-6 place-items-center rounded-full text-xs', step === i + 1 ? 'bg-white/20' : 'bg-slate-100']">{{ i + 1 }}</span>
                        {{ label }}
                    </button>
                </li>
            </ol>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="min-w-0 lg:col-span-2">
                    <!-- STEP 1: Layanan -->
                    <section v-show="step === 1">
                        <h2 class="mb-3 text-lg font-bold text-slate-900">Pilih layanan</h2>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <button v-for="s in services" :key="s.id" type="button" @click="chooseService(s)"
                                :class="['card group p-4 text-left transition hover:border-brand hover:shadow-md', form.service_id === s.id && 'border-brand ring-2 ring-brand-200']">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-semibold text-slate-900">{{ s.name }}</h3>
                                    <span class="shrink-0 font-bold text-brand">{{ rupiah(s.price) }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">{{ s.description }}</p>
                                <div class="mt-3 flex items-center justify-between text-xs">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600">⏱ {{ durasi(s.duration_minutes) }}</span>
                                    <span class="font-semibold text-brand opacity-0 transition group-hover:opacity-100">Pilih →</span>
                                </div>
                            </button>
                        </div>
                        <p v-if="!services.length" class="card p-6 text-center text-slate-500">Belum ada layanan yang tersedia.</p>
                    </section>

                    <!-- STEP 2: Tanggal & Jam -->
                    <section v-show="step === 2">
                        <h2 class="mb-3 text-lg font-bold text-slate-900">Pilih tanggal</h2>
                        <div class="-mx-4 flex snap-x gap-2 overflow-x-auto px-4 pb-2">
                            <button v-for="d in days" :key="d.date" type="button" :disabled="d.closed" :title="d.reason"
                                @click="form.date = d.date; form.time = null"
                                :class="['flex w-16 shrink-0 snap-start flex-col items-center rounded-xl border py-2.5 text-center transition',
                                    form.date === d.date ? 'border-brand bg-brand text-white shadow' : d.closed ? 'border-slate-100 bg-slate-50 text-slate-300' : 'border-slate-200 bg-white hover:border-brand']">
                                <span class="text-[11px] font-medium uppercase">{{ namaHari(d.date, true) }}</span>
                                <span class="text-xl font-bold">{{ parseDate(d.date).getDate() }}</span>
                                <span class="text-[11px]">{{ parseDate(d.date).toLocaleString('id-ID', { month: 'short' }) }}</span>
                            </button>
                        </div>

                        <div class="mt-6 flex items-end justify-between">
                            <h2 class="text-lg font-bold text-slate-900">Pilih jam</h2>
                            <span v-if="form.date && !loadingSlots && !closedReason" class="text-xs text-slate-500">{{ availableCount }} slot tersedia</span>
                        </div>
                        <p v-if="form.date" class="text-sm text-slate-500">{{ tanggalPanjang(form.date) }}</p>
                        <p v-if="form.errors.time" class="mt-3 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ form.errors.time }}</p>

                        <div v-if="loadingSlots" class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-5">
                            <div v-for="n in 10" :key="n" class="h-11 animate-pulse rounded-xl bg-slate-200" />
                        </div>
                        <p v-else-if="closedReason" class="card mt-4 p-6 text-center text-slate-500">{{ closedReason }}</p>
                        <p v-else-if="form.date && !availableCount" class="card mt-4 p-6 text-center text-slate-500">Slot di tanggal ini sudah penuh. Silakan pilih tanggal lain 🙏</p>
                        <div v-else class="mt-4 space-y-4">
                            <div v-for="[label, list] in slotGroups" :key="label">
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ label }}</p>
                                <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                                    <button v-for="s in list" :key="s.time" type="button" :disabled="!s.available" @click="form.time = s.time"
                                        :class="['rounded-xl border py-2.5 text-sm font-semibold transition',
                                            form.time === s.time ? 'border-brand bg-brand text-white' : s.available ? 'border-slate-200 bg-white hover:border-brand hover:text-brand' : 'border-slate-100 bg-slate-50 text-slate-300 line-through']">
                                        {{ jam(s.time) }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 hidden justify-between lg:flex">
                            <button class="btn-secondary" @click="step = 1">← Ganti layanan</button>
                            <button class="btn-primary" :disabled="!form.time" @click="step = 3">Lanjut isi data →</button>
                        </div>
                    </section>

                    <!-- STEP 3: Data diri -->
                    <section v-show="step === 3">
                        <h2 class="mb-3 text-lg font-bold text-slate-900">Data pemesan</h2>
                        <form class="card space-y-4 p-5" @submit.prevent="submit">
                            <div>
                                <label class="label" for="name">Nama lengkap *</label>
                                <input id="name" v-model="form.customer_name" class="input" placeholder="mis. Siti Rahma" autocomplete="name" />
                                <p v-if="form.errors.customer_name" class="error">{{ form.errors.customer_name }}</p>
                            </div>
                            <div>
                                <label class="label" for="phone">Nomor WhatsApp *</label>
                                <input id="phone" v-model="form.customer_phone" class="input" type="tel" inputmode="tel" placeholder="081234567890" autocomplete="tel" />
                                <p v-if="form.errors.customer_phone" class="error">{{ form.errors.customer_phone }}</p>
                                <p v-else class="mt-1 text-xs text-slate-400">Konfirmasi booking akan dikirim lewat WhatsApp.</p>
                            </div>
                            <div>
                                <label class="label" for="email">Email (opsional)</label>
                                <input id="email" v-model="form.customer_email" class="input" type="email" placeholder="nama@email.com" autocomplete="email" />
                                <p v-if="form.errors.customer_email" class="error">{{ form.errors.customer_email }}</p>
                            </div>
                            <div>
                                <label class="label" for="notes">Catatan (opsional)</label>
                                <textarea id="notes" v-model="form.notes" rows="3" class="input" placeholder="mis. ingin dengan kapster tertentu" />
                                <p v-if="form.errors.notes" class="error">{{ form.errors.notes }}</p>
                            </div>
                            <div class="hidden justify-between lg:flex">
                                <button type="button" class="btn-secondary" @click="step = 2">← Ganti jadwal</button>
                                <button type="submit" class="btn-primary" :disabled="form.processing">{{ form.processing ? 'Memproses…' : 'Konfirmasi Booking' }}</button>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Ringkasan (desktop) -->
                <aside class="hidden lg:block">
                    <div class="card sticky top-20 p-5">
                        <h3 class="font-bold text-slate-900">Ringkasan booking</h3>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div><dt class="text-slate-400">Layanan</dt><dd class="font-medium">{{ selectedService?.name || '—' }}</dd></div>
                            <div><dt class="text-slate-400">Tanggal</dt><dd class="font-medium">{{ form.date && form.service_id ? tanggalPanjang(form.date) : '—' }}</dd></div>
                            <div><dt class="text-slate-400">Jam</dt><dd class="font-medium">{{ selectedSlot ? `${jam(selectedSlot.time)} – ${jam(selectedSlot.end)} WIB` : '—' }}</dd></div>
                        </dl>
                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">
                            <span class="text-sm text-slate-500">Total</span>
                            <span class="text-lg font-bold text-brand">{{ selectedService ? rupiah(selectedService.price) : '—' }}</span>
                        </div>
                        <p class="mt-3 text-xs text-slate-400">Pembayaran dilakukan di tempat.</p>
                    </div>
                </aside>
            </div>
        </div>

        <!-- Bar bawah (mobile) -->
        <div v-if="selectedService" class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white p-3 shadow-[0_-4px_12px_rgba(0,0,0,0.05)] lg:hidden">
            <div class="mx-auto flex max-w-5xl items-center gap-3">
                <div class="min-w-0 flex-1 text-sm">
                    <p class="truncate font-semibold">{{ selectedService.name }}</p>
                    <p class="truncate text-xs text-slate-500">
                        {{ selectedSlot ? `${namaHari(form.date)}, ${parseDate(form.date).getDate()} · ${jam(selectedSlot.time)}` : rupiah(selectedService.price) }}
                    </p>
                </div>
                <button v-if="step === 1" class="btn-primary" @click="step = 2">Pilih jadwal</button>
                <button v-else-if="step === 2" class="btn-primary" :disabled="!form.time" @click="goStep3">Lanjut</button>
                <button v-else class="btn-primary" :disabled="form.processing" @click="submit">{{ form.processing ? 'Memproses…' : 'Konfirmasi' }}</button>
            </div>
        </div>
    </PublicLayout>
</template>
