<template>
        <div ref="draggableDiv"   class="z-40 fixed shadow shadow-md bottom-0 right-5   flex-col
         bg-white dark:bg-vidgaze-blue-dropdown rounded-t-xl overflow-hidden group w-96" v-bind:class="playerStore.show ? 'flex' : 'hidden' ">

            <div v-if="queueStore.items[queueStore.index] !== undefined" class="overflow-hidden h-0 group-hover: h-full  group-hover: p-2 duration-300 ease-in-out transition delay-75 flex flex-row gap-x-2 ">
                <div class="flex-shrink-0 cursor-pointer h-12 my-auto aspect-square rounded-full bg-zinc-200 dark:bg-zinc-800">
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

            <!--video title-->
            <div v-if="queueStore.items[queueStore.index] !== undefined" class="flex flex-col gap-y-1 p-2">
                <p class="text-sm font-semibold text-left" v-text="queueStore.items[queueStore.index].object.title"></p>
                <div class="my-0.5 border border-zinc-300 dark:border-zinc-800"/>
            </div>

            <div class="flex flex-col  ">
                <div v-for="(item, index) in queueStore.items" @click="queueStore.changeIndex(index)" class="flex flex-row gap-x-2 p-1 cursor-pointer">
                    <div class=" mx-1 ml-3 my-auto flex h-3 aspect-square">
                        <font-awesome-icon
                            v-if="queueStore.index === index"
                            :icon="['fas', 'play']"
                            class=" h-full  my-auto"
                        />
                    </div>
                    <img class="h-12 aspect-[21/12] rounded-lg" :src="item.object.thumbnail_url">
                    <div class="flex-grow my-auto">
                        <p class="text-sm font-semibold text-left" v-text="item.object.title"></p>
                        <p class="text-xs text-left" v-text="item.object.creator.name"></p>
                    </div>
                </div>
            </div>

        </div>

</template>

<script setup>
import {onMounted, ref} from "vue";

import {usePlayerStore} from "@/Stores/PlayerModalStore";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useQueueStore} from "@/Stores/QueueStore";
import RowDivider from "@/Components/ContentRows/Partials/RowDivider.vue";
const playerStore = usePlayerStore();
const queueStore = useQueueStore();

const name = "VideoPlayerModal";

const draggableDiv = ref(null);
let offsetX = 0;
let offsetY = 0;
let isDragging = false;

onMounted(() => {

// Set the initial position of the draggable element
//     draggableDiv.value.style.right = '15px';
//     draggableDiv.value.style.bottom = '15px';
//
//     draggableDiv.value.addEventListener('mousedown', (event) => {
//         event.preventDefault();
//         offsetX = event.clientX - draggableDiv.value.getBoundingClientRect().left;
//         offsetY = event.clientY - draggableDiv.value.getBoundingClientRect().top + 0.5 * draggableDiv.value.offsetHeight;
//         isDragging = true;
//     });
//
//     document.addEventListener('mousemove', (event) => {
//         if (isDragging) {
//             event.preventDefault();
//
//             // Calculate the potential new position
//             const newRight = window.innerWidth - event.clientX - offsetX;
//             const newBottom = window.innerHeight - event.clientY - offsetY;
//
//             // Check if the new position exceeds window bounds
//             const maxX = window.innerWidth - draggableDiv.value.offsetWidth;
//             const maxY = window.innerHeight - draggableDiv.value.offsetHeight;
//             const clampedRight = Math.max(0, Math.min(newRight, maxX));
//             const clampedBottom = Math.max(0, Math.min(newBottom, maxY));
//
//             draggableDiv.value.style.right = clampedRight + 'px';
//             draggableDiv.value.style.bottom = clampedBottom + 'px';
//         }
//     });
//
//     document.addEventListener('mouseup', () => {
//         isDragging = false;
//     });


      //load the YouTube api
    const tag = document.createElement('script');
    tag.src = 'https://www.youtube.com/iframe_api';

    const firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);


});

</script>


