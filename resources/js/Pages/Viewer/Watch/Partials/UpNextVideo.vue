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
        <Link :href="route('watch.show', {slug: props.item.slug})">
            <div class="flex-shrink-0  relative rounded overflow-hidden h-24 aspect-video bg-vidgaze-blue  ">

                <!--thumbnail-->
                    <img class="object-cover w-full h-full bg-zinc-900" :src="props.item.thumbnail_url"
                         alt="thumbnail">

            </div>

            <div class="w-full ">
                    <div class="w-full flex align-bottom flex-col leading-none pl-2 ">
                        <!--video title-->
                        <p class="text text-white text-md font-bold leading-4 line-clamp-2 pr-2 break-words"
                           v-text="props.item.title"></p>
                        <!--creator name-->
                        <p class="text-sm text text-zinc-400 font-semibold w-max mt-1"
                           v-text="props.item.creator.name"></p>

                    </div>
            </div>
        </Link>
    </div>
</template>


