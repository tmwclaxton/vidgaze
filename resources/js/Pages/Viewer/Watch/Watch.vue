<script setup>
import {onMounted, onUnmounted, ref, watch} from "vue";
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

const playerStore = usePlayerStore();
const queueStore = useQueueStore();
const playlistModalStore = usePlaylistModalStore();
const shareModalStore = useShareModalStore();
const contentModalStore = useContentModalStore();

const name = 'Watch';

const theatre = ref(false);



const layout = AuthenticatedLayout;

const video = ref(null);
const comments = ref(null)
const isCollapsed = ref(true);



const props = defineProps({
    item: {
        type: Object,
        required: true


    },
    type: {
        type: String,
        required: true
    }
});

const playlistToggled = ref(false); // can't seem to get it work directly with the store
const showShare = ref(false);
function togglePlaylistModal()  {
    if (props.item.data.type !== 'video') {
        return;
    }
    playlistModalStore.videoIds = [props.item.data.id];


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
        contentModalStore.itemType = props.item.data.type;
        contentModalStore.item = props.item.data;
        contentModalStore.shareContent();
    }
    showShare.value = !showShare.value;

};

onMounted( () => {
    // console.log(props.item.data);

    playerStore.destroyPlayers();


    // if video/stream was already playing by accessing the queueStore's index and is same as props.item.external_id, resume from where it left off
    const queueStoreItem = queueStore.items[queueStore.index];
    const queueStoreExternalId = queueStoreItem !== undefined ? queueStoreItem.object.external_id : null;

    // check if video was on queue or not
    if (queueStoreExternalId === null || queueStoreExternalId !== props.item.data.external_id) {

        console.log("start from beginning");
        playerStore.currentTimePosition = 0;
        playerStore.buildPlayer('watch_player', props.item.data, 0, true);
    } else {

        console.log("resume from where it left off");
        const currentTime = round(playerStore.currentTimePosition);
        playerStore.buildPlayer('watch_player', props.item.data, currentTime, true);


    }




    // get video details


    // get video suggestions

    // get video comments


});

onUnmounted(() => {
    // stop view record
    playerStore.stopViewRecord();
    // destroy players
    playerStore.destroyPlayers();

    // watch showMiniPlayer if it is changed to true check if queueStore has any items if so then build the player
    if (queueStore.items.length > 0) {


        const queueStoreItem = queueStore.items[queueStore.index];
        const queueStoreExternalId = queueStoreItem !== undefined ? queueStoreItem.object.external_id : null;

        let currentTime = 0;

        // if the video that was playing was in the queue get time and rebuild player with time in mini player
        if (queueStoreExternalId !== null && queueStoreExternalId === props.item.data.external_id) {
            // get the current time from the playerStore
            currentTime = round(playerStore.currentTimePosition);

        } else {
            // get time by checking the server's view history time

        }

        playerStore.buildPlayer('miniplayer_div_holder', queueStoreItem.object, currentTime, true, true);
    }
});







</script>

