<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import { useContentRoutesStore } from "@/Stores/ContentRoutesStore";
import { usePinModalStore } from "@/Stores/PinModalStore";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import { debounce } from "lodash";
import ErrorMessage from "@/Components/Errors/ErrorMessage.vue";
import VideosRow from "@/Components/ContentRows/VideosRow.vue";

const loaded = ref(false);
const currentTab = ref("streams"); // Tracks whether "streams" or "videos" is active
const category = ref(null);
const streams = ref([]);
const videos = ref([]);
const hasStreams = ref(false);
const hasVideos = ref(false);

const props = defineProps({
    slug: {
        type: String,
        required: true,
    },
});

// Fetch streams during infinite scrolling
const getStreams = async () => {
    if (streams.value.length % 12 === 0) {
        await useContentRoutesStore()
            .getStreams(12, category.value.id, streams.value.length)
            .then((response) => {
                streams.value = streams.value.concat(response);
                if (response.length > 0) hasStreams.value = true;
            });
    }
};

const debouncedGetStreams = debounce(getStreams, 500);

// Fetch videos during mount
const getVideos = async () => {
    let videoIDs = videos.value.map((video) => video.id);
    // convert videoIDs to string
    videoIDs = videoIDs.join(",");
    const videosResponse = await useContentRoutesStore().getCategoryVideos(category.value.slug, 36, videoIDs);

    // concat videos to existing videos but make sure there are no duplicates
    videos.value = videos.value.concat(videosResponse.filter((video) => !videoIDs.includes(video.id)));

    if (videosResponse.length > 0) hasVideos.value = true;
};

const debouncedGetVideos = debounce(getVideos, 500);

// Fetch initial data
onMounted(async () => {
    await useContentRoutesStore()
        .getCategory(props.slug)
        .then((response) => {
            category.value = response;
        })
        .then(async () => {
            // Fetch both streams and videos
            await Promise.all([getStreams(), getVideos()]);
            loaded.value = true;

            // Attach listener for infinite scrolling (only for streams)
            window.addEventListener("scroll", handleScroll);
        });
});

const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;
    // Check if user has reached the bottom of the page
    if (scrollPosition >= bodyHeight - 200) {
        debouncedGetStreams();
        debouncedGetVideos();

    }
};

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
    <ConsistentPadding>
        <div v-if="category !== null" class="flex flex-col">
            <!-- Category Header -->
            <div class="flex flex-row">
                <img v-if="category.thumbnail_url" class="h-48 shadow rounded" v-bind:src="category.thumbnail_url" />
                <div class="flex flex-col ml-3">
                    <span class="font-bold text-4xl mb-4" v-text="category.name" />
                    <!-- Category Tags -->
                    <div class="flex flex-row flex-wrap gap-2">
                        <div
                            v-for="tag in category.tags"
                            class="cursor-pointer px-3 p-1 rounded-full text-xs font-bold bg-zinc-200 dark:bg-zinc-700"
                            :key="tag"
                        >
                            <p v-text="tag" />
                        </div>
                    </div>
                    <div class="text-sm">
                        <p id="description" v-text="category.description" />
                    </div>
                </div>
            </div>

            <row-divider />

            <!-- Tabs for switching (only when both videos and streams are available) -->
            <div v-if="hasStreams && hasVideos" class="flex justify-center space-x-4 mb-4 border-b border-gray-300">
                <button
                    class="py-2 px-4"
                    :class="currentTab === 'streams' ? 'border-b-2 border-blue-500 font-bold' : ''"
                    @click="currentTab = 'streams'"
                >
                    Streams
                </button>
                <button
                    class="py-2 px-4"
                    :class="currentTab === 'videos' ? 'border-b-2 border-blue-500 font-bold' : ''"
                    @click="currentTab = 'videos'"
                >
                    Videos
                </button>
            </div>

            <!-- Videos Section -->
            <div v-if="loaded && currentTab === 'videos' && hasVideos">
                <div class="px-5 pb-10">
<!--                    <VideosRow :videos="videos" :title="`Videos from ${category.name}`" :showCategoryTag="false" />-->
                    <VideoStreamCard v-for="video in videos" :key="video.id" :item="video" :category_page="true" />

                </div>

                <div v-if="loaded && videos.length === 0">
                    <ErrorMessage :message="'Whoops, we couldn\'t find any videos'" />
                </div>
            </div>

            <!-- Streams Section -->
            <div v-if="loaded && currentTab === 'streams' && hasStreams">
                <div class="px-5 pb-10">
                    <div class="px-5 pb-10">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                            <VideoStreamCard v-for="video in videos" :key="video.id" :item="video" :category_page="true" />
                        </div>
                    </div>
                </div>

                <div v-if="loaded && streams.length === 0">
                    <ErrorMessage :message="'Whoops, we couldn\'t find any streams'" />
                </div>
            </div>

            <!-- Only Streams Available -->
            <div v-if="loaded && !hasVideos && hasStreams">
                <div class="px-5 pb-10">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <VideoStreamCard v-for="stream in streams" :key="stream.id" :item="stream" :category_page="true" />
                    </div>
                </div>
            </div>

            <!-- Only Videos Available -->
            <div v-if="loaded && !hasStreams && hasVideos">
                <div class="px-5 pb-10">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <VideoStreamCard v-for="video in videos" :key="video.id" :item="video" :category_page="true" />
                    </div>
                </div>
            </div>

            <!-- No Data Available -->
            <div v-if="loaded && !hasStreams && !hasVideos">
                <ErrorMessage :message="'Whoops, we couldn\'t find any content for this category.'" />
            </div>
        </div>
    </ConsistentPadding>
</template>
