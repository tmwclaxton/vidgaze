
<script setup>
import PodcastCard from "@/Components/Cards/PodcastCards/PocastCard/PodcastCard.vue";
import PodcastSkeleton from "@/Components/Cards/PodcastCards/PocastCard/PodcastSkeleton.vue";

import { onMounted, onUnmounted, ref, computed } from "vue";
import { debounce } from "lodash";
const name = 'ExplorePodcasts';
const podcasts = ref([]);

onMounted(async () => {
    await fetchPodcasts();
    window.addEventListener('scroll', handleScrollPodcasts);
});
onUnmounted(() => {
    window.removeEventListener('scroll', handleScrollPodcasts);
    debouncedFetchPodcasts.cancel(); // cancel any pending debounced calls
});

const handleScrollPodcasts = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;

    // check if user has reached the bottom of the page
    if (scrollPosition >= bodyHeight - 400) {
        debouncedFetchPodcasts(); // call the debounced version of debouncedFetchPodcasts
    }
};

const fetchPodcasts = async () => {
    const podcastsIds = podcasts.value.map(podcast => podcast.id).join(',');
    axios
        .get(route('podcasts.infinite'), {
            params: {
                perPage: 24,
                podcastIds: podcastsIds
            }
        })
        .then(response => {
            setTimeout(() => {
                podcasts.value = podcasts.value.concat(response.data.data);
            }, 100); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};

const debouncedFetchPodcasts = debounce(() => {
    fetchPodcasts();
}, 500);

// Compute the number of skeletons to show based on the number of items
const skeletonCount = computed(() => {
    const itemCount = podcasts.value.length;
    const remainingItems = itemCount % 6;
    const skeletonsForFullRows = 12;
    return remainingItems === 0 ? skeletonsForFullRows : skeletonsForFullRows + (6 - remainingItems);
});
</script>
<template>
    <div class="flex flex-row gap-2 my-4 mb-8">
        <font-awesome-icon :icon="['fas', 'fire']" class="w-6 h-6 my-auto" />
        <!--<font-awesome-icon :icon="['fas', 'otter']"  class="w-6 h-6 my-auto"/>-->
        <p class="font-bold text-2xl select-none">Popular Podcasts</p>
    </div>

    <div
        ref="el"
        class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4"
    >
        <PodcastCard v-for="podcast in podcasts" :podcast="podcast" />
        <PodcastSkeleton v-for="i in skeletonCount" />
    </div>

    <!--skeleton loading-->
    <div v-if="podcasts.length === 0" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <PodcastSkeleton v-for="i in 24" />
    </div>
</template>
