<template>
    <!-- Content will remain hidden until videos are loaded -->
    <template v-if="isLoaded">
        <div class="flex flex-row flex-wrap items-center gap-3 my-6 mb-6 sm:my-8 sm:mb-7">
            <slot></slot>
            <div
                v-if="title || subtitle"
                class="flex min-w-0 flex-col gap-0.5"
            >
                <h2
                    v-if="title"
                    class="font-bold text-xl sm:text-2xl tracking-tight text-zinc-900 dark:text-white"
                    v-text="title"
                />
                <p
                    v-if="subtitle"
                    class="text-sm font-medium text-zinc-500 dark:text-zinc-400 truncate max-w-[90vw] sm:max-w-2xl"
                    v-text="subtitle"
                />
            </div>
        </div>

        <!-- Show a row of popular videos -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-3 sm:gap-3.5">
            <template v-if="videos !== null && videos.length > 0" v-for="(video, index) in videos" :key="video.id">
                <VideoStreamCard v-if="video != undefined" :item="video" :channel_page="channel_page" :category_page="showCategoryTag" />
            </template>

            <!-- Skeletons: full rows at 2/3/4/6 cols + extra pair at 2xl for 7 cols -->
            <VideoGridSkeletonPlaceholders
                v-else
                :blocks="1"
                prefix="vrow-sk"
            />
        </div>

        <RowDivider v-if="rowDivider" />
    </template>
</template>

<script setup>
import { ref, watchEffect } from 'vue';
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoGridSkeletonPlaceholders from "@/Components/ContentRows/VideoGridSkeletonPlaceholders.vue";
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
        default: null,
    },
    subtitle: {
        type: String,
        required: false,
        default: null,
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
    waitTillLoaded: {
        type: Boolean,
        required: false,
        default: false,
    }
});

// Reactive property to track the loading state
const isLoaded = ref(false);

// Watch for changes in `videos`, and mark as loaded when videos are populated
watchEffect(() => {
    if (!props.waitTillLoaded) {
        isLoaded.value = true;
        return;
    }

    if (props.videos !== null && props.videos.length > 0) {
        isLoaded.value = true;
    }
});
</script>
