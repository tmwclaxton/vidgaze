

<script setup>
import {computed, onMounted, onUnmounted, ref, watch} from "vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {usePlayerStore} from "@/Stores/PlayerStore";
import {useQueueStore} from "@/Stores/QueueStore";
import {round} from "lodash";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";

import ShareIcon from '~/images/icons/share.svg'
import LibraryIcon from '~/images/icons/library.svg';
import TheatreIcon from '~/images/icons/expand.svg';
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import {useContentModalStore} from "@/Stores/ContentModalStore";

import TertiaryButton from "@/Components/Buttons/TertiaryButton.vue";
import LikeDislikeButtons from "@/Components/Buttons/LikeDislikeButtons.vue";
import CommentSection from "@/Components/CommentSection/CommentSection.vue";
import FeatureCreatorButton from "@/Components/Buttons/FeatureCreatorButton.vue";
import {useNavStore} from "@/Stores/NavStore";
import QueueItem from "@/Components/Modals/MiniPlayers/Partials/QueueItem.vue";
import SuggestionsScreen from "@/Pages/Viewer/Watch/Partials/SuggestionsScreen/SuggestionsScreen.vue";

import EndScreen from "@/Pages/Viewer/Watch/Partials/EndScreen.vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import Title from "@/Components/General/Title.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import WatchQueue from "@/Pages/Viewer/Watch/Partials/WatchQueue.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";

const playerStore = usePlayerStore();
const queueStore = useQueueStore();
const playlistModalStore = usePlaylistModalStore();
const shareModalStore = useShareModalStore();
const contentModalStore = useContentModalStore();
const NavStore = useNavStore();
const authStore = useAuthStore();
const name = 'Watch';

const theatre = ref(false);
const item = ref([]);

const comments = ref(null)
const isDescriptionCollapsed = ref(true);
const showCommentSection = ref(false);
const playlistToggled = ref(false); // can't seem to get it work directly with the store
const showShare = ref(false);
const showMoreDescriptionButton = ref(false);
const suggestedVideos = ref(null);
const ready = ref(false);

const props = defineProps({
    type: {
        type: String,
        required: true
    },
    slug: {
        type: String,
        required: true
    },
});

function togglePlaylistModal()  {
    if (props.type !== 'video') {
        return;
    }
    playlistModalStore.videoIds = [item.value.id];
    if (!playlistToggled.value) {
        playlistModalStore.getPlaylists();
        playlistModalStore.showMenu = true;
    } else {
        playlistModalStore.showMenu = false;
    }
    playlistToggled.value = !playlistToggled.value;
}

const share = () => {
    if (showShare.value) {
        shareModalStore.showMenu = false;
    } else {
        contentModalStore.itemType = item.value.type;
        contentModalStore.item = item.value;
        contentModalStore.shareContent();
    }
    showShare.value = !showShare.value;
};

function shouldShowMoreDescriptionButton() {
    if (document.getElementById('description') === null) {
        return false;
    }
    const el = document.getElementById('description');
    const divHeight = el.offsetHeight;
    const lineHeight = parseInt(el.style.lineHeight);
    return divHeight / lineHeight >= 3;
}

onMounted(  () => {
    usePlayerStore().destroyPlayers().then(async () => {
        // close sidebar
        NavStore.showingNavigationDropdown = false;

        // get video / stream details
        if (props.type === 'video') {
            item.value = await useContentRoutesStore().getVideo(props.slug);
        }
    });
});


// watch item for changes
watch(item, async (newItem) => {
    if (newItem !== null) {
        ready.value = true;
        suggestedVideos.value = await useContentRoutesStore().getVideos("popular", 10);
        showMoreDescriptionButton.value = shouldShowMoreDescriptionButton();
        // if not in queue build player like normal
        if (queueStore.items.length === 0 || queueStore.currentItem.external_id === null || queueStore.currentItem.external_id !== item.value.external_id) {
            await usePlayerStore().buildPlayer('watch_player', item.value, 0, true,true);
        } else {
            // console.log('building queue player' + [useQueueStore().currentPlayer.currentTime]);
            // if in queue build player with time
            await usePlayerStore().buildPlayer('watch_player', item.value, queueStore.currentPlayer.currentTime, true,true);
        }
    }
});

onUnmounted(() => {
    // if the queue has items destroy the players and rebuild the player with the current item in the mini player
    if (queueStore.items.length > 0) {
        playerStore.destroyPlayers().then(() => {
            queueStore.rebuildPlayer();
        });
    } else {
        console.log('destroying all players');
        // otherwise destroy all players and remove the players in playerstore entirely
        playerStore.destroyPlayers(true);
    }
});






</script>

