
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaddingLayout from "@/Layouts/PaddingLayout.vue";
export default {
    components: {PaddingLayout},
    layout: AuthenticatedLayout,

};
</script>
<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCard/VideoStreamCard.vue";
import {computed, onMounted, ref} from "vue";
import CreatorCarousel from "@/Pages/Viewer/Home/CreatorCarousel.vue";
import Skeleton from "@/Components/Cards/VideoStreamCard/VideoStreamSkeleton.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCard/VideoStreamSkeleton.vue";
import ShortsSkeleton from "@/Components/Cards/ShortsCard/ShortsSkeleton.vue";
import ShortsCard from "@/Components/Cards/ShortsCard/ShortsCard.vue";



const videos = ref([]);
const streams = ref([]);
const shorts = ref([]);
const category = ref('trending');

onMounted(async () => {
    await fetchVideos();
    await fetchStreams();
    await fetchShorts();
});

const fetchVideos = async () => {
    axios.get(route('videos.infinite'),  { params: { category: category.value, perPage: 6  } } )
        .then(response => {
            setTimeout(() => {
                videos.value = response.data.data;
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};

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

const fetchShorts = async () => {
    axios.get(route('videos.infinite'),  { params: { category: category.value, shorts: true, perPage: 8  } } )
        .then(response => {
            setTimeout(() => {
                shorts.value = response.data.data;
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};



</script>
<template>
        <Head title="Home" />

            <!--show 12 videos, hide videos if they don't fill the row completely-->
            <div class="flex-grow hidden md:flex">
                <CreatorCarousel />
            </div>

        <PaddingLayout>
            <div class="flex flex-row -mt-4 mb-4 ">
                <!--<font-awesome-icon class="my-auto h-6" :icon="['fas', 'play']" />-->
                <p class="font-bold text-2xl select-none">Trending Videos</p>
            </div>

            <!--Show a row of popular videos-->
            <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <template v-for="(video, index) in videos" :key="video.id">
                    <VideoStreamCard :item="video" />
                </template>
                <!--skeleton loading-->
                <template v-if="videos.length === 0" v-for="i in 6">
                    <VideoStreamSkeleton />
                </template>
            </div>

            <hr class="my-8 border-2 border-zinc-100 dark:border-zinc-800" />

            <p   class="font-bold text-2xl select-none my-4 mb-4">Popular Streams</p>

            <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <template v-for="(stream, index) in streams" :key="stream.id">
                    <VideoStreamCard :item="stream" />
                </template>
                <!--skeleton loading-->
                <template v-if="streams.length === 0" v-for="i in 6">
                    <VideoStreamSkeleton />
                </template>
            </div>

            <hr class="my-8 border-2 border-zinc-100 dark:border-zinc-800" />
            <p class="font-bold text-2xl select-none mt-4 mb-8">Rising Shorts</p>
            <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8 gap-7 mx-10">
                <template  v-for="(short, index) in shorts" :key="short.id">
                    <ShortsCard :item="short" />
                </template>
                <!--skeleton loading-->
                <template v-if="shorts.length === 0" v-for="i in 8">
                    <ShortsSkeleton />
                </template>
            </div>


            <hr class="my-8 border-2 border-zinc-100 dark:border-zinc-800" />
            <p class="font-bold text-2xl select-none mt-4 mb-8">Explore</p>

        </PaddingLayout>


</template>
