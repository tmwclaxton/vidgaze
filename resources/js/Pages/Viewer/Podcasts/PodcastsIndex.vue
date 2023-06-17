<script setup>
import { Head } from '@inertiajs/vue3';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
import PodcastCreatorCard from "@/Components/Cards/PodcastCards/PocastCreatorCard/PodcastCreatorCard.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import PodcastCard from "@/Components/Cards/PodcastCards/PocastCard/PodcastCard.vue";
import PodcastCategoryCard from "@/Components/Cards/PodcastCards/PodcastCategoryCard/PodcastCategoryCard.vue";
import PodcastSkeleton from "@/Components/Cards/PodcastCards/PocastCard/PodcastSkeleton.vue";
import PodcastCreatorSkeleton from "@/Components/Cards/PodcastCards/PocastCreatorCard/PodcastCreatorSkeleton.vue";
import InfinitePodcasts from "@/Components/ContentRows/InfinitePodcasts.vue";
import TopPodcastersRow from "@/Components/ContentRows/TopPodcastersRow.vue";
</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout
};
</script>
<template>
    <Head title="VidGaze Podcasts" />


    <PaddingLayout class="-mt-4">


        <TopPodcastersRow />

        <InfinitePodcasts />


        <!--<RowDivider />-->


        <!--<div class="flex flex-row gap-2  my-4 mb-8 ">-->

        <!--    <font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-6"/>-->
        <!--    <p class="font-bold text-2xl select-none">Categories</p>-->
        <!--</div>-->

        <!--<div class=" grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">-->
        <!--    <template v-for="i in 6">-->
        <!--        <PodcastCategoryCard />-->

        <!--    </template>-->
        <!--</div>-->

        <!--<RowDivider />-->


        <!--<div class="flex flex-row gap-2  my-4 mb-8 ">-->
        <!--    <font-awesome-icon :icon="['fas', 'dice']"  class="w-6 h-6 my-auto" />-->
        <!--    <p class="font-bold text-2xl select-none">Something Different?</p>-->
        <!--</div>-->

        <!--<div class=" grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">-->
        <!--    <template v-for="i in 6">-->
        <!--        <PodcastCard />-->
        <!--    </template>-->
        <!--</div>-->


    </PaddingLayout>


</template>
