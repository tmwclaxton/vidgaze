<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";

const name = 'ChannelHome';

const props = defineProps({
    videos: {
        type: Array
    }
});

</script>
<template>
    <div class="mb-15 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <template  v-if="videos && videos.length > 0" v-for="(video, index) in videos" :key="video.id">
            <VideoStreamCard :item="video" />
        </template>
        <!--skeleton loading-->
        <template v-if="!videos" v-for="i in 24">
            <VideoStreamSkeleton />
        </template>
    </div>
</template>
