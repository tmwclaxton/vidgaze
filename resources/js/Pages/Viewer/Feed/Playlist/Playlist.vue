<script setup>
import {onMounted, ref, watch} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import PlaylistLock from "@/Components/Cards/PlaylistCard/Partials/PlaylistLock.vue";

const playlist = ref([]);
const videos = ref([]);

onMounted(async () => {
    {
        //grab playlist id from url /playlist/{id}
        let playlistId = window.location.pathname.split('/')[2];

        if (useAuthStore().user !== null) {
            [playlist.value, videos.value] = await usePlaylistModalStore().getPlaylist(playlistId,0,6);
        } else {
            watch(() => useAuthStore().user, async () => {
                [playlist.value, videos.value] = await usePlaylistModalStore().getPlaylist(playlistId,0,6);
            });
        }
    }
});

</script>
<template>
    <Head title="" />
    <div class=" mx-auto ">



            <div class="flex flex-col xs:flex-row max-h-[calc(100vh-4rem)] overflow-hidden">

                <!--playlist details-->
                <div class="xs:bg-zinc-200 dark:xs:bg-zinc-900 w-full xs:w-72 md:w-96 flex-shrink-0 pt-5 px-5">
                    <div class="relative rounded-lg overflow-hidden aspect-video bg-vidgaze-blue w-full cursor-pointer ">
                        <img v-if="playlist.recent_video_image !== null" class="object-cover w-full h-full " v-bind:src="playlist.recent_video_image" />
                        <div class="absolute rounded overflow-hidden w-full bottom-0 right-0  ">
                            <a href="/watch"
                               class="relative w-full text-white font-semibold bg-black opacity-80 px-auto flex flex-row   rounded-sm text-sm dark:text-zinc-200 justify-center">
                                <p class="text-center py-2 uppercase">Play all</p>
                            </a>
                        </div>
                    </div>
                    <div class="flex flex-row p-1 -mb-2">
                        <p class="text dark:textDark text-xl font-semibold" v-text="playlist.name"></p>


                    </div>


                    <div class=" space-y-1 text dark:textDark px-1">
                        <p class="inline-flex mr-2" v-text="playlist.video_count + ' · ' + 'Updated ' + playlist.updated_at + ' · ' "></p>
                        <PlaylistLock :visibility="playlist.visibility" class="inline-flex w-3 mx-1"/>
                        <div class="inline-flex relative ml-1.5">
                            <div class="inline-flex relative cursor-pointer w-full">
                                <span class="capitalize select-none" v-text="playlist.visibility"></span>
                            </div>
                        </div>
                    </div>


                    <div class="relative mt-3 mx-1 w-full flex flex-row space-x-4 text dark:textDark">
                        <a href="/watch/" class="m-0 p-0 flex flex-col">
                            <!--<x-icon name="shuffle" class=" w-4 mt-1" />-->
                        </a>
                    </div>


                    <p class="text text-sm px-1 " v-text="playlist.description"/>
                    <x-hr class="mt-5 mb-5"/>
                    <div class="flex flex-row px-2 mb-5">
                        <a href="/channel/" class="flex flex-row">
                            <img class="bg-white h-10 aspect-square rounded-full "
                                 src="">
                            <div class=" flex flex-col my-auto px-5 overflow-hidden">
                                <span class="text dark:textDark text-lg font-bold  break-words"></span>
                            </div>
                        </a>
                        <div class="my-auto  ml-auto flex flex-row gap-x-2">
                        <!--    subscribe button-->
                        </div>
                    </div>
                </div>

                <!--playlist videos-->
                <div class=" w-full min-h-screen h-full">

                </div>
            </div>

        </div>
</template>
