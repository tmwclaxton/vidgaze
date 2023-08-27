<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {useQueueStore} from "@/Stores/QueueStore";
import QueueItem from "@/Components/Modals/MiniPlayers/Partials/QueueItem.vue";
import {onMounted, watch} from "vue";

const name = 'WatchQueue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    ready: {
        type: Boolean,
        required: true
    }
});

watch(() => props.ready, () => {
    // this code basically handles if someone is watching 1 video on the mini player, and then clicks on another video
    if (props.ready && useQueueStore().items.length > 0) {
        if (props.item.external_id !== useQueueStore().currentItem.external_id) {
            useQueueStore().setIndexByExternalID(props.item.external_id)
        }
    }
    // this code scrolls to the current item in the queueStoreHolder
    if (useQueueStore().items.length > 1) {
        // get index of the current item then get element by id and scroll to it in the queueStoreHolder
        const externalId = 'queueItem_'+  useQueueStore().items[useQueueStore().index].external_id;
        const element = document.getElementById(externalId);

        const miniPlayerItemsHolder = document.getElementById('miniPlayerItemsHolder');

        // scroll to the element so that it is in the middle of the queueStoreHolder
        miniPlayerItemsHolder.scrollTo({
            top: element.offsetTop - (miniPlayerItemsHolder.offsetHeight / 2),
            behavior: 'smooth'
        });
    }

});



</script>
<template>
    <consistent-content-holder v-if="useQueueStore().items.length > 0">
        <div >
            <div class="flex-col mb-1   border-b border-zinc-200 dark:border-zinc-700">
                <div class="p-2 generic-background   dark:bg-zinc-900">

                    <Link v-if="useQueueStore().playlist" :href="route('playlist.show', {slug: useQueueStore().playlist.slug})">
                        <p class="font-bold text-2xl" v-text="useQueueStore().playlist.name"></p>
                    </Link>
                    <p v-else class="font-bold text-2xl" v-text="'Queue'"></p>

                    <p v-if="useQueueStore().playlist" class="font-bold text-xs opacity-80 inline-flex flex-row ">
                        <Link v-text="useQueueStore().playlist.creator.name" :href="route('channel.show', {slug: useQueueStore().playlist.creator.slug})"></Link>
                        <span v-text=" ' · ' + useQueueStore().positionText" class="ml-1"/>
                    </p>
                </div>
            </div>

            <div id="miniPlayerItemsHolder" class="relative flex flex-col pb-1 max-h-96 overflow-y-auto">
                <div v-for="(item, index) in useQueueStore().items">
                    <QueueItem :item="item" :index="index" :key="index"/>
                </div>
            </div>
        </div>

    </consistent-content-holder>
</template>


