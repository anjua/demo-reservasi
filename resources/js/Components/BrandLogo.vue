<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({ size: { type: String, default: 'md' }, light: Boolean });
const brand = computed(() => usePage().props.brand);
const initials = computed(() =>
    brand.value.name.split(' ').filter(Boolean).slice(0, 2).map((w) => w[0]).join('').toUpperCase()
);
const box = computed(() => (props.size === 'sm' ? 'h-8 w-8 text-xs' : 'h-10 w-10 text-sm'));
</script>

<template>
    <span class="flex items-center gap-2.5">
        <img v-if="brand.logo" :src="brand.logo" :alt="brand.name" :class="[box, 'rounded-xl object-cover']" />
        <span v-else :class="[box, 'grid place-items-center rounded-xl bg-brand font-bold text-white']">{{ initials }}</span>
        <span :class="['font-bold leading-tight', light ? 'text-white' : 'text-slate-900']">{{ brand.name }}</span>
    </span>
</template>
