<script setup>
defineProps({ status: String, label: String });
const styles = {
    pending: 'bg-amber-100 text-amber-800 ring-amber-200',
    confirmed: 'bg-sky-100 text-sky-800 ring-sky-200',
    completed: 'bg-emerald-100 text-emerald-800 ring-emerald-200',
    cancelled: 'bg-slate-100 text-slate-500 ring-slate-200',
};
</script>

<template>
    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset whitespace-nowrap', styles[status]]">
        {{ label }}
    </span>
</template>
