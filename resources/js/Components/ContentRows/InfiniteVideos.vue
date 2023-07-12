<template>
    <div class="flex flex-row gap-2  my-4 mb-8 ">
        <font-awesome-icon :icon="['fas', 'compass']" class="my-auto h-6" />
        <p class="font-bold text-2xl select-none">Explore</p>
    </div>


    <div class="mb-15 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <template v-for="(video, index) in videos" :key="video.id">
            <VideoStreamCard :item="video" />
        </template>
        <!--skeleton loading-->
        <template v-if="videos.length === 0" v-for="i in 6">
            <VideoStreamSkeleton />
        </template>
    </div>
</template>

<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";

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
