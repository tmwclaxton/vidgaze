
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaddingLayout from "@/Layouts/PaddingLayout.vue";
export default {
    components: {PaddingLayout},
    layout: AuthenticatedLayout,

};
</script>
<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCard.vue";
import {computed} from "vue";
import CreatorCarousel from "@/Pages/Viewer/Home/CreatorCarousel.vue";

//props below
const props = defineProps({
    videos: Object,
});



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
            <div :class="`grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4`">
                <template v-for="(video, index) in videos" :key="video.id">
                    <VideoStreamCard  :video="video" />
                </template>
            </div>

            <p class="font-bold text-2xl my-4 mb-4">Popular Streams</p>

            <div class="h-96 mb-96">

            </div>


            <!--show a line-->
            <!--show categories i.e podcasts streams and shorts-->
            <!--show a line-->

        </PaddingLayout>


</template>
