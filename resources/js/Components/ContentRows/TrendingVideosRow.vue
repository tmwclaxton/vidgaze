<template>
    <div class="flex flex-row gap-2  my-4 mb-8 ">
        <font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-6"/>
        <p class="font-bold text-2xl select-none">Trending Videos</p>
    </div>

    <!--Show a row of popular videos-->
    <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <template v-for="(video, index) in trending_videos" :key="video.id">
            <VideoStreamCard :item="video" />
        </template>
        <!--skeleton loading-->
        <template v-if="trending_videos === null || trending_videos.length === 0" v-for="i in 6">
            <VideoStreamSkeleton />
        </template>
    </div>


    <RowDivider />
</template>

<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";
import RowDivider from "@/Components/General/RowDivider.vue";

const name = 'TrendingVideosRow';
const props = defineProps({
    trending_videos: {
        type: Array,
        required: false,
        default: null
    }
});
</script>

<style scoped>

</style>