<template>
    <AuthenticatedLayout>

        <div class="grid grid-cols-12  gap-4 grid-flow-row-dense h-full" :class="[theatre ? '' : 'm-4 md:mx-24']">


            <!--player with theatre mode-->
            <div :class="[theatre ? 'col-span-12   w-full ' : ' col-span-12 lg:col-span-8  ']" class=" w-full  relative flex flex-col gap-y-4">

                <div :class="[theatre ? '   ' : ' rounded-lg ']" class="bg-black max-h-[calc(100vh-10rem)] overflow-hidden">
                    <div :class="[ theatre ? 'aspect-video  h-full w-full' : 'w-full aspect-video max-h-screen']">
                        <!--video player-->
                        <div id="watch_player" class="w-full h-full  without-ring flex relative ">

                        </div>


                    </div>

                </div>

                <div :class="[theatre ? 'px-2 sm:px-5 ' : 'px-5 sm:px-0 ']" class="">

                    <!--video details-->
                    <div class="bg-re d-500 w-full  " :class="[theatre ? ' ' : ' ']">
                        <div class="px-3 sm:px-0 pt-3 ">

                            <p class="text-lg font-bold leading-6 line-clamp-2 text dark:textDark" v-text="props.item.data.title"/>
                            <div class="flex py-3 justify-between text dark:textDark">
                                <div class="flex flex-col">
                                    <p class=" pr-3 " v-text="props.item.data.view_count + ' · ' + props.item.data.time_published"/>
                                    <span class="pr-3  pt-0.5 font-bold text-xs text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-pink-600"
                                            v-text="props.item.data.live_viewer_count + ' Watching'"/>
                                </div>
                                <div class="text dark:textDark ml-auto flex flex-row gap-x-2 md:gap-x-5 mr-2 align-top justify-end font-semibold select-none">

                                    <!--<x-share link="{{route('watch', ['video'=> $video->slug])}}"-->
                                    <!--         title="Check out this cool video on VidGaze - {{$video->title}}">-->
                                    <div @click="share" class="flex flex-row cursor-pointer align-middle items-center">
                                        <ShareIcon class="h-5"/>
                                        <p class="pl-2  ">Share</p>
                                    </div>
                                    <!--</x-share>-->

                                    <div v-if="$page.props.auth.user" @click="togglePlaylistModal()" class="flex flex-row cursor-pointer align-middle items-center" >
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

                            <RowDivider/>

                            <div class=" py-6 ">
                                <div class="flex justify-between">
                                    <span class="flex flex-row   w-full overflow-hidden">
                                        <a v-if="props.item.data.creator != null" href="/channel/" class="flex-shrink-0">
                                            <img class="hover:cursor-pointer my-auto object-cover w-11 h-11 mr-2 rounded-full flex-shrink-0"
                                                v-bind:src="props.item.data.creator.avatar_url" alt="Profile image"/>
                                        </a>
                                        <div class="pl-1 flex flex-col my-auto">
                                            <a href="/channel/"
                                               class="text-sm font-bold hover:cursor-pointer text dark:textDark w-44 xs:w-full break-words">
                                                <span v-text="props.item.data.creator.name"></span>
                                            </a>
                                            <p class="text-xs text dark:textDark leading-4" v-text="props.item.data.creator.subscriber_count"/>
                                        </div>

                                        <div class="ml-auto my-auto">

                                           <SubscribeButton :channel="props.item.data.creator"  />
                                        </div>
                                    </span>


                                </div>


                                <div
                                     style="" class=" ml-14   pt-3   text dark:textDark text-sm">
                                    <p id="description" style="line-height: 20px;"
                                       v-bind:class="{' line-clamp-3': isCollapsed}" v-text="props.item.data.description"/>

                                    <button class="font-bold mt-5 text-xs uppercase"
                                            @click="isCollapsed = !isCollapsed"
                                            v-text="!isCollapsed ? 'Show less' : 'Show more'"
                                    ></button>
                                </div>
                            </div>
                            <RowDivider/>
                        </div>

                    </div>

                    <div class="col-span-12 col-span-8 "  >
                        <p>Open Comment section button</p>
                    </div>

                </div>
            </div>


            <!--video suggestions-->

            <div class="bg-green-500 relative w-full gap-2 flex flex-col " :class="[theatre ? 'col-span-12' : 'col-span-12 lg:col-span-4 rounded-lg ']">

                <div class="h-96 bg-blue-400 w-full" v-for="n in 10" :key="n">

                </div>


            </div>



        </div>




        <!--<div id="holder" v-on:keyup.esc="theatre = false"-->
        <!--     :class="[theatre ? 'px-0 pt-0  ' : ' sm:px-5 sm:pt-5 ld:px-14 lg:px-12 xl:px-24']"-->
        <!--      class="h-full w-full mx-auto relative">-->

        <!--    <div class="w-full grid grid-cols-12 ">-->
        <!--        <div :class="[theatre ? 'col-span-12  ' : 'col-span-12 lg:col-span-8']">-->
        <!--            <div-->
        <!--                :class="[ theatre ? 'h-[calc(100vh-10rem)] px-39 w-full  ' : '']"-->
        <!--                class="mx-auto w-full bg-black rounded-lg  relative"-->
        <!--            >-->
        <!--                <div :class="[ theatre ? 'aspect-video mx-auto h-full ' : 'w-full aspect-video max-h-screen']">-->
        <!--                    &lt;!&ndash;video player&ndash;&gt;-->
        <!--                    <div id="player" class="w-full h-full  without-ring flex relative">-->

        <!--                    </div>-->


        <!--                </div>-->

        <!--            </div>-->
        <!--        </div>-->
        <!--        -->
        <!--        &lt;!&ndash;video details and commentsection&ndash;&gt;-->
        <!--        <div class="  col-span-12 lg:col-span-8"-->
        <!--             :class=" theatre ? 'lg:pl-12 pt-2' : ''">-->

        <!--            <div class="px-3 sm:px-0 pt-3 ">-->

        <!--                &lt;!&ndash;video title&ndash;&gt;-->
        <!--                &lt;!&ndash;<p v-text="item.title" class="text-lg font-bold leading-6 line-clamp-2 text dark:textDark">&ndash;&gt;-->
        <!--                &lt;!&ndash;</p>&ndash;&gt;-->
        <!--                <div class="flex py-3 justify-between text dark:textDark">-->
        <!--                    <div class="flex flex-col">-->
        <!--                        <p class=" pr-3 ">-->
        <!--                            &lt;!&ndash;{{number_format_short($video->views)}} {{Str::plural('view', $video->views)}}&ndash;&gt;-->
        <!--                            &lt;!&ndash;· {{Carbon::make($video->time_published)->toFormattedDateString()}}&ndash;&gt;-->

        <!--                        </p>-->
        <!--                        <span class="pr-3  pt-0.5 font-bold text-xs text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-pink-600">-->
        <!--                                 &lt;!&ndash;number_format_short($video->live_viewer_count + 1)&ndash;&gt;-->

        <!--                        Watching-->
        <!--                    </span>-->
        <!--                    </div>-->
        <!--                    <div class="text dark:textDark ml-auto flex flex-row align-top justify-end font-semibold select-none">-->
        <!--                        &lt;!&ndash;<livewire:like-button :video="$video" :buttonType="'watch'"/>&ndash;&gt;-->

        <!--                        &lt;!&ndash;<x-share link="{{route('watch', ['video'=> $video->slug])}}"&ndash;&gt;-->
        <!--                        &lt;!&ndash;         title="Check out this cool video on VidGaze - {{$video->title}}">&ndash;&gt;-->
        <!--                        &lt;!&ndash;    <div @click="shadowDiv=true;" class="flex flex-row    cursor-pointer">&ndash;&gt;-->
        <!--                        &lt;!&ndash;        <x-icon name="share" class="h-5"/>&ndash;&gt;-->
        <!--                        &lt;!&ndash;        <p class="pl-2  ">Share</p>&ndash;&gt;-->
        <!--                        &lt;!&ndash;    </div>&ndash;&gt;-->
        <!--                        &lt;!&ndash;</x-share>&ndash;&gt;-->
        <!--                        &lt;!&ndash;@auth&ndash;&gt;-->
        <!--                        &lt;!&ndash;<div class="flex flex-row    cursor-pointer"&ndash;&gt;-->
        <!--                        &lt;!&ndash;     @click="saveDropdown = true; shadowDiv = true">&ndash;&gt;-->
        <!--                        &lt;!&ndash;    <x-icon name="library" class="h-5"/>&ndash;&gt;-->
        <!--                        &lt;!&ndash;    <p class="pl-2">Save</p>&ndash;&gt;-->
        <!--                        &lt;!&ndash;</div>&ndash;&gt;-->
        <!--                        &lt;!&ndash;@endauth&ndash;&gt;-->
        <!--                        &lt;!&ndash;<x-award-button type="video" object_id="{{$video->id}}">&ndash;&gt;-->
        <!--                        &lt;!&ndash;    <div class="flex flex-row    cursor-pointer"&ndash;&gt;-->
        <!--                        &lt;!&ndash;         @click=" shadowDiv = true">&ndash;&gt;-->
        <!--                        &lt;!&ndash;        <x-icon name="present" class="h-5"/>&ndash;&gt;-->
        <!--                        &lt;!&ndash;        <p class="pl-2">Award</p>&ndash;&gt;-->
        <!--                        &lt;!&ndash;    </div>&ndash;&gt;-->
        <!--                        &lt;!&ndash;</x-award-button>&ndash;&gt;-->
        <!--                        <div-->
        <!--                            class="hidden lg:flex   flex-row  cursor-pointer"-->
        <!--                            v-on:click="theatre = ! theatre">-->
        <!--                            &lt;!&ndash;<x-icon name="expand" class="h-5"/>&ndash;&gt;-->
        <!--                            <p class="pl-2">Theatre</p>-->
        <!--                        </div>-->


        <!--                    </div>-->
        <!--                </div>-->
        <!--                &lt;!&ndash;video awards&ndash;&gt;-->
        <!--                &lt;!&ndash;<livewire:awards-bar type="video" :object="$video"/>&ndash;&gt;-->

        <!--                <x-hr class="mt-3"/>-->
        <!--                <div class=" py-6 ">-->
        <!--                    <div class="flex justify-between">-->
        <!--                    <span class="flex flex-row   w-full overflow-hidden">-->
        <!--                        &lt;!&ndash;<a href="/channel/{{$creator->slug}}" class="flex-shrink-0">&ndash;&gt;-->
        <!--                        &lt;!&ndash;     @if(isset($creator->avatar_url))&ndash;&gt;-->
        <!--                        &lt;!&ndash;        <img&ndash;&gt;-->
        <!--                        &lt;!&ndash;            class="hover:cursor-pointer my-auto object-cover w-11 h-11 mr-2 rounded-full flex-shrink-0"&ndash;&gt;-->
        <!--                        &lt;!&ndash;            src="{{$creator->avatar_url}}"&ndash;&gt;-->
        <!--                        &lt;!&ndash;            alt="Profile image"/>&ndash;&gt;-->
        <!--                        &lt;!&ndash;    @else&ndash;&gt;-->
        <!--                        &lt;!&ndash;        <x-icon name="profile_default"&ndash;&gt;-->
        <!--                        &lt;!&ndash;                class="hover:cursor-pointer my-auto object-cover w-11 h-11 mr-2 rounded-full flex-shrink-0"/>&ndash;&gt;-->
        <!--                        &lt;!&ndash;    @endif&ndash;&gt;-->
        <!--                        &lt;!&ndash;</a>&ndash;&gt;-->
        <!--                        &lt;!&ndash;<div class="pl-1 flex flex-col my-auto">&ndash;&gt;-->
        <!--                        &lt;!&ndash;    <a href="/channel/{{$creator->slug}}"&ndash;&gt;-->
        <!--                        &lt;!&ndash;       class="text-sm font-bold hover:cursor-pointer text dark:textDark w-44 xs:w-full break-words">{{$creator->name}}</a href="/channel/{{$creator->slug}}">&ndash;&gt;-->
        <!--                        &lt;!&ndash;    <p class="text-xs text dark:textDark leading-4">{{$creator->subscriber_count}}  {{Str::plural('subscribers', $creator->subscriber_count)}}</p>&ndash;&gt;-->
        <!--                        &lt;!&ndash;</div>&ndash;&gt;-->

        <!--                        &lt;!&ndash;<div class="ml-auto my-auto">&ndash;&gt;-->

        <!--                        &lt;!&ndash;    @auth&ndash;&gt;-->
        <!--                        &lt;!&ndash;        @if(Auth::user()->creator->slug != $creator->slug)&ndash;&gt;-->
        <!--                        &lt;!&ndash;            <livewire:subscribe-button :creator="$creator"/>&ndash;&gt;-->
        <!--                        &lt;!&ndash;        @endif&ndash;&gt;-->
        <!--                        &lt;!&ndash;    @else&ndash;&gt;-->
        <!--                        &lt;!&ndash;        <p @click=" flash('Login to subscribe');"&ndash;&gt;-->
        <!--                        &lt;!&ndash;           class="  subscribe  ">Subscribe</p>&ndash;&gt;-->
        <!--                        &lt;!&ndash;    @endauth&ndash;&gt;-->
        <!--                        &lt;!&ndash;</div>&ndash;&gt;-->
        <!--                    </span>-->


        <!--                    </div>-->


        <!--                    &lt;!&ndash;<div v-data=" descriptionHandler() "&ndash;&gt;-->
        <!--                    &lt;!&ndash;     style="" class=" ml-14   pt-3   text dark:textDark text-sm">&ndash;&gt;-->
        <!--                    &lt;!&ndash;    <p id="description" style="line-height: 20px;"&ndash;&gt;-->
        <!--                    &lt;!&ndash;       x-bind:class="{' line-clamp-3': !isCollapsed}">&ndash;&gt;-->
        <!--                    &lt;!&ndash;        {{$video->description}}&ndash;&gt;-->
        <!--                    &lt;!&ndash;    </p>&ndash;&gt;-->

        <!--                    &lt;!&ndash;    <button x-show="countLines()" class="font-bold mt-5 text-xs uppercase"&ndash;&gt;-->
        <!--                    &lt;!&ndash;            @click="isCollapsed = !isCollapsed"&ndash;&gt;-->
        <!--                    &lt;!&ndash;            x-text="isCollapsed ? 'Show less' : 'Show more'"&ndash;&gt;-->
        <!--                    &lt;!&ndash;    ></button>&ndash;&gt;-->
        <!--                    &lt;!&ndash;</div>&ndash;&gt;-->

        <!--                </div>-->
        <!--                <RowDivider/>-->
        <!--            </div>-->
        <!--        </div>-->

        <!--    </div>-->
        <!--    &lt;!&ndash;Side suggestions and playlist holder&ndash;&gt;-->
        <!--    <div id="holder1" class="w-full grid grid-cols-12 lg:absolute lg:top-0 lg:left-0 pointer-events-none "-->
        <!--         :class=" theatre ? 'px-0 pt-2  ' : 'pt-3 px-2 sm:px-0 sm:pt-5 lg:px-12 xl:px-24'">-->
        <!--        <div class="pointer-events-none  col-span-12 lg:col-span-8 bg-transparent h-0 "-->
        <!--        >-->
        <!--        </div>-->
        <!--        &lt;!&ndash;playlists&ndash;&gt;-->
        <!--        <div class="h-full col-span-12 lg:col-span-4 pointer-events-auto lg:pl-5 "-->
        <!--             :class=" theatre ? 'lg:mt-[calc(100vh-10rem)]  p-2 pt-4   lg:mr-24' : ' '" >-->

        <!--            &lt;!&ndash;@isset($playlist)&ndash;&gt;-->

        <!--            &lt;!&ndash;<div class="border-generic dark:border-generic-dark flex-col mb-5">&ndash;&gt;-->
        <!--            &lt;!&ndash;    <div class="p-2 generic-background   dark:bg-zinc-900">&ndash;&gt;-->
        <!--            &lt;!&ndash;        <p class="font-bold text dark:textDark">&ndash;&gt;-->
        <!--            &lt;!&ndash;            {{$playlist->name}}&ndash;&gt;-->
        <!--            &lt;!&ndash;        </p>&ndash;&gt;-->

        <!--            &lt;!&ndash;        <p class="font-bold text dark:textDark text-xs opacity-80 ">&ndash;&gt;-->
        <!--            &lt;!&ndash;            <a href="/channel/{{$playlist->owner->slug}}">&ndash;&gt;-->
        <!--            &lt;!&ndash;                {{$playlist->owner->name}}&ndash;&gt;-->
        <!--            &lt;!&ndash;            </a>&ndash;&gt;-->
        <!--            &lt;!&ndash;        </p>&ndash;&gt;-->
        <!--            &lt;!&ndash;    </div>&ndash;&gt;-->
        <!--            &lt;!&ndash;    <x-hr class="w-full"/>&ndash;&gt;-->
        <!--            &lt;!&ndash;    <div id="playlistHolder" class=" flex flex-col h-96 overflow-y-scroll ">&ndash;&gt;-->

        <!--                    &lt;!&ndash;@foreach($playlist_videos as $index=>$playlist_video)&ndash;&gt;-->
        <!--                    &lt;!&ndash;@if ($index >= ($current_video_key-50) && $index < ($current_video_key + 50))&ndash;&gt;-->

        <!--                    &lt;!&ndash;<a @if ($index == $current_video_key) id="currentVideo" @endif&ndash;&gt;-->
        <!--                    &lt;!&ndash;   href="{{ route('watch.playlist',['video'=>$playlist_video,'playlist'=>$playlist]) }}"&ndash;&gt;-->
        <!--                    &lt;!&ndash;   class="w-full py-3 px-4 hover:bg-zinc-300 dark:hover:bg-zinc-800 flex flex-row gap-x-5 cursor-pointer">&ndash;&gt;-->
        <!--                    &lt;!&ndash;    @if($playlist_video->id == $video->id)&ndash;&gt;-->
        <!--                    &lt;!&ndash;    <x-icon name="extend-down" class="w-5 fill dark:fill-dark -rotate-90"/>&ndash;&gt;-->
        <!--                    &lt;!&ndash;    @else&ndash;&gt;-->
        <!--                    &lt;!&ndash;    <p class="w-5 text dark:textDark text-center font-bold my-auto">{{($index + 1)}}</p>&ndash;&gt;-->
        <!--                    &lt;!&ndash;    @endif&ndash;&gt;-->
        <!--                    &lt;!&ndash;    <x-watch-playlist-video :video="$playlist_video" :playlist="$playlist"/>&ndash;&gt;-->
        <!--                    &lt;!&ndash;</a>&ndash;&gt;-->
        <!--                    &lt;!&ndash;@if($index+1 != count($playlist_videos))&ndash;&gt;-->
        <!--                    &lt;!&ndash;<x-hr class="w-full "/>&ndash;&gt;-->
        <!--                    &lt;!&ndash;@endif&ndash;&gt;-->
        <!--                    &lt;!&ndash;@endif&ndash;&gt;-->

        <!--                    &lt;!&ndash;@endforeach&ndash;&gt;-->
        <!--            &lt;!&ndash;    </div>&ndash;&gt;-->
        <!--            &lt;!&ndash;</div>&ndash;&gt;-->


        <!--            &lt;!&ndash;@endisset&ndash;&gt;-->

        <!--            &lt;!&ndash;infinite scroll&ndash;&gt;-->
        <!--            <div class="px-2 " :class=" theatre ? '   lg:mr-8 ' : ''">-->
        <!--                &lt;!&ndash;<livewire:watch-infinite-scroll :creator="$creator" :video="$video"/>&ndash;&gt;-->


        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->

        <!--    &lt;!&ndash;commentsection&ndash;&gt;-->
        <!--    <div class="grid grid-cols-12 px-4 sm:px-2 lg:px-0 pt-3 ">-->
        <!--        <div class="  col-span-12 lg:col-span-8 "-->
        <!--             :class=" theatre ? 'lg:pl-12' : ''">-->

        <!--            &lt;!&ndash;<livewire:comment-section :video="$video" :simple="false" :firstCommentSlug="$firstCommentSlug"/>&ndash;&gt;-->

        <!--        </div>-->
        <!--        <div class="col-span-4 bg-transparent pointer-events-none"></div>-->
        <!--    </div>-->
        <!--</div>-->



    </AuthenticatedLayout>



</template>
