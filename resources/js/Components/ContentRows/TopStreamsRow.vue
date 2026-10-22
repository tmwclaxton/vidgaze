<script setup>

import {onMounted, ref} from "vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";

import StreamIcon from '~/images/icons/livestreams.svg';
import RowDivider from "@/Components/General/RowDivider.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
const streams = ref(null); // null while loading
const name = 'PopularStreams';
const contentRoutesStore = useContentRoutesStore();
const fetchStreams = async () => {
    try {
        streams.value = (await contentRoutesStore.getStreams(6)) ?? [];
    } catch (e) {
        streams.value = [];
    }
}

onMounted(() => {
    fetchStreams();
})
</script>


<template>
    <div class="flex flex-row gap-2  my-4 mb-8 ">
        <StreamIcon class="w-6 h-6 my-auto"/>
        <h2 class="font-bold text-2xl ">Popular Streams</h2>
    </div>

    <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <template v-for="(stream, index) in (streams ?? [])" :key="stream.id">
            <VideoStreamCard :item="stream" />
        </template>
        <!--skeleton loading-->
        <template v-if="streams === null" v-for="i in 6">
            <VideoStreamSkeleton />
        </template>
        <div v-if="streams !== null && streams.length === 0" class="col-span-full flex flex-col items-center text-center gap-3 py-10">
            <img src="/images/mascot/Confused.png" alt="" class="h-28 w-auto object-contain" />
            <p class="text-xl font-bold">No live streams right now</p>
            <p class="text-base text-zinc-500 dark:text-zinc-400 max-w-md">Live streams from Twitch and other platforms will show here once they are connected. Check back soon, or explore videos in the meantime.</p>
            <Link :href="route('landing')" class="text-base font-semibold underline">Browse videos</Link>
        </div>
    </div>

    <RowDivider />
</template>


<style scoped>

</style>
