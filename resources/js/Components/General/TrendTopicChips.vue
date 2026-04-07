<script setup>
import axios from 'axios';
import {onMounted, ref} from 'vue';
import {Link} from '@inertiajs/vue3';

const props = defineProps({
    activeKey: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['trend-change']);

const topics = ref([]);
const updatedAt = ref(null);
const loading = ref(true);

/** Distinct hover themes for dynamic trend pills (cycle by index). */
const trendHoverPalettes = [
    {icon: ['fas', 'hashtag'], hover: 'hover:border-amber-400 hover:bg-amber-50 hover:text-amber-950 dark:hover:border-amber-500/50 dark:hover:bg-amber-950/35 dark:hover:text-amber-100'},
    {icon: ['fas', 'bolt'], hover: 'hover:border-cyan-400 hover:bg-cyan-50 hover:text-cyan-950 dark:hover:border-cyan-500/50 dark:hover:bg-cyan-950/35 dark:hover:text-cyan-100'},
    {icon: ['fas', 'fire'], hover: 'hover:border-rose-400 hover:bg-rose-50 hover:text-rose-950 dark:hover:border-rose-500/50 dark:hover:bg-rose-950/35 dark:hover:text-rose-100'},
    {icon: ['fas', 'star'], hover: 'hover:border-violet-400 hover:bg-violet-50 hover:text-violet-950 dark:hover:border-violet-500/50 dark:hover:bg-violet-950/35 dark:hover:text-violet-100'},
    {icon: ['fas', 'chart-line'], hover: 'hover:border-lime-500 hover:bg-lime-50 hover:text-lime-950 dark:hover:border-lime-500/50 dark:hover:bg-lime-950/30 dark:hover:text-lime-100'},
    {icon: ['fas', 'compass'], hover: 'hover:border-fuchsia-400 hover:bg-fuchsia-50 hover:text-fuchsia-950 dark:hover:border-fuchsia-500/50 dark:hover:bg-fuchsia-950/35 dark:hover:text-fuchsia-100'},
    {icon: ['fas', 'newspaper'], hover: 'hover:border-teal-400 hover:bg-teal-50 hover:text-teal-950 dark:hover:border-teal-500/50 dark:hover:bg-teal-950/35 dark:hover:text-teal-100'},
    {icon: ['fas', 'globe'], hover: 'hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-950 dark:hover:border-indigo-500/50 dark:hover:bg-indigo-950/35 dark:hover:text-indigo-100'},
];

const quickLinks = [
    {
        label: 'Live',
        routeName: 'streams.index',
        icon: ['fas', 'tower-broadcast'],
        hover: 'hover:border-red-400 hover:bg-red-50 hover:text-red-900 dark:hover:border-red-500/50 dark:hover:bg-red-950/40 dark:hover:text-red-100',
    },
    {
        label: 'Categories',
        routeName: 'category.index',
        icon: ['fas', 'layer-group'],
        hover: 'hover:border-violet-400 hover:bg-violet-50 hover:text-violet-900 dark:hover:border-violet-500/50 dark:hover:bg-violet-950/40 dark:hover:text-violet-100',
    },
    {
        label: 'Podcasts',
        routeName: 'podcasts.index',
        icon: ['fas', 'headphones'],
        hover: 'hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-900 dark:hover:border-emerald-500/50 dark:hover:bg-emerald-950/40 dark:hover:text-emerald-100',
    },
];

const homeIcon = ['fas', 'house'];

function topicPalette(index) {
    return trendHoverPalettes[index % trendHoverPalettes.length];
}

async function loadTopics() {
    loading.value = true;
    try {
        const res = await axios.get(route('api.video.trend-feed.topics'));
        topics.value = Array.isArray(res.data?.topics) ? res.data.topics : [];
        updatedAt.value = res.data?.updated_at ?? null;
    } catch {
        topics.value = [];
    } finally {
        loading.value = false;
    }
}

onMounted(loadTopics);

function selectHome() {
    emit('trend-change', {key: null, label: null});
}

function selectTopic(topic) {
    emit('trend-change', {key: topic.key, label: topic.label});
}
</script>

<template>
    <nav class="mb-6 lg:mb-8 -mx-1" aria-label="Trending topics and discover">
        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-xs font-semibold shadow-sm transition-all duration-150 sm:text-sm"
                :class="activeKey === null
                    ? 'border-sky-500 bg-sky-50 text-sky-900 shadow-[0_0_18px_-6px_rgba(56,189,248,0.45)] dark:border-sky-400 dark:bg-sky-950/60 dark:text-sky-100 dark:shadow-[0_0_20px_-6px_rgba(56,189,248,0.35)]'
                    : 'border-zinc-200 bg-white text-zinc-800 hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 hover:shadow-[0_0_14px_-8px_rgba(56,189,248,0.28)] dark:border-zinc-600 dark:bg-zinc-900/80 dark:text-zinc-100 dark:hover:border-sky-500/50 dark:hover:bg-sky-950/40 dark:hover:text-sky-100 dark:hover:shadow-[0_0_16px_-8px_rgba(56,189,248,0.22)]'"
                @click="selectHome"
            >
                <font-awesome-icon :icon="homeIcon" class="h-3.5 w-3.5 shrink-0 opacity-90" aria-hidden="true" />
                Home feed
            </button>

            <template v-for="q in quickLinks" :key="q.label">
                <Link
                    :href="route(q.routeName)"
                    class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-zinc-800 shadow-sm transition-all duration-150 dark:border-zinc-600 dark:bg-zinc-900/80 dark:text-zinc-100 sm:text-sm hover:shadow-[0_0_12px_-8px_rgba(167,139,250,0.2)]"
                    :class="q.hover"
                >
                    <font-awesome-icon :icon="q.icon" class="h-3.5 w-3.5 shrink-0 opacity-85" aria-hidden="true" />
                    {{ q.label }}
                </Link>
            </template>

            <template v-if="!loading && topics.length > 0">
                <span
                    class="hidden h-4 w-px bg-zinc-300 dark:bg-zinc-600 sm:inline"
                    aria-hidden="true"
                />
                <template v-for="(topic, idx) in topics" :key="topic.key">
                    <button
                        type="button"
                        class="inline-flex max-w-[14rem] items-center gap-2 truncate rounded-full border px-3.5 py-1.5 text-left text-xs font-medium transition-all duration-150 sm:text-sm"
                        :class="[
                            activeKey === topic.key
                                ? 'border-orange-500/80 bg-orange-50 text-orange-950 shadow-[0_0_16px_-6px_rgba(251,146,60,0.35)] dark:border-orange-400 dark:bg-orange-950/50 dark:text-orange-100 dark:shadow-[0_0_18px_-6px_rgba(251,146,60,0.25)]'
                                : 'border-zinc-200 bg-zinc-50/90 text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-200',
                            activeKey !== topic.key ? topicPalette(idx).hover : '',
                        ]"
                        :title="topic.label"
                        @click="selectTopic(topic)"
                    >
                        <font-awesome-icon
                            :icon="topicPalette(idx).icon"
                            class="h-3.5 w-3.5 shrink-0 opacity-85"
                            aria-hidden="true"
                        />
                        <span class="truncate">{{ topic.label }}</span>
                    </button>
                </template>
            </template>
        </div>
    </nav>
</template>
