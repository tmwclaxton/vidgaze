<template>
    <div>
        <ul v-if="rows.length" class="space-y-3">
            <li v-for="row in rows" :key="row.label + (row.slug ?? '')">
                <div class="flex items-baseline justify-between gap-3 text-base">
                    <span class="min-w-0 truncate text-zinc-800 dark:text-zinc-200">
                        <Link v-if="row.href" :href="row.href" class="hover:underline">{{ row.label }}</Link>
                        <span v-else>{{ row.label }}</span>
                        <span v-if="row.sub" class="block truncate text-sm text-zinc-500 dark:text-zinc-400">{{ row.sub }}</span>
                    </span>
                    <span class="shrink-0 text-sm font-semibold tabular-nums text-zinc-900 dark:text-white">
                        {{ fmt(row.count) }} {{ unit }}<span v-if="showShare" class="font-normal text-zinc-500 dark:text-zinc-400">, {{ share(row.count) }}%</span>
                    </span>
                </div>
                <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800" aria-hidden="true">
                    <div class="h-full rounded-full bg-sky-500" :style="{ width: width(row.count) }"></div>
                </div>
            </li>
        </ul>
        <p v-else class="text-base text-zinc-500 dark:text-zinc-400">{{ empty }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    rows: { type: Array, default: () => [] },
    unit: { type: String, default: '' },
    empty: { type: String, default: 'Nothing recorded yet.' },
    showShare: { type: Boolean, default: false },
});
const fmt = (n) => new Intl.NumberFormat('en-GB').format(n ?? 0);
const max = computed(() => Math.max(1, ...props.rows.map((r) => r.count)));
const total = computed(() => props.rows.reduce((a, r) => a + r.count, 0) || 1);
const width = (n) => `${Math.max(2, (n / max.value) * 100)}%`;
const share = (n) => Math.round((n / total.value) * 1000) / 10;
</script>
