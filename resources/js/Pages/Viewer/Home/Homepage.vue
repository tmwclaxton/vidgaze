
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
import {computed, onMounted, onUnmounted, ref} from "vue";
import CreatorCarousel from "@/Pages/Viewer/Home/CreatorCarousel.vue";
import Skeleton from "@/Components/Cards/VideoStreamCard/VideoStreamSkeleton.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCard/VideoStreamSkeleton.vue";
import ShortsSkeleton from "@/Components/Cards/ShortsCard/ShortsSkeleton.vue";
import ShortsCard from "@/Components/Cards/ShortsCard/ShortsCard.vue";
import {debounce} from "lodash";

import StreamIcon from '~/images/icons/livestreams.svg';


const trending_videos = ref([]);
const videos = ref([]);
const streams = ref([]);
const shorts = ref([]);
const category = ref('');

onMounted(async () => {
    await fetchTrendingVideos();
    await fetchStreams();
    await fetchShorts();

    //wait , this gives time for trending_videos to be populated
    await new Promise(resolve => setTimeout(resolve, 1000));
    await debouncedFetchVideos(); // call it immediately on mount

    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    debouncedFetchVideos.cancel(); // cancel any pending debounced calls
});

const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;
    // console.log(scrollPosition, bodyHeight)

    // check if user has reached the bottom of the page
    if (scrollPosition >= bodyHeight - 100) {
        // console.log('bottom of page')
        debouncedFetchVideos(); // call the debounced version of fetchVideos
    }
};

// debounced version of fetchVideos that waits for 500ms before calling
const debouncedFetchVideos = debounce(async () => {
    await fetchVideos([...trending_videos.value, ...videos.value]);
}, 500);

const fetchTrendingVideos = async () => {
    axios.get(route('videos.infinite'),  { params: { category: 'trending', perPage: 6  } } )
        .then(response => {
            setTimeout(() => {
                trending_videos.value = response.data.data;
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};
const fetchVideos = async (videoArray) => {
    const videoIds = videoArray.map(video => video.id).join(',');
    axios.get(route('videos.infinite'),  {
        params: {
            category: 'popular',
            perPage: 40,
            videoIds
        } } )
        .then(response => {
            setTimeout(() => {
                videos.value = videos.value.concat(response.data.data);
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
            <div class="flex flex-row gap-2 mb-8 ">
                <font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-6"/>
                <p class="font-bold text-2xl select-none">Trending Videos</p>
            </div>

            <!--Show a row of popular videos-->
            <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <template v-for="(video, index) in trending_videos" :key="video.id">
                    <VideoStreamCard :item="video" />
                </template>
                <!--skeleton loading-->
                <template v-if="trending_videos.length === 0" v-for="i in 6">
                    <VideoStreamSkeleton />
                </template>
            </div>

            <hr class="my-8 border-2 border-zinc-100 dark:border-zinc-800" />

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

            <hr class="hidden md:flex my-8 border-2 border-zinc-100 dark:border-zinc-800" />


            <div class="hidden md:flex flex-row gap-2  my-4 mb-8 ">
                <font-awesome-icon :icon="['fas', 'fire']"  class="my-auto h-6"/>
                <p class="font-bold text-2xl select-none">Rising Shorts</p>
            </div>

            <div class="hidden md:grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8 gap-7 mx-10">
                <template  v-for="(short, index) in shorts" :key="short.id">
                    <ShortsCard :item="short" />
                </template>
                <!--skeleton loading-->
                <template v-if="shorts.length === 0" v-for="i in 8">
                    <ShortsSkeleton />
                </template>
            </div>


            <hr class="my-8 border-2 border-zinc-100 dark:border-zinc-800" />


            <div class="flex flex-row gap-2  my-4 mb-8 ">
                <font-awesome-icon :icon="['fas', 'compass']" class="my-auto h-6" />
                <p class="font-bold text-2xl select-none">Explore</p>
            </div>


            <div class="mb-15 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <template v-for="(video, index) in videos" :key="video.id">
                    <VideoStreamCard :item="video" />
                </template>
                <!--skeleton loading-->
                <template v-if="videos.length === 0" v-for="i in 6">
                    <VideoStreamSkeleton />
                </template>
            </div>
        </PaddingLayout>


</template>
