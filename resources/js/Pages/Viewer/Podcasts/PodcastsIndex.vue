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


    <!--<PaddingLayout class="-mt-4">-->



        <div class=" mx-auto   h-[calc(100vh-4rem)] py-auto justify-center flex flex-col">
            <div class="mx-10 my-auto flex-col  flex   dark:bg-transparent  ">

                <div class="flex flex-col space-y-2 items-center justify-center anim ate-pulse">
                    <div class="w-52 h-52 rounded-full p-8 bg-zinc-300 dark:bg-zinc-200 animate-bou nce">
                        <!--<font-awesome-icon :icon="['fas', 'wrench']" class="w-full h-full text-zinc-600 dark:text-zinc-400 animate-bounce"/>-->
                        <font-awesome-icon :icon="['fas', 'wrench']"  class="w-full h-full text-zinc-600 dark:text-vidgaze-blue"/>
                    </div>
                    <p class="  text-zinc-600 dark:textDark font-bold text-4xl pt-8 text-center">VidGaze Podcasts is Under Construction</p>
                    <!--<p class="text dark:textDark text-sm justify-center text-justify ">-->
                    <!--    VidGaze Music will be composed of ITunes, YouTube Music and SoundCloud and will be a feature of the next update!-->
                    <!--</p>-->

                </div>
            </div>

        </div>


        <!--<TopPodcastersRow />-->

        <!--<InfinitePodcasts />-->







        <!--<RowDivider />-->


        <!--<div class="flex flex-row gap-2  my-4 mb-8 ">-->

        <!--    <font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-6"/>-->
        <!--    <p class="font-bold text-2xl ">Categories</p>-->
        <!--</div>-->

        <!--<div class=" grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">-->
        <!--    <template v-for="i in 6">-->
        <!--        <PodcastCategoryCard />-->

        <!--    </template>-->
        <!--</div>-->

        <!--<RowDivider />-->


        <!--<div class="flex flex-row gap-2  my-4 mb-8 ">-->
        <!--    <font-awesome-icon :icon="['fas', 'dice']"  class="w-6 h-6 my-auto" />-->
        <!--    <p class="font-bold text-2xl ">Something Different?</p>-->
        <!--</div>-->

        <!--<div class=" grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">-->
        <!--    <template v-for="i in 6">-->
        <!--        <PodcastCard />-->
        <!--    </template>-->
        <!--</div>-->


    <!--</PaddingLayout>-->


</template>
