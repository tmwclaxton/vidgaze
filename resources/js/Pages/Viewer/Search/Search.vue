<script setup>
import axios from "axios";
import { onMounted, onUnmounted, ref } from "vue";
import CreatorSearchCard from "@/Components/Cards/CreatorSearchCard/CreatorSearchCard.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import CreatorSearchSkeleton from "@/Components/Cards/CreatorSearchCard/CreatorSearchSkeleton.vue";
import VideoStreamSearchCard from "@/Components/Cards/VideoStreamCards/VideoStreamSearchCard/VideoStreamSearchCard.vue";
import VideoStreamSearchSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamSearchCard/VideoStreamSearchSkeleton.vue";
import ErrorMessage from "@/Components/Errors/ErrorMessage.vue";
import PodcastCard from "@/Components/Cards/PodcastCards/PocastCard/PodcastCard.vue";
import PodcastCardSkeleton from "@/Components/Cards/PodcastCards/PocastCard/PodcastCardSkeleton.vue";

const props = defineProps({
    searchQuery: String,
});

const filters = ref(false);
const searchQuery = ref(props.searchQuery);

const visibleCreatorsCount = ref(2);
const expandCreators = ref(false);

const toggleVisibleCreators = () => {
    expandCreators.value = !expandCreators.value;
    visibleCreatorsCount.value = expandCreators.value ? creators.value.length : 2;
};

const loading = ref(true);
const creators = ref([]);
const videos = ref([]);
const playlists = ref([]);
const podcasts = ref([]);
const streams = ref([]);

const finished = ref(false);

/** @param {Record<string, unknown>} data */
function resultFingerprint(data) {
    const d = (x) => (Array.isArray(x) ? x.length : 0);
    const vr = data.video_ranking && typeof data.video_ranking === "object" ? data.video_ranking : {};
    const rankState = vr.pending ? "p" : vr.cached ? "c" : vr.skipped ? "s" : "n";
    const videoIds = (data.videos?.data ?? [])
        .map((v) => v.id)
        .join(",");
    return [
        d(data.creators?.data),
        d(data.videos?.data),
        d(data.playlists?.data),
        d(data.podcasts?.data),
        d(data.streams?.data),
        rankState,
        videoIds,
    ].join("|");
}

function applyResultsPayload(data) {
    if (data.creators !== undefined) {
        creators.value = data.creators.data;
    } else {
        visibleCreatorsCount.value = 0;
    }
    if (data.videos !== undefined) {
        videos.value = data.videos.data;
    }
    if (data.playlists !== undefined) {
        playlists.value = data.playlists.data;
    }
    if (data.podcasts !== undefined) {
        podcasts.value = data.podcasts.data;
    }
    if (data.streams !== undefined) {
        streams.value = data.streams.data;
    }

    if (
        creators.value.length > 0 ||
        videos.value.length > 0 ||
        playlists.value.length > 0 ||
        podcasts.value.length > 0 ||
        streams.value.length > 0
    ) {
        loading.value = false;
    }
}

function startSearch(q) {
    const url = route("api.search.start_query", { q: q.value });
    axios.post(url).catch(function (error) {
        console.log(error);
    });
}

let resultsAbort = null;

function retrieveResults(q) {
    resultsAbort?.abort();
    resultsAbort = new AbortController();
    const url = route("api.search.get_results", { q: q.value });
    return axios
        .get(url, { signal: resultsAbort.signal })
        .then(function (response) {
            return response.data;
        })
        .catch(function (error) {
            if (axios.isCancel?.(error) || error?.code === "ERR_CANCELED") {
                return null;
            }
            console.log(error);
            return null;
        });
}

let pollTimer = null;
let cancelled = false;
const POLL_FAST_MS = 850;
const POLL_SLOW_MS = 2200;
const POLL_PHASE_MS = 14000;
const MAX_WAIT_MS = 30000;

