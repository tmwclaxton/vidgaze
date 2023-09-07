<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";
import {computed, onMounted, ref} from "vue";
import VideosRow from "@/Components/ContentRows/VideosRow.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import {shuffle} from "lodash";

const name = 'ChannelHome';

const props = defineProps({
    videos: {
        type: Array,
        required: false,
    },
    channel: {
        type: Object,
        required: true
    }
});

const popularVideos = ref([]);

const getPopularVideos = async () => {
    popularVideos.value = await useContentRoutesStore().getVideos('popular', 6, null, false, false, props.channel.id);
}

onMounted(() => {
    getPopularVideos();
});

const popularVideosComputed = computed(() => {
    // if not enough popular videos, fill missing with props.videos shuffled
    if (popularVideos.value.length < 6) {
        if ( props.videos.length > 6) {
            const shuffledVideos = shuffle(props.videos)
            const missingVideos = 6 - popularVideos.value.length;
            for (let i = 0; i < missingVideos; i++) {
                popularVideos.value.push(shuffledVideos[i]);
            }
        }
    }
    return popularVideos.value;
});

const slicedVideos = computed(() => {
    if (props.videos.length === 0) {
        return props.videos;
    }
    // return an array of the first 6 videos but if there are less than 6 videos, return exactly what we have
    return props.videos.length > 6 ? props.videos.slice(0, 6) : props.videos.slice(0, props.videos.length);
});
</script>
<template>
    <!--first 6 videos-->
    <VideosRow :videos="slicedVideos"
               title="Latest Videos">
        <!--<font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-6"/>-->
    </VideosRow>
    <!--popular channel videos-->
    <VideosRow :videos="popularVideosComputed" :rowDivider="false"
               title="Popular Videos">
        <!--<font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-6"/>-->
    </VideosRow>
</template>
