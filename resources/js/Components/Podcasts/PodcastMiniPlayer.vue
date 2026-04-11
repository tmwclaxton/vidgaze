<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { debounce } from 'lodash';
import { storeToRefs } from 'pinia';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

import { usePodcastPlayerStore } from '@/Stores/PodcastPlayerStore';
import { formatPlaybackTime } from '@/utils/podcastPlayback';

const podcastPlayer = usePodcastPlayerStore();
const { episode, podcast, isPlaying, showFloating, hasTrack, currentTime, duration, pendingSeek } =
    storeToRefs(podcastPlayer);

const audioRef = ref(null);
const draggableDiv = ref(null);

const panelVisible = computed(
    () => !!(hasTrack.value && showFloating.value && podcast.value && episode.value),
);

let initialX = 0;
let initialY = 0;
let isDragging = false;

const onMouseDown = (event) => {
    event.preventDefault();
    initialX = event.clientX;
    initialY = event.clientY;
    isDragging = true;
};

const onMouseMove = (event) => {
    if (!isDragging || !draggableDiv.value) return;
    event.preventDefault();

    let left = parseInt(draggableDiv.value.style.left, 10);
    let top = parseInt(draggableDiv.value.style.top, 10);
    if (Number.isNaN(left)) {
        left = 15;
    }
    if (Number.isNaN(top)) {
        top = Math.max(15, window.innerHeight - draggableDiv.value.offsetHeight - 15);
    }

    const deltaX = event.clientX - initialX;
    const deltaY = event.clientY - initialY;

    const newLeft = left + deltaX;
    const newTop = top + deltaY;

    const maxX = window.innerWidth - draggableDiv.value.offsetWidth - 15;
    const maxY = window.innerHeight - draggableDiv.value.offsetHeight - 15;
    const clampedLeft = Math.max(15, Math.min(newLeft, maxX));
    const clampedTop = Math.max(15, Math.min(newTop, maxY));

    draggableDiv.value.style.left = `${clampedLeft}px`;
    draggableDiv.value.style.top = `${clampedTop}px`;

    initialX = event.clientX;
    initialY = event.clientY;
};

const onMouseUp = () => {
    isDragging = false;
};

const debouncedViewportCheck = debounce(() => {
    setTimeout(() => {
        if (!draggableDiv.value) return;
        const rect = draggableDiv.value.getBoundingClientRect();
        const isInViewport =
            rect.top >= 15 &&
            rect.left >= 15 &&
            rect.bottom <= window.innerHeight - 15 &&
            rect.right <= window.innerWidth - 15;

        if (!isInViewport) {
            const maxX = window.innerWidth - draggableDiv.value.offsetWidth - 15;
            const maxY = window.innerHeight - draggableDiv.value.offsetHeight - 15;
            const clampedLeft = Math.max(15, Math.min(rect.left, maxX));
            const clampedTop = Math.max(15, Math.min(rect.top, maxY));
            draggableDiv.value.style.left = `${clampedLeft}px`;
            draggableDiv.value.style.top = `${clampedTop}px`;
        }
    }, 100);
}, 100);

const onWindowResize = () => debouncedViewportCheck();

const closePlayer = () => {
    podcastPlayer.clear();
};

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

const onTimeUpdate = () => {
    const el = audioRef.value;
    if (!el) return;
    podcastPlayer.syncTimeState(el.currentTime, el.duration);
};

const onLoadedMetadata = () => {
    const el = audioRef.value;
    if (!el) return;
    podcastPlayer.syncTimeState(el.currentTime, el.duration);
};

const placeFloatingPanel = () => {
    if (!draggableDiv.value) return;
    draggableDiv.value.style.left = '15px';
    draggableDiv.value.style.top = `${window.innerHeight - draggableDiv.value.offsetHeight - 15}px`;
    debouncedViewportCheck();
};

watch(pendingSeek, (t) => {
    if (t == null || !audioRef.value) return;
    audioRef.value.currentTime = t;
    podcastPlayer.consumePendingSeek();
});

watch(
    () => episode.value?.audio_url,
    async (newUrl, oldUrl) => {
        const el = audioRef.value;
        if (!el) return;
        if (!newUrl) {
            el.pause();
            el.removeAttribute('src');
            podcastPlayer.syncTimeState(0, 0);
            return;
        }
        if (newUrl !== oldUrl) {
            el.src = newUrl;
            el.load();
            await nextTick();
        }
        if (podcastPlayer.isPlaying) {
            try {
                await el.play();
            } catch {
                podcastPlayer.pause();
            }
        } else {
            el.pause();
        }
    },
    { flush: 'post' },
);

watch(isPlaying, async (playing) => {
    const el = audioRef.value;
    if (!el || !episode.value?.audio_url) return;
    if (playing) {
        try {
            await el.play();
        } catch {
            podcastPlayer.pause();
        }
    } else {
        el.pause();
    }
});