onMounted(() => {
    startSearch(searchQuery);
    const startedAt = Date.now();
    let stableRounds = 0;
    let lastFingerprint = "";

    function scheduleNext() {
        if (cancelled) {
            return;
        }
        const elapsed = Date.now() - startedAt;
        if (elapsed >= MAX_WAIT_MS) {
            finished.value = true;
            loading.value = false;
            return;
        }
        const delay = elapsed < POLL_PHASE_MS ? POLL_FAST_MS : POLL_SLOW_MS;
        pollTimer = setTimeout(runPoll, delay);
    }

    function runPoll() {
        if (cancelled) {
            return;
        }
        retrieveResults(searchQuery).then((data) => {
            if (cancelled || data === null) {
                if (!cancelled) {
                    scheduleNext();
                }
                return;
            }

            applyResultsPayload(data);

            const fp = resultFingerprint(data);
            if (fp === lastFingerprint) {
                stableRounds += 1;
            } else {
                stableRounds = 1;
                lastFingerprint = fp;
            }

            const rankingPending =
                data.video_ranking &&
                typeof data.video_ranking === "object" &&
                data.video_ranking.pending === true;

            const hasAny =
                creators.value.length > 0 ||
                videos.value.length > 0 ||
                playlists.value.length > 0 ||
                podcasts.value.length > 0 ||
                streams.value.length > 0;

            if (hasAny && !rankingPending && stableRounds >= 2) {
                finished.value = true;
                loading.value = false;
                return;
            }

            if (Date.now() - startedAt >= MAX_WAIT_MS) {
                finished.value = true;
                loading.value = false;
                return;
            }

            scheduleNext();
        });
    }

    runPoll();
});

onUnmounted(() => {
    cancelled = true;
    if (pollTimer) {
        clearTimeout(pollTimer);
    }
    resultsAbort?.abort();
});
</script>

