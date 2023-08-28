<script setup>
import CornerInfo from "@/Components/Cards/VideoStreamCards/Partials/CornerInfo.vue";
import WatchLater from "@/Components/Cards/VideoStreamCards/Partials/WatchLater.vue";
import Queue from "@/Components/Cards/VideoStreamCards/Partials/Queue.vue";
import {useAuthStore} from "@/Stores/AuthStore";

const name = 'VideoStreamSuggestionCard';
const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

</script>

<template>
    <div class="relative flex   group z-0">
        <div class="flex-shrink-0  relative rounded overflow-hidden h-24 aspect-video  ">

            <Link :href="route('watch.show', {slug: item.slug})">
                <img class="object-cover w-full h-full bg-zinc-900 rounded-lg" v-bind:src="item.thumbnail_url" alt="thumbnail">
            </Link>

            <CornerInfo v-if="item.duration != null" :item="item" class="absolute bottom-0 right-0 m-1.5">
                <p class="my-auto" v-text="item.duration"/>
            </CornerInfo>

            <CornerInfo v-if="item.viewers != null" :item="item" class="absolute bottom-0 right-0 m-1.5">
                <p class="my-auto" v-text="item.viewers"/>
            </CornerInfo>

            <div  class="flex flex-col absolute top-0 right-0 m-1.5 space-y-1 items-end opacity-0  duration-500 delay-500 group-hover:opacity-100 transition-none group-hover:transition-opacity">
                <WatchLater v-if="item.duration != null && useAuthStore().user != null" :item="item" />
                <Queue  :item="item" :itemType="item.type" />
            </div>
        </div>

        <div class="w-full ">
                <div class="w-full flex align-bottom flex-col leading-none pl-2 ">
                    <Link :href="route('watch.show', {slug: item.slug})">
                        <p style="" class=" text-md font-bold leading-4 line-clamp-2 pr-2 break-words" v-text="item.title"/>
                    </Link>

                    <Link :href="route('channel.show', {slug: item.creator.slug})" class="text-xs  w-max">
                        <span v-text="item.creator.name"/>
                    </Link>

                </div>
        </div>

    </div>

</template>
