
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



const videos = ref([]);
const streams = ref([]);
const category = ref('trending');

onMounted(async () => {
    await fetchVideos();
    await fetchStreams();
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
};



</script>
<template>
        <Head title="Home" />

            <!--show 12 videos, hide videos if they don't fill the row completely-->
            <div class="flex-grow hidden md:flex">
                <CreatorCarousel />
            </div>

        <PaddingLayout>
            <p class="font-bold text-2xl -mt-4 mb-4">Trending Videos</p>

            <!--Show a row of popular videos-->
            <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <template v-for="(video, index) in videos" :key="video.id">
                    <VideoStreamCard :item="video" />
                </template>
                <!--skeleton loading-->
                <template v-if="videos.length === 0" v-for="i in 6">
                    <VideoStreamSkeleton />
                </template>
            </div>

            <p   class="font-bold text-2xl my-4 mb-4">Popular Streams</p>

            <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <template v-for="(stream, index) in streams" :key="stream.id">
                    <VideoStreamCard :item="stream" />
                </template>
                <!--skeleton loading-->
                <template v-if="streams.length === 0" v-for="i in 6">
                    <VideoStreamSkeleton />
                </template>
            </div>

            <!--show a line-->
            <!--show categories i.e podcasts streams and shorts-->
            <!--show a line-->

        </PaddingLayout>


</template>
