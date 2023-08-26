<script setup>
import {onMounted, ref, watch} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import PlaylistLock from "@/Components/Cards/PlaylistCard/Partials/PlaylistLock.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import PlaylistVideo from "@/Pages/Viewer/Feed/Playlist/Partials/PlaylistVideo.vue";
import PLaylistName from "@/Pages/Viewer/Feed/Playlist/Partials/PLaylistName.vue";
import PLaylistVisibility from "@/Pages/Viewer/Feed/Playlist/Partials/PLaylistVisibility.vue";

const playlist = ref(null);
const videos = ref([]);

onMounted(async () => {
    {
        //grab playlist id from url /playlist/{id}
        let playlistId = window.location.pathname.split('/')[2];

        if (useAuthStore().user !== null) {
            [playlist.value, videos.value] = await usePlaylistModalStore().getPlaylist(playlistId,0,20);
        } else {
            watch(() => useAuthStore().user, async () => {
                [playlist.value, videos.value] = await usePlaylistModalStore().getPlaylist(playlistId,0,20);
            });
        }
    }
});

</script>
<template>
    <Head v-if="playlist != null" :title="playlist.name" />


            <div v-if="playlist != null" class="flex flex-col xs:flex-row max-h-[calc(100vh-4rem)] overflow-hidden">

                <!--playlist details-->
                <div  class="w-full xs:w-72 md:w-96 flex-shrink-0 pt-5 px-5">
                    <div class="relative rounded-lg overflow-hidden aspect-video bg-vidgaze-blue w-full cursor-pointer ">
                        <img v-if="playlist.recent_video_image !== null" class="object-cover w-full h-full " v-bind:src="playlist.recent_video_image" />
                        <div class="absolute   overflow-hidden w-full bottom-0 right-0  ">
                            <a href="/watch"
                               class="relative w-full text-white font-semibold bg-black opacity-80 px-auto flex flex-row  text-sm dark:text-zinc-200 justify-center">
                                <p class="text-center py-2 uppercase">Play all</p>
                            </a>
                        </div>
                    </div>

                    <PLaylistName :playlist="playlist"/>


                    <div class=" space-y-1  px-1">
                        <p class="inline-flex mr-2" v-text="playlist.video_count + ' · ' + 'Updated ' + playlist.updated_at + ' · ' "></p>
                        <PLaylistVisibility :playlist="playlist" class="inline-flex "/>
                    </div>


                    <div class="relative mt-3 mx-1 w-full flex flex-row space-x-4 ">
                        <div class="m-0 p-0 flex flex-col">
                            <font-awesome-icon :icon="['fas', 'shuffle']" class="w-4 mt-1"></font-awesome-icon>
                        </div>
                        <div class="m-0 p-0 flex flex-col">
                            <font-awesome-icon :icon="['fas', 'share']" class="w-4 mt-1"></font-awesome-icon>
                        </div>
                        <div class="m-0 p-0 flex flex-col">
                            <font-awesome-icon :icon="['fas', 'ellipsis-h']" class="w-4 mt-1"></font-awesome-icon>
                        </div>
                    </div>


                    <p class="text text-sm px-1 " v-text="playlist.description"/>
                    <row-divider class="mt-5 mb-5 rounded-2xl"></row-divider>

                    <div class="flex flex-row px-2 mb-5">
                        <a href="/channel/" class="flex flex-row">
                            <img class="bg-white h-10 aspect-square rounded-full "
                                 :src="playlist.creator.avatar_url"/>
                        </a>
                        <div class="flex flex-col ml-2">
                            <a href="/channel/" class="text text-sm font-semibold dark:text-zinc-200"
                               v-text="playlist.creator.name"></a>
                            <p class="text text-sm dark:text-zinc-200" v-text="playlist.creator.subscriber_count"></p>
                        </div>
                        <div v-if="useAuthStore().user === null" class="my-auto  ml-auto flex flex-row gap-x-2">
                            <SubscribeButton :channel="playlist.creator" class="my-auto"/>
                        </div>
                    </div>
                </div>

                <!--playlist videos-->
                <div class=" w-full xs:bg-zinc-200 dark:xs:bg-zinc-900 ">
                    <div class="h-[calc(100vh-4rem)] overflow-y-auto flex flex-col ">
                        <PlaylistVideo v-if="videos.length > 0"
                            v-for="(video,index) in videos" :key="video.id" :video="video" :index="index" class="w-full"/>
                    </div>

                </div>
            </div>
</template>
