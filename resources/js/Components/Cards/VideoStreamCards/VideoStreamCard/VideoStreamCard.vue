<script setup>
import ClockIcon from '#icons/clock_nofill.svg';
import ClockFillIcon from '#icons/clock.svg';
import FireIcon from '#icons/shorts.svg';
import DotsIcon from '#icons/3dots.svg';
import ThumbnailPlaceholderIcon from '#icons/thumbnail.svg';
import LivePlaceholderIcon from '#icons/live.svg';
import WatchLater from "@/Components/Cards/VideoStreamCards/Partials/WatchLater.vue";
// Import the contentModalStore module
import {useContentModalStore} from "@/Stores/ContentModalStore.js";
import {computed, ref, watch} from "vue";
import Queue from "@/Components/Cards/VideoStreamCards/Partials/Queue.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import Viewers from "@/Components/Cards/VideoStreamCards/Partials/CornerInfo.vue";
import CornerInfo from "@/Components/Cards/VideoStreamCards/Partials/CornerInfo.vue";
import Badge from "@/Components/General/Badge.vue";
import {useAuthStore} from "@/Stores/AuthStore";

const props = defineProps({
    item: Object,
    category_page: {
        type: Boolean,
        required: false,
        default: false
    },
    channel_page: {
        type: Boolean,
        required: false,
        default: false
    },
});

const contentModalStore = useContentModalStore();
const shareModalStore = useShareModalStore();
const playlistModalStore = usePlaylistModalStore();

const name = "VideoStreamCard";
const hideItem = ref(false);
const thumbnailErrored = ref(false);

watch(
    () => [props.item?.id, props.item?.thumbnail_url],
    () => {
        thumbnailErrored.value = false;
    }
);

