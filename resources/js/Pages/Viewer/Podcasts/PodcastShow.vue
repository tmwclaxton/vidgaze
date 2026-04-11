<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Link } from '@inertiajs/vue3';

import { usePodcastPlayerStore } from '@/Stores/PodcastPlayerStore';
import { formatEpisodeDate } from '@/utils/podcastPlayback';

const props = defineProps({
    podcast: { type: Object, required: true },
    episodes: { type: Array, default: () => [] },
});

const podcastPlayer = usePodcastPlayerStore();

const playEpisode = (ep, event) => {
    event.preventDefault();
    event.stopPropagation();
    if (!ep.audio_url) return;
    podcastPlayer.playTrack(props.podcast, ep);
};

const isCurrentEpisode = (ep) =>
    podcastPlayer.podcast?.slug === props.podcast.slug && podcastPlayer.episode?.slug === ep.slug;
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
            :title="podcast.title || 'Podcast'"
            :description="podcast.description || ''"
            :image="podcast.thumbnail_url || null"
        />

        <Link
            :href="route('podcasts.index')"
            class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-vidgaze-blue dark:text-zinc-400"
        >
            <font-awesome-icon :icon="['fas', 'arrow-left']" class="h-3 w-3" />
            Podcasts
        </Link>

        <header
            class="mt-8 overflow-hidden rounded-2xl border border-zinc-200/80 bg-gradient-to-br from-zinc-50 via-white to-emerald-50/40 dark:border-zinc-700/80 dark:from-zinc-900 dark:via-zinc-900 dark:to-emerald-950/30"
        >
            <div class="flex flex-col gap-6 p-6 sm:flex-row sm:items-end sm:p-8">
                <div
                    class="mx-auto w-40 shrink-0 overflow-hidden rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 sm:mx-0 sm:w-48"
                >
                    <img
                        v-if="podcast.thumbnail_url"
                        :src="podcast.thumbnail_url"
                        :alt="podcast.title"
                        class="aspect-square w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex aspect-square w-full items-center justify-center bg-zinc-200 dark:bg-zinc-800"
                    >
                        <font-awesome-icon class="h-14 w-14 text-zinc-400" :icon="['fas', 'microphone']" />
                    </div>
                </div>
                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                        {{ podcast.title }}
                    </h1>
                    <p v-if="podcast.creator?.name" class="mt-2 text-emerald-700 dark:text-emerald-400">
                        {{ podcast.creator.name }}
                    </p>
                    <p
                        v-if="podcast.description"
                        class="mt-4 max-w-2xl text-pretty text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 sm:text-base"
                    >
                        {{ podcast.description }}
                    </p>
                </div>
            </div>
        </header>

        <section class="mt-12">
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">Episodes</h2>
                <span v-if="episodes.length" class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ episodes.length }} episode{{ episodes.length === 1 ? '' : 's' }}
                </span>
            </div>

            <ul
                v-if="episodes.length"
                role="list"
                class="mt-6 divide-y divide-zinc-200/90 rounded-xl border border-zinc-200/80 bg-white/60 dark:divide-zinc-700/90 dark:border-zinc-700/80 dark:bg-zinc-900/40"
            >
                <li v-for="ep in episodes" :key="ep.slug">
                    <div
                        class="flex flex-col gap-3 p-4 transition hover:bg-zinc-50/80 sm:flex-row sm:items-center sm:gap-4 dark:hover:bg-zinc-800/40"
                    >
                        <div
                            class="relative h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-zinc-200 dark:bg-zinc-800 sm:h-20 sm:w-20"
                        >
                            <img
                                v-if="ep.thumbnail_url || podcast.thumbnail_url"
                                :src="ep.thumbnail_url || podcast.thumbnail_url"
                                :alt="ep.title"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center text-zinc-400">
                                <font-awesome-icon :icon="['fas', 'headphones']" />
                            </div>
                            <span
                                v-if="isCurrentEpisode(ep)"
                                class="absolute inset-x-1 bottom-1 rounded bg-emerald-600/90 px-1 py-0.5 text-center text-[10px] font-semibold uppercase tracking-wide text-white"
                            >
                                Playing
                            </span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <Link
                                :href="route('podcast.episode', { podcastSlug: podcast.slug, episodeSlug: ep.slug })"
                                class="font-semibold text-zinc-900 hover:text-vidgaze-blue dark:text-white dark:hover:text-vidgaze-blue"
                            >
                                {{ ep.title }}
                            </Link>
                            <div
                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400"
                            >
                                <span v-if="ep.time_published">{{ formatEpisodeDate(ep.time_published) }}</span>
                                <span v-if="ep.duration">{{ ep.duration }}</span>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-row items-center">
                            <button
                                v-if="ep.audio_url"
                                type="button"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                                :aria-label="isCurrentEpisode(ep) && podcastPlayer.isPlaying ? 'Pause episode' : 'Play episode'"
                                @click="playEpisode(ep, $event)"
                            >
                                <font-awesome-icon
                                    v-if="isCurrentEpisode(ep) && podcastPlayer.isPlaying"
                                    :icon="['fas', 'pause']"
                                    class="h-4 w-4"
                                />
                                <font-awesome-icon v-else :icon="['fas', 'play']" class="h-4 w-4 pl-0.5" />
                            </button>
                        </div>
                    </div>
                </li>
            </ul>
            <p v-else class="mt-6 rounded-xl border border-dashed border-zinc-300 p-8 text-center text-zinc-500 dark:border-zinc-600 dark:text-zinc-400">
                No episodes loaded yet.
            </p>
        </section>
    </div>
</template>
