<script setup>
import ClockIcon from '#icons/clock_nofill.svg';
import ClockFillIcon from '#icons/clock.svg';
import FireIcon from '#icons/shorts.svg';
import DotsIcon from '#icons/3dots.svg';
import Duration from "@/Components/Cards/VideoStreamCard/Partials/Duration.vue";
import WatchLater from "@/Components/Cards/VideoStreamCard/Partials/WatchLater.vue";
// Import the contentModalStore module
import {useContentModalStore} from "@/Stores/ContentModalStore.js";
import {computed, ref} from "vue";
import Queue from "@/Components/Cards/VideoStreamCard/Partials/Queue.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";

const contentModalStore = useContentModalStore();
const shareModalStore = useShareModalStore();
const playlistModalStore = usePlaylistModalStore();

const name = "VideoStreamCard";
const hideItem = ref(false);
//props below
const props = defineProps({
    item: Object,
    channel_page: Boolean,
});

// Define the setItemId method to call the setItemId method of contentModalStore with the provided id
const itemType = computed(() => {
    if ( props.item['duration'] === undefined ) {
        return "stream";
    } else {
        return "video";
    }
});
async function setContentModalValues() {
    contentModalStore.item = props.item;
    contentModalStore.itemType = itemType.value;
    await new Promise(resolve => setTimeout(resolve, 100)); // wait for 100 milliseconds
    contentModalStore.setMenuShow(!contentModalStore.showMenu);
};


function hideItemToggle() {
    hideItem.value = !hideItem.value;
}
const dotsIconShow = computed(() => {
    return contentModalStore.item !== null && contentModalStore.item.id === props.item.id && contentModalStore.itemType === itemType.value && (contentModalStore.showMenu || playlistModalStore.showMenu || shareModalStore.showMenu);
});
</script>

<template>
    <div :id="'box_' + itemType + '_' + item.id" class="relative group   text dark:textDark min-h-64">
        <!--hide content hidden button and cover-->
        <div :id="'hide_' + itemType + '_' + item.id" @click="hideItemToggle" class="w-0 h-0 opacity-0 pointer-events-none " ></div>
        <div  v-if="hideItem" class="w-full h-full rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex flex-col align-middle justify-center items-center select-none">
            <p class="text-md font-bold">Content Hidden</p>
            <div  @click="hideItemToggle()" class="text-blue-600 dark:text-blue-400 font-semibold cursor-pointer">
                Show
            </div>
        </div>
        <div  v-if="!hideItem">
            <div class="relative aspect-[21/12] overflow-hidden rounded-md ">
                <a :href="route('watch.show', {slug: item.slug})">
                    <img class="object-cover w-full h-full bg-zinc-900" v-bind:src="item.thumbnail_url"   alt=""/>
                </a>

                <Duration v-if="item.duration != null" :item="item" class="absolute bottom-0 right-0 m-1.5"/>

                <div v-if="item.viewers != null" class="absolute bottom-0 right-0 m-1.5 py-auto px-2 flex flex-col align-middle text-white font-semibold text-sm bg-black opacity-75
                    rounded dark:text-zinc-200">

                    <p class="my-auto" v-text="item.viewers"/>
                </div>

                <div  class="flex flex-col absolute top-0 right-0 m-1.5 space-y-1 items-end opacity-0  duration-500 delay-500 group-hover:opacity-100 transition-none group-hover:transition-opacity">
                    <WatchLater v-if="item.duration != null && $page.props.auth.user != null" :item="item" />
                    <Queue v-if="item.duration != null" :item="item" />
                </div>


            </div>
            <div class="pl-1   py-2">
                <div class="flex flex-row">


                    <div class=" flex flex-col overflow-hidden  ">

                    <span
                        class="pt-1 line-clamp-2 overflow-hidden leading-5 font-bold  text-base  inline-flex">

                        <a :href="route('watch.show', {slug: item.slug})" v-text="item.title" class="pr-2"></a>


                    </span>

                        <div class=" flex flex-row pt-1">
                            <div class=" mt-1 flex-shrink-0">
                                <div v-if="!channel_page" class="flex-shrink-0 pr-2">
                                    <a class="without-ring " :href="route('channel.show', {creator: {slug: item.creator.slug}})">

                                        <img v-if="item.creator.avatar_url != null"
                                             class=" pointer-events-auto w-9 aspect-square rounded-full bg-zinc-800 "
                                             v-bind:src="item.creator.avatar_url">
                                    </a>
                                </div>
                            </div>
                            <div class="my-auto">
                                <div class="  space-y-0    text-xs font-normal">
                                    <a v-if="!channel_page" :href="route('channel.show', {creator: {slug: item.creator.slug}})"
                                       class="w-max without-ring pointer-events-auto line-clamp-1 text-hover dark:text-hover-dark  ">

                                        <p class="text-sm font-medium " v-text="item.creator.name"></p>
                                    </a>

                                    <div v-if="item.duration != null" class=" info-tag dark:info-tag-dark inline-flex pb-0.5 ">
                                        <ClockIcon class="w-3 h-3 mr-1 my-auto dark:hidden"/>
                                        <ClockFillIcon class="w-3 h-3 mr-1 my-auto hidden dark:flex"/>
                                        <p class="line-clamp-1 " v-text="(item.time_published)"/>

                                    </div>
                                    <a  v-if="item.category != null" :href="route('category.show',{slug:item.category.slug})" class=" info-tag dark:info-tag-dark inline-flex mb-0.5 ">
                                        <!--<ClockIcon class="w-3 h-3 mr-1 .5 my-auto dark:hidden"/>-->
                                        <!--<ClockFillIcon class="w-3 h-3 mr-1 .5 my-auto hidden dark:flex"/>-->
                                        <font-awesome-icon class="my-auto mr-1" :icon="['fas', 'gamepad']" />
                                        <p class="line-clamp-1 font-semibold" v-text="(item.category.name)"/>

                                    </a>

                                    <div v-if="item.live_viewer_count > 0" class="
                                        capitalize flex flex-row items-center  text-xs font-semibold
                                        text text-red-600 dark:text-red-400   ">
                                        <FireIcon class="w-3 h-3 my-auto mr-1 "/>
                                        <p class="line-clamp-1 " v-text="item.live_viewer_count + ' Watching'"></p>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!--3 dots button-->
                    <div :id="'dotsButton_' + itemType + '_' + item.id" class="col-span-1 ml-auto pt-2 w-8 without-ring h-max" >
                        <button @click="setContentModalValues()" class="flex without-ring m-0 mt-0 opacity-90 w-6 rounded-full text-zinc-500 ml-auto   pointer-events-auto">
                            <DotsIcon class="w-6 h-6 opacity-0  duration-500 delay-500 group-hover:opacity-100 transition-none group-hover:transition-opacity" :class="{ 'opacity-100': dotsIconShow}" />
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>
