<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    podcast: { type: Object, required: true },
    episodes: { type: Array, default: () => [] },
});
</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
};
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8">
        <SeoHead
            :title="podcast.title || 'Podcast'"
            :description="podcast.description || ''"
            :image="podcast.thumbnail_url || null"
        />

        <Link :href="route('podcasts.index')" class="text-sm text-zinc-500 hover:text-vidgaze-blue dark:text-zinc-400">
            ← Podcasts
        </Link>

        <div class="mt-6 flex flex-col gap-6 sm:flex-row sm:items-start">
            <img
                v-if="podcast.thumbnail_url"
                :src="podcast.thumbnail_url"
                :alt="podcast.title"
                class="w-48 shrink-0 rounded-lg shadow-md"
            />
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">{{ podcast.title }}</h1>
                <p v-if="podcast.creator?.name" class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ podcast.creator.name }}
                </p>
                <p v-if="podcast.description" class="mt-4 text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">
                    {{ podcast.description }}
                </p>
            </div>
        </div>

        <h2 class="mt-10 text-xl font-semibold text-zinc-900 dark:text-white">Episodes</h2>
        <ul class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
            <li v-for="ep in episodes" :key="ep.slug" class="py-4">
                <Link
                    :href="route('podcast.episode', { podcastSlug: podcast.slug, episodeSlug: ep.slug })"
                    class="font-medium text-zinc-900 hover:text-vidgaze-blue dark:text-white dark:hover:text-vidgaze-blue"
                >
                    {{ ep.title }}
                </Link>
                <p v-if="ep.time_published" class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ ep.time_published }}
                </p>
            </li>
        </ul>
        <p v-if="episodes.length === 0" class="mt-4 text-zinc-500 dark:text-zinc-400">No episodes loaded yet.</p>
    </div>
</template>
