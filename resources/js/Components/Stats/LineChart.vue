<template>
    <div>
        <div class="relative" @mousemove="onMove" @mouseleave="hover = null">
            <div class="flex">
                <div class="flex w-12 shrink-0 flex-col justify-between pr-2 text-right text-sm tabular-nums text-zinc-500 dark:text-zinc-400" :style="{ height: height + 'px' }">
                    <span>{{ fmt(max) }}</span>
                    <span>{{ fmt(Math.round(max / 2)) }}</span>
                    <span>0</span>
                </div>
                <div ref="plot" class="relative min-w-0 flex-1" :style="{ height: height + 'px' }">
                    <svg class="absolute inset-0 h-full w-full overflow-visible" viewBox="0 0 1000 100" preserveAspectRatio="none" role="img" :aria-label="ariaLabel">
                        <line v-for="y in [0, 50, 100]" :key="y" x1="0" x2="1000" :y1="y" :y2="y" stroke="currentColor" class="text-zinc-200 dark:text-zinc-700" vector-effect="non-scaling-stroke" />
                        <template v-for="s in series" :key="s.label">
                            <path v-if="s.fill" :d="area(s.data)" :fill="s.color" fill-opacity="0.15" />
                            <path :d="line(s.data)" fill="none" :stroke="s.color" stroke-width="2.5" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </template>
                        <line v-if="hover !== null" :x1="x(hover)" :x2="x(hover)" y1="0" y2="100" stroke="currentColor" class="text-zinc-400" vector-effect="non-scaling-stroke" />
                    </svg>
                    <div v-if="hover !== null" class="pointer-events-none absolute top-0 z-10 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-md dark:border-zinc-700 dark:bg-zinc-900"
                         :style="tipStyle">
                        <p class="font-semibold text-zinc-900 dark:text-white">{{ fmtLabel(labels[hover]) }}</p>
                        <p v-for="s in series" :key="s.label" class="whitespace-nowrap text-zinc-700 dark:text-zinc-300">
                            <span class="mr-1 inline-block h-3 w-3 rounded-sm align-middle" :style="{ background: s.color }"></span>{{ s.label }}: {{ fmt(s.data[hover]) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="ml-12 mt-2 flex justify-between text-sm text-zinc-500 dark:text-zinc-400">
                <span>{{ fmtLabel(labels[0]) }}</span>
                <span class="hidden sm:inline">{{ fmtLabel(labels[Math.floor(labels.length / 2)]) }}</span>
                <span>{{ fmtLabel(labels[labels.length - 1]) }}</span>
            </div>
        </div>
        <ul class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-base text-zinc-700 dark:text-zinc-300">
            <li v-for="s in series" :key="s.label" class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded-sm" :style="{ background: s.color }"></span>
                <span>{{ s.label }}: <strong class="tabular-nums text-zinc-900 dark:text-white">{{ fmt(sum(s.data)) }}</strong> {{ totalSuffix }}</span>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    labels: { type: Array, required: true },
    series: { type: Array, required: true },
    height: { type: Number, default: 220 },
    monthly: { type: Boolean, default: false },
    totalSuffix: { type: String, default: 'in total' },
    ariaLabel: { type: String, default: 'Chart' },
});

const plot = ref(null);
const hover = ref(null);
const fmt = (n) => new Intl.NumberFormat('en-GB').format(n ?? 0);
const sum = (a) => a.reduce((t, v) => t + v, 0);
const max = computed(() => {
    const m = Math.max(1, ...props.series.flatMap((s) => s.data));
    const step = m <= 4 ? 2 : Math.pow(10, Math.floor(Math.log10(m))) / 2;
    return Math.ceil(m / step) * step;
});
const n = computed(() => Math.max(1, props.labels.length - 1));
const x = (i) => (i / n.value) * 1000;
const y = (v) => 100 - (v / max.value) * 100;
const line = (d) => d.map((v, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${y(v).toFixed(2)}`).join(' ');
const area = (d) => `${line(d)} L1000,100 L0,100 Z`;

const fmtLabel = (s) => {
    if (!s) return '';
    if (props.monthly) {
        return new Date(`${s}-01T00:00:00`).toLocaleDateString('en-GB', { month: 'short', year: 'numeric' });
    }
    return new Date(`${s}T00:00:00`).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
};

const onMove = (e) => {
    const r = plot.value?.getBoundingClientRect();
    if (!r || e.clientX < r.left || e.clientX > r.right) { hover.value = null; return; }
    hover.value = Math.round(((e.clientX - r.left) / r.width) * n.value);
};
const tipStyle = computed(() => {
    const pct = (hover.value / n.value) * 100;
    return pct > 55 ? { right: `${100 - pct + 2}%` } : { left: `${pct + 2}%` };
});
</script>
