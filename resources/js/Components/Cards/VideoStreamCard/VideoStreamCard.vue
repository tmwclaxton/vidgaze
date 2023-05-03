<script setup>
import EyeIcon from '#icons/eye.svg';
import ClockIcon from '#icons/clock_nofill.svg';
import ClockFillIcon from '#icons/clock.svg';
import FireIcon from '#icons/shorts.svg';
import DotsIcon from '#icons/3dots.svg';
import Duration from "@/Components/Cards/VideoStreamCard/Partials/Duration.vue";
import WatchLater from "@/Components/Cards/VideoStreamCard/Partials/WatchLater.vue";
// Import the contentModalStore module
import {useContentModalStore} from "@/Stores/ContentModalStore.js";
import {computed, onMounted, onUnmounted, ref} from "vue";
import Queue from "@/Components/Cards/VideoStreamCard/Partials/Queue.vue";
const contentModalStore = useContentModalStore();

const name = "VideoStreamCard";
const hideItem = ref(false);
//props below
const props = defineProps({
    item: Object,
    channel_page: Boolean,
});

// Define the setItemId method to call the setItemId method of contentModalStore with the provided id

function setContentModalValues() {
    contentModalStore.item = props.item;
    contentModalStore.itemType = "video";
    contentModalStore.setMenuShow(!contentModalStore.showMenu);
};

function hideItemToggle() {
    hideItem.value = !hideItem.value;
}

</script>

<template>
    <div :id="'box_'+ item.id" class="relative group overflow-hidden text dark:textDark min-h-64">
        <!--hide content hidden button and cover-->
        <div :id="'hide_' + item.id" @click="hideItemToggle" class="w-0 h-0 opacity-0 pointer-events-none " ></div>
        <div  v-if="hideItem" class="w-full h-full rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex flex-col align-middle justify-center items-center select-none">
            <p class="text-md font-bold">Content Hidden</p>
            <button  @click="hideItemToggle()" class="text-blue-600 dark:text-blue-400 font-semibold cursor-pointer">
                Show
            </button>
        </div>
        <div  v-if="!hideItem">
            <div class="relative aspect-[21/12] overflow-hidden rounded-md">
                <a v-bind:href="'/watch/' + item.slug">
                    <img class="object-cover w-full h-full bg-zinc-900" v-bind:src="item.thumbnail_url"   alt=""/>
                </a>
                <Duration v-if="item.duration != null" :item="item" class="absolute bottom-0 right-0 m-1.5"/>
                <div v-if="item.duration != null"  class="flex flex-col absolute top-0 right-0 m-1.5 space-y-1 items-end ">
                    <WatchLater v-if="$page.props.auth.user != null" :item="item" />
                    <Queue :item="item" />
                </div>


            </div>
            <div class="pl-1   py-2">
                <div class="flex flex-row">


                    <div class=" flex flex-col overflow-hidden  ">

                    <span
                        class="pt-1 line-clamp-2 overflow-hidden leading-5 font-bold  text-base  inline-flex">

                        <a v-bind:href="/watch/ + item.slug" v-text="item.title" class="pr-2"></a>


                    </span>

                        <div class=" flex flex-row pt-1">
                            <div class=" mt-1 flex-shrink-0">
                                <div v-if="!channel_page" class="flex-shrink-0 pr-2">
                                    <a class="without-ring " v-bind:href="'/channel/' + item.creator.slug">

                                        <img v-if="item.creator.avatar_url != null"
                                             class=" pointer-events-auto w-9 aspect-square rounded-full bg-zinc-800 "
                                             v-bind:src="item.creator.avatar_url">
                                    </a>
                                </div>
                            </div>
                            <div class="my-auto">
                                <div class="  space-y-0    text-xs font-normal">
                                    <a v-if="!channel_page" v-bind:href="/channel/ + item.creator.slug"
                                       class="w-max without-ring pointer-events-auto line-clamp-1 text-hover dark:text-hover-dark  ">

                                        <p class="text-sm font-medium " v-text="item.creator.name"></p>
                                    </a>

                                    <div v-if="item.duration != null" class=" info-tag dark:info-tag-dark inline-flex  ">
                                        <!--<ClockIcon class="w-3 h-3 mr-1 .5 my-auto dark:hidden"/>-->
                                        <!--<ClockFillIcon class="w-3 h-3 mr-1 .5 my-auto hidden dark:flex"/>-->
                                        <p class="line-clamp-1 mb-0.5" v-text="(item.time_published)"/>

                                    </div>
                                    <!--Video Source Info-->
                                    <!--<x-source-tag :source="video.preferred_source" class="inline-flex "/>-->

                                    <div v-if="item.live_viewer_count > 0" class="
                                        capitalize flex flex-row items-center  text-xs font-semibold
                                        text text-red-600 dark:text-red-400   ">
                                        <!--<FireIcon class="w-3 h-3 mr-1.5"/>-->
                                        <p class="line-clamp-1 " v-text="item.live_viewer_count + ' Watching'"></p>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!--3 dots button-->
                    <div :id="'dotsButton_' + item.id" class="col-span-1 ml-auto pt-2 w-8 without-ring h-max" >
                        <button @click="setContentModalValues()" class="flex without-ring m-0 mt-0 opacity-90 w-6 rounded-full text-zinc-500 ml-auto   pointer-events-auto">
                            <DotsIcon class="w-6 h-6"/>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>
