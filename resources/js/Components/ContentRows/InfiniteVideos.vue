<template>
    <div class="flex flex-row items-center gap-3 my-6 mb-6 sm:my-8 sm:mb-7">
        <font-awesome-icon :icon="['fas', 'compass']" class="my-auto h-5 w-5 sm:h-6 sm:w-6 text-sky-600 dark:text-sky-400 shrink-0" />
        <h2 class="font-bold text-xl sm:text-2xl tracking-tight text-zinc-900 dark:text-white">Explore</h2>
    </div>


    <div class="mb-15 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-3 sm:gap-3.5">
        <template v-for="(video, index) in videos" :key="video.id">
            <VideoStreamCard :item="video" />
        </template>
        <VideoGridSkeletonPlaceholders
            v-if="videos.length === 0"
            :blocks="2"
            prefix="explore-sk"
        />
    </div>
</template>

<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoGridSkeletonPlaceholders from "@/Components/ContentRows/VideoGridSkeletonPlaceholders.vue";

const name = 'ExploreVideosRow';
const props = defineProps({
    videos: {
        type: Array,
        required: true
    }
});
</script>

<style scoped>

</style>
