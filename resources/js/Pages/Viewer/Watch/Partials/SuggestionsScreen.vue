<script setup>
import {ref} from "vue";

const name = 'SuggestionsScreen';
const nextUpScreen = ref(false);
const readyForNextVideo = ref(false);

const suggestions = ref(null);
</script>
<template>
    <!--endscreen-->
    <div id="suggestionsScreen"
         class="hidden w-full h-full  without-ring flex relative duration-600 transition w-full h-full flex relative ">
        <div v-show="nextUpScreen" id="nextUpScreen"
             class=" mx-auto mt-5 sm:my-auto w-96  flex flex-col gap-y-3">
            <p class="text-left text text-zinc-400 font-bold ">Up next in <span
                class="text-white" id="myTimer"></span></p>
            <div class="  ">
                <!--if playlist show next one-->
                <!--@isset($playlist)-->
                <!--<x-watch-end-screen-next :video="$videoNext" :playlist="$playlist"/>-->
                <!--@else-->
                <!--<x-watch-end-screen-next :video="$videoNext"/>-->
                <!--@endif-->
            </div>
            <div id="buttons" class="  flex flex-row gap-x-4 select-none ">

                <div @click="nextUpScreen = false; readyForNextVideo = false;"
                     class="bg-zinc-900 rounded-full p-2 px-14 w-max cursor-pointer image-wrapper shine">
                    <p class="   uppercase text-white text-sm font-bold">
                        Cancel
                    </p>
                </div>
                <div @click="playNext()"
                     class="bg-zinc-800 rounded-full p-2 px-14 w-max cursor-pointer  image-wrapper shine">
                    <p class=" uppercase text-white text-sm font-bold">
                        Play now
                    </p>
                </div>
            </div>
        </div>
        <div v-show="!nextUpScreen" id="suggestionsScreen"
             class="m-10 grid grid-cols-3 gap-5 h-max mt-10 sm:my-auto">
            <!--@foreach($endScreenSuggestions as $suggestion)-->
            <!--<x-watch-end-screen-suggestion :video="$suggestion"/>-->
            <!--@endforeach-->
        </div>
        <div
            class="absolute top-5 left-5 hidden sm:flex flex-row select-none cursor-pointer">
            <!--<a href="/channel/{{$creator->slug}}">-->
            <!--    <img src="{{$creator->avatar_url}}" class="w-8 h-8 rounded-full"/>-->
            <!--</a>-->
            <!--<p @click="" class="line-clamp-1 ml-2 my-auto text-white text dark:textDark text-lg uppercase font-bold">{{$video->title}}</p>-->
        </div>
        <div @click="resetPlayer();nextUpScreen = true;readyForNextVideo = false;"
             class="absolute bottom-5 left-5 flex flex-row select-none cursor-pointer">
            <!--<x-icon name="restart" class="  h-3 my-auto aspect-square text-white"/>-->
            <p class="ml-1 text-white text dark:textDark uppercase font-bold">Restart</p>
        </div>
    </div>

</template>


