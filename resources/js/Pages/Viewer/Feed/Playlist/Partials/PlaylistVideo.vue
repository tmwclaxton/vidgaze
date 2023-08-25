<script setup>
const name = "PlaylistVideo";

const props = defineProps({
    video: {
        type: Object,
        required: true
    },
    playlist: {
        type: Object,
        required: false
    }
});

</script>
<template>
    <div class="relative flex   group z-0">
        <div>
            <div class="flex-shrink-0  relative rounded overflow-hidden  @if($playlist) h-24 @else h-16 @endif aspect-video bg-vidgaze-blue  ">

                <img class="object-cover w-full h-full bg-zinc-900" :src="video.thumbnail_url" alt=""/>

                <div class="absolute rounded overflow-hidden w-min bottom-0 right-0 m-1">
                    <div class="relative w-full text-white font-semibold bg-black
                      px-auto flex flex-row   rounded-sm text-sm dark:text-zinc-200 justify-center">
                        <p class="text-center p-0.5 text-xs" v-text="video.duration"></p>
                    </div>
                </div>

            </div>
        </div>

        <div class="w-full ">
            <div class="w-full flex align-bottom flex-col leading-none pl-2 ">
                <p style="" class="text dark:textDark text-md font-bold leading-4 line-clamp-2 pr-2 mt-1" v-text="video.title"/>
                <div  class="text-xs text dark:textDark w-max my-1" v-text="video.creator.name"/>
            </div>
        </div>

    </div>

</template>
