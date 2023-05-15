<script setup>

import {onMounted, ref} from "vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCard/VideoStreamSkeleton.vue";

import StreamIcon from '~/images/icons/livestreams.svg';
const streams = ref([]);
const name = 'PopularStreams';
const fetchStreams = async () => {
    axios.get(route('streams.top'))
        .then(response => {
            setTimeout(() => {
                streams.value = response.data;
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
}

onMounted(() => {
    fetchStreams();
})
</script>


<template>
    <div class="flex flex-row gap-2  my-4 mb-8 ">
        <StreamIcon class="w-6 h-6 my-auto"/>
        <p class="font-bold text-2xl select-none">Popular Streams</p>
    </div>

    <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <template v-for="(stream, index) in streams" :key="stream.id">
            <VideoStreamCard :item="stream" />
        </template>
        <!--skeleton loading-->
        <template v-if="streams.length === 0" v-for="i in 6">
            <VideoStreamSkeleton />
        </template>
    </div>

    <hr class="hid den md: flex my-8 border-2 border-zinc-100 dark:border-zinc-800" />
</template>


<style scoped>

</style>
