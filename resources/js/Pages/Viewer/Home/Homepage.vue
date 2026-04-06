

<script setup>
import axios from 'axios';
import {onMounted, onUnmounted, ref} from "vue";
import CreatorCarousel from "@/Pages/Viewer/Home/CreatorCarousel.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import {debounce} from "lodash";

import TopStreamsRow from "@/Components/ContentRows/TopStreamsRow.vue";
import TrendTopicChips from "@/Components/General/TrendTopicChips.vue";
import TopShortsRow from "@/Components/ContentRows/TopShortsRow.vue";

import VideosRow from "@/Components/ContentRows/VideosRow.vue";
import InfiniteVideos from "@/Components/ContentRows/InfiniteVideos.vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoGridSkeletonPlaceholders from "@/Components/ContentRows/VideoGridSkeletonPlaceholders.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import {useAuthStore} from "@/Stores/AuthStore";
import {usePinModalStore} from "@/Stores/PinModalStore";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const pinModalStore = usePinModalStore();
const contentRoutesStore = useContentRoutesStore();

const trending_videos = ref([]);
const videos = ref([]);
const shorts = ref([]);
const category = ref('');

const vimeoPinned = ref([]);
const rumblePinned = ref([]);
const vidgazePicks = ref([]);
const musicPinned = ref([]);
const musicDiscoverySubtitle = ref(null);
const cryptoPinned = ref([]);
const gamingPinned = ref([]);
const gamingDiscoverySubtitle = ref(null);
const alternatePinned = ref([]);

/** Anonymous / cross-session helper so category-feed API can rotate recent videos. */
const categoryFeedClientId =
    typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function'
        ? crypto.randomUUID()
        : null;

const selectedTrendKey = ref(null);
const selectedTrendLabel = ref('');
const trendVideos = ref([]);
const trendVideosLoading = ref(false);

const categorySlugs = {
    'music': 'music',
    'crypto': 'crypto_currency',
    'gaming': 'gaming',
    'alternate': 'alternate_news',
    'vidgaze': 'vidgaze_picks'
};

async function onTrendChange({key, label}) {
    selectedTrendKey.value = key;
    selectedTrendLabel.value = key ? (label || '') : '';

    if (!key) {
        trendVideos.value = [];
        trendVideosLoading.value = false;
        return;
    }

    trendVideosLoading.value = true;
    trendVideos.value = [];
    try {
        const res = await axios.get(route('api.video.trend-feed.videos'), {
            params: {key},
        });
        const data = res.data?.videos?.data ?? res.data?.videos ?? [];
        trendVideos.value = Array.isArray(data) ? data : [];
    } catch {
        trendVideos.value = [];
    } finally {
        trendVideosLoading.value = false;
    }
}

async function loadCategoryDiscoveryRow(slug) {
    try {
        const res = await axios.get(route('api.video.category-feed.videos'), {
            params: {
                category_slug: slug,
                limit: 12,
                ...(categoryFeedClientId ? { feed_client: categoryFeedClientId } : {}),
            },
        });
        const raw = res.data?.videos?.data ?? res.data?.videos ?? [];
        const list = Array.isArray(raw) ? raw : [];
        if (list.length === 0) {
            return { videos: null, label: null };
        }
        return { videos: list, label: res.data?.label ?? null };
    } catch {
        return { videos: null, label: null };
    }
}

onMounted(async () => {
    vidgazePicks.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.vidgaze);

    const musicDisc = await loadCategoryDiscoveryRow(categorySlugs.music);
    if (musicDisc.videos?.length) {
        musicPinned.value = musicDisc.videos;
        musicDiscoverySubtitle.value = musicDisc.label;
    } else {
        musicPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.music);
        musicDiscoverySubtitle.value = null;
    }

    cryptoPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.crypto);

    const gamingDisc = await loadCategoryDiscoveryRow(categorySlugs.gaming);
    if (gamingDisc.videos?.length) {
        gamingPinned.value = gamingDisc.videos;
        gamingDiscoverySubtitle.value = gamingDisc.label;
    } else {
        gamingPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.gaming);
        gamingDiscoverySubtitle.value = null;
    }

    alternatePinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.alternate);

    await debouncedFetchVideos();

    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    debouncedFetchVideos.cancel();
});

const handleScroll = () => {
    if (selectedTrendKey.value) {
        return;
    }
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;
    if (scrollPosition >= bodyHeight - 800) {
        debouncedFetchVideos();
    }
};

