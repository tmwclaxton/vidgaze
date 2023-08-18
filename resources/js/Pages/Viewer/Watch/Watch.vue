

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

const playerStore = usePlayerStore();
const queueStore = useQueueStore();
const playlistModalStore = usePlaylistModalStore();
const shareModalStore = useShareModalStore();
const contentModalStore = useContentModalStore();
const NavStore = useNavStore();
const authStore = useAuthStore();
const name = 'Watch';

const theatre = ref(false);
const item = ref(null);
const comments = ref(null)
const isDescriptionCollapsed = ref(true);
const showCommentSection = ref(false);
const playlistToggled = ref(false); // can't seem to get it work directly with the store
const showShare = ref(false);
const showMoreDescriptionButton = ref(false);


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
    playlistModalStore.videoIds = [props.item.id];


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
        contentModalStore.itemType = props.type;
        contentModalStore.item = props.item;
        contentModalStore.shareContent();
    }
    showShare.value = !showShare.value;

};
function shouldShowMoreDescriptionButton() {
    const el = document.getElementById('description');
    const divHeight = el.offsetHeight;
    const lineHeight = parseInt(el.style.lineHeight);
    return divHeight / lineHeight >= 3;
}
onMounted( async () => {
    // close sidebar
    NavStore.showingNavigationDropdown = false;

    // get video / stream details
    if (props.type === 'video') {
        item.value = await useContentRoutesStore().getVideo(props.slug);
    } else {

    }


    watch(() => props.item, (newVal, oldVal) => {
        if (newVal !== null) {
            // // should description show more button be shown?
            showMoreDescriptionButton.value = shouldShowMoreDescriptionButton();
            //
            // get video suggestions
            // axios.get(`/api/videos/${props.item.id}/suggestions`).then((response) => {
            //     props.suggestions = response.data.data;
            // }).catch((error) => {
            //     console.log(error);
            // });
            //
            // // get video comments
            // axios.get(`/api/videos/${props.item.id}/comments`).then((response) => {
            //     props.comments = response.data.data;
            // }).catch((error) => {
            //     console.log(error);
            // });
            //
            // playerStore.destroyPlayers().then(() => {
            //     console.log("players destroyed");
            //
            //
            //     // if video/stream was already playing by accessing the queueStore's index and is same as props.item.external_id, resume from where it left off
            //     const queueStoreItem = queueStore.items[queueStore.index];
            //     const queueStoreExternalId = queueStoreItem !== undefined ? queueStoreItem.object.external_id : null;
            //
            //
            //
            //     // check if video was on queue or not
            //     if (queueStoreExternalId === null || queueStoreExternalId !== props.item.external_id) {
            //
            //         console.log("start from beginning");
            //         playerStore.currentTimePosition = 0;
            //         playerStore.buildPlayer('watch_player', props.item, 0, true);
            //     } else {
            //
            //         console.log("resume from where it left off");
            //         const currentTime = round(playerStore.currentTimePosition);
            //         playerStore.buildPlayer('watch_player', props.item, currentTime, true);
            //
            //
            //     }
            //
            //
            // });

        }
    });

});

onUnmounted(() => {
    // // stop view record
    // playerStore.stopViewRecord();
    // // destroy players
    // playerStore.destroyPlayers().then(() => {
    //     console.log("players destroyed");
    //
    //     // watch showMiniPlayer if it is changed to true check if queueStore has any items if so then build the player
    //     if (queueStore.items.length > 0) {
    //
    //
    //         const queueStoreItem = queueStore.items[queueStore.index];
    //         const queueStoreExternalId = queueStoreItem !== undefined ? queueStoreItem.object.external_id : null;
    //
    //         let currentTime = 0;
    //
    //         // if the video that was playing was in the queue get time and rebuild player with time in mini player
    //         if (queueStoreExternalId !== null && queueStoreExternalId === props.item.external_id) {
    //             // get the current time from the playerStore
    //             currentTime = round(playerStore.currentTimePosition);
    //
    //         } else {
    //             // get time by checking the server's view history time
    //
    //         }
    //
    //         playerStore.buildPlayer('miniplayer_div_holder', queueStoreItem.object, currentTime, true, true);
    //     }
    // });
});






</script>

