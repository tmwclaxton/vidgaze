<script setup>
import {onMounted, ref, watch} from "vue";

import {usePlayerStore} from "@/Stores/PlayerStore";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useQueueStore} from "@/Stores/QueueStore";
import CornerInfo from "@/Components/Cards/VideoStreamCard/Partials/CornerInfo.vue";
import QueueItem from "@/Components/Modals/MiniPlayers/Partials/QueueItem.vue";
import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";
import {useToastStore} from "@/Stores/ToastStore";
import {debounce} from "lodash";
const confirmStore = useConfirmModalStore();
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


onMounted(() => {

 // using top and left position the initial position of the draggable div 15px fro mthe bottom right corner
    draggableDiv.value.style.left = (window.innerWidth - 384 - 15) + 'px';
    draggableDiv.value.style.top = (window.innerHeight - 348 - 15) + 'px';

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

            const newLeft = parseInt(draggableDiv.value.style.left) + deltaX;
            const newTop = parseInt(draggableDiv.value.style.top) + deltaY;

            const maxX = window.innerWidth - draggableDiv.value.offsetWidth - 15;
            const maxY = window.innerHeight - draggableDiv.value.offsetHeight - 15;
            const clampedLeft = Math.max(15, Math.min(newLeft, maxX));
            const clampedTop = Math.max(15, Math.min(newTop, maxY));


            draggableDiv.value.style.left = clampedLeft + 'px';
            draggableDiv.value.style.top = clampedTop + 'px';

            initialX = event.clientX;
            initialY = event.clientY;
        }
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });

    window.addEventListener('resize', () => {
        checkIfInViewport();

    });


});

const checkIfInViewport = debounce(() => {


    setTimeout(() => {
        console.log('checkIfInViewport');
        const rect = draggableDiv.value.getBoundingClientRect();
        if (!draggableDiv.value) return;
        const isInViewport =
            rect.top >= 15 &&
            rect.left >= 15 &&
            rect.bottom <= window.innerHeight - 15 &&
            rect.right <= window.innerWidth - 15;

        if (!isInViewport) {
            const maxX = window.innerWidth - draggableDiv.value.offsetWidth - 15;
            const maxY = window.innerHeight - draggableDiv.value.offsetHeight - 15;

            const clampedLeft = Math.max(15, Math.min(rect.left, maxX));
            const clampedTop = Math.max(15, Math.min(rect.top, maxY));

            draggableDiv.value.style.left = clampedLeft + 'px';
            draggableDiv.value.style.top = clampedTop + 'px';
        }
    }, 100);
}, 300);



const toggleExpandQueue = () => {
    expandQueue.value = !expandQueue.value;
    // wait for the animation to finish
    checkIfInViewport();
};


// watch for changes in the length of the queue
watch(() => queueStore.items.length, () => {
    if (queueStore.items.length > 1) {
        // wait for the animation to finish
        checkIfInViewport();
    }
});

//watch for changes in the index of the queue if so then scroll to the new index
watch(() => queueStore.index, (x, y) => {
    if (expandQueue.value && queueStore.items.length > 1 && playerStore.showMiniPlayer) {
        // get index of the current item then get element by id and scroll to it in the queueStoreHolder
        const externalId = 'queueItem_'+  queueStore.items[queueStore.index].object.external_id;
        const element = document.getElementById(externalId);
        // console.log(element);

        const miniPlayerItemsHolder = document.getElementById('miniPlayerItemsHolder');
        // console.log(miniPlayerItemsHolder);

        // scroll to the element so that it is in the middle of the queueStoreHolder
        miniPlayerItemsHolder.scrollTo({
            top: element.offsetTop - (miniPlayerItemsHolder.offsetHeight / 2),
            behavior: 'smooth'
        });
    }
});

const closeMiniPlayer = () => {
    // confirm that the user wants to close the mini player as it will destroy the queue
    confirmStore.buttonOneText = 'Cancel';
    confirmStore.buttonTwoText = 'Delete';
    confirmStore.title = 'Are you sure, this will delete the queue?';
    confirmStore.show = true;
    confirmStore.continue = () => {
        playerStore.destroyPlayers();
        queueStore.removeAll();
    };
};

</script>

<template>
    <div ref="draggableDiv"   class="z-40 fixed shadow shadow-md
     bg-white dark:bg-vidgaze-blue-dropdown rounded-xl overflow-hidden  w-96" v-bind:class="playerStore.showMiniPlayer ? 'flex flex-col' : 'hidden' ">
        <!--top part with creator & subscribe button-->
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
            <font-awesome-icon class="cursor-pointer my-auto px-5 h-5 aspect-square " :icon="['fas', 'times']" @click="closeMiniPlayer" />
        </div>

        <!--player-->
        <div class="flex justify-between   select-none">
                <div class="player w-full aspect-21/12 overflow-hidden">
                    <!--this is where the embed gets build inside-->
                    <div id="player_div_holder" class="w-full h-full bg-black"></div>
                </div>
        </div>

        <div v-if="queueStore.items[queueStore.index] !== undefined" class="flex flex-col gap-y-1 p-3">
            <div class="flex flex-row justify-between">
                <!--video title-->
                <div class="flex flex-col">
                    <p class="text-sm font-semibold text-left" v-text="queueStore.items[queueStore.index].object.title"></p>
                    <p class="text-xs font-normal text-left" >Queue · <span v-text="(queueStore.index) + 1 + ' / ' + queueStore.items.length"></span></p>
                </div>
                <!--expand queue button-->
                <div @click="toggleExpandQueue" class="my-auto mr-2">
                    <font-awesome-icon v-if="expandQueue" :icon="['fass', 'chevron-up']"/>
                    <font-awesome-icon v-if="!expandQueue" :icon="['fass', 'chevron-down']"/>
                </div>
            </div>
        </div>

        <div class="my-0.5 border border-zinc-200 dark:border-zinc-800" v-if="expandQueue"/>
        <div  id="miniPlayerItemsHolder" class="relative flex flex-col pb-1 max-h-48 overflow-y-auto" v-if="expandQueue">
            <div  v-for="(item, index) in queueStore.items"  >
                <QueueItem :item="item" :index="index" :key="index"/>
            </div>
        </div>

    </div>

</template>


