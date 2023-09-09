<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";
import {debounce} from "lodash";
import {onMounted, onUnmounted} from "vue";

const name = 'ChannelVideos';

const props = defineProps({
    videos: {
        type: Array
    }
});

const emits = defineEmits(['fetchVideos']);

// detect when user scrolls to bottom of page
const handleScroll = debounce(() => {
    let bottomOfWindow = document.documentElement.scrollTop + window.innerHeight === document.documentElement.offsetHeight;
    if (bottomOfWindow) {
        // console.log('bottom of page')
        emits('fetchVideos')
    }
}, 1000);

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});


</script>
<template>
    <div
         class="min-h-screen mb-32 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <template  v-if="videos.length > 0" v-for="(video, index) in videos" :key="video.id">
            <VideoStreamCard :item="video" :channel_page="true" />
        </template>
        <!--skeleton loading-->
        <template v-else v-for="i in 24">
            <VideoStreamSkeleton />
        </template>
    </div>
</template>
