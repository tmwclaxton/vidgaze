<template>
    <div  ref="draggableDiv"  class="z-40 fixed shadow shadow-md    flex flex-col
     bg-white dark:bg-vidgaze-blue-dropdown rounded-xl overflow-hidden group ">

        <div class="overflow-hidden h-0 group-hover:h-16 duration-300 ease-in-out transition delay-75 ">

            <p class="text-lg my-auto font-semibold p-2 select-none">Jordan peterson video title</p>
        </div>

        <div class="flex justify-between   select-none">
                <div class="player h-52 aspect-21/12">
                    <YouTubeWrapper v-if="playerStore.type === 'YouTube'" />
                </div>
        </div>
    </div>
</template>

<script setup>
import YouTubeWrapper from "@/Components/EmbedWrappers/YouTubeWrapper.vue";
import {onMounted, ref} from "vue";

import {usePlayerStore} from "@/Stores/PlayerModalStore";
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


