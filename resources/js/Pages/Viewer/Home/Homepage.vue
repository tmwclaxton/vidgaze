
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
export default {
    components: {PaddingLayout},
    layout: AuthenticatedLayout,

};
</script>
<script setup>
import {onMounted, onUnmounted, ref} from "vue";
import CreatorCarousel from "@/Pages/Viewer/Home/CreatorCarousel.vue";
import {debounce} from "lodash";

import TopStreamsRow from "@/Components/ContentRows/TopStreamsRow.vue";
import TopShortsRow from "@/Components/ContentRows/TopShortsRow.vue";

import TrendingVideosRow from "@/Components/ContentRows/TrendingVideosRow.vue";
import InfiniteVideos from "@/Components/ContentRows/InfiniteVideos.vue";


const trending_videos = ref([]);
const videos = ref([]);
const shorts = ref([]);
const category = ref('');

onMounted(async () => {
    await fetchTrendingVideos();


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
const debouncedFetchVideos = debounce(() => {
    fetchVideos([...trending_videos.value, ...videos.value]);
}, 500);

const fetchTrendingVideos = async () => {
    axios.get(route('videos.infinite'),  { params: { category: 'trending', perPage: 6  } } )
        .then(response => {
            setTimeout(() => {
                trending_videos.value = response.data.data;
            }, 200); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};
const fetchVideos = async (videoArray) => {
    try {
        const videoIds = videoArray.map(video => video.id).join(',');
        const response = await axios.get(route('videos.infinite'), {
            params: {
                category: 'popular',
                perPage: 40,
                videoIds
            }
        });

        setTimeout(() => {
            if (!response.data.error) {
                videos.value = videos.value.concat(response.data.data);
            } else {
                console.log(response.data.error);
            }
        }, 200); // 200ms delay
    } catch (error) {
        console.log(error);
    }
};






</script>
<template>
        <Head title="Home" />

            <!--show 12 videos, hide videos if they don't fill the row completely-->
            <div class="flex-grow hidden md:flex">
                <CreatorCarousel />
            </div>

        <PaddingLayout class="-mt-4">

            <TrendingVideosRow :trending_videos="trending_videos"/>

            <TopStreamsRow/>

            <TopShortsRow/>

            <InfiniteVideos :videos="videos" />

        </PaddingLayout>

</template>
