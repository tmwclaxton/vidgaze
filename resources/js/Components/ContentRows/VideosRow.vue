<template>
    <!-- Content will remain hidden until videos are loaded -->
    <template v-if="isLoaded">
        <div class="flex flex-row gap-2 my-4 mb-8">
            <slot></slot>
            <p class="font-bold text-2xl" v-text="title"></p>
        </div>

        <!-- Show a row of popular videos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <template v-if="videos !== null && videos.length > 0" v-for="(video, index) in videos" :key="video.id">
                <VideoStreamCard v-if="video != undefined" :item="video" :channel_page="channel_page" :category_page="showCategoryTag" />
            </template>

            <!-- Skeleton loading -->
            <template v-else v-for="i in 6">
                <VideoStreamSkeleton />
            </template>
        </div>

        <RowDivider v-if="rowDivider" />
    </template>
</template>

<script setup>
import { ref, watchEffect } from 'vue';
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";
import RowDivider from "@/Components/General/RowDivider.vue";

const name = 'VideosRow';

const props = defineProps({
    videos: {
        type: Array,
        required: false,
        default: null,
    },
    channel_page: {
        type: Boolean,
        required: false,
        default: false,
    },
    title: {
        type: String,
        required: false,
        default: 'Videos',
    },
    rowDivider: {
        type: Boolean,
        required: false,
        default: true,
    },
    showCategoryTag: {
        type: Boolean,
        required: false,
        default: false,
    },
});

// Reactive property to track the loading state
const isLoaded = ref(false);

// Watch for changes in `videos`, and mark as loaded when videos are populated
watchEffect(() => {
    if (props.videos !== null && props.videos.length > 0) {
        isLoaded.value = true;
    }
});
</script>
