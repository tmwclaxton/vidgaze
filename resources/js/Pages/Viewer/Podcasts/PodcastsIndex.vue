<script setup>
import { Head } from '@inertiajs/vue3';
import PaddingLayout from "@/Layouts/PaddingLayout.vue";
import PodcastCreatorCard from "@/Components/Cards/PodcastCards/PocastCreatorCard/PodcastCreatorCard.vue";
import RowDivider from "@/Components/ContentRows/Partials/RowDivider.vue";
import PodcastCard from "@/Components/Cards/PodcastCards/PocastCard/PodcastCard.vue";
import PodcastCategoryCard from "@/Components/Cards/PodcastCards/PodcastCategoryCard/PodcastCategoryCard.vue";
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


        <div class="flex flex-row gap-2  my-4 mb-8 ">
            <font-awesome-icon :icon="['fass', 'microphone']" class="w-6 h-6 my-auto" />
            <p class="font-bold text-2xl select-none">VidGaze Podcasters</p>
        </div>

        <div class=" grid grid-cols-3 sm:grid-cols-3 md:grid-cols-3 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <template v-for="i in 6">
                <PodcastCreatorCard />
            </template>
        </div>

        <RowDivider />

        <div class="flex flex-row gap-2  my-4 mb-8 ">
            <font-awesome-icon :icon="['fas', 'fire']" class="w-6 h-6 my-auto" />
            <!--<font-awesome-icon :icon="['fas', 'otter']"  class="w-6 h-6 my-auto"/>-->
            <p class="font-bold text-2xl select-none">Popular Podcasts</p>
        </div>

        <div class=" grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <template v-for="i in 6">
                <PodcastCard />
            </template>
        </div>

        <RowDivider />


        <div class="flex flex-row gap-2  my-4 mb-8 ">

            <font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-6"/>
            <p class="font-bold text-2xl select-none">Categories</p>
        </div>

        <div class=" grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <template v-for="i in 6">
                <PodcastCategoryCard />

            </template>
        </div>

        <RowDivider />


        <div class="flex flex-row gap-2  my-4 mb-8 ">
            <font-awesome-icon :icon="['fas', 'dice']"  class="w-6 h-6 my-auto" />
            <p class="font-bold text-2xl select-none">Something Different?</p>
        </div>

        <div class=" grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <template v-for="i in 6">
                <PodcastCard />
            </template>
        </div>


    </PaddingLayout>


</template>