watch(panelVisible, (visible) => {
    if (visible) {
        nextTick(() => placeFloatingPanel());
    }
});

onMounted(() => {
    if (panelVisible.value) {
        nextTick(() => placeFloatingPanel());
    }

    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
    window.addEventListener('resize', onWindowResize);
});

onUnmounted(() => {
    debouncedViewportCheck.cancel();
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', onMouseUp);
    window.removeEventListener('resize', onWindowResize);
});
</script>

<template>
    <audio
        v-show="false"
        ref="audioRef"
        preload="metadata"
        @timeupdate="onTimeUpdate"
        @loadedmetadata="onLoadedMetadata"
        @ended="podcastPlayer.pause()"
    />
    <!-- Teleport + z-50: match stacking above nav (z-40) / top bar (z-50); avoid clipping under layout (same idea as floating modals). -->
    <Teleport to="body">
        <div
            v-if="panelVisible"
            ref="draggableDiv"
            class="fixed z-50 flex w-80 flex-col overflow-hidden rounded-xl bg-white shadow shadow-md dark:bg-vidgaze-blue-dropdown"
        >
        <div
            class="flex cursor-move flex-row items-center gap-2 border-b border-zinc-200 px-2 py-2 dark:border-zinc-800"
            @mousedown="onMouseDown"
        >
            <div class="min-w-0 flex-1 pl-1">
                <p class="truncate text-xs font-medium text-zinc-500 dark:text-zinc-400">Now playing</p>
                <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ episode?.title }}</p>
            </div>
            <button
                type="button"
                class="cursor-pointer p-2 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white"
                aria-label="Close podcast player"
                @mousedown.stop
                @click.stop="closePlayer"
            >
                <font-awesome-icon class="h-4 w-4" :icon="['fas', 'times']" />
            </button>
        </div>

        <div class="relative aspect-square w-full bg-zinc-900">
            <img
                v-if="podcastPlayer.artUrl"
                :src="podcastPlayer.artUrl"
                :alt="episode?.title || ''"
                class="h-full w-full object-cover"
            />
            <div v-else class="flex h-full w-full items-center justify-center text-zinc-500">
                <font-awesome-icon class="h-16 w-16 opacity-40" :icon="['fas', 'microphone']" />
            </div>
        </div>

        <div class="flex flex-col gap-2 p-3">
            <input
                type="range"
                class="RANGE_PODCAST h-1.5 w-full cursor-pointer appearance-none rounded-full bg-zinc-200 accent-vidgaze-blue dark:bg-zinc-700"
                :max="progressMax()"
                :value="progressDisplay()"
                min="0"
                step="0.1"
                aria-label="Seek"
                @input="onScrubInput"
                @change="onScrubCommit"
            />
            <div class="flex flex-row items-center justify-between text-xs tabular-nums text-zinc-500 dark:text-zinc-400">
                <span>{{ formatPlaybackTime(progressDisplay()) }}</span>
                <span>{{ formatPlaybackTime(duration) }}</span>
            </div>
            <div class="flex flex-row items-center justify-between gap-2">
                <button
                    type="button"
                    class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                    :aria-label="isPlaying ? 'Pause' : 'Play'"
                    @mousedown.stop
                    @click.stop="podcastPlayer.togglePlay()"
                >
                    <font-awesome-icon v-if="isPlaying" class="h-4 w-4" :icon="['fas', 'pause']" />
                    <font-awesome-icon v-else class="h-4 w-4 pl-0.5" :icon="['fas', 'play']" />
                </button>
                <div class="min-w-0 flex-1">
                    <Link
                        :href="route('podcast.episode', { podcastSlug: podcast.slug, episodeSlug: episode.slug })"
                        class="block truncate text-sm font-semibold text-zinc-900 hover:text-vidgaze-blue dark:text-white dark:hover:text-vidgaze-blue"
                        @mousedown.stop
                    >
                        {{ episode?.title }}
                    </Link>
                    <Link
                        :href="route('podcast.show', { slug: podcast.slug })"
                        class="mt-0.5 block truncate text-xs text-zinc-500 hover:text-vidgaze-blue dark:text-zinc-400"
                        @mousedown.stop
                    >
                        {{ podcast?.title }}
                    </Link>
                </div>
            </div>
        </div>
        </div>
    </Teleport>
</template>

<style scoped>
input[type='range'].RANGE_PODCAST::-webkit-slider-thumb {
    @apply h-3 w-3 cursor-pointer appearance-none rounded-full bg-vidgaze-blue;
}
input[type='range'].RANGE_PODCAST::-moz-range-thumb {
    @apply h-3 w-3 cursor-pointer rounded-full border-0 bg-vidgaze-blue;
}
</style>
