<script setup>

import {onMounted, ref} from "vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoGridSkeletonPlaceholders from "@/Components/ContentRows/VideoGridSkeletonPlaceholders.vue";

import StreamIcon from '~/images/icons/livestreams.svg';
import RowDivider from "@/Components/General/RowDivider.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
const streams = ref([]);
const name = 'PopularStreams';
const contentRoutesStore = useContentRoutesStore();
const fetchStreams = async () => {
    await contentRoutesStore.getStreams(6)
        .then(response => {
            streams.value = response;
        })
}

onMounted(() => {
    fetchStreams();
})
</script>


<template>
    <div class="flex flex-row flex-wrap items-center gap-3 my-6 mb-6 sm:my-8 sm:mb-7">
        <StreamIcon class="w-5 h-5 sm:w-6 sm:h-6 my-auto shrink-0 text-red-500 dark:text-red-400"/>
        <h2 class="font-bold text-xl sm:text-2xl tracking-tight text-zinc-900 dark:text-white">Live &amp; popular streams</h2>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-7 gap-3 sm:gap-3.5">
        <template v-for="(stream, index) in streams" :key="stream.id">
            <VideoStreamCard :item="stream" />
        </template>
        <VideoGridSkeletonPlaceholders
            v-if="streams.length === 0"
            :blocks="1"
            prefix="streams-sk"
        />
    </div>

    <RowDivider />
</template>


<style scoped>

</style>
