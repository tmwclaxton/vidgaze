<script setup>
import TickIcon from '#icons/tick.svg';
import ClockIcon from '#icons/clock.svg';
import ShareIcon from '#icons/share.svg';
import PlaylistIcon from '#icons/add2playlist.svg';
import ExitIcon from '#icons/exit.svg';
import Checkbox from "@/Components/Inputs/Checkbox.vue";
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import Option from "@/Components/Modals/Partials/Option.vue";
import { ref, onMounted } from 'vue';
import { vOnClickOutside } from '@vueuse/components';
import TextInput from "@/Components/Inputs/TextInput.vue";
import SelectInput from "@/Components/Inputs/SelectInput.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
const playlistModalStore = usePlaylistModalStore();
import {useToastStore} from "@/Stores/ToastStore";
import CreatePlaylistPartial from "@/Components/Modals/Partials/CreatePlaylistPartial.vue";
const toastStore =  useToastStore();

onMounted(() => {
    playlistModalStore.getMyPlaylists();
});

const name = "PlaylistModal";


const toggle = ((videos_present_in_playlist, playlist_slug) => {
    if (videos_present_in_playlist) {
        playlistModalStore.removeVideosFromPlaylist(playlist_slug)
    } else {
        playlistModalStore.addVideosToPlaylist(playlist_slug)
    }
});


const ignoreElRef = ref();
const onClickOutsideHandler = [
    (ev) => {
        // console.log(ev)
        close();
    },
    { ignore: [ignoreElRef] }
]

const close = () => {
    if (playlistModalStore.showMenu) {
        playlistModalStore.showMenu = false;
        usePlaylistModalStore().createPage  = false;
    }
}

const togglePlaylistCreate = () => {
    if (usePlaylistModalStore().videoIds > 0) {
        usePlaylistModalStore().createPage = !usePlaylistModalStore().createPage;
    } else {
        usePlaylistModalStore().showMenu = false;
    }
}

</script>


<template>
    <div v-if="playlistModalStore.showMenu"  class="pointer-events-none z-40 absolute left-1/2 right-1/2 flex-grow h-max w-max flex flex-row justify-center">
        <div class="pointer-events-none  fixed my-auto inset-y-0 h-max flex">
            <OptionHolder class="min-w-64 shadow-md h-max mx-auto pointer-events-auto" v-on-click-outside="onClickOutsideHandler" >
                <!--<div class="w-full flex flex-row p-4 select-none ">-->
                <div class="flex justify-between px-4 py-2  ">
                    <p class="text-lg my-auto font-semibold ">Save to...</p>
                    <ExitIcon class="w-6 aspect-square ml-auto my-auto cursor-pointer" @click="close"/>
                </div>
                <!--</div>-->
                <div v-if="!usePlaylistModalStore().createPage" class="h-52 overflow-y-auto ">
                    <hr class="border-1 border-zinc-300 dark:border-zinc-800 my-1">
                    <Option class="items-center w-full" v-for="playlist in playlistModalStore.playlists"  :key="playlist.id" @click="toggle(playlist.videos_present_in_playlist,playlist.slug)">
                        <Checkbox :checked="playlist.videos_present_in_playlist" class="my-auto" :id="'playlist_' + playlist.id" :name="'playlist_' + playlist.id" :value="playlist.id" />
                        <p v-text="playlist.name"/>
                        <span class="flex-grow"/>
                        <font-awesome-icon :icon="['fas', 'lock']" v-if="playlist.visibility === 'private'"/>
                        <font-awesome-icon :icon="['fas', 'earth-americas']" v-if="playlist.visibility === 'public'"/>
                        <font-awesome-icon :icon="['fas', 'link']" v-if="playlist.visibility === 'unlisted'"/>
                    </Option>
                </div>

                <hr class="border-1 border-zinc-300 dark:border-zinc-800 my-0.5 mt-1">

                <Option v-if="!usePlaylistModalStore().createPage" @click="usePlaylistModalStore().createPage = true">
                    <PlaylistIcon class="w-5 aspect-square " />
                    <p>Create new playlist</p>
                </Option>

                <create-playlist-partial v-if="usePlaylistModalStore().createPage" @backEvent="usePlaylistModalStore().createPage = false" @createEvent="togglePlaylistCreate" />

            </OptionHolder>

        </div>

    </div>
</template>

