<script setup>
import RowDivider from "@/Components/General/RowDivider.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";

const name = "PlaylistVideo";

const props = defineProps({
    video: {
        type: Object,
        required: true
    },
    index: {
        type: Number,
        required: true
    },
    playlist: {
        type: Object,
        required: true
    },
    editable: {
        type: Boolean,
        default: true
    }
});

const emits = defineEmits(['deleteVideo']);

const deleteVideo = async () => {
    usePlaylistModalStore().videoIds = [props.video.id];
    await usePlaylistModalStore().removeVideosFromPlaylist(props.playlist.slug);
    usePlaylistModalStore().videoIds = [];
    emits('deleteVideo', props.video.id);
};

</script>
<template>
    <div class="relative w-full hover:bg-zinc-300 dark:hover:bg-zinc-800 ">
        <div class=" h-full flex flex-row px-2 cursor-pointer">
                <Link :href="route('watch.show', {slug: video.slug})" class="flex flex-row w-full py-5 pl-5">
                <div class="my-auto px-4 w-12 h-full font-semibold flex flex-col justify-center flex-grow ">
                    <p v-text="index + 1"></p>
                </div>
                <div class="pr-10 w-full my-auto ">
                    <div class="relative flex group z-0">
                        <div>
                            <div class="flex-shrink-0  relative rounded overflow-hidden aspect-video bg-vidgaze-blue  ">

                                <img class=" w-52  aspect-video flex-shrink-0 bg-zinc-900 " :src="video.thumbnail_url" alt=""/>

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
                </div>
                </Link>
                <div v-if="props.editable" class="my-auto px-4 w-12 h-full font-semibold flex flex-col justify-center flex-grow pr-5" @click="deleteVideo">
                    <font-awesome-icon :icon="['fas', 'trash']" />
                </div>
            </div>
    </div>
    <hr class="flex border-1 border-zinc-300 dark:border-zinc-800" />

</template>