const debouncedFetchVideos = debounce(() => {
    if (selectedTrendKey.value) {
        return;
    }
    if (trending_videos.value) {
        fetchVideos([...trending_videos.value, ...videos.value]);
    } else {
        fetchVideos([...videos.value]);
    }
}, 500);

const fetchVideos = async (videoArray) => {
    if (selectedTrendKey.value) {
        return;
    }
    const videoIds = videoArray.map(video => video.id).join(',');
    const response = await contentRoutesStore.getVideos('trending', 40, videoIds)

    if (response === undefined) {
        window.removeEventListener('scroll', handleScroll);
    } else {
        videos.value = videos.value.concat(response);
    }
};

</script>
<template>
    <div class="min-h-screen">
        <Head :title="selectedTrendKey ? `Trend: ${selectedTrendLabel}` : 'Home'" />

        <div class="hidden md:block w-full border-b border-zinc-200/80 dark:border-zinc-800/80 bg-gradient-to-b from-zinc-50/90 to-transparent dark:from-zinc-900/50 dark:to-transparent">
            <div class="w-full max-w-[1680px] mx-auto px-4 py-4 sm:px-6 sm:py-5 lg:px-10">
                <CreatorCarousel />
            </div>
        </div>

        <ConsistentPadding class="md:-mt-1">

            <TrendTopicChips
                :active-key="selectedTrendKey"
                @trend-change="onTrendChange"
            />

            <section
                v-if="selectedTrendKey"
                class="mb-10"
            >
                <div class="flex flex-row flex-wrap items-center gap-3 mb-6">
                    <font-awesome-icon
                        :icon="['fas', 'arrow-trend-up']"
                        class="h-5 w-5 shrink-0 text-orange-600 dark:text-orange-400"
                    />
                    <h2 class="font-bold text-xl sm:text-2xl tracking-tight text-zinc-900 dark:text-white truncate max-w-[85vw]">
                        {{ selectedTrendLabel }}
                    </h2>
                </div>

                <div
                    v-if="trendVideosLoading"
                    class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-3 sm:gap-3.5"
                >
                    <VideoGridSkeletonPlaceholders
                        :blocks="1"
                        prefix="trend-sk"
                    />
                </div>
                <div
                    v-else-if="trendVideos.length > 0"
                    class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-3 sm:gap-3.5"
                >
                    <VideoStreamCard
                        v-for="v in trendVideos"
                        :key="v.id"
                        :item="v"
                    />
                </div>
                <p
                    v-else
                    class="text-sm text-zinc-600 dark:text-zinc-400"
                >
                    No videos were stored for this topic yet. Run <code class="text-xs">php artisan app:fetch-twitter-trends-search</code> after indexing content.
                </p>
            </section>

            <template v-if="!selectedTrendKey">
                <VideosRow :videos="vidgazePicks" title="VidGaze Picks">
                    <font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-5 w-5 sm:h-6 sm:w-6 text-sky-600 dark:text-sky-400 shrink-0"/>
                </VideosRow>

                <TopStreamsRow/>

                <VideosRow
                    :videos="musicPinned"
                    title="Top in Music"
                    :subtitle="musicDiscoverySubtitle"
                >
                    <font-awesome-icon :icon="['fas', 'music']" class="my-auto h-5 w-5 sm:h-6 sm:w-6 text-sky-600 dark:text-sky-400 shrink-0"/>
                </VideosRow>

                <VideosRow
                    :videos="gamingPinned"
                    title="Gaming"
                    :subtitle="gamingDiscoverySubtitle"
                    :showCategoryTag="false"
                >
                    <font-awesome-icon :icon="['fas', 'gamepad']" class="my-auto h-5 w-5 sm:h-6 sm:w-6 text-sky-600 dark:text-sky-400 shrink-0"/>
                </VideosRow>

                <VideosRow :videos="cryptoPinned" title="Crypto Currency" :showCategoryTag="false">
                    <font-awesome-icon :icon="['fas', 'coins']" class="my-auto h-5 w-5 sm:h-6 sm:w-6 text-sky-600 dark:text-sky-400 shrink-0"/>
                </VideosRow>

                <VideosRow :videos="alternatePinned" title="Alternate News" :showCategoryTag="false">
                    <font-awesome-icon :icon="['fas', 'newspaper']" class="my-auto h-5 w-5 sm:h-6 sm:w-6 text-sky-600 dark:text-sky-400 shrink-0"/>
                </VideosRow>

                <TopShortsRow v-if="useAuthStore().areShortsEnabled()"/>

                <InfiniteVideos :videos="videos" />
            </template>

        </ConsistentPadding>
    </div>
</template>
