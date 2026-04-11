<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';

import { usePodcastPlayerStore } from '@/Stores/PodcastPlayerStore';
import { formatEpisodeDate, formatPlaybackTime } from '@/utils/podcastPlayback';

const props = defineProps({
    podcast: { type: Object, required: true },
    episode: { type: Object, required: true },
});

const podcastPlayer = usePodcastPlayerStore();
const { isPlaying, currentTime, duration, hasTrack } = storeToRefs(podcastPlayer);

const isThisEpisode = computed(
    () =>
        podcastPlayer.podcast?.slug === props.podcast.slug && podcastPlayer.episode?.slug === props.episode.slug,
);

const scrubbing = ref(false);
const scrubValue = ref(0);

const progressMax = () => (duration.value > 0 ? duration.value : 1);
const progressDisplay = () => (scrubbing.value ? scrubValue.value : currentTime.value);

const onScrubInput = (e) => {
    scrubbing.value = true;
    scrubValue.value = Number(e.target.value);
};

const onScrubCommit = () => {
    podcastPlayer.requestSeek(scrubValue.value);
    scrubbing.value = false;
};

const art = computed(
    () => props.episode.thumbnail_url || props.podcast.thumbnail_url || podcastPlayer.artUrl,
);

const publishedLabel = computed(() => formatEpisodeDate(props.episode.time_published));

watch(
    () => [props.podcast.slug, props.episode.slug],
    () => {
        scrubbing.value = false;
    },
);
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-gradient-to-b from-zinc-50 to-zinc-100/90 shadow-lg dark:border-zinc-700/80 dark:from-zinc-900 dark:to-zinc-950"
    >
        <div class="flex flex-col gap-6 p-6 sm:flex-row sm:items-stretch">
            <div
                class="mx-auto aspect-square w-full max-w-[280px] shrink-0 overflow-hidden rounded-xl bg-zinc-800 shadow-inner sm:mx-0 sm:w-56"
            >
                <img v-if="art" :src="art" :alt="episode.title" class="h-full w-full object-cover" />
                <div v-else class="flex h-full w-full items-center justify-center text-zinc-500">
                    <font-awesome-icon class="h-20 w-20 opacity-35" :icon="['fas', 'microphone']" />
                </div>
            </div>

            <div class="flex min-w-0 flex-1 flex-col justify-center gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-snug text-zinc-900 dark:text-white sm:text-2xl">
                        {{ episode.title }}
                    </h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ podcast.title }}</p>
                    <p v-if="publishedLabel" class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">{{ publishedLabel }}</p>
                </div>

                <div v-if="episode.audio_url" class="flex flex-col gap-3">
                    <input
                        type="range"
                        class="RANGE_PODCAST_EP h-2 w-full cursor-pointer appearance-none rounded-full bg-zinc-200 accent-emerald-600 dark:bg-zinc-700 dark:accent-emerald-500"
                        :max="isThisEpisode && hasTrack ? progressMax() : 1"
                        :value="isThisEpisode && hasTrack ? progressDisplay() : 0"
                        min="0"
                        step="0.1"
                        :disabled="!isThisEpisode || !hasTrack"
                        aria-label="Seek episode"
                        @input="onScrubInput"
                        @change="onScrubCommit"
                    />
                    <div
                        class="flex flex-row justify-between text-xs tabular-nums text-zinc-500 dark:text-zinc-400"
                    >
                        <span>{{
                            isThisEpisode && hasTrack ? formatPlaybackTime(progressDisplay()) : '0:00'
                        }}</span>
                        <span>{{ isThisEpisode && hasTrack ? formatPlaybackTime(duration) : '—' }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-md transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                            :aria-label="isThisEpisode && isPlaying ? 'Pause' : 'Play'"
                            @click="
                                isThisEpisode
                                    ? podcastPlayer.togglePlay()
                                    : podcastPlayer.playTrack(podcast, episode)
                            "
                        >
                            <font-awesome-icon
                                v-if="isThisEpisode && isPlaying"
                                class="h-6 w-6"
                                :icon="['fas', 'pause']"
                            />
                            <font-awesome-icon v-else class="h-6 w-6 pl-1" :icon="['fas', 'play']" />
                        </button>
                        <button
                            v-if="isThisEpisode && hasTrack"
                            type="button"
                            class="rounded-lg border border-zinc-300 px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800"
                            @click="podcastPlayer.clear()"
                        >
                            Stop
                        </button>
                        <p v-if="episode.duration" class="text-sm text-zinc-600 dark:text-zinc-400">
                            <span class="font-medium text-zinc-500 dark:text-zinc-500">Length</span>
                            {{ episode.duration }}
                        </p>
                    </div>
                </div>
                <p v-else class="text-sm text-zinc-500 dark:text-zinc-400">No audio is available for this episode.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
input[type='range'].RANGE_PODCAST_EP::-webkit-slider-thumb {
    @apply h-4 w-4 cursor-pointer appearance-none rounded-full bg-emerald-600;
}
input[type='range'].RANGE_PODCAST_EP::-moz-range-thumb {
    @apply h-4 w-4 cursor-pointer rounded-full border-0 bg-emerald-600;
}
</style>
