<script setup>
import { onMounted, onUnmounted, ref } from "vue";
import { debounce } from "lodash";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import VideosRow from "@/Components/ContentRows/VideosRow.vue";
import { usePinModalStore } from "@/Stores/PinModalStore";

const pinModalStore = usePinModalStore();
const pinnedVideos = ref([]); // Holds all video data grouped by category
const currentIndex = ref(0); // Tracks which group of categories are currently being fetched
const categoriesPerBatch = 5; // Load 5 categories per request

// State to manage infinite scrolling
const isLoading = ref(false); // Prevents multiple fetches if a fetch is already in progress
const allCategoriesFetched = ref(false); // Flag to indicate all categories are loaded

// Debounced function to fetch the next batch of categories
const debouncedFetchNextCategories = debounce(async () => {
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
}, 500); // Prevent repeated firing with debounced behavior

// Handle scrolling
const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;

    // Check if user has scrolled near the bottom of the page
    if (scrollPosition >= bodyHeight - 500) {
        debouncedFetchNextCategories();
    }
};

onMounted(async () => {
    // Fetch all categories on mount
    await pinModalStore.getVideoCategories();

    // Prefetch the first batch of categories
    await debouncedFetchNextCategories();

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
                :title="item.category.name"
                :showCategoryTag="false"
            >
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
