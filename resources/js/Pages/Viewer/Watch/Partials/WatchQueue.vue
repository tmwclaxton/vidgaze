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
    if (props.ready && useQueueStore().items.length > 0) {
        if (props.item.external_id !== useQueueStore().currentItem.external_id) {
            useQueueStore().setIndexByExternalID(props.item.external_id)
        }
    }
});

</script>
<template>
    <consistent-content-holder v-if="useQueueStore().items.length > 0">
        <div >
            <div class="flex-col mb-1   border-b border-zinc-200 dark:border-zinc-700">
                <div class="p-2 generic-background   dark:bg-zinc-900">
                    <p class="font-bold text-2xl" v-text="useQueueStore.playlist ? useQueueStore.playlist.name : 'Queue'"></p>

                    <!--<p v-if="useQueueStore.playlist" class="font-bold text dark:textDark text-xs opacity-80 ">-->
                        <!--<a href="/channel/{{$playlist->owner->slug}}">-->
                        <!--    {{$playlist->owner->name}}-->
                        <!--</a>-->
                    <!--</p>-->
                </div>
            </div>

            <div id="miniPlayerItemsHolder" class="relative flex flex-col pb-1 max-h-48 overflow-y-auto">
                <div v-for="(item, index) in useQueueStore().items">
                    <QueueItem :item="item" :index="index" :key="index"/>
                </div>
            </div>
        </div>

    </consistent-content-holder>
</template>


