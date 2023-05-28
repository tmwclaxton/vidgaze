<script setup>


import PlaylistIcon from '#icons/playlists.svg';
import TickIcon from '#icons/tick.svg';
import {ref} from "vue";
import {useToastStore} from "@/Stores/ToastStore.js";
import {useQueueStore} from "@/Stores/QueueStore.js";
import {usePage} from "@inertiajs/vue3";
import axios from 'axios';

const toastStore = useToastStore();
const queueStore = useQueueStore();

//props below
const props = defineProps({
    item: Object,
    itemType: String,
});
const name = 'QueueList';
const inQueue = ref(false);
const QueueOpen = ref(false);
const addToQueue = () => {

    if (inQueue.value) {
        if(queueStore.remove(props.item.id, props.itemType)) {
            inQueue.value = false;
        }
    } else {
        if (queueStore.add({object: props.item,type: props.itemType,}) ) {
            inQueue.value = true;
        }

    }

}

</script>
<template>
    <div  @mouseenter="QueueOpen = true" @mouseleave="QueueOpen = false" @click="addToQueue()" key="queue"
         class="h-7 w-max text-sm px-1.5 z-1 pointer-events-auto cursor-pointer flex flex-row gap-x-3
      group-hover:opacity-100 opacity-0 transition duration-300
       bg-zinc-900/90 rounded ">
        <div v-if="QueueOpen"
             class="   text-white text-xs font-bold text-center my-auto pl-1 ">
            <p  v-if="inQueue">Added to Queue</p>
            <p  v-if="!inQueue">Add to Queue</p>
        </div>
        <div class="  my-auto text-white font-semibold rounded-sm flex flex-col justify-center">
            <TickIcon v-if="inQueue" class="w-3.5 my-auto mx-auto"/>
            <PlaylistIcon v-if="!inQueue" class="w-4 my-auto mx-auto"/>
        </div>
    </div>

</template>


