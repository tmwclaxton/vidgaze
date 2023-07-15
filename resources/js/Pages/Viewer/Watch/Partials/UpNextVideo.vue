<script setup>
const name = "UpNextVideo";
const props = defineProps({
    item: {
        type: Object,
        required: true
    },
});
</script>


<template>
    <div class="relative flex   group z-0">
        <div class="flex-shrink-0  relative rounded overflow-hidden h-24 aspect-video bg-vidgaze-blue  ">

            <!--thumbnail-->
            <a :href="route('watch', [item.slug])">
                <img class="object-cover w-full h-full bg-zinc-900" :src="item.thumbnail_url"
                     alt="thumbnail">
            </a>

            <!--<x-duration :video="$video" minimised="true"/>-->


        </div>

        <div class="w-full ">
            <a :href="route('watch', [item.slug])">
                <div class="w-full flex align-bottom flex-col leading-none pl-2 ">
                    <!--video title-->
                    <p class="text text-white text-md font-bold leading-4 line-clamp-2 pr-2 break-words"
                       v-text="item.title"></p>
                    <!--creator name-->
                    <a href="" class="text-sm text text-zinc-400 font-semibold w-max mt-1"
                       v-text="item.creator.name"></a>


                </div>
            </a>
        </div>

    </div>

</template>


