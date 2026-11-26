<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');
let timer;

watch(
    () => page.props.flash,
    (flash) => {
        const msg = flash?.success || flash?.error;
        if (!msg) return;
        message.value = msg;
        type.value = flash.error ? 'error' : 'success';
        visible.value = true;
        clearTimeout(timer);
        timer = setTimeout(() => (visible.value = false), 4000);
    },
    { immediate: true, deep: true }
);
</script>

<template>
    <Transition enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0 translate-y-2" enter-active-class="transition" leave-active-class="transition">
        <div v-if="visible" class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-md sm:right-6 sm:left-auto">
            <div :class="['flex items-start gap-3 rounded-xl px-4 py-3 text-sm text-white shadow-lg', type === 'error' ? 'bg-rose-600' : 'bg-slate-900']">
                <span>{{ type === 'error' ? '⚠️' : '✅' }}</span>
                <p class="flex-1">{{ message }}</p>
                <button class="opacity-70 hover:opacity-100" @click="visible = false" aria-label="Tutup">✕</button>
            </div>
        </div>
    </Transition>
</template>
