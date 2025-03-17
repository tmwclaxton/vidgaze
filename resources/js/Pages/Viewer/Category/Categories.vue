<script setup>
import { onMounted, onUnmounted, ref} from "vue";
import { debounce } from "lodash";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import VideosRow from "@/Components/ContentRows/VideosRow.vue";
import { usePinModalStore } from "@/Stores/PinModalStore";
import RumbleIcon from "#icons/rumble.svg";
import TwitchIcon from "#icons/twitch.svg";
import VimeoIcon from "#icons/vimeo.svg";
import DailymotionIcon from "#icons/dailymotion.svg";
import YouTubeIcon from "#icons/youtube.svg";
// import VidGazeIcon from "#icons/youtube.svg";

const pinModalStore = usePinModalStore();
const pinnedVideos = ref([]); // Holds all video data grouped by category
const currentIndex = ref(0); // Tracks which group of categories are currently being fetched
const categoriesPerBatch = 5; // Load 5 categories per request

// State to manage infinite scrolling
const isLoading = ref(false); // Prevents multiple fetches if a fetch is already in progress
const allCategoriesFetched = ref(false); // Flag to indicate all categories are loaded
const fetchNextCategories = async () => {
    if (isLoading.value || allCategoriesFetched.value) return;

    isLoading.value = true;

    try {
        // Get the next batch of 5 categories (or fewer if we're at the end)
        const nextBatch = pinModalStore.categories.data.slice(
            currentIndex.value,
            currentIndex.value + categoriesPerBatch
        );

        if (nextBatch.length === 0) {
            // No more categories to fetch
            allCategoriesFetched.value = true;
        } else {
            // Fetch pinned videos for each category in the batch
            const batchPromises = nextBatch.map(async (category) => {
                const videos = await pinModalStore.getPinnedVideos(6, 1, category.slug);
                return { category, videos };
            });

            // Await all batch fetch requests
            const results = await Promise.all(batchPromises);

            // if the category is VidGaze Picks, add it to the beginning of the pinnedVideos array and remove it from the results
            if (results[0].category.name === 'VidGaze Picks') {
                pinnedVideos.value.unshift(results[0]);
                results.shift();
            }

            // Add fetched videos to the pinnedVideos array
            pinnedVideos.value = pinnedVideos.value.concat(results);



            // Update index to skip the fetched batch
            currentIndex.value += categoriesPerBatch;
        }
    } catch (error) {
        console.error("Error fetching categories:", error);
    } finally {
        isLoading.value = false;
    }
} // Prevent repeated firing with debounced behavior


// Debounced function to fetch the next batch of categories
const debouncedFetchNextCategories = debounce(fetchNextCategories, 500);


// Handle scrolling
const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;

    // Check if user has scrolled near the bottom of the page
    if (scrollPosition >= bodyHeight - 500) {
        debouncedFetchNextCategories();
    }
};

const vimeoPinned = ref([]);
const rumblePinned = ref([]);
const youtubePinned = ref([]);

onMounted(async () => {
    // Fetch all categories on mount
    await pinModalStore.getVideoCategories();

    vimeoPinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'Vimeo');
    rumblePinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'Rumble');
    youtubePinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'YouTube');

    // push the pinned videos to the pinnedVideos array
    pinnedVideos.value.push({ category: { name: 'Vimeo', slug: null }, videos: vimeoPinned.value });
    pinnedVideos.value.push({ category: { name: 'Rumble', slug: null }, videos: rumblePinned.value });
    pinnedVideos.value.push({ category: { name: 'YouTube', slug: null }, videos: youtubePinned.value });

    // Prefetch the first batch of categories
    await fetchNextCategories();

    // Attach the scroll listener for infinite scrolling
    window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
    // Cleanup scroll listener and debounce handler on unmount
    window.removeEventListener("scroll", handleScroll);
    debouncedFetchNextCategories.cancel();
});


</script>

<template>
    <div>
        <Head title="Categories" />

        <ConsistentPadding class="-mt-4">
            <!-- Render each category and its videos -->
            <VideosRow
                v-for="item in pinnedVideos"
                :videos="item.videos"
                :key="item.category.id"
                :showCategoryTag="true"
                :wait-till-loaded="true"
            >
<!--                <VidGazeIcon v-if="item.category.name === 'VidGaze Picks'" class="w-full h-full"/>-->

                <Link v-if="item.category.slug !== null"
                    :href="route('category.show',{slug:item.category.slug})"
                      class="hover:underline text-3xl font-bold">
                    {{ item.category.name }}
                </Link>
                <div v-else class="flex flex-row gap-x-2">
                    <div class="flex w-8 items-center justify-center">
                        <YouTubeIcon v-if="item.category.name === 'YouTube'" class="w-full h-full"/>
                        <TwitchIcon v-if="item.category.name === 'Twitch'" class="w-full h-full"/>
                        <VimeoIcon v-if="item.category.name === 'Vimeo'" class="w-full h-full"/>
                        <DailymotionIcon v-if="item.category.name === 'Dailymotion'" class="w-full h-full"/>
                        <RumbleIcon src="/" v-if="item.category.name === 'Rumble'" class="w-full h-full"/>
                    </div>

                    <span class="text-3xl font-bold" v-text="item.category.name" />
                </div>
            </VideosRow>

            <!-- Show a loading spinner if fetching -->
            <div v-if="isLoading" class="text-center py-4">Loading...</div>

            <!-- Show a message when all categories are fetched -->
            <div v-if="allCategoriesFetched" class="text-center py-4 text-gray-600">
                All categories loaded.
            </div>
        </ConsistentPadding>
    </div>
</template>
