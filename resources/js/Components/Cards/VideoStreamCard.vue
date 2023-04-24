<script setup>
import EyeIcon from '#icons/eye.svg';
import ClockIcon from '#icons/clock_nofill.svg';
import ClockFillIcon from '#icons/clock.svg';
import FireIcon from '#icons/shorts.svg';
import DotsIcon from '#icons/3dots.svg';
import Duration from "@/Components/Cards/Partials/Duration.vue";
import WatchLater from "@/Components/Cards/Partials/WatchLater.vue";
//props below
defineProps({
    video: Object,
    channel_page: Boolean,
});
// Import the itemStore module
import itemStore from "@/Stores/itemSelect.js";

// Define the setItemId method to call the setItemId method of itemStore with the provided id
const setItemId = (id) => {
    itemStore.setItemId(id);
};
</script>
<script>
export default {
    name: "BoxVideo",
}

</script>

<template>
    <div id="box_{{ video.id}}" class="relative group overflow-hidden  text dark:textDark">


        <div class="relative aspect-[21/12] overflow-hidden rounded-md">
            <a v-bind:href="'/watch/' + video.slug">
                <img class="object-cover w-full h-full bg-zinc-900" v-bind:src="video.thumbnail_url"   alt=""/>
            </a>
            <Duration v-if="video.duration != null" :video="video" class="absolute bottom-0 right-0 m-1.5"/>
            <WatchLater :video="video" class="absolute top-0 right-0 m-1.5"/>


        </div>
        <div class="pl-1   py-2">
            <div class="flex flex-row">


                <div class=" flex flex-col overflow-hidden  ">

                <span
                    class="pt-1 line-clamp-2 overflow-hidden leading-5 font-bold  text-base  inline-flex">

                    <a v-bind:href="/watch/ + video.slug" v-text="video.title" class="pr-2"></a>


                </span>

                    <div class=" flex flex-row pt-1">
                        <div class=" mt-1 flex-shrink-0">
                            <div v-if="!channel_page" class="flex-shrink-0 pr-2">
                                <a class="without-ring " v-bind:href="'/channel/' + video.creator.slug">

                                    <img v-if="video.creator.avatar_url != null"
                                         class=" pointer-events-auto w-9 aspect-square rounded-full bg-zinc-800 "
                                         v-bind:src="video.creator.avatar_url">
                                </a>
                            </div>
                        </div>
                        <div class="my-auto">
                            <div class="  space-y-0    text-xs font-normal">
                                <a v-if="!channel_page" v-bind:href="/channel/ + video.creator.slug"
                                   class="w-max without-ring pointer-events-auto line-clamp-1 text-hover dark:text-hover-dark  ">

                                    <p class="text-sm font-medium " v-text="video.creator.name"></p>
                                </a>

                                <div class=" info-tag dark:info-tag-dark inline-flex  ">
                                    <!--<ClockIcon class="w-3 h-3 mr-1 .5 my-auto dark:hidden"/>-->
                                    <!--<ClockFillIcon class="w-3 h-3 mr-1 .5 my-auto hidden dark:flex"/>-->
                                    <p class="line-clamp-1 mb-0.5" v-text="(video.time_published)"/>

                                </div>
                                <!--Video Source Info-->
                                <!--<x-source-tag :source="video.preferred_source" class="inline-flex "/>-->

                                <div v-if="video.live_viewer_count > 0" class="
                                    capitalize flex flex-row items-center  text-xs font-bold
                                    text text-red-600 dark:text-red-400   ">
                                    <!--<FireIcon class="w-3 h-3 mr-1.5"/>-->
                                    <p class="line-clamp-1 " v-text="video.live_viewer_count + ' Watching'"></p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!--3 dots button-->
                <div class="col-span-1 ml-auto pt-2 w-8 without-ring" >
                    <button @click="setItemId(video.id)" class="flex without-ring m-0 mt-0 opacity-90 w-6 rounded-full text-zinc-500 ml-auto   pointer-events-auto">
                        <DotsIcon class="w-6 h-6"/>
                    </button>
                </div>

            </div>
        </div>


    </div>
</template>



<style scoped>

</style>
