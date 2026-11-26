<script setup>
import { watch, onBeforeUnmount } from 'vue';

const props = defineProps({ show: Boolean, title: String, maxWidth: { type: String, default: 'max-w-lg' } });
const emit = defineEmits(['close']);

const onKey = (e) => e.key === 'Escape' && props.show && emit('close');
watch(() => props.show, (v) => {
    document.body.style.overflow = v ? 'hidden' : '';
    v ? window.addEventListener('keydown', onKey) : window.removeEventListener('keydown', onKey);
});
onBeforeUnmount(() => { document.body.style.overflow = ''; window.removeEventListener('keydown', onKey); });
</script>

<template>
    <Teleport to="body">
        <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" enter-active-class="transition" leave-active-class="transition">
            <div v-if="show" class="fixed inset-0 z-40 flex items-end justify-center bg-slate-900/50 p-0 sm:items-center sm:p-4" @click.self="emit('close')">
                <div :class="['max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-xl sm:rounded-2xl', maxWidth]">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <h3 class="font-semibold text-slate-900">{{ title }}</h3>
                        <button class="text-slate-400 hover:text-slate-600" @click="emit('close')" aria-label="Tutup">✕</button>
                    </div>
                    <div class="p-5"><slot /></div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
