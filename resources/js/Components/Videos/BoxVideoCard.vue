<template>
    <div id="box_{{ video.id}}" class="relative group overflow-hidden">


        <div class="relative aspect-[21/12] overflow-hidden rounded-xl">
            <a v-bind:href="'/watch/' + video.slug">
                <img class="object-cover w-full h-full bg-zinc-900" v-bind:src="video.thumbnail_url"   alt=""/>
            </a>
            <!--<livewire:watch-later-button wire:key="{{time().video.id}}" :video="$video"/>-->

            <!--<x-duration :video="$video"/>-->


        </div>
        <div class="pl-0   py-2">
            <div class="flex flex-row">
                <div v-if="!channel_page" class="flex-shrink-0 pr-2">
                    <a class="without-ring " v-bind:href="'/channel/' + video.creator.slug">

                        <img v-if="video.creator.avatar_url != null"
                             class=" pointer-events-auto w-10 mt-1  aspect-square rounded-full bg-zinc-800 "
                             v-bind:src="video.creator.avatar_url">
                    <!--    <x-icon v-else name="profile_default"-->
                    <!--            class="pointer-events-auto w-10 mt-1  aspect-square rounded-full bg-zinc-800 "/>-->
                    </a>
                </div>

                <div class=" flex flex-col overflow-hidden  ">

                <span
                    class="pt-1 line-clamp-2 overflow-hidden leading-5 font-bold  text-base text dark:textDark  inline-flex">

                    <a v-bind:href="/watch/ + video.slug" v-text="video.title" class="pr-2"></a>


                </span>

                    <a v-if="!channel_page" v-bind:href="/channel/ + video.creator.slug"
                       class="w-max without-ring pointer-events-auto line-clamp-1 leading-4 text dark:textDark text-hover dark:text-hover-dark font-semibold text-xs    ">

                        <p class="" v-text="video.creator.name"></p>
                    </a>
                    <div class="    space-y-0.5 ">

                        <div class=" info-tag dark:info-tag-dark text dark:textDark  inline-flex">
                            <!--<x-icon name="eye" class="w-3 h-3 mr-1.5"/>-->
                            <p v-text="video.views + ' View'"></p>
                        </div>
                        <div class=" info-tag dark:info-tag-dark text dark:textDark   inline-flex ">
                            <!--<x-icon name="clock" class="w-3 h-3 mr-1.5"/>-->
                            <!--Convert time_published to a human readable format-->
                            <!--<inline-svg src="/images/icons/clock.svg"-->
                            <!--<ClockSVG class="fill-white"/>-->

                            <p class="line-clamp-1" v-text="(video.time_published)" >
                            </p>

                        </div>
                        <!--Video Source Info-->
                        <!--<x-source-tag :source="video.preferred_source" class="inline-flex "/>-->

                        <div v-if="video.live_viewer_count > 0" class="
                        capitalize flex flex-row items-center h-5 p-1 text-xs font-bold leading-tight   rounded-sm w-max
                        text text-zinc-100 dark:text-white   inline-flex bg-red-600 dark:bg-red-700 ">
                            <!--<x-icon name="shorts" class="w-3 h-3 mr-1.5"/>-->
                            <p class="line-clamp-1 " v-text="video.live_viewer_count + ' Watching'"></p>
                        </div>
                    </div>

                </div>
                <!--3 dots button-->
                <!--<div x-on:click="-->
                <!--    videoOptionsDropdown = !videoOptionsDropdown;-->
                <!--    positionOptionCard('{{ video.id }}');-->
                <!--    videoSlug='{{video.slug}}';-->
                <!--    document.getElementById('setVideoSlugVariable').click();-->
                <!--    shareText='Check out this cool video on VidGaze - {{addslashes(video.title)}}';-->
                <!--    shareLink='{{route('watch',video.slug)}}';-->
                <!--    "-->
                <!--    class="col-span-1 ml-auto pt-2 w-8 without-ring" >-->
                <!--    <button  id="box_dots_{{ video.id }}" class="flex without-ring m-0 mt-0 opacity-90 w-6-->
                <!--    rounded-full text-zinc-500 ml-auto   pointer-events-auto">-->
                <!--    <x-icon  name="3dots"></x-icon>-->
                <!--    </button>-->
                <!--</div>-->

            </div>
        </div>


    </div>
</template>

<script setup>
import ClockSVG from "~/images/icons/clock.svg";
defineProps({
    video: Object,
    channel_page: Boolean,
});

</script>
<script>
//accept prop for video
export default {
    name: "BoxVideo",
}

</script>

<style scoped>

</style>
