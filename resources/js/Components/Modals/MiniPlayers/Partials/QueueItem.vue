<template>
    <div class="relative group flex flex-row gap-x-2 p-1 cursor-pointer ">
        <div class=" mx-0.5 ml-2 my-auto flex h-3 aspect-square" @click="queueStore.changeIndex(index)">
            <font-awesome-icon
                v-if="queueStore.index === props.index"
                :icon="['fas', 'play']"
                class=" h-full  my-auto"
            />
        </div>
        <div class="relative h-12 aspect-21/12" @click="queueStore.changeIndex(index)">
            <img class="absolute w-full h-full rounded-lg" :src="props.item.object.thumbnail_url">
            <CornerInfo v-if="props.item.object.duration != null" :item="props.item.object" class="absolute bottom-0 right-0 m-1">
                <p class="my-auto text-xs" v-text="props.item.object.duration"/>
            </CornerInfo>

            <CornerInfo v-if="props.item.object.viewers != null" :item="props.item.object" class="absolute bottom-0 right-0 m-1 ">
                <p class="my-auto text-xs" v-text="'Live'"/>
            </CornerInfo>
        </div>
        <div class="flex-grow my-auto">
            <p class="text-sm font-semibold text-left" v-text="props.item.object.title"></p>
            <p class="text-xs text-left" v-text="props.item.object.creator.name "></p>
        </div>
        <font-awesome-icon @click="queueStore.remove(props.item.object.id, props.item.type)" :icon="['fas', 'trash-can']"  class="cursor-pointer my-auto mx-5 h-4 aspect-square text-red-500 hidden group-hover:flex" />
    </div>
</template>

<script setup>
import { useQueueStore} from "@/Stores/QueueStore";
const queueStore = useQueueStore();
import { usePlayerStore} from "@/Stores/PlayerStore";
import CornerInfo from "@/Components/Cards/VideoStreamCard/Partials/CornerInfo.vue";
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


</script>
