<script setup>
import ClockIcon from '#icons/clock_nofill.svg';
import ClockFillIcon from '#icons/clock.svg';
import FireIcon from '#icons/shorts.svg';
import DotsIcon from '#icons/3dots.svg';
import WatchLater from "@/Components/Cards/VideoStreamCards/Partials/WatchLater.vue";
// Import the contentModalStore module
import {useContentModalStore} from "@/Stores/ContentModalStore.js";
import {computed, ref} from "vue";
import Queue from "@/Components/Cards/VideoStreamCards/Partials/Queue.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import Viewers from "@/Components/Cards/VideoStreamCards/Partials/CornerInfo.vue";
import CornerInfo from "@/Components/Cards/VideoStreamCards/Partials/CornerInfo.vue";
import Badge from "@/Components/General/Badge.vue";
import {useAuthStore} from "@/Stores/AuthStore";

const contentModalStore = useContentModalStore();
const shareModalStore = useShareModalStore();
const playlistModalStore = usePlaylistModalStore();

const name = "VideoStreamCard";
const hideItem = ref(false);
//props below
const props = defineProps({
    item: Object,
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

    <div :id="'box_' + itemType + '_' + item.id" class="relative   ">
        <!--hide content hidden button and cover-->
        <div :id="'hide_' + itemType + '_' + item.id" @click="hideItemToggle" class="w-0 h-0 opacity-0 pointer-events-none " ></div>
        <div  v-if="hideItem" class="w-full h-32 rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex flex-col align-middle justify-center items-center ">
            <p class="text-md font-bold">Content Hidden</p>
            <div  @click="hideItemToggle()" class="text-blue-600 dark:text-blue-400 font-semibold cursor-pointer">
                Show
            </div>
        </div>
        <div  v-if="!hideItem" class="flex flex-row ">
            <div class="group relative w-48 lg:w-96 aspect-[21/12] h-max overflow-hidden rounded-md flex-shrink-0">
                <Link :href="route('watch.show', {slug: item.slug})" class="">
                    <img class="object-cover w-full h-full bg-zinc-900" v-bind:src="item.thumbnail_url"   alt=""/>
                </Link>

                <!--<Duration />-->

                <CornerInfo v-if="item.duration != null" :item="item" class="absolute bottom-0 right-0 m-1.5">
                    <p class="my-auto" v-text="item.duration"/>
                </CornerInfo>

                <CornerInfo v-if="item.viewers != null" :item="item" class="absolute bottom-0 right-0 m-1.5">
                    <p class="my-auto" v-text="item.viewers"/>
                </CornerInfo>

                <div  class="flex flex-col absolute top-0 right-0 m-1.5 space-y-1 items-end opacity-0  duration-500 delay-500 group-hover:opacity-100 transition-none group-hover:transition-opacity">
                    <WatchLater v-if="item.duration != null && useAuthStore().user != null" :item="item" />
                    <Queue  :item="item" :itemType="itemType" />
                </div>


            </div>
            <div class="pl-5 w-full" >
                <div class="flex flex-row">


                    <div class=" flex flex-col overflow-hidden  ">


                    <!-- title -->
                    <span
                        class="pt-1 line-clamp-2 overflow-hidden leading-5  font-bold text-md lg:text-2xl inline-flex">
                        <Link :href="route('watch.show', {slug: item.slug})" v-text="item.title" :title="item.title" class="pr-2 line-clamp-2 mb-0.5 break-all"></Link>
                    </span>

                        <div class=" flex flex-row pt-1">
                            <div class=" mt-1 flex-shrink-0">
                                <!--profile picture-->
                                <div v-if="!item.channel_page" class="flex-shrink-0 pr-2">
                                    <Link class="without-ring " :href="route('channel.show', {slug: item.creator.slug})">
                                        <img v-if="item.creator.avatar_url != null"
                                             class=" pointer-events-auto w-9 aspect-square rounded-full bg-zinc-800 "
                                             v-bind:src="item.creator.avatar_url">
                                    </Link>
                                </div>
                            </div>
                            <div class="my-auto">
                                <div class="  flex flex-col gap-y-1 ">

                                    <!--channel name-->
                                    <Link v-if="!item.channel_page" :href="route('channel.show', {slug: item.creator.slug})"
                                       class="w-max without-ring pointer-events-auto line-clamp-1 text-hover dark:text-hover-dark   ">
                                        <p class="text-sm sm:text-lg font-bold " v-text="item.creator.name"></p>
                                    </Link>

                                    <!--<div v-if="item.duration != null" class=" info-tag dark:info-tag-dark flex pb-0.5 ">-->
                                    <!--    &lt;!&ndash;<ClockIcon class="w-3 h-3 mr-1 my-auto dark:hidden"/>&ndash;&gt;-->
                                    <!--    <ClockFillIcon class="w-3 h-3 mr-1 my-auto hid den dark: flex"/>-->
                                    <!--    <p class="line-clamp-1 " v-text="(item.time_published)"/>-->
                                    <!--</div>-->

                                    <Badge v-if="item.preferred_source != null" :source="item.preferred_source" :text="item.preferred_source"/>



                                    <div v-if="item.live_viewer_count > 0" class="
                                        capitalize flex flex-row items-center  text-xs font-semibold
                                        text text-red-600 dark:text-red-400   ">
                                        <FireIcon class="w-3 h-3 my-auto mr-1 "/>
                                        <p class="line-clamp-1 " v-text="item.live_viewer_count + ' Watching'"></p>
                                    </div>

                                    <div class="hidden lg:flex">
                                        <p class="line-clamp-2 font-semibold text-sm mt-3 px-5 break-all" v-text="item.description"></p>
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
