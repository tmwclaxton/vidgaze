<script setup>
import {onMounted, ref} from "vue";

import {usePlayerStore} from "@/Stores/PlayerStore";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useQueueStore} from "@/Stores/QueueStore";
import CornerInfo from "@/Components/Cards/VideoStreamCard/Partials/CornerInfo.vue";
const playerStore = usePlayerStore();
const queueStore = useQueueStore();
const expandQueue = ref(false);
const name = "VideoPlayerModal";

const draggableDiv = ref(null);
let offsetX = 0;
let offsetY = 0;
let isDragging = false;

let initialX = 0;
let initialY = 0;


const loadScript = (src, id) => {
    if (!document.getElementById(id)) {
        const tag = document.createElement('script');
        tag.src = src;
        tag.id = id;
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }
};

onMounted(() => {

    draggableDiv.value.style.right = '15px';
    draggableDiv.value.style.bottom = '15px';

    draggableDiv.value.addEventListener('mousedown', (event) => {
        event.preventDefault();
        initialX = event.clientX;
        initialY = event.clientY;
        isDragging = true;
    });

    document.addEventListener('mousemove', (event) => {
        if (isDragging) {
            event.preventDefault();

            const deltaX = event.clientX - initialX;
            const deltaY = event.clientY - initialY;

            const newRight = parseInt(draggableDiv.value.style.right) - deltaX;
            const newBottom = parseInt(draggableDiv.value.style.bottom) - deltaY;

            const maxX = window.innerWidth - draggableDiv.value.offsetWidth;
            const maxY = window.innerHeight - draggableDiv.value.offsetHeight;

            const clampedRight = Math.max(0, Math.min(newRight, maxX));
            const clampedBottom = Math.max(0, Math.min(newBottom, maxY));

            draggableDiv.value.style.right = clampedRight + 'px';
            draggableDiv.value.style.bottom = clampedBottom + 'px';

            initialX = event.clientX;
            initialY = event.clientY;
        }
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });

    window.addEventListener('resize', () => {
        const maxX = window.innerWidth - draggableDiv.value.offsetWidth;
        const maxY = window.innerHeight - draggableDiv.value.offsetHeight;

        const clampedRight = Math.max(0, Math.min(parseInt(draggableDiv.value.style.right), maxX));
        const clampedBottom = Math.max(0, Math.min(parseInt(draggableDiv.value.style.bottom), maxY));

        draggableDiv.value.style.right = clampedRight + 'px';
        draggableDiv.value.style.bottom = clampedBottom + 'px';
    });



    loadScript('https://www.youtube.com/iframe_api', 'youtube-api');
    loadScript('https://player.vimeo.com/api/player.js', 'vimeo-api');
    loadScript('https://player.twitch.tv/js/embed/v1.js', 'twitch-api');
    // loadScript('https://api.dmcdn.net/all.js', 'dailymotion-api')

    // Dailymotion works by loading the script specifically for each individual video
});


</script>

<template>
    <div ref="draggableDiv"   class="z-50 fixed shadow shadow-md bottom-5 right-5
     bg-white dark:bg-vidgaze-blue-dropdown rounded-xl overflow-hidden group w-96" v-bind:class="playerStore.showMiniPlayer ? 'flex flex-col' : 'hidden' ">

        <div v-if="queueStore.items[queueStore.index] !== undefined" class="overflow-hidden h-0 group-hover: h-full  group-hover: p-2 duration-300 ease-in-out transition delay-75 flex flex-row gap-x-2 ">
            <div class="flex-shrink-0 cursor-pointer h-12 my-auto aspect-square rounded-full bg-zinc-200 dark:bg-zinc-800 relative">
                <img class="w-full h-full rounded-full" v-bind:src="queueStore.items[queueStore.index].object.creator.avatar_url">
            </div>
            <div class=" flex-grow  rounded-full px-2 -mt-0.5 ">
                <div class="flex flex-col   h-full">
                    <div class="flex flex-col ">
                        <p class="font-bold text-lg text-left " v-text="queueStore.items[queueStore.index].object.creator.name"></p>
                        <SubscribeButton :channel="queueStore.items[queueStore.index].object.creator" :key="[queueStore.index]"/>
                    </div>
                </div>
            </div>
            <!--exit button-->
            <font-awesome-icon class="cursor-pointer my-auto px-5 h-5 aspect-square " :icon="['fas', 'times']" @click="playerStore.show = false" />
        </div>

        <div class="flex justify-between   select-none">
                <div class="player w-full aspect-21/12">
                    <!--this is where the embed gets build inside-->
                    <div id="player_div_holder" class="w-full h-full bg-black"></div>
                </div>
        </div>

        <div v-if="queueStore.items[queueStore.index] !== undefined" class="flex flex-col gap-y-1 p-3">
            <div class="flex flex-row justify-between">
                <!--video title-->
                <div class="flex flex-col">
                    <p class="text-sm font-semibold text-left" v-text="queueStore.items[queueStore.index].object.title"></p>
                    <p class="text-xs font-normal text-left" >Queue · <span v-text="queueStore.index+1 + ' / ' + queueStore.items.length"></span></p>
                </div>
                <!--expand queue button-->
                <div @click="expandQueue = !expandQueue" class="my-auto mr-2">
                    <font-awesome-icon v-if="expandQueue" :icon="['fass', 'chevron-up']"/>
                    <font-awesome-icon v-if="!expandQueue" :icon="['fass', 'chevron-down']"/>
                </div>
            </div>
        </div>

        <div class="my-0.5 border border-zinc-200 dark:border-zinc-800" v-if="expandQueue"/>
        <div class="flex flex-col pb-1 max-h-32 overflow-y-auto" v-if="expandQueue">
            <div v-for="(item, index) in queueStore.items" @click="queueStore.changeIndex(index)" class="flex flex-row gap-x-2 p-1 cursor-pointer ">
                <div class=" mx-0.5 ml-2 my-auto flex h-3 aspect-square">
                    <font-awesome-icon
                        v-if="queueStore.index === index"
                        :icon="['fas', 'play']"
                        class=" h-full  my-auto"
                    />
                </div>
                <div class="relative h-12 aspect-21/12">
                    <img class="absolute w-full h-full rounded-lg" :src="item.object.thumbnail_url">
                    <CornerInfo v-if="item.object.duration != null" :item="item.object" class="absolute bottom-0 right-0 m-1">
                        <p class="my-auto text-xs" v-text="item.object.duration"/>
                    </CornerInfo>

                    <CornerInfo v-if="item.object.viewers != null" :item="item.object" class="absolute bottom-0 right-0 m-1 ">
                        <p class="my-auto text-xs" v-text="'Live'"/>
                    </CornerInfo>
                </div>
                <div class="flex-grow my-auto">
                    <p class="text-sm font-semibold text-left" v-text="item.object.title"></p>
                    <p class="text-xs text-left" v-text="item.object.creator.name "></p>
                </div>
            </div>
        </div>

    </div>

</template>


