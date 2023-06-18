<script setup>
import {onMounted, ref} from "vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const name = 'Watch';

const theatre = ref(false);

const layout = AuthenticatedLayout;

const video = ref(null);
const comments = ref(null);

const playNext = () => {

}

const resetPlayer = () => {

}

onMounted( () => {

    // get video details

    // get video suggestions

    // get video comments


});


</script>

<template>
    <AuthenticatedLayout>

        <div id="holder" v-on:keyup.esc="theatre = false"
             :class="[theatre ? 'px-0 pt-0  ' : ' sm:px-5 sm:pt-5 ld:px-14 lg:px-12 xl:px-24']"
              class="h-full w-full mx-auto relative">

            <div class="w-full grid grid-cols-12 ">
                <div :class="[theatre ? 'col-span-12  ' : 'col-span-12 lg:col-span-8']">
                    <div
                        :class="[ theatre ? 'h-[calc(100vh-10rem)] px-39 w-full  ' : '']"
                        class="mx-auto w-full bg-black rounded-lg shadow-lg relative"
                    >
                        <div :class="[ theatre ? 'aspect-video mx-auto h-full ' : 'w-full aspect-video max-h-screen']">
                            <!--video player-->
                            <div id="player" class="w-full h-full  without-ring flex relative">

                            </div>


                        </div>

                    </div>
                </div>
                <div class="  col-span-12 lg:col-span-8"
                     :class=" theatre ? 'lg:pl-12 pt-2' : ''">

                    <div class="px-3 sm:px-0 pt-3 ">

                        <!--video title-->
                        <!--<p v-text="item.title" class="text-lg font-bold leading-6 line-clamp-2 text dark:textDark">-->
                        <!--</p>-->
                        <div class="flex py-3 justify-between text dark:textDark">
                            <div class="flex flex-col">
                                <p class=" pr-3 ">
                                    <!--{{number_format_short($video->views)}} {{Str::plural('view', $video->views)}}-->
                                    <!--· {{Carbon::make($video->time_published)->toFormattedDateString()}}-->

                                </p>
                                <span class="pr-3  pt-0.5 font-bold text-xs text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-pink-600">
                                         <!--number_format_short($video->live_viewer_count + 1)-->

                                Watching
                            </span>
                            </div>
                            <div class="text dark:textDark ml-auto flex flex-row align-top justify-end font-semibold select-none">
                                <!--<livewire:like-button :video="$video" :buttonType="'watch'"/>-->

                                <!--<x-share link="{{route('watch', ['video'=> $video->slug])}}"-->
                                <!--         title="Check out this cool video on VidGaze - {{$video->title}}">-->
                                <!--    <div @click="shadowDiv=true;" class="flex flex-row mr-2 md:mr-5    cursor-pointer">-->
                                <!--        <x-icon name="share" class="h-5"/>-->
                                <!--        <p class="pl-2  ">Share</p>-->
                                <!--    </div>-->
                                <!--</x-share>-->
                                <!--@auth-->
                                <!--<div class="flex flex-row mr-2 md:mr-5    cursor-pointer"-->
                                <!--     @click="saveDropdown = true; shadowDiv = true">-->
                                <!--    <x-icon name="library" class="h-5"/>-->
                                <!--    <p class="pl-2">Save</p>-->
                                <!--</div>-->
                                <!--@endauth-->
                                <!--<x-award-button type="video" object_id="{{$video->id}}">-->
                                <!--    <div class="flex flex-row mr-2 md:mr-5    cursor-pointer"-->
                                <!--         @click=" shadowDiv = true">-->
                                <!--        <x-icon name="present" class="h-5"/>-->
                                <!--        <p class="pl-2">Award</p>-->
                                <!--    </div>-->
                                <!--</x-award-button>-->
                                <div
                                    class="hidden lg:flex   mr-2 md:mr-5 flex-row  cursor-pointer"
                                    v-on:click="theatre = ! theatre">
                                    <!--<x-icon name="expand" class="h-5"/>-->
                                    <p class="pl-2">Theatre</p>
                                </div>


                            </div>
                        </div>
                        <!--video awards-->
                        <!--<livewire:awards-bar type="video" :object="$video"/>-->

                        <x-hr class="mt-3"/>
                        <div class=" py-6 ">
                            <div class="flex justify-between">
                            <span class="flex flex-row   w-full overflow-hidden">
                                <!--<a href="/channel/{{$creator->slug}}" class="flex-shrink-0">-->
                                <!--     @if(isset($creator->avatar_url))-->
                                <!--        <img-->
                                <!--            class="hover:cursor-pointer my-auto object-cover w-11 h-11 mr-2 rounded-full flex-shrink-0"-->
                                <!--            src="{{$creator->avatar_url}}"-->
                                <!--            alt="Profile image"/>-->
                                <!--    @else-->
                                <!--        <x-icon name="profile_default"-->
                                <!--                class="hover:cursor-pointer my-auto object-cover w-11 h-11 mr-2 rounded-full flex-shrink-0"/>-->
                                <!--    @endif-->
                                <!--</a>-->
                                <!--<div class="pl-1 flex flex-col my-auto">-->
                                <!--    <a href="/channel/{{$creator->slug}}"-->
                                <!--       class="text-sm font-bold hover:cursor-pointer text dark:textDark w-44 xs:w-full break-words">{{$creator->name}}</a href="/channel/{{$creator->slug}}">-->
                                <!--    <p class="text-xs text dark:textDark leading-4">{{$creator->subscriber_count}}  {{Str::plural('subscribers', $creator->subscriber_count)}}</p>-->
                                <!--</div>-->

                                <!--<div class="ml-auto my-auto">-->

                                <!--    @auth-->
                                <!--        @if(Auth::user()->creator->slug != $creator->slug)-->
                                <!--            <livewire:subscribe-button :creator="$creator"/>-->
                                <!--        @endif-->
                                <!--    @else-->
                                <!--        <p @click=" flash('Login to subscribe');"-->
                                <!--           class="  subscribe  ">Subscribe</p>-->
                                <!--    @endauth-->
                                <!--</div>-->
                            </span>


                            </div>


                            <!--<div v-data=" descriptionHandler() "-->
                            <!--     style="" class=" ml-14   pt-3   text dark:textDark text-sm">-->
                            <!--    <p id="description" style="line-height: 20px;"-->
                            <!--       x-bind:class="{' line-clamp-3': !isCollapsed}">-->
                            <!--        {{$video->description}}-->
                            <!--    </p>-->

                            <!--    <button x-show="countLines()" class="font-bold mt-5 text-xs uppercase"-->
                            <!--            @click="isCollapsed = !isCollapsed"-->
                            <!--            x-text="isCollapsed ? 'Show less' : 'Show more'"-->
                            <!--    ></button>-->
                            <!--</div>-->

                        </div>
                        <RowDivider/>
                    </div>
                </div>

            </div>
            <!--Side suggestions and playlist holder-->
            <div id="holder1" class="w-full grid grid-cols-12 lg:absolute lg:top-0 lg:left-0 pointer-events-none "
                 :class=" theatre ? 'px-0 pt-2  ' : 'pt-3 px-2 sm:px-0 sm:pt-5 lg:px-12 xl:px-24'">
                <div class="pointer-events-none  col-span-12 lg:col-span-8 bg-transparent h-0 "
                >
                </div>
                <!--playlists-->
                <div class="h-full col-span-12 lg:col-span-4 pointer-events-auto lg:pl-5 "
                     :class=" theatre ? 'lg:mt-[calc(100vh-10rem)]  p-2 pt-4   lg:mr-24' : ' '" >

                    <!--@isset($playlist)-->

                    <!--<div class="border-generic dark:border-generic-dark flex-col mb-5">-->
                    <!--    <div class="p-2 generic-background   dark:bg-zinc-900">-->
                    <!--        <p class="font-bold text dark:textDark">-->
                    <!--            {{$playlist->name}}-->
                    <!--        </p>-->

                    <!--        <p class="font-bold text dark:textDark text-xs opacity-80 ">-->
                    <!--            <a href="/channel/{{$playlist->owner->slug}}">-->
                    <!--                {{$playlist->owner->name}}-->
                    <!--            </a>-->
                    <!--        </p>-->
                    <!--    </div>-->
                    <!--    <x-hr class="w-full"/>-->
                    <!--    <div id="playlistHolder" class=" flex flex-col h-96 overflow-y-scroll ">-->

                            <!--@foreach($playlist_videos as $index=>$playlist_video)-->
                            <!--@if ($index >= ($current_video_key-50) && $index < ($current_video_key + 50))-->

                            <!--<a @if ($index == $current_video_key) id="currentVideo" @endif-->
                            <!--   href="{{ route('watch.playlist',['video'=>$playlist_video,'playlist'=>$playlist]) }}"-->
                            <!--   class="w-full py-3 px-4 hover:bg-zinc-300 dark:hover:bg-zinc-800 flex flex-row gap-x-5 cursor-pointer">-->
                            <!--    @if($playlist_video->id == $video->id)-->
                            <!--    <x-icon name="extend-down" class="w-5 fill dark:fill-dark -rotate-90"/>-->
                            <!--    @else-->
                            <!--    <p class="w-5 text dark:textDark text-center font-bold my-auto">{{($index + 1)}}</p>-->
                            <!--    @endif-->
                            <!--    <x-watch-playlist-video :video="$playlist_video" :playlist="$playlist"/>-->
                            <!--</a>-->
                            <!--@if($index+1 != count($playlist_videos))-->
                            <!--<x-hr class="w-full "/>-->
                            <!--@endif-->
                            <!--@endif-->

                            <!--@endforeach-->
                    <!--    </div>-->
                    <!--</div>-->


                    <!--@endisset-->

                    <!--infinite scroll-->
                    <div class="px-2 " :class=" theatre ? '   lg:mr-8 ' : ''">
                        <!--<livewire:watch-infinite-scroll :creator="$creator" :video="$video"/>-->


                    </div>
                </div>
            </div>

            <!--commentsection-->
            <div class="grid grid-cols-12 px-4 sm:px-2 lg:px-0 pt-3 ">
                <div class="  col-span-12 lg:col-span-8 "
                     :class=" theatre ? 'lg:pl-12' : ''">

                    <!--<livewire:comment-section :video="$video" :simple="false" :firstCommentSlug="$firstCommentSlug"/>-->

                </div>
                <div class="col-span-4 bg-transparent pointer-events-none"></div>
            </div>
        </div>



    </AuthenticatedLayout>



</template>
