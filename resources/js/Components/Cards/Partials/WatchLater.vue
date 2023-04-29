<script setup>

import ClockIcon from '#icons/clock.svg';
import TickIcon from '#icons/tick.svg';
import {ref} from "vue";
import { useContentModalStore } from "@/Stores/ContentModalStore";
const contentModalStore = useContentModalStore();


//props below
const props = defineProps({
    video: Object,
    channel_page: Boolean,
});
const name = 'WatchLater';
const inPlaylist = ref(false);
const open = ref(false);

const toggleWatchLater = () => {
    if (contentModalStore.toggleWatchLater(props.video.id, inPlaylist.value)) {
        inPlaylist.value = !inPlaylist.value;
    }
}

// this is used when the modal is used to add a video to the watch later playlist instead
const inWatchLater = () => {
    inPlaylist.value = true;
}
const notInWatchLater = () => {
    inPlaylist.value = false;
}
</script>
<template>
    <div class="flex flex-row h-0 -mt-1">
        <div :id="'toggleInWatchLater_' + video.id" @click="inWatchLater" class="w-0 h-0 opacity-0 pointer-events-none " key="watch_later_simple"></div>
        <div :id="'toggleNotInWatchLater_' + video.id" @click="notInWatchLater" class="w-0 h-0 opacity-0 pointer-events-none  " key="watch_later_simple"></div>
    </div>
    <div  @mouseenter="open = true" @mouseleave="open = false" @click="toggleWatchLater()" key="watch_later"
         class="h-7 w-max text-sm px-1.5 z-1 pointer-events-auto cursor-pointer flex flex-row gap-x-3
      group-hover:opacity-100 opacity-0 transition duration-300
       bg-zinc-900/90 rounded ">
        <div v-if="open"
             class="   text-white text-xs font-bold text-center my-auto pl-1 ">
            <p  v-if="inPlaylist">Added to Watch Later</p>
            <p  v-if="!inPlaylist">Add to Watch Later</p>
        </div>
        <div class="  my-auto text-white font-semibold rounded-sm flex flex-col justify-center">
            <TickIcon v-if="inPlaylist" class="w-3.5 my-auto mx-auto"/>
            <ClockIcon v-if="!inPlaylist" class="w-4 my-auto mx-auto"/>
        </div>
    </div>
</template>


