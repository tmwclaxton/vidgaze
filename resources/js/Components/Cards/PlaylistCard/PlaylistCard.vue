<script setup>
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
            <Link :href="route('playlist', {playlist: item.slug})">
                <div class="relative aspect-[21/12] overflow-hidden rounded-lg">
                    <div class="h-full w-full  bg-vidgaze-blue-nav">
                        <img v-if="item.recent_video_image !== null" class="object-cover w-full h-full" v-bind:src="item.recent_video_image" />
                    </div>
                    <div class="absolute h-full w-full top-0 right-0 ">
                        <div class="relative h-full ml-auto  w-1/3  text-white font-semibold bg-black px-auto flex flex-col px-2 rounded-sm text-sm dark:text-zinc-200 opacity-80 justify-center">
                            <font-awesome-icon :icon="['fas', 'lock']" v-if="item.visibility === 'private'"/>
                            <font-awesome-icon :icon="['fas', 'earth-americas']" v-if="item.visibility === 'public'"/>
                            <font-awesome-icon :icon="['fas', 'link']" v-if="item.visibility === 'unlisted'"/>
                            <p class="text-center text-white font-bold mt-1" v-text="item.video_count"></p>
                        </div>
                    </div>
                </div>
            </Link>
            <div class="pl-0 py-2">
                <div class="flex flex-row">
                    <div class=" flex flex-col  overflow-hidden  ">
                        <Link :href="route('playlist', {playlist: item.slug})">
                            <span
                                class="  line-clamp-2 overflow-hidden leading-4 font-bold  text-base text-zinc-900 dark:text-zinc-200 inline-flex">
                                <span class="pr-2" v-text="item.name"/>
                            </span>
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