const showThumbnailPlaceholder = computed(
    () => !props.item?.thumbnail_url || thumbnailErrored.value
);

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
    // await new Promise(resolve => setTimeout(resolve, 200)); // wait for 100 milliseconds
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
    <div :id="'box_' + itemType + '_' + item.id"
         class="relative group min-h-64 w-full cursor-pointer overflow-hidden rounded-md shadow-lg shadow-zinc-300 ring-1 ring-transparent transition-[transform,box-shadow,ring-color] duration-300 ease-in-out dark:bg-zinc-900 dark:shadow-zinc-900 xl:hover:scale-105 xl:hover:transform xl:hover:ring-cyan-400/20 xl:hover:shadow-[0_0_32px_-12px_rgba(34,211,238,0.22)]">
        <!--hide content hidden button and cover-->
        <div :id="'hide_' + itemType + '_' + item.id" @click="hideItemToggle" class="w-0 h-0 opacity-0 pointer-events-none " ></div>
        <div  v-if="hideItem" class="w-full h-full rounded-lg overflow-hidden bg-zinc-100 dark:bg-zinc-800 flex flex-col align-middle justify-center items-center ">
            <p class="text-md font-bold">Content Hidden</p>
            <div  @click="hideItemToggle()" class="text-blue-600 dark:text-blue-400 font-semibold cursor-pointer">
                Show
            </div>
        </div>
        <div  v-if="!hideItem">
            <div class="relative aspect-[21/12] overflow-hidden rounded-md bg-gradient-to-br from-zinc-800 via-zinc-900 to-zinc-950 dark:from-zinc-900 dark:via-zinc-950 dark:to-black">
                <Link
                    :href="itemType === 'video' ? route('watch.show', {slug: item.slug}) : route('stream.show', {slug: item.slug})"
                    class="relative block h-full w-full min-h-[5rem]"
                >
                    <div
                        v-show="showThumbnailPlaceholder"
                        class="absolute inset-0 z-0 flex flex-col items-center justify-center gap-1.5 text-zinc-500 dark:text-zinc-600"
                        aria-hidden="true"
                    >
                        <ThumbnailPlaceholderIcon v-if="itemType === 'video'" class="h-10 w-10 shrink-0 opacity-70" />
                        <LivePlaceholderIcon v-else class="h-9 w-9 shrink-0 opacity-70" />
                        <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500/90 dark:text-zinc-500">No preview</span>
                    </div>
                    <img
                        v-if="item.thumbnail_url && !thumbnailErrored"
                        class="absolute inset-0 z-10 h-full w-full object-cover"
                        :src="item.thumbnail_url"
                        alt=""
                        @error="thumbnailErrored = true"
                    />
                </Link>

                <!--<Duration />-->

                <CornerInfo v-if="item.duration != null" :item="item" class="absolute bottom-0 right-0 m-1.5">
                    <p class="my-auto" v-text="item.duration"/>
                </CornerInfo>

                <CornerInfo v-if="item.viewers != null" :item="item" class="absolute bottom-0 right-0 m-1.5">
                    <p class="my-auto" v-text="'LIVE'"/>
                </CornerInfo>

                <div
                    v-if="item.preferred_source != null"
                    class="pointer-events-none absolute bottom-0 left-0 z-20 m-1.5 max-w-[calc(100%-5rem)]"
                >
                    <Badge :source="item.preferred_source" :text="item.preferred_source" />
                </div>

                <div class="z-20 flex flex-col absolute top-0 right-0 m-1.5 space-y-1 items-end opacity-0 duration-500 delay-500 group-hover:opacity-100 transition-none group-hover:transition-opacity">
                    <WatchLater v-if="item.duration != null && useAuthStore().user != null" :item="item" />
                    <Queue  :item="item" :itemType="itemType" />
                </div>


            </div>
            <div class="pl-1   py-2" >
                <div class="flex flex-row">


                    <div class=" flex flex-col overflow-hidden  ">

                    <span
                        class="pt-1 line-clamp-2 overflow-hidden leading-5 font-bold  text-base  inline-flex">

                        <Link :href="itemType === 'video' ? route('watch.show', {slug: item.slug}) : route('stream.show', {slug: item.slug})"
                              v-text="item.title" :title="item.title" class="pr-2 line-clamp-2 mb-0.5"></Link>

                    </span>

                        <div class=" flex flex-row pt-1">
                            <div class=" mt-1 flex-shrink-0">
                                <div v-if="!channel_page" class="flex-shrink-0 pr-2">
                                    <Link class="without-ring " :href="route('channel.show', {slug: item.creator.slug})">

                                        <img v-if="item.creator.avatar_url != null"
                                             class=" pointer-events-auto w-9 aspect-square rounded-full bg-zinc-800 "
                                             v-bind:src="item.creator.avatar_url">
                                    </Link>
                                </div>
                            </div>
                            <div class="my-auto">
                                <div class="  space-y-0    text-xs font-normal">
                                    <Link v-if="!channel_page" :href="route('channel.show', {slug: item.creator.slug})"
                                       class="w-max without-ring pointer-events-auto line-clamp-1 text-hover dark:text-hover-dark  ">

                                        <p class="text-sm font-medium " v-text="item.creator.name"></p>
                                    </Link>

                                    <div v-if="item.duration != null" class=" info-tag dark:info-tag-dark flex pb-0.5 ">
                                        <!--<ClockIcon class="w-3 h-3 mr-1 my-auto dark:hidden"/>-->
                                        <ClockFillIcon class="w-3 h-3 mr-1 my-auto hid den dark: flex"/>
                                        <p class="line-clamp-1 " v-text="(item.time_published)"/>
                                    </div>

                                    <div v-if="itemType === 'video' && item.view_count !== undefined && item.view_count !== '0 Views'" class=" info-tag dark:info-tag-dark flex flex-row gap-2 items-center align-middle pb-0.5 ">
                                        <font-awesome-icon :icon="['fas', 'eye']" class="h-3 my-auto" />
                                        <p class="line-clamp-1" v-text="(item.view_count)"/>
                                    </div>

                                    <Link  v-if="item.category != null && !category_page" :href="route('category.show',{slug:item.category.slug})" class=" info-tag dark:info-tag-dark inline-flex mb-0.5 ">
                                        <!--<ClockIcon class="w-3 h-3 mr-1 .5 my-auto dark:hidden"/>-->
                                        <!--<ClockFillIcon class="w-3 h-3 mr-1 .5 my-auto hidden dark:flex"/>-->
                                        <font-awesome-icon class="my-auto mr-1" :icon="['fas', 'gamepad']" />
                                        <p class="line-clamp-1 font-semibold" v-text="(item.category.name)"/>

                                    </Link>

                                    <div v-if="item.live_viewer_count > 0" class="
                                        capitalize flex flex-row items-center  text-xs font-semibold
                                        text text-red-600 dark:text-red-400   ">
                                        <FireIcon class="w-3 h-3 my-auto mr-2 "/>
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
