<script setup>
import PlaylistLock from "@/Components/Cards/PlaylistCard/Partials/PlaylistLock.vue";

const name = "PlaylistCard";
const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    channel: {
        type: Boolean,
        required: false,
        default: false
    },
});
</script>

<template>
    <div class="relative group min-h-40 w-full">
        <div class="relative group overflow-hidden">
            <Link :href="route('playlist.show', {slug: item.slug})">
                <div
                    class="relative aspect-[21/12] overflow-hidden rounded-lg ring-1 ring-transparent transition-all duration-300 group-hover:ring-fuchsia-400/30 group-hover:shadow-[0_0_28px_-12px_rgba(232,121,249,0.22)]"
                >
                    <div class="h-full w-full  bg-vidgaze-blue-nav">
                        <img v-if="item.recent_video_image !== null" class="object-cover w-full h-full" v-bind:src="item.recent_video_image" />
                    </div>
                    <div class="absolute h-full w-full top-0 right-0 ">
                        <div class="relative h-full ml-auto  w-1/3  text-white font-semibold bg-black px-auto flex flex-col px-2 rounded-sm text-sm dark:text-zinc-200 opacity-80 justify-center">
                            <PlaylistLock :visibility="item.visibility"/>
                            <p class="text-center text-white font-bold mt-1" v-text="item.video_count"></p>
                        </div>
                    </div>
                </div>
            </Link>
            <div class="pl-0 py-2">
                <div class="flex flex-row">
                    <div class=" flex flex-col  overflow-hidden  ">
                        <Link :href="route('playlist.show', {slug: item.slug})">
                            <p
                                class="line-clamp-2 overflow-hidden bg-gradient-to-r from-cyan-600 to-fuchsia-600 bg-clip-text pr-2 text-base font-bold leading-4 text-transparent dark:from-cyan-400 dark:to-fuchsia-400"
                                v-text="item.name"
                            />
                        </Link>
                        <Link v-if="!channel" :href="route('channel', {channel: item.creator.slug})"
                           class="w-max pt-1 without-ring pointer-events-auto line-clamp-1 leading-4  font-normal text-xs   text-vidgaze-blue  dark:text-zinc-200 hover:dark:text-zinc-500">
                            <p v-text="item.creator.name"></p>

                        </Link>


                    </div>

                </div>
            </div>


        </div>


    </div>
</template>
