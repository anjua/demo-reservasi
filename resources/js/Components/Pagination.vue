<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ links: Array });
const label = (l) => l.replace('&laquo; Sebelumnya', '‹').replace('Berikutnya &raquo;', '›').replace('&laquo; Previous', '‹').replace('Next &raquo;', '›').replace('&laquo;', '‹').replace('&raquo;', '›');
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap gap-1">
        <template v-for="(l, i) in links" :key="i">
            <span v-if="!l.url" class="rounded-lg px-3 py-1.5 text-sm text-slate-300" v-html="label(l.label)" />
            <Link v-else :href="l.url" preserve-scroll
                :class="['rounded-lg px-3 py-1.5 text-sm', l.active ? 'bg-brand text-white' : 'bg-white text-slate-600 hover:bg-slate-100']" v-html="label(l.label)" />
        </template>
    </nav>
</template>
