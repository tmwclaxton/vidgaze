<script setup>
import {computed, onMounted, onUnmounted, ref, watch} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import PlaylistLock from "@/Components/Cards/PlaylistCard/Partials/PlaylistLock.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import PlaylistVideo from "@/Pages/Viewer/Feed/Playlist/Partials/PlaylistVideo.vue";
import PLaylistName from "@/Pages/Viewer/Feed/Playlist/Partials/PLaylistName.vue";
import PLaylistVisibility from "@/Pages/Viewer/Feed/Playlist/Partials/PLaylistVisibility.vue";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import PlaylistPageModal from "@/Components/Modals/PlaylistPageModal.vue";
import { OnClickOutside } from '@vueuse/components'
const playlist = ref(null);
const videos = ref([]);
const page = ref(2);
const perPage = ref(20);
const showShare = ref(false);
import { vInfiniteScroll } from '@vueuse/components'; // don't remove this import
import {throttle, shuffle} from "lodash";
import {useQueueStore} from "@/Stores/QueueStore";
import {router} from "@inertiajs/vue3";
onMounted(async () => {
    {
        //grab playlist id from url /playlist/{id}
        let playlistId = window.location.pathname.split('/')[2];
        [playlist.value, videos.value] = await usePlaylistModalStore().getPlaylist(playlistId,1,perPage.value);
        watch(() => useAuthStore().user, async () => {
    [playlist.value, videos.value] = await usePlaylistModalStore().getPlaylist(playlistId,1,perPage.value);
            });

    }
});

// on scroll to bottom of the playlist_video_holder, load more videos
const exhausted = ref(false);
const loadMore = async () => {
    if (videos.value.length === 0 || exhausted.value) return;
    let newVideos = await usePlaylistModalStore().getPlaylist(playlist.value.slug, page.value, perPage.value);
    videos.value = videos.value.concat(newVideos[1]);
    if (newVideos[1].length === 0) exhausted.value = true;
    page.value++;
};

//throttle laod more function
const throttledLoadMore = throttle(loadMore, 1000);


const editable = computed(() => {
    if (!playlist.value) return true;
    if (!useAuthStore().user) return true;
    // if playlist isn't server made and the user is the owner of the playlistX
    return !playlist.value.server_made && playlist.value.creator.id !== useAuthStore().user.creator.id;
});

const share = () => {
    if (showShare.value) {
        showShare.value = false;
    } else {
        showShare.value = true;
        useShareModalStore().showMenu = true;
        useShareModalStore().getShareLinks(route('playlist.show', playlist.value.slug), "Check out this playlist on VidGaze: " + playlist.value.name);
    }
};


// add playlist to queue
const addPlaylistToQueue = async (shuffleItems = false, index = 0) => {
    useQueueStore().index = index;
    useQueueStore().playlist = playlist.value;
    useQueueStore().playlistLoading = true;
    useQueueStore().page = 1;
    useQueueStore().perPage = 20;
    //not needed because we are using the playlist store for the videos.vlaue anyway
    // grab first 20 videos in videos.value
    if (shuffleItems) {
        useQueueStore().items = shuffle(videos.value.slice(0, 20));
    } else {
        useQueueStore().items = videos.value.slice(0, 20);
    }
    useQueueStore().shuffle = shuffleItems;
    // redirect to watch page of first video in queue
    // router.push({name: 'watch', params: {video: useQueueStore().items[0].slug}});
    router.visit(route('watch.show', useQueueStore().items[index].slug));
};









const playlistPageModal = ref(false);
</script>
<template>
    <Head v-if="playlist != null" :title="playlist.name" />


            <div v-if="playlist != null" class="flex flex-col xs:flex-row max-h-[calc(100vh-4rem)] overflow-hidden">

                <!--playlist details-->
                <div  class="w-full xs:w-72 md:w-96 flex-shrink-0 pt-5 px-5">
                    <div @click="addPlaylistToQueue()"
                        class="relative rounded-lg overflow-hidden aspect-video bg-vidgaze-blue w-full cursor-pointer ">
                        <img v-if="playlist.recent_video_image !== null" class="object-cover w-full h-full " v-bind:src="playlist.recent_video_image" />
                        <div class="absolute   overflow-hidden w-full bottom-0 right-0  ">
                            <div class="relative w-full text-white font-semibold bg-black opacity-80 px-auto flex flex-row  text-sm dark:text-zinc-200 justify-center">
                                <p class="text-center py-2 uppercase">Play all</p>
                            </div>
                        </div>
                    </div>

                    <PLaylistName :playlist="playlist" :editable="editable"/>


                    <div class=" space-y-1  px-1">
                        <p class="inline-flex mr-2" v-text="playlist.video_count + ' · ' + 'Updated ' + playlist.updated_at + ' · ' "></p>
                        <PLaylistVisibility :playlist="playlist" :editable="editable"/>
                    </div>

                    <OnClickOutside @trigger="playlistPageModal = false">
                        <div class="relative mt-3 mx-1 w-full flex flex-row space-x-4 " >
                            <div class="m-0 p-0 flex flex-col" @click="addPlaylistToQueue(true)">
                                <font-awesome-icon :icon="['fas', 'shuffle']" class="w-4 mt-1 cursor-pointer"></font-awesome-icon>
                            </div>
                            <div class="m-0 p-0 flex flex-col" @click="share">
                                <font-awesome-icon :icon="['fas', 'share']" class="w-4 mt-1 cursor-pointer"></font-awesome-icon>
                            </div>
                            <div class="m-0 p-0 flex flex-col"  v-if="editable" >
                                <font-awesome-icon :icon="['fas', 'ellipsis-h']" class="w-4 mt-1 cursor-pointer"
                                                   @click="playlistPageModal = !playlistPageModal"/>
                                <PlaylistPageModal v-if="playlistPageModal" :playlist="playlist"/>
                            </div>
                        </div>
                    </OnClickOutside>


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
                <div id="playlist_video_holder" class=" w-full xs:bg-zinc-200 dark:xs:bg-zinc-900 ">
                    <div  v-infinite-scroll="throttledLoadMore" class="h-[calc(100vh-4rem)] overflow-y-auto flex flex-col pb-36">

                        <PlaylistVideo v-if="videos.length > 0"
                                       @addPlaylistToQueue="addPlaylistToQueue(false, index)"
                                       @deleteVideo="videos.splice(index, 1)" :editable="editable"
                            v-for="(video,index) in videos" :key="video.id" :video="video" :index="index" :playlist="playlist"/>
                    </div>

                </div>
            </div>
</template>