<template>
    <SeoHead
        :title="searchQuery ? `Search: ${searchQuery}` : 'Search'"
        :description="
            searchQuery
                ? `Search results for “${searchQuery}” on VidGaze—videos, creators, streams, and more.`
                : 'Search VidGaze for videos, creators, categories, and live streams.'
        "
    />

    <div class="mx-auto max-w-[1680px] px-4 py-6 sm:px-6 lg:px-10 pb-12">
        <div
            v-if="loading && !finished"
            class="mb-8 flex flex-col sm:flex-row sm:items-center gap-4 rounded-xl border border-zinc-200/80 bg-zinc-50/90 px-4 py-4 sm:px-5 dark:border-zinc-800 dark:bg-zinc-900/60"
            role="status"
            aria-live="polite"
        >
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600 dark:bg-sky-950/80 dark:text-sky-400"
            >
                <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="h-5 w-5 animate-pulse" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-zinc-900 dark:text-zinc-100">
                    <template v-if="searchQuery">
                        Searching across platforms for
                        <span class="font-semibold text-sky-700 dark:text-sky-300">
                            "{{ searchQuery }}"
                        </span>
                    </template>
                    <template v-else>Searching across platforms</template>
                </p>
                <p class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-400">
                    Gathering channels, videos, and podcasts — this may take a few seconds.
                </p>
                <div
                    class="mt-3 h-1 w-full max-w-md overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800"
                >
                    <div class="vg-search-loading-bar h-full w-1/3 rounded-full bg-gradient-to-r from-sky-500/0 via-sky-500 to-sky-500/0" />
                </div>
            </div>
        </div>

        <button v-if="false" class="mb-2 ml-1 flex text-left text-base" @click="filters = !filters">
            <span class="my-auto ml-3 whitespace-nowrap text-sm font-bold uppercase text dark:textDark">
                Filters</span
            >
        </button>
        <div v-show="false" class="my-8 ml-1 mt-5 text-sm text dark:textDark">
            <div class="grid w-full grid-cols-3 gap-7 sm:flex sm:flex-row">
                <div class="flex flex-col gap-y-1">
                    <p class="w-24 text-xs font-bold uppercase">Platform</p>
                    <p>YouTube</p>
                    <p>Dailymotion</p>
                    <p>Rumble</p>
                    <p>Twitch</p>
                    <p>Odysee</p>
                    <p>Vimeo</p>
                </div>
                <div class="flex flex-col gap-y-1">
                    <p class="w-24 text-xs font-bold uppercase">Upload date</p>
                    <p>Last hour</p>
                    <p>Today</p>
                    <p>This week</p>
                    <p>This month</p>
                    <p>This year</p>
                </div>
                <div class="flex flex-col gap-y-1">
                    <p class="w-24 text-xs font-bold uppercase">Type</p>
                    <p>Video</p>
                    <p>Channel</p>
                    <p>Playlist</p>
                </div>
                <div class="flex flex-col gap-y-1">
                    <p class="w-24 text-xs font-bold uppercase">Duration</p>
                    <p>Under 4 minutes</p>
                    <p>4 - 20 minutes</p>
                    <p>Over 20 minutes</p>
                </div>
                <div class="flex flex-col gap-y-1">
                    <p class="w-24 text-xs font-bold uppercase">Sort by</p>
                    <p>Relevance</p>
                    <p>Upload date</p>
                    <p>View count</p>
                    <p>Rating</p>
                </div>
            </div>
        </div>

        <section class="mt-2 sm:mt-4 space-y-8">
            <div v-if="loading || creators.length > 0">
                <h2
                    class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400"
                >
                    <font-awesome-icon :icon="['fas', 'users']" class="h-3.5 w-3.5 opacity-80" />
                    Channels
                </h2>
                <div class="flex flex-col gap-4">
                    <template v-if="creators.length > 0">
                        <CreatorSearchCard
                            v-for="creator in creators.slice(0, visibleCreatorsCount)"
                            :key="creator.id ?? creator.slug"
                            :creator="creator"
                        />
                    </template>
                    <template v-else-if="loading">
                        <CreatorSearchSkeleton v-for="i in 2" :key="`cr-sk-${i}`" />
                    </template>
                </div>
            </div>

            <RowDivider
                v-if="creators.length > 2 && !loading"
                class="mt-2 mb-2"
                :text="expandCreators ? 'Show less' : 'Show more'"
                @click="toggleVisibleCreators"
            >
                <font-awesome-icon v-if="expandCreators" :icon="['fas', 'caret-up']" />
                <font-awesome-icon v-if="!expandCreators" :icon="['fas', 'caret-down']" />
            </RowDivider>
            <RowDivider
                v-else-if="
                    loading ||
                    (creators.length > 0 && creators.length < 3 && (videos.length > 0 || loading))
                "
                class="mt-2 mb-2"
            />

            <div v-if="loading || videos.length > 0">
                <h2
                    class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400"
                >
                    <font-awesome-icon :icon="['fas', 'play']" class="h-3.5 w-3.5 opacity-80" />
                    Videos &amp; streams
                </h2>
                <div class="relative grid w-full grid-cols-1 gap-5 sm:gap-6">
                    <template v-if="!loading && videos !== undefined && videos.length > 0">
                        <VideoStreamSearchCard
                            v-for="video in videos"
                            :key="video.id"
                            :item="video"
                        />
                    </template>
                    <template v-else-if="loading">
                        <VideoStreamSearchSkeleton v-for="i in 8" :key="`vs-sk-${i}`" />
                    </template>
                </div>
            </div>

            <div v-if="podcasts.length > 0" class="mt-2">
                <div class="mb-6 flex flex-row items-center gap-3">
                    <font-awesome-icon
                        :icon="['fas', 'headphones']"
                        class="my-auto h-5 w-5 shrink-0 text-sky-600 dark:text-sky-400 sm:h-6 sm:w-6"
                    />
                    <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-2xl">
                        Podcasts
                    </h2>
                </div>
                <div
                    class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6"
                >
                    <PodcastCard v-for="podcast in podcasts" :key="podcast.id" :podcast="podcast" />
                </div>
            </div>

            <div v-else-if="loading" class="mt-2">
                <div class="mb-6 flex flex-row items-center gap-3">
                    <font-awesome-icon
                        :icon="['fas', 'headphones']"
                        class="my-auto h-5 w-5 shrink-0 text-sky-600/70 dark:text-sky-400/80 sm:h-6 sm:w-6"
                    />
                    <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-2xl">
                        Podcasts
                    </h2>
                </div>
                <div
                    class="grid grid-cols-2 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6"
                >
                    <PodcastCardSkeleton v-for="i in 6" :key="`pod-sk-${i}`" />
                </div>
            </div>

            <div
                class="mt-20"
                v-if="
                    !loading &&
                    (videos === undefined || videos.length === 0) &&
                    creators.length === 0 &&
                    podcasts.length === 0 &&
                    streams.length === 0 &&
                    finished
                "
            >
                <ErrorMessage :message="'Whoops we couldn\'t find any content'" />
            </div>
        </section>
    </div>
</template>
