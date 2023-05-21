
<script setup>
import HeartPodcastButton from "@/Components/Cards/PodcastCards/PocastCard/Partials/HeartPodcastButton.vue";

const name = 'PodcastCard';
const props = defineProps({
    podcast: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <div class="cursor-pointer select-none rounded   w-full  ">
        <div class=" relative  group">
            <a href="">
                <img class="w-full  block rounded "
                     v-bind:src="podcast.thumbnail_url"/>
            </a>
            <div class="absolute bg-black rounded pointer-events-none
        bg-opacity-0 group-hover:bg-opacity-40 group-hover:opacity-100
        w-full h-full top-0 flex items-center transition justify-evenly duration-300">

                <div class="pointer-events-auto flex flex-row gap-x-2 absolute bottom-3 left-3 ">
                    <div class="rounded-full bg-white w-10 h-10 flex opacity-75">
                        <font-awesome-icon :icon="['fas', 'play']"  class="pl-1 w-5 h-4 my-auto mx-auto text-black"/>
                    </div>
                </div>
                <HeartPodcastButton podcast="podcast" />

            </div>
        </div>
        <a href="">
            <div class="p-2 px-2">
                <h3 class="text dark:textDark font-bold text-md" v-html="podcast.title"></h3>
            </div>
        </a>
    </div>

</template>
