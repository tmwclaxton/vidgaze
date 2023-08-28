<script setup>
const name = 'EndScreenSuggestion';
const props = defineProps({
    video: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="relative flex flex-col  group z-0 h-max">
        <Link :href="route('watch.show', {slug: video.slug})" class="flex flex-col group-hover:opacity-75">
            <div class="relative rounded overflow-hidden   aspect-video bg-vidgaze-blue  ">

                <img class="object-cover w-full h-full bg-zinc-900" :src="video.thumbnail_url" alt="thumbnail">


                <div class="linear-gradient h-full duration-300 transition opacity-0 group-hover:opacity-100 absolute top-0 left-0 w-full flex align-bottom flex-col leading-none pl-2 ">
                    <p style="" class="text text-white text-md font-bold leading-4 line-clamp-2 pr-2 break-words" v-text="video.title"></p>
                    <p class="text-sm text text-zinc-400 font-semibold w-max mt-1" v-text="video.creator.name"></p>
                </div>


            </div>

        </Link>
    </div>

</template>