<template>
        <Head  :title="item.title"  />

        <div class="grid grid-cols-12  gap-4 grid-flow-row-dense h-full" :class="[theatre ? '' : 'm-4 md:mx-24']">
            <!--player with theatre mode-->
            <div :class="[theatre ? 'col-span-12   w-full ' : ' col-span-12 lg:col-span-8  ']" class=" w-full  relative flex flex-col gap-y-4">

                <div :class="[theatre ? '   ' : ' rounded-lg ']" class="bg-black max-h-[calc(100vh-10rem)] overflow-hidden">
                    <div   :class="[ theatre ? 'aspect-video  h-full w-full' : 'w-full aspect-video max-h-screen']">
                        <!--video player-->
                        <div id="watch_player" :class="playerStore.players.length > 0 ? 'w-full h-full bg-black without-ring flex relative ' : 'opacity-0'"/>

                        <!--end screen-->
                        <!--<EndScreen v-if="ready && playerStore.players.length === 0" :item="item" class="h-full w-full"/>-->

                    </div>
                </div>

                <div :class="[theatre ? 'px-2 sm:px-5 ' : 'px-0 sm:px-0 ']" class="">

                    <!--video details-->
                    <div  class="w-full">
                        <p class="text-lg font-bold leading-6 line-clamp-2 text dark:textDark" v-text="ready ? item.title : 'Loading...'"/>
                        <div class="px-3 sm:px-0 flex pt-2 -mb-2 justify-between text dark:textDark flex flex-row flex-wrap gap-8 ">
                            <div v-if="ready" class="flex flex-col">
                                <p class=" pr-3 " v-text="item.view_count + ' · ' + item.time_published"/>
                                <span class="pr-3  pt-0.5 font-bold text-xs text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-pink-600"
                                      v-text="item.live_viewer_count + ' Watching'"/>
                            </div>
                            <div class="text dark:textDark ml-auto flex flex-row flex-wrap gap-x-2 md:gap-x-5 mr-2 align-top justify-end font-semibold select-none">
                                <FeatureCreatorButton v-if="ready && authStore.admin" :creator_id="item.creator.id"/>

                                <TertiaryButton>
                                    <LikeDislikeButtons v-if="item" :item="item" :orientationVertical="false"/>
                                </TertiaryButton>

                                <div @click="share" class="flex flex-row cursor-pointer align-middle items-center">
                                    <ShareIcon class="h-5"/>
                                    <p class="pl-2">Share</p>
                                </div>

                                <div v-if="authStore.user" @click="togglePlaylistModal()" class="flex flex-row cursor-pointer align-middle items-center" >
                                    <LibraryIcon class="h-5"/>
                                    <p class="pl-2">Save</p>
                                </div>

                                <div
                                    class="hidden lg:flex   flex-row cursor-pointer align-middle items-center"
                                    @click="theatre = ! theatre">
                                    <TheatreIcon class="h-5"/>
                                    <p class="pl-2">Theatre</p>
                                </div>

                            </div>
                        </div>

                        <RowDivider class="my-2"/>

                        <div class=" py-6 ">
                            <div v-if="ready" class="flex justify-between">
                                <span class="flex flex-row   w-full overflow-hidden">
                                    <a  href="/channel/" class="flex-shrink-0">
                                        <img class="hover:cursor-pointer my-auto object-cover w-11 h-11 mr-2 rounded-full flex-shrink-0"
                                             v-bind:src="item.creator.avatar_url" alt="Profile image"/>
                                    </a>
                                    <div class="pl-1 flex flex-col my-auto">
                                        <a href="/channel/"
                                           class="text-sm font-bold hover:cursor-pointer text dark:textDark w-44 xs:w-full break-words">
                                            <span v-text="item.creator.name"></span>
                                        </a>
                                        <p class="text-xs text dark:textDark leading-4" v-text="item.creator.subscriber_count"/>
                                    </div>

                                    <div class="ml-auto sm:ml-5 my-auto">
                                       <SubscribeButton :channel="item.creator"  />
                                    </div>
                                </span>
                            </div>
                            <div class=" ml-14   pt-3   text dark:textDark text-sm">
                                <p id="description" style="line-height: 20px;"
                                   v-bind:class="{' line-clamp-3': isDescriptionCollapsed}" v-html="item.description"/>
                                <button v-if="showMoreDescriptionButton" class="font-bold mt-5 text-xs uppercase"
                                        @click="isDescriptionCollapsed = !isDescriptionCollapsed"
                                        v-text="!isDescriptionCollapsed ? 'Show less' : 'Show more'"
                                ></button>
                            </div>
                        </div>
                        <RowDivider/>
                    </div>

                    <TertiaryButton v-if="!showCommentSection" class="w-full text-center"
                                    v-bind:class="[showCommentSection ? 'hidden ' : 'lg:hidden']"

                                    @click="showCommentSection = !showCommentSection">
                        <p class="w-full text-center">Open Comments</p>
                    </TertiaryButton>

                    <CommentSection v-if="item.creator != undefined" :item="item"
                                    v-bind:class="[showCommentSection ? 'flex' : 'hidden lg:flex']" />

                    <RowDivider class=" " :class="[theatre ? 'flex ' : 'flex lg:hidden ']"/>

                </div>
            </div>

            <!--video suggestions-->
            <div class=" relative w-full gap-4 flex flex-col " :class="[theatre ? 'col-span-12' : 'col-span-12 lg:col-span-4  ']">
                <!--playlist-->
                <WatchQueue :item="item" :ready="ready"/>

                <!--suggested videos-->
                <div class="flex flex-row flex-wrap">
                    <QuaternaryButton class="mr-2" @click="">
                        <font-awesome-icon :icon="['fas', 'fire']" class="my-auto"/>
                        <span class="font-semibold">Recommended</span>
                    </QuaternaryButton>
                    <QuaternaryButton class="mr-2" @click="">
                        <font-awesome-icon :icon="['fas', 'heart']" class="my-auto"/>
                        <span class="font-semibold">Channel</span>
                    </QuaternaryButton>
                </div>

                <div v-if="suggestedVideos && suggestedVideos.length > 0">
                    <div id="miniPlayerItemsHolder" class="relative flex flex-col pb-1 max-h-48 overflow-y-auto">
                        <div v-for="(item, index) in suggestedVideos">
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>
