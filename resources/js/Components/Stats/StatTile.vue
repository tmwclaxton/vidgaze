<template>
    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ label }}</p>
        <p class="mt-1 text-2xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ fmt(value) }}</p>
        <p v-if="hint" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ hint }}</p>
    </div>
</template>

<script setup>
defineProps({ label: String, value: Number, hint: String });
const fmt = (n) => new Intl.NumberFormat('en-GB').format(n ?? 0);
</script>
