<script setup>
import { Link } from '@inertiajs/vue3';

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
    <div class="mx-auto max-w-3xl px-4 py-8">
        <SeoHead
            :title="episode.title || 'Episode'"
            :description="episode.description || podcast.description || ''"
            og-type="article"
        />

        <Link
            :href="route('podcast.show', { slug: podcast.slug })"
            class="text-sm text-zinc-500 hover:text-vidgaze-blue dark:text-zinc-400"
        >
            ← {{ podcast.title }}
        </Link>

        <h1 class="mt-6 text-2xl font-bold text-zinc-900 dark:text-white">{{ episode.title }}</h1>
        <p v-if="episode.time_published" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            {{ episode.time_published }}
        </p>

        <div v-if="episode.audio_url" class="mt-6">
            <audio class="w-full" controls preload="metadata" :src="episode.audio_url" />
        </div>

        <p
            v-if="episode.description"
            class="mt-6 whitespace-pre-wrap text-zinc-700 dark:text-zinc-300"
        >{{ episode.description }}</p>
    </div>
</template>
