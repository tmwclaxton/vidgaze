<template>
    <div v-if="playerStore.show" ref="draggableDiv"  class="z-40 fixed shadow shadow-md    flex flex-col
     bg-white dark:bg-vidgaze-blue-dropdown rounded-xl overflow-hidden group ">
        <YouTubeWrapper v-if="playerStore.object.preferred_source === 'YouTube'" />

        <div v-if="playerStore.object.creator !== undefined" class="overflow-hidden h-0 group-hover: h-full duration-300 ease-in-out transition delay-75 flex flex-row group-hover: p-2 gap-x-2 ">
            <div class="flex-shrink-0 cursor-pointer h-12 my-auto aspect-square rounded-full bg-zinc-200 dark:bg-zinc-800">
                <img class="w-full h-full rounded-full" v-bind:src="playerStore.object.creator.avatar_url">
            </div>
            <div class="  cursor-pointer flex-grow  rounded-full px-2 -mt-0.5 ">
                <div class="flex flex-col   h-full">
                    <div class="flex flex-col ">
                        <p class="font-bold text-lg text-left " v-text="playerStore.object.creator.name"></p>
                        <SubscribeButton :channel="playerStore.object.creator" />
                    </div>
                </div>
            </div>
            <!--exit button-->
            <font-awesome-icon class="cursor-pointer my-auto px-5 h-5 aspect-square " :icon="['fas', 'times']" @click="playerStore.show = false" />
        </div>

        <div class="flex justify-between   select-none">
                <div class="player h-52 aspect-21/12">
                    <div id="player_div_holder" class="w-full h-full"></div>
                </div>
        </div>
    </div>
</template>

<script setup>
import YouTubeWrapper from "@/Components/EmbedWrappers/YouTubeWrapper.vue";
import {onMounted, ref} from "vue";

import {usePlayerStore} from "@/Stores/PlayerModalStore";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
const playerStore = usePlayerStore();


const name = "VideoPlayerModal";

const draggableDiv = ref(null);
let offsetX = 0;
let offsetY = 0;
let isDragging = false;

onMounted(() => {

    //set the initial position of the draggable element
    draggableDiv.value.style.right = '15px';
    draggableDiv.value.style.bottom = '15px';

    draggableDiv.value.addEventListener('mousedown', (event) => {
        event.preventDefault();
        offsetX = event.clientX - draggableDiv.value.getBoundingClientRect().left;
        offsetY = event.clientY - draggableDiv.value.getBoundingClientRect().top + 0.5 * draggableDiv.value.offsetHeight
        isDragging = true;
    });

    document.addEventListener('mousemove', (event) => {
        if (isDragging) {
            event.preventDefault();
            draggableDiv.value.style.right = (window.innerWidth - event.clientX - offsetX) + 'px';
            draggableDiv.value.style.bottom = (window.innerHeight - event.clientY - offsetY) + 'px';
        }
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });




});

</script>


