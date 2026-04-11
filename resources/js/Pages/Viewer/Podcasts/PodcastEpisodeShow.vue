<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Link } from '@inertiajs/vue3';

import PodcastEpisodePlayerControls from '@/Components/Podcasts/PodcastEpisodePlayerControls.vue';

defineProps({
    podcast: { type: Object, required: true },
    episode: { type: Object, required: true },
});
</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
};
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <SeoHead
            :title="episode.title || 'Episode'"
            :description="episode.description || podcast.description || ''"
            :image="episode.thumbnail_url || podcast.thumbnail_url || null"
            og-type="article"
        />

        <Link
            :href="route('podcast.show', { slug: podcast.slug })"
            class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-vidgaze-blue dark:text-zinc-400"
        >
            <font-awesome-icon :icon="['fas', 'arrow-left']" class="h-3 w-3" />
            {{ podcast.title }}
        </Link>

        <div class="mt-8">
            <PodcastEpisodePlayerControls :podcast="podcast" :episode="episode" />
        </div>

        <article
            v-if="episode.description"
            class="prose prose-zinc mt-10 max-w-none dark:prose-invert prose-p:leading-relaxed prose-p:text-zinc-700 dark:prose-p:text-zinc-300"
        >
            <h2 class="!mb-3 text-lg font-semibold text-zinc-900 dark:text-white">About this episode</h2>
            <p class="whitespace-pre-wrap">{{ episode.description }}</p>
        </article>
    </div>
</template>
