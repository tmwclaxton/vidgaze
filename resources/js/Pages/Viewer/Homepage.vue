<script setup>
import BoxVideoCard from "@/Components/Videos/BoxVideoCard.vue";
import {computed} from "vue";

//props below
const props = defineProps({
    videos: Object,
});



</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaddingLayout from "@/Layouts/PaddingLayout.vue";
export default {
    components: {PaddingLayout},
    layout: AuthenticatedLayout,

};
</script>

<template>
        <Head title="Home" />

        <PaddingLayout>
            <!--show 12 videos, hide videos if they don't fill the row completely-->
            <div :class="`grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4`">
                <template v-for="(video, index) in videos" :key="video.id">
                    <BoxVideoCard  :video="video" />
                </template>
            </div>



            <!--show a line-->
            <!--show categories i.e podcasts streams and shorts-->
            <!--show a line-->

        </PaddingLayout>


</template>
