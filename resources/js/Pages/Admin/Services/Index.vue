<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import { rupiah, durasi } from '@/lib/format';

defineProps({ services: Array });
const editing = ref(null);
const show = ref(false);
const form = useForm({ name: '', description: '', duration_minutes: 60, price: 0, is_active: true, sort_order: 0 });

function openForm(service = null) {
    editing.value = service;
    form.clearErrors();
    if (service) {
        form.defaults({ ...service });
        form.reset();
        Object.assign(form, { name: service.name, description: service.description, duration_minutes: service.duration_minutes, price: service.price, is_active: service.is_active, sort_order: service.sort_order });
    } else {
        Object.assign(form, { name: '', description: '', duration_minutes: 60, price: 0, is_active: true, sort_order: 0 });
    }
    show.value = true;
}

function save() {
    const opts = { preserveScroll: true, onSuccess: () => (show.value = false) };
    editing.value ? form.put(`/admin/layanan/${editing.value.id}`, opts) : form.post('/admin/layanan', opts);
}

function toggle(s) {
    router.put(`/admin/layanan/${s.id}`, { ...s, is_active: !s.is_active }, { preserveScroll: true });
}

function remove(s) {
    if (confirm(`Hapus layanan "${s.name}"?`)) router.delete(`/admin/layanan/${s.id}`, { preserveScroll: true });
}
</script>

<template>
    <AdminLayout title="Layanan">
        <template #actions>
            <button class="btn-primary btn-sm sm:btn" @click="openForm()">+ Tambah layanan</button>
        </template>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <div v-for="s in services" :key="s.id" :class="['card flex flex-col p-4', !s.is_active && 'opacity-60']">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-semibold text-slate-900">{{ s.name }}</h3>
                    <span class="shrink-0 font-bold text-brand">{{ rupiah(s.price) }}</span>
                </div>
                <p class="mt-1 flex-1 text-sm text-slate-500">{{ s.description || '—' }}</p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1">⏱ {{ durasi(s.duration_minutes) }}</span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1">{{ s.reservations_count }} reservasi</span>
                    <span :class="['rounded-full px-2.5 py-1', s.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600']">{{ s.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <div class="mt-4 flex gap-2 border-t border-slate-100 pt-3">
                    <button class="btn-secondary btn-sm" @click="openForm(s)">Ubah</button>
                    <button class="btn-secondary btn-sm" @click="toggle(s)">{{ s.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    <button class="btn-sm btn ml-auto text-rose-600 hover:bg-rose-50" @click="remove(s)">Hapus</button>
                </div>
            </div>
        </div>
        <p v-if="!services.length" class="card p-8 text-center text-slate-500">Belum ada layanan. Tambahkan layanan pertama.</p>

        <Modal :show="show" :title="editing ? 'Ubah layanan' : 'Tambah layanan'" @close="show = false">
            <form class="space-y-4" @submit.prevent="save">
                <div>
                    <label class="label" for="sname">Nama layanan *</label>
                    <input id="sname" v-model="form.name" class="input" placeholder="mis. Potong Rambut" />
                    <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="label" for="sdesc">Deskripsi</label>
                    <textarea id="sdesc" v-model="form.description" rows="2" class="input" />
                    <p v-if="form.errors.description" class="error">{{ form.errors.description }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="sdur">Durasi (menit) *</label>
                        <input id="sdur" v-model.number="form.duration_minutes" type="number" min="5" step="5" class="input" />
                        <p v-if="form.errors.duration_minutes" class="error">{{ form.errors.duration_minutes }}</p>
                    </div>
                    <div>
                        <label class="label" for="sprice">Harga (Rp) *</label>
                        <input id="sprice" v-model.number="form.price" type="number" min="0" step="1000" class="input" />
                        <p v-if="form.errors.price" class="error">{{ form.errors.price }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="sorder">Urutan tampil</label>
                        <input id="sorder" v-model.number="form.sort_order" type="number" min="0" class="input" />
                    </div>
                    <label class="mt-7 flex items-center gap-2 text-sm">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300" /> Tampilkan di halaman booking
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-secondary" @click="show = false">Batal</button>
                    <button class="btn-primary" :disabled="form.processing">Simpan</button>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