<template>

        <Head v-if="item != null" :title="item.title"  />


        <div v-if="item != null" class="grid grid-cols-12  gap-4 grid-flow-row-dense h-full" :class="[theatre ? '' : 'm-4 md:mx-24']">


            <!--player with theatre mode-->
            <div :class="[theatre ? 'col-span-12   w-full ' : ' col-span-12 lg:col-span-8  ']" class=" w-full  relative flex flex-col gap-y-4">

                <div :class="[theatre ? '   ' : ' rounded-lg ']" class="bg-black max-h-[calc(100vh-10rem)] overflow-hidden">
                    <div :class="[ theatre ? 'aspect-video  h-full w-full' : 'w-full aspect-video max-h-screen']">
                        <!--video player-->
                        <div id="watch_player" :class="playerStore.players.length > 0 ? 'w-full h-full bg-black without-ring flex relative ' : 'opacity-0'">

                        </div>

                        <!--end screen-->
                        <EndScreen v-if="playerStore.players.length === 0" :item="item" class="h-full w-full"/>


                    </div>

                </div>

                <div :class="[theatre ? 'px-2 sm:px-5 ' : 'px-0 sm:px-0 ']" class="">

                    <!--video details-->
                    <div class="bg-re d-500 w-full  " :class="[theatre ? ' ' : ' ']">
                        <div class=" w-full  ">

                            <p class="text-lg font-bold leading-6 line-clamp-2 text dark:textDark" v-text="item.title"/>
                            <div class="px-3 sm:px-0 flex pt-2 -mb-2 justify-between text dark:textDark flex flex-row flex-wrap gap-8 ">

                                <div class="flex flex-col">
                                    <p class=" pr-3 " v-text="item.view_count + ' · ' + item.time_published"/>
                                    <span class="pr-3  pt-0.5 font-bold text-xs text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-pink-600"
                                            v-text="item.live_viewer_count + ' Watching'"/>
                                </div>
                                <div class="text dark:textDark ml-auto flex flex-row flex-wrap gap-x-2 md:gap-x-5 mr-2 align-top justify-end font-semibold select-none">


                                    <FeatureCreatorButton v-if="authStore.admin" :creator_id="item.creator.id"/>


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

                                    <!--<x-award-button type="video" object_id="{{$video->id}}">-->
                                    <!--    <div class="flex flex-row    cursor-pointer"-->
                                    <!--         @click=" shadowDiv = true">-->
                                    <!--        <x-icon name="present" class="h-5"/>-->
                                    <!--        <p class="pl-2">Award</p>-->
                                    <!--    </div>-->
                                    <!--</x-award-button>-->
                                    <div
                                        class="hidden lg:flex   flex-row cursor-pointer align-middle items-center"
                                        @click="theatre = ! theatre">
                                        <TheatreIcon class="h-5"/>
                                        <p class="pl-2">Theatre</p>
                                    </div>


                                </div>
                            </div>

                            <!--<livewire:awards-bar type="video" :object="$video"/>-->

                            <RowDivider class="my-2"/>

                            <div class=" py-6 ">
                                <div class="flex justify-between">
                                    <span class="flex flex-row   w-full overflow-hidden">
                                        <a v-if="item.creator != null" href="/channel/" class="flex-shrink-0">
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


                                <div
                                     style="" class=" ml-14   pt-3   text dark:textDark text-sm">
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

                    </div>

                    <TertiaryButton v-if="!showCommentSection" class="w-full text-center"
                                    v-bind:class="[showCommentSection ? 'hidden ' : 'lg:hidden']"

                                    @click="showCommentSection = !showCommentSection">
                        <p class="w-full text-center">Open Comments</p>
                    </TertiaryButton>

                    <!--<CommentSection :item="item"-->
                    <!--                v-bind:class="[showCommentSection ? 'flex' : 'hidden lg:flex']" />-->

                    <RowDivider class=" " :class="[theatre ? 'flex ' : 'flex lg:hidden ']"/>

                </div>
            </div>


            <!--video suggestions-->

            <div class=" relative w-full gap-2 flex flex-col " :class="[theatre ? 'col-span-12' : 'col-span-12 lg:col-span-4  ']">



                    <!--playlist-->

                <div v-if="queueStore.playlist !== null">
                    <div class="border-generic dark:border-generic-dark flex-col mb-5">
                        <div class="p-2 generic-background   dark:bg-zinc-900">
                            <p class="font-bold text dark:textDark">
                                <!--playlist name-->
                            </p>

                            <p class="font-bold text dark:textDark text-xs opacity-80 ">
                                <!--<a href="/channel/{{$playlist->owner->slug}}">-->
                                <!--    {{$playlist->owner->name}}-->
                                <!--</a>-->
                            </p>
                        </div>
                    </div>

                    <div id="miniPlayerItemsHolder" class="relative flex flex-col pb-1 max-h-48 overflow-y-auto">
                        <div v-for="(item, index) in queueStore.items">
                            <QueueItem :item="item" :index="index" :key="index"/>
                        </div>
                    </div>
                </div>


                <!--suggested videos-->




            </div>



        </div>





</template>
