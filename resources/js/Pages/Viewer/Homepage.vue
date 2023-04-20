
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
            <!--<BoxVideoCard :channel_page="false" v-for="video in videos.data" :video="video" :key="video.id"></BoxVideoCard>-->
            <!--show 8 videos-->
            <!--show a line-->
            <!--show categories i.e podcasts streams and shorts-->
            <!--show a line-->

        </PaddingLayout>


</template>
