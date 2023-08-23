<template>
    <div :id="'queueItem_' + props.item.external_id" class="relative group flex flex-row justify-between gap-x-2  cursor-pointer ">
        <div class="relative group flex flex-row gap-x-2 p-1  cursor-pointer " @click="queueAction">
            <div class=" mx-0.5 ml-2 my-auto flex h-3 aspect-square" >
                <font-awesome-icon
                    v-if="queueStore.index === props.index"
                    :icon="['fas', 'play']"
                    class=" h-full  my-auto"
                />
            </div>
            <div class="relative h-12 aspect-21/12" >
                <img class="absolute w-full h-full rounded-lg" :src="props.item.thumbnail_url">
                <CornerInfo v-if="props.item.duration != null" :item="props.item.object" class="absolute bottom-0 right-0 m-1">
                    <p class="my-auto text-xs" v-text="props.item.duration"/>
                </CornerInfo>

                <CornerInfo v-if="props.item.viewers != null" :item="props.item.object" class="absolute bottom-0 right-0 m-1 ">
                    <p class="my-auto text-xs" v-text="'Live'"/>
                </CornerInfo>
            </div>
            <div class="flex-grow my-auto">
                <p class="text-sm font-semibold text-left line-clamp-2" v-text="props.item.title"></p>
                <p class="text-xs text-left line-clamp-1" v-text="props.item.creator.name "></p>
            </div>
        </div>
        <div class="px-5 flex flex-row flex-shrink-0">
            <font-awesome-icon @click="queueStore.remove(props.item.external_id)" :icon="['fas', 'trash-can']"  class="cursor-pointer my-auto w-full aspect-square aspect-square text-red-500 hidden group-hover:flex" />
        </div>
    </div>
</template>

<script setup>
import { useQueueStore} from "@/Stores/QueueStore";
const queueStore = useQueueStore();
import { usePlayerStore} from "@/Stores/PlayerStore";
import CornerInfo from "@/Components/Cards/VideoStreamCards/Partials/CornerInfo.vue";
import {router} from "@inertiajs/vue3";
const playerStore = usePlayerStore();

//props
const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    index: {
        type: Number,
        required: true
    }
});

const name = 'QueueItem';

const queueAction = () => {
    if (route().current('watch.show')) {
        // route('watch.show', {slug: item.slug})
        queueStore.index = props.index
        router.visit(route('watch.show', {slug: props.item.slug}))

    } else {
        queueStore.changeIndex(props.index)
    }
};
</script>
